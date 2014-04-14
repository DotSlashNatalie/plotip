<?php 
//You can test this on the command line on most Linux systems like so:
// alias php-cgi="php -r '"'parse_str(implode("&", array_slice($argv, 2)), $_GET); include($argv[1]);'"' --"
// php-cgi request.php ip=8.8.8.8
include("config.class.php");
$ip = sprintf("%u", ip2long(trim($_GET['ip'])));
$sql1 = "SELECT `locId` FROM `blocks` WHERE ( $ip BETWEEN `startIpNum` AND `endIpNum` ) ;";
mysql_connect($host,$user,$pass);
mysql_select_db($db);
#We work in utf8 round here
header("Content-Type: text/plain; charset=utf-8");
mysql_query("SET character set 'utf8';");
#die($sql1);
$res = mysql_query($sql1) or die(mysql_error());
$arr = mysql_fetch_array($res);
$locid = $arr[0];
if($locid){
    $sel = "`latitude` , `longitude`, `city`";
    $sql2 = "SELECT $sel FROM `location` WHERE `locId` =$locid;";
    $res = mysql_query($sql2) or die(mysql_error());
    $arr = mysql_fetch_array($res);
    print "$arr[0]:$arr[1]:$arr[2]";
} else {
    print "unknown";
}
?>
