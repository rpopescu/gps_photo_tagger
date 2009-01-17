<?php
/*
from : http://gpsd.berlios.de/NMEA.txt

RMC - Recommended Minimum Navigation Information
                                                            12
        1         2 3       4 5        6  7   8   9    10 11|  13
        |         | |       | |        |  |   |   |    |  | |   |
 $--RMC,hhmmss.ss,A,llll.ll,a,yyyyy.yy,a,x.x,x.x,xxxx,x.x,a,m,*hh<CR><LF>
                1 2         3 4          5 6    7      8      9 10 11 12 13
$GPRMC,080136.482,A,5222.2484,N,00454.5497,E,0.57,106.95,140109, ,  ,  A*6F

 Field Number: 
  1) UTC Time
  2) Status, V=Navigation receiver warning A=Valid
  3) Latitude
  4) N or S
  5) Longitude
  6) E or W
  7) Speed over ground, knots
  8) Track made good, degrees true
  9) Date, ddmmyy
 10) Magnetic Variation, degrees
 11) E or W
 12) FAA mode indicator (NMEA 2.3 and later)
 13) Checksum

A status of V means the GPS has a valid fix that is below an internal
quality threshold, e.g. because the dilution of precision is too high 
or an elevation mask test failed.
*/
class rmc
{
	public $utc_time;
	public $status;
	public $lat;
	public $lat_ns;
	public $long;
	public $long_ew;
	public $speed_knots;
	public $track_made_good;
	public $date;
	public $mag_var;
	public $mag_var_ew;
	public $faa_mode;
	public $checksum;

	// derived values 
	public $timestamp;

	function __construct($entries)
	{
		$this->utc_time = $entries[1];
		$this->status = $entries[2];
		$this->lat = $entries[3];
		$this->lat_ns = $entries[4];
		$this->long = $entries[5];
		$this->long_ew = $entries[6];
		$this->speed_knots = $entries[7];
		$this->track_made_good = $entries[8];
		$this->date = $entries[9];
		$this->mag_var = $entries[10];
		$this->mag_var_ew = $entries[11];
		$this->faa_mode = substr($entries[12], 0, 1);
		$this->checksum = substr($entries[12], 2, 2);
		$this->make_timestamp();
	}

	function make_timestamp()
	{
		$yy = substr($this->date, 4, 2); $yy += 2000;
		$date = "$yy-".substr($this->date, 2, 2)."-".substr($this->date, 0, 2);
		$time = substr($this->utc_time, 0, 2).":".substr($this->utc_time, 2, 2).":".substr($this->utc_time, 4, 2);
		$this->timestamp = strtotime("$date $time UTC");
	}

	function time_str()
	{
		return "$this->timestamp - ".date("H:i:s", $this->timestamp);
	}
}

class nmea_log
{
	public $filename;
	public $entries;

	function __construct($filename)
	{
		$this->filename = $filename;
		$f = fopen($this->filename, "r");
		if(!$f)
		{
			echo "file not found\n";
			return;
		}
		echo "reading ".$this->filename."\n";
		while(!feof($f))
		{
			$atoms = fgetcsv($f, 1024, ",");
			if($atoms[0] == "\$GPRMC")
			{
				$this->entries[] = $foo = new rmc($atoms);
				echo $foo->time_str()."\n";
			}
		}
		fclose($f);
	}

	function dump()
	{
		print_r($this->entries);
	}
}

$l = new nmea_log("data/GPS_20090114_080103.log");
$l->dump();
?>
