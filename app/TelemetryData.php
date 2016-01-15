<?php

class TelemetryData extends Moloquent
{
    protected $fillable = [
        'session_id',
        'session_batch',
        'res_changed',
        'g_debug',
        'console',
        'pos_x',
        'pos_y',
        'pos_z',
        'ft',
        'created_at',
        'updated_at'
    ];

    protected $table = 'telemetry_data';

    public function sessions()
    {
        return $this->belongsTo('TelemetrySession');
    }
}