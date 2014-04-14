#!/bin/bash

# Script to load GCL tables into the database for plotip
# See http://geolite.maxmind.com/download/geoip/database/GeoLiteCity_CSV/
# You need to set up the user and database, and this will make the
# tables and load in the data.

glc_file="`echo GeoLiteCity_*.tar.xz`"

if [ ! -e "$glc_file" ] ; then
    echo "No GLC file found in current directory."
    echo "You need to download the latest GeoLiteCity database in"
    echo "CSV/XZ format from http://dev.maxmind.com/geoip/geolite"
    exit 1
fi

if ! which lzma >/dev/null 2>&1 ; then
    echo "WARNING:"
    echo "This script will try to unpack $glc_file using the"
    echo "tar command with the --lzma option, but lzma was not found"
    echo "on your system, so this may not work."
fi


# Setup mysql, directly from config.class.php
# This assumes you have the PHP command line.
eval $( php -r "include(\"config.class.php\"); print \"db='\$db' dbuser='\$user' dbpass='\$pass'\n\";" )

# Otherwise this is a crude way to get the same effect.
#eval $( sed -e 's/[[:space:]]\+//g' -e 's/^[#/].*\|.*[<>?]\{2\}.*\|^\$\|;$//g' config.class.php )

# Or you could do it here manually:
#db=plotip
#dbuser=jimbob
#dbpass=plot

mysql="mysql -u $dbuser --password=$dbpass --local-infile $db"
echo "MySQL command is $mysql"

#Test mySQL
if ! $mysql -e "show databases;" | grep -q "$db" ; then
    echo "Database connection problem"
    exit 1
fi

# Table schema modified 4/13/2014 by Nathan Adams <adamsna@datanethost.net>
# Making city column bigger and allowing nulls in postalcode, areacode, metrocode
#Sort tables
echo "Purging and re-adding tables"
$mysql <<"__END"
SET character_set_client = utf8;

DROP TABLE IF EXISTS `blocks`;
CREATE TABLE `blocks` (
	`startIpNum` INT(15) UNSIGNED NOT NULL,
	`endIpNum` INT(10) UNSIGNED NOT NULL,
	`locId` INT(15) UNSIGNED NOT NULL
)
COLLATE='latin1_swedish_ci'
ENGINE=MyISAM;

DROP TABLE IF EXISTS `location`;
CREATE TABLE `location` (
	`locId` INT(15) UNSIGNED NOT NULL,
	`country` VARCHAR(2) NOT NULL,
	`region` VARCHAR(2) NOT NULL,
	`city` VARCHAR(70) NOT NULL,
	`postalCode` VARCHAR(50) NULL DEFAULT NULL,
	`latitude` VARCHAR(10) NOT NULL,
	`longitude` VARCHAR(10) NOT NULL,
	`metroCode` INT(5) UNSIGNED NULL DEFAULT NULL,
	`areaCode` INT(5) UNSIGNED NULL DEFAULT NULL
)
COLLATE='latin1_swedish_ci'
ENGINE=MyISAM;
__END

#The files are apparently encoded in LATIN1 or something very similar.
load_foo='CHARACTER SET "LATIN1"'
load_foo="$load_foo FIELDS TERMINATED BY \",\" OPTIONALLY ENCLOSED BY '\"'"
load_foo="$load_foo IGNORE 2 LINES"

tar --wildcards --lzma -xOvf "$glc_file" "**/GeoLiteCity-Blocks.csv" | \
    $mysql -e 'LOAD DATA LOCAL INFILE "/dev/stdin" INTO TABLE `blocks` '" $load_foo;"

# Modifying SQL to insert NULL if the field is blank - 4/13/2014 Nathan Adams <adamsna@datanethost.net>
tar --wildcards --lzma -xOvf "$glc_file" "**/GeoLiteCity-Location.csv" | \
    $mysql -e 'LOAD DATA LOCAL INFILE "/dev/stdin" INTO TABLE `location` '" $load_foo (locId, country, region, city, @postalCode, latitude, longitude, @metroCode, @areaCode) set postalCode = nullif(@postalCode, ''), metroCode = nullif(@metroCode, ''), areaCode = nullif(@areaCode, '');"
