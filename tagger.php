<?php
require_once("config.php");
require_once("nmea.php");
require_once("exif.php");


if($argc < 3)
{
	echo "Usage: tagger.php nmea_gps_log file1 [... fileN]\n";
	echo "where files can also be shell patterns (e.g. *.jpg)\n";
	exit(1);
}

function match_timestamps($nmea, $pt)
{
	$nr_files = count($pt);
	$i = 0;
	foreach($pt as $p)
	{
		$i++;
		$dt = $min = MATCH_TOLERANCE * 1000; // current delta t(ime) and the min one found
		echo "$i/$nr_files\r";
		foreach($nmea->entries as $entry)
		{
			$dt = abs($entry->timestamp - $p->timestamp);
			if($dt < $min)
			{
				$min = $dt;
				$entry_ref = $entry;
			}
		}
		if(TAGGER_DEBUG) echo "\n[debug] found min time delta ".$min."s at gps time: ".$entry_ref->utc_time."; photo time: ".$p->time_str()."\n";
		if($min > MATCH_TOLERANCE)
		{
			echo "\n[warn ] photo '$p->fname' - match tolerance exceeded (got $min s, expected ".MATCH_TOLERANCE." s; photo time: ".$p->time_str().")\n";
			continue;
		}
		$lat = $entry_ref->calc_lat();
		$long = $entry_ref->calc_long();
		set_photo_gps_info($p->fname, $lat, $entry_ref->lat_ref, $long, $entry_ref->long_ref, $entry_ref->timestamp);
	}
	echo "\n";
}


array_shift($argv);
$log = array_shift($argv);
echo "Processing $log\n";
$nmea = new nmea_log($log);
echo "Done\n";
echo "Loading photo time information\n";
$pt = get_photo_times($argv);
echo "Done\n";
echo "Matching timestamps\n";
match_timestamps($nmea, $pt);
echo "Done\n";
?>
