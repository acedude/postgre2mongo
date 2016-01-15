<?php

class Checkin extends Moloquent {
	protected $fillable = ['csid', 'developer_id', 'description', 'files_added', 'files_deleted', 'files_modified', 'branch'];

    public function developer() {
        return $this->belongsTo('Developer');
    }
}