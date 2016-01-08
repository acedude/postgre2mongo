<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ControlApiController extends BaseApiController {
	public function __construct() {
		parent::__construct( 200 );
	}

	public function toggle() {
		$status = Cache::get( 'isProcessing', false );

		if ( $status ) {
			Cache::forever( 'isProcessing', false );
			Cache::forever( 'timeStarted', false );
			Cache::forever( 'timeFinished', false );

			return $this->respond( false );
		} else {
			Cache::forever( 'isProcessing', true );
			Cache::forever( 'rowsProcessedThisRun', (int) 0 );
			Cache::forever( 'timeStarted', Carbon::now() );
			Cache::forever( 'timeFinished', false );

			return $this->respond( true );
		}
	}
} 