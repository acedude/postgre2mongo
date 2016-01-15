<?php

class SubnauticaFeedbackTicket extends Moloquent {
	protected $fillable = [
        'unique_id', 'ip', 'cpu', 'gpu', 'ram', 'os', 'position_x', 'position_y', 'position_z', 'orientation_w',
        'orientation_x', 'orientation_z', 'mean_frame_time_30', 'emotion', 'text', 'screenshot', 'csid', 'email'
    ];

    protected $hidden = ['email'];

    public function categories()
    {
        return $this->embedsMany('SubnauticaFeedbackCategory');//, 'subnautica_feedback_p')->select(['name']);
    }
}