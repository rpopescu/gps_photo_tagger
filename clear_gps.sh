#!/bin/bash
exiftool -overwrite_original -GPSAltitude= -GPSAltitudeRef= -GPSDateStamp= -GPSDateTime= -GPSLatitude= -GPSLatitudeRef= -GPSLongitude= -GPSLongitudeRef= -GPSTimeStamp= -GPSVersionID= $*
