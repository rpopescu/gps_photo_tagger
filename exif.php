<?php

class photo_time
{
	public $tz;
	public $date;

	// derived values
	public $timestamp; // UTC timestamp
	
	function __construct($tz, $date)
	{
		$this->tz = $tz;
		$this->date = $date;
		$this->make_timestamp();
	}

	function make_timestamp()
	{
		$str = "$this->date GMT";
		$str .= ($this->tz > 0 ? "+" : "-");
		$str .= $this->tz;
		$this->timestamp = strtotime($str);
		$offset_sec = $this->tz * 60 * 60;
		$this->timestamp += ($this->tz > 0 ? -$offset_sec : $offset_sec);
	}
	
	function time_str()
	{
		return "$this->timestamp - ".date("H:i:s", $this->timestamp);
	}
}

/// Returns an array of photo_time, given an array of file names.
function get_photo_times($fnames_array, $debug_cmd = FALSE)
{
	$cmd = "bash -c 'exiftool -q -fast --TAG \"-EXIF:CreateDate\" --TAG \"-EXIF:TimeZoneOffset\" -d \"%Y-%m-%d %H:%M:%S\" -printFormat \"\\\$TimeZoneOffset, \\\$CreateDate\"";
	foreach($fnames_array as $fname) $cmd .= " \"$fname\"";
	$cmd .= "'";
	if($debug_cmd) echo "$cmd\n";
	$resp = `$cmd`;
	if($resp == "")
	{
		echo "Unable to extract EXIF time information\n";
		return FALSE;
	}
	$lines = explode("\n", $resp);
	$info = array();
	foreach($lines as $l)
	{
		$l = trim($l);
		if($l == "") break;
		$atoms = explode(",", $l);
		$info[] = $foo = new photo_time($atoms[0], trim($atoms[1]));
		echo $foo->time_str()."\n";
	}
	return $info;
}

function usage($argv)
{
	array_shift($argv);
	if($pt = get_photo_times($argv))
	{
		var_dump($pt);
	}
}

usage($argv);

?>
