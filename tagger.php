<?php
require_once("nmea.php");
require_once("exif.php");

if($argc < 3)
{
	echo "Usage: tagger.php nmea_gps_log file1 [... fileN]\n";
	echo "where files can also be shell patterns (e.g. *.jpg)"
}

array_shift($argv);
?>
