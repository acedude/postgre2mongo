<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatusApiController extends BaseApiController {
	public function __construct() {
		parent::__construct( 200 );
	}

	public function index() {
		$connections = [
			'pgsql'   => DB::connection( 'pgsql' ) instanceof \Illuminate\Database\PostgresConnection,
			'cache'   => Cache::put( 'test', true, 15 ) == null && Cache::get( 'test' ) == true,
			'mongodb' => DB::connection( 'mongodb' ) instanceof \Jenssegers\Mongodb\Connection,
		];

		$tables = Cache::get( 'tables', [ 'pgsql' => [ ], 'mongodb' => [ ] ] );
		$rows   = Cache::get( 'rows', [ 'pgsql' => [ ], 'mongodb' => [ ] ] );

		$info['isProcessing'] = Cache::get( 'isProcessing', false );
		$timeStarted          = Cache::get( 'timeStarted', false );
		$info['timeStarted']  = $timeStarted ? $timeStarted->toDateTimeString() : false;
		$info['timeFinished'] = Cache::get( 'timeFinished', false ) ? Cache::get( 'timeFinished', false )->toDateTimeString() : false;
		if ( $info['timeFinished'] ) {
			$info['timeElapsed'] = Cache::get( 'timeFinished' )->diffForHumans( $timeStarted, true );
		} else {
			$info['timeElapsed'] = $timeStarted ? $timeStarted->diffForHumans( null, true ) : false;
		}

		if ( $info['isProcessing'] ) {
			$rowsPerSecond         = Cache::get( 'rowsProcessedThisRun', 0 ) / $timeStarted->diffInSeconds();
			$rowsRemaining         = ( array_sum( $rows['pgsql'] ) - array_sum( $rows['mongodb'] ) );
			$secondsLeft           = $rowsRemaining / ( ( $rowsPerSecond == 0 ) ? 1 : $rowsPerSecond );
			$info['timeRemaining'] = ( $secondsLeft == 0 OR $rowsPerSecond == 0 ) ? false : Carbon::now()->addSeconds( $secondsLeft )->diffForHumans();
			$info['rowsPerSecond'] = number_format( round( $rowsPerSecond ) );
		}

		$info['completionPercent'] = ( array_sum( $rows['pgsql'] ) > 0 ) ? round( array_sum( $rows['mongodb'] ) / array_sum( $rows['pgsql'] ) * 100, 3 ) : 0;

		return $this->respond( compact( 'connections', 'tables', 'rows', 'info' ) );
	}
} 