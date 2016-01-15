<?php

class TelemetrySession extends Moloquent
{
    protected $fillable = [
        'entity_id',
        'gpu_name',
        'gpu_mem',
        'cpu_logical_cores',
        'cpu_name',
        'sys_mem',
        'sys_os',
        'cs_id',
        'quality',
        'res_x',
        'res_y',
        'has_end',
        'is_dev',
        'is_pirate',
        'updated_at'
    ];
}