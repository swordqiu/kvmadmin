<?php
/*****************************************************************************
 *
 *  This file is part of kvmadmin, a php-based KVM virtual machine management
 *  platform.
 *
 *  kvmadmin is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License (LGPL)
 *  as published by the Free Software Foundation, either version 3 of 
 *  the License, or (at your option) any later version.
 *
 *  kvmadmin is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with kvmadmin.  If not, see <http://www.gnu.org/licenses/>.
 *  @license GNU Lesser General Public License
 *
 *  CopyRight 2010-2012 QIU Jian (sordqiu@gmail.com)
 *
 ****************************************************************************/
?>
<?php

#$conn = ssh2_connect('192.168.0.10', 22, array('hostkey'=>'ssh-rsa'));
#if (ssh2_auth_pubkey_file($conn, 'vm', dirname(__FILE__)."/etc/id_rsa.pub", dirname(__FILE__)."/etc/id_rsa")) {
	
#}else {
#	echo "auth failed!";
#}

#$str = file_get_contents("test.output");
#$lines = explode("\n", $str);
#var_dump(preg_split("/\s+/", trim($lines[1])));
#var_dump(preg_split("/\s+/", trim($lines[2])));

include_once("config.inc.php");
define("LIBUI_DB_QUERY_SCRIPT", "");
define("LIBUI_REQUEST_HANDLER_SCRIPT", "");
define("LIBUI_RPC_SCRIPT", "");
include_once(JIALIB_PATH."/jialib.inc");
include_once("include/host.class.inc");
include_once("include/network.class.inc");
include_once("include/template.class.inc");
include_once("include/guest.class.inc");
include_once("include/portmap.class.inc");

$db = mysql_open();
$guest = new VMGuest(30);
$guest->initByDB($db);
#echo $guest->createDiskOfSize(8, $db);
#echo $guest->createNIC(1, $db);
#$guest->initByDB($db);
$host = $guest->getHost();
#echo $host->makeVM($guest);
#echo $host->destroyVM($guest);
#echo $guest->remove($db);
echo $host->startVM($guest, $db);
echo $host->stopVM($guest, $db);
#$ret = VMPortmap::syncConfig($db);
#var_dump($ret);

?>
