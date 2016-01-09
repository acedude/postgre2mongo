<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Process extends Command {
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'postgre2mongo:process';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Run a single iteration';

	/**
	 * Create a new command instance.
	 *
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle() {
		if ( Cache::get( 'isWorkerStarted', false ) ) {
			return;
		}

		if ( ! Cache::get( 'isProcessing', false ) ) {
			return;
		}

		Cache::forever( 'isWorkerStarted', true );
		set_time_limit( 0 );
		$skipTables = explode( ',', env( 'SKIP_TABLES', '' ) );

		if ( Cache::has( 'tables' ) ) {
			$tables = Cache::get( 'tables' );
		} else {
			$tables      = [ 'pgsql' => [ ], 'mongodb' => [ ] ];
			$pgsqlTables = DB::connection( 'pgsql' )->getDoctrineSchemaManager()->listTableNames();
			foreach ( $pgsqlTables as $table ) {
				$tables['pgsql'][] = str_replace( '"', '', $table );
			}

			$tables['mongodb'] = DB::connection( 'mongodb' )->getMongoDB()->getCollectionNames();
			Cache::put( 'tables', $tables, 15 );
		}

		if ( Cache::has( 'rows' ) ) {
			$rows = Cache::get( 'rows' );
		} else {
			$rows = [ 'pgsql' => [ ], 'mongodb' => [ ] ];
			foreach ( $tables['pgsql'] as $table ) {
				if ( in_array( $table, $skipTables ) ) {
					$rows['pgsql'][ $table ] = 0;
					continue;
				}

				$rows['pgsql'][ $table ] = DB::connection( 'pgsql' )->table( $table )->count();
			}

			foreach ( $tables['mongodb'] as $table ) {
				$rows['mongodb'][ $table ] = DB::connection( 'mongodb' )->table( $table )->raw( function ( $collection ) {
					return $collection->count();
				} );
			}

			Cache::put( 'rows', $rows, 15 );
		}

		$runStartedAt = time();
		$wasWorking   = false;

		while ( $runStartedAt + 60 * 10 >= time() ) {
			foreach ( $tables['pgsql'] as $table ) {
				if ( ! Schema::connection( 'mongodb' )->hasCollection( $table ) ) {
					Schema::connection( 'mongodb' )->create( $table );
					$tables['mongodb'][]       = $table;
					$rows['mongodb'][ $table ] = 0;
					Cache::put( 'tables', $tables, 15 );
					Cache::put( 'rows', $rows, 15 );
				}

				if ( in_array( $table, $skipTables ) ) {
					continue;
				}

				if ( $rows['pgsql'][ $table ] > $rows['mongodb'][ $table ] ) {
					$batchSize = 10000;
					DB::connection( 'mongodb' )->disableQueryLog();
					DB::connection( 'pgsql' )->disableQueryLog();

					$data = DB::connection( 'pgsql' )->table( $table )->skip( $rows['mongodb'][ $table ] )->take( $batchSize )->get();

					DB::connection( 'mongodb' )->table( $table )->insert( json_decode( json_encode( $data ), true ) );

					$rows['mongodb'][ $table ] += $batchSize;
					Cache::put( 'rows', $rows, 15 );
					Cache::increment( 'rowsProcessedThisRun', $batchSize );

					$wasWorking = true;
					break;
				}
			}
		}

		if ( $wasWorking == false ) {
			Cache::forever( 'timeFinished', Carbon::now() );
			Cache::forever( 'isProcessing', false );
		}

		Cache::forever( 'isWorkerStarted', false );
	}
}
