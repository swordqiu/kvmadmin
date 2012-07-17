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

include_once("config.inc.php");
include_once(JIALIB_PATH."/jialib.inc");
include_once("include/host.class.inc");
include_once("include/network.class.inc");
include_once("include/template.class.inc");
include_once("include/guest.class.inc");
include_once("include/portmap.class.inc");

$handler = new RequestHandler();

function GET_VM_STATE(&$req) {
	$db = mysql_open();
	$guest = new VMGuest($req->_id);
	$guest->initByDB($db);
	$output = array();
	$host = $guest->getHost();
	if ($host->isVMRunning($guest)) {
		$output['_state'] = 1;
	}else {
		$output['_state'] = 0;
	}
	$output['_fn'] = $req->_fn;
	$req->responseJSON(json_encode($output));
	return TRUE;
}
$handler->register('GET_VM_STATE', 'GET_VM_STATE');

function GET_ISO_LIST(&$req) {
	$db = mysql_open();
	$host = new VMHost($req->_host_id);
	$host->initByDB($db);
	$iso_list = $host->getISOList();
	$output = array('_iso_list' => $iso_list, '_fn'=>$req->_fn);
	$req->responseJSON(json_encode($output));
	return TRUE;
}
$handler->register('GET_ISO_LIST', 'GET_ISO_LIST');

function openVNCConsole(&$req) {
	if (isset($req->_id)) {
		$db = mysql_open();
		$guest = new VMGuest($req->_id);
		$guest->initByDB($db);
		$host = $guest->getHost();
		$vncport = $guest->getVNCPort();
		if ($vncport >= 5900) {
			$cmd = VNC_PROXY_COMMAND." {$host->getIPAddress()} {$guest->getVNCPort()} > /dev/null 2>&1";
			system($cmd, $ret);
			$output = array('_port' => $vncport);
			$req->responseJSON(json_encode($output));
			return TRUE;
		}else {
			$req->error("Guest has no valid VNC port: {$vncport}");
			return FALSE;
		}
	}else {
		$req->error("no guest id");
		return FALSE;
	}
	return TRUE;
}
$handler->register('openVNCConsole', 'openVNCConsole');

function syncNetworkPortmapConfig(&$req) {
	$db = mysql_open();
	$ret = VMPortmap::syncConfig($db);
	$req->responseJSON(json_encode($ret));
	return TRUE;
}
$handler->register('syncNetworkPortmapConfig', 'syncNetworkPortmapConfig');

function getHostNetworkList(&$req) {
	$db = mysql_open();
	$dbrows = $db->get_assocs("a.net_id as val, CONCAT(b.vc_name, '(', b.vc_addr_start, '/', b.tiu_netmask, ')') as txt", "vm_host_net_tbl a left join vm_network_tbl b on a.net_id=b.id", "a.host_id={$req->_host_id}");
	if (!is_null($dbrows)) {
		$output = array('_net_list' => $dbrows, '_fn'=>$req->_fn);
		$req->responseJSON(json_encode($output));
	}else {
		$req->error("Failed to get host network list!{$req->_host_id}");
		return FALSE;
	}
	return TRUE;
}
$handler->register('GET_HOST_NET_LIST', 'getHostNetworkList');


$handler->accept();

?>
