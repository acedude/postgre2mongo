<?php

class CodeDiff extends Moloquent {
	protected $fillable = ['csid', 'file_name', 'lines_modified', 'lines_added', 'lines_deleted', 'diff'];
}