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
 *  along with JiaLib.  If not, see <http://www.gnu.org/licenses/>.
 *  @license GNU Lesser General Public License
 *
 *  CopyRight 2009-2012 QIU Jian (sordqiu@gmail.com)
 *
 ****************************************************************************/
?>
<?php

include_once("config.inc.php");
include_once(JIALIB_PATH."/jialib.inc");
include_once("utils.inc.php");
include_once('include/host.class.inc');
include_once('include/network.class.inc');
include_once('include/template.class.inc');
include_once('include/guest.class.inc');
include_once('include/portmap.class.inc');


$handler = new RequestHandler();

require("framework/default_handlers.inc.php");

function saveVMHost($db, $id, $name, $addr, $mem, $disk, $bridge, $vmdir, $tmpdir, $isodir) {
	$host = new VMHost($id);
	$host->initByValue($name, $addr, $mem, $disk, $bridge, $vmdir, $tmpdir, $isodir);
	$vm = $host->memory();
	if (is_string($vm)) {
		return "无法访问主机, 原因：".$vm;
	}else {
		$mem_total = intval(($vm['mem_used'] + $vm['mem_free'])/1000);
		echo $mem."/".$mem_total;
		if(intval($mem) > $mem_total) {
			return "内存超过物理内存容量: {$mem_total}";
		}else{
			$ret = $host->save($db);
			if($ret === TRUE) {
				if ($id == 0) {
					logvmevent($db, $host->getID(), '', "创建物理主机{$name}");
				}else {
					logvmevent($db, $id, '', "更新物理主机属性{$name}");
				}
				return TRUE;
			}else {
				return $ret;
			}
		}
	}
}

function addVMHost(&$req) {
	$db = mysql_open();
	$ret = saveVMHost($db, 0, $req->_vc_name, $req->_vc_address, $req->_siu_memory, $req->_siu_disk_size, $req->_vc_bridge_name, $req->_vc_vm_dir, $req->_vc_template_dir, $req->_vc_iso_dir);
	if ($ret !== TRUE) {
		$req->error($ret);
		return FALSE;
	}
	return TRUE;
}
$handler->register("ADD_VMHOST", "addVMHost");

function delVMHost(&$req) {
	$db = mysql_open();
	$host = new VMHost($req->_id);
	$ret = $host->remove($db);
	if ($ret === TRUE) {
		logvmevent($db, $req->_id, '', "删除物理主机(id={$req->_id})");
	}else {
		$req->error($ret);
		return FALSE;
	}
	return TRUE;
}
$handler->register("DEL_VMHOST", "delVMHost");

function editVMHost(&$req) {
	$db = mysql_open();
	$ret = saveVMHost($db, $req->_id, $req->_vc_name, $req->_vc_address, $req->_siu_memory, $req->_siu_disk_size, $req->_vc_bridge_name, $req->_vc_vm_dir, $req->_vc_template_dir, $req->_vc_iso_dir);
	if ($ret !== TRUE) {
		$req->error($ret);
		return FALSE;
	}
	return TRUE;
}
$handler->register("EDIT_VMHOST", "editVMHost");

function addNetwork(&$req) {
	$db = mysql_open();
	$net = new VMNetwork();
	$net->initByValue($req->_vc_name, $req->_vc_gateway, $req->_vc_public_ip, $req->_tiu_netmask, $req->_siu_vlan_id, $req->_vc_addr_start, $req->_vc_addr_end);
	$ret = $net->save($db);
	if ($ret !== TRUE) {
		$req->error($ret);
		return FALSE;
	}
	return TRUE;
}
$handler->register("ADD_NETWORK", "addNetwork");

function editNetwork(&$req) {
	$db = mysql_open();
	$net = new VMNetwork($req->_id);
	$net->initByValue($req->_vc_name, $req->_vc_gateway, $req->_vc_public_ip, $req->_tiu_netmask, $req->_siu_vlan_id, $req->_vc_addr_start, $req->_vc_addr_end);
	$ret = $net->save($db);
	if ($ret !== TRUE) {
		$req->error($ret);
		return FALSE;
	}
	return TRUE;
}
$handler->register("EDIT_NETWORK", "editNetwork");

function delNetwork(&$req) {
	$db = mysql_open();
	$net = new VMNetwork($req->_id);
	$ret = $net->remove($db);
	if ($ret !== TRUE) {
		$req->error($ret);
		return FALSE;
	}
	return TRUE;
}
$handler->register("DEL_NETWORK", "delNetwork");

function addVMGuest(&$req) {
	$db = mysql_open();
	$guest = new VMGuest();
	$host = new VMHost($req->_host_id);
	$host->initByDB($db);
	$guest->initByValue($host, $req->_vc_name, $req->_siu_memory, $req->_vc_iso, $req->_vc_user, $req->_vc_bootorder);
	$ret = $guest->save($db);
	if ($ret === TRUE) {
		$host->makeVM($guest);
		$ret = $guest->createNIC($req->_net_id, $db);
		if ($ret === TRUE) {
			if ($req->_disk_mode == 'new') {
				$ret = $guest->createDiskOfSize($req->_siu_disk_sz, $db);
			}else {
				$ret = $guest->createDiskFromTemplate($req->_template_id, $db);
			}
			if ($ret === TRUE) {
				#logvmevent($db, $req->_host_id, $req->_vc_name, "创建虚拟机");
			}else {
				$req->error($ret);
				return FALSE;
			}
		}else {
			$req->error($ret);
			return FALSE;
		}
	} else {
		$req->error($ret);
		return FALSE;
	}
	return TRUE;
}
$handler->register("ADD_VMGUEST", "addVMGuest");

function delVMGuest(&$req) {
	if($req->_vc_user == 'admin' || $db->get_item_count("vm_guest_tbl", "id={$req->_id} and vc_user='{$req->_vc_user}'") == 1) {
		$db = mysql_open();
		$guest = new VMGuest($req->_id);
		$guest->initByDB($db);
		$host = $guest->getHost();
		$ret = $host->destroyVM($guest);
		if ($ret === TRUE) {
			$ret = $guest->remove($db);
			if ($ret === TRUE) {
			}else {
				$req->error($ret);
				return FALSE;
			}
		}else {
			$req->error($ret);
			return FALSE;
		}
	}else {
		$req->error("{$req->_vc_user} is not authorized to delete VM");
		return FALSE;
	}
	return TRUE;
}
$handler->register("DEL_VMGUEST", "delVMGuest");

function editVMGuest(&$req) {
	if($req->_vc_user=='admin' || $db->get_item_count("vm_guest_tbl", "id={$req->_id} and vc_user='{$req->_vc_user}'") == 1) {
		$db = mysql_open();
		$guest = new VMGuest($req->_id);
		$guest->initByDB($db);
		$guest->initByValue($guest->getHost(), $req->_vc_name, $req->_siu_memory, $req->_vc_iso, $req->_vc_user, $req->_vc_bootorder);
		$ret = $guest->save($db);
		if ($ret === TRUE) {
		}else {
			$req->error($ret);
			return FALSE;
		}
	}else {
		$req->error("{$req->_vc_user}没有权限编辑此虚拟机");
		return FALSE;
	}
	return TRUE;
}
$handler->register("EDIT_VMGUEST", "editVMGuest");

function addVMGuestNIC(&$req) {
	if (isset($req->_guest_id)) {
		$db = mysql_open();
		$guest = new VMGuest($req->_guest_id);
		$guest->initByDB($db);
		$ret = $guest->createNIC($req->_net_id, $db);
		if ($ret === TRUE) {
		}else {
			$req->error($ret);
			return FALSE;
		}
	}else {
		$req->error("没有_guest_id");
		return FALSE;
	}
	return TRUE;
}
$handler->register("ADD_VMGUEST_NIC", "addVMGuestNIC");

function delVMGuestNIC(&$req) {
	if (isset($req->_id)) {
		$db = mysql_open();
		$nic = new VMGuestNIC($req->_id);
		$ret = $nic->remove($db);
		if ($ret !== TRUE) {
			$req->error($ret);
			return FALSE;
		}
	}
	return TRUE;
}
$handler->register("DEL_VMGUEST_NIC", "delVMGuestNIC");

function addVMGuestDisk(&$req) {
	if(isset($req->_guest_id)) {
		$db = mysql_open();
		$guest = new VMGuest($req->_guest_id);
		$guest->initByDB($db);
		if($req->_disk_mode == 'new') {
			$ret = $guest->createDiskOfSize($req->_siu_disk_sz, $db);
		}else {
			$ret = $guest->createDiskFromTemplate($req->_template_id, $db);
		}
		if($ret !== TRUE) {
			$req->error($ret);
			return FALSE;
		}
	}else {
		$req->error("NO _guest_id");
		return FALSE;
	}
	return TRUE;
}
$handler->register("ADD_VMGUEST_DISK", "addVMGuestDisk");

function delVMGuestDisk(&$req) {
	if(isset($req->_id)) {
		$db = mysql_open();
		$disk = new VMGuestDisk($req->_id);
		$disk->initByDB($db);
		$guest = new VMGuest($disk->getGuestID());
		$guest->initByDB($db);
		$host = $guest->getHost();
		$ret = $disk->remove($db);
		if ($ret === TRUE) {
			$ret = $host->removeGuestDisk($guest, $disk);
			if($ret === TRUE) {
			}else {
				$req->error($ret);
				return FALSE;
			}
		}else {
			$req->error($ret);
			return FALSE;
		}
	}else {
		$req->error("No _id");
		return FALSE;
	}
	return TRUE;
}
$handler->register("DEL_VMGUEST_DISK", "delVMGuestDisk");

function startVMGuest(&$req) {
	if($req->_vc_user=='admin' || $db->get_item_count("vm_guest_tbl", "id={$req->_id} and vc_user='{$req->_vc_user}'") == 1) {
		$db = mysql_open();
		$guest = new VMGuest($req->_id);
		$guest->initByDB($db);
		$ret = $guest->tryLock($db);
		if ($ret === TRUE) {
			$host = $guest->getHost();
			$ret = $host->startVM($guest, $db);
			$guest->unlock($db, VM_GUEST_STATE_RUNNING);
		}
		if ($ret !== TRUE) {
			$req->error($ret);
			return FALSE;
		}
	}else {
		$req->error("没有权限开启虚拟机！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("START_VMGUEST", "startVMGuest");

function stopVMGuest(&$req) {
	$db = mysql_open();
	if($req->_vc_user=='admin' || $db->get_item_count("vm_guest_tbl", "id={$req->_id} and vc_user='{$req->_vc_user}'") == 1) {
		$db = mysql_open();
		$guest = new VMGuest($req->_id);
		$guest->initByDB($db);
		$ret = $guest->tryLock($db);
		if ($ret === TRUE) {
			$host = $guest->getHost();
			$ret = $host->stopVM($guest, $db);
			$guest->unlock($db, VM_GUEST_STATE_FREE);
		}
		if ($ret !== TRUE) {
			$req->error($ret);
			return FALSE;
		}
	}else {
		$req->error("没有权限停止虚拟机！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("STOP_VMGUEST", "stopVMGuest");

function saveVMGuestDiskAsTemplate(&$req) {
	if (isset($req->_disk_id)) {
		$db = mysql_open();
		$disk = new VMGuestDisk($req->_disk_id);
		$disk->initByDB($db);
		$guest = new VMGuest($disk->getGuestID());
		$guest->initByDB($db);
		$host = $guest->getHost();
		$template = new VMTemplate();
		$template->initByValue($req->_vc_name, $req->_vc_os, $req->_vc_version, $req->_tiu_bits, $disk->getGBSize(), $req->_vc_lang);
		$ret = $template->save($db);
		if ($ret === TRUE) {
			$ret = $host->createTemplate($guest, $disk, $template);
			if ($ret === TRUE) {
			}else {
				$req->error($ret);
				return FALSE;
			}
		}else {
			$req->error($ret);
			return FALSE;
		}
	}else {
		$req->error("No disk id!");
		return FALSE;
	}
	return TRUE;
}
$handler->register("SAVE_VMGUEST_DISK_TEMPLATE", "saveVMGuestDiskAsTemplate");

function delTemplate(&$req) {
	if (isset($req->_id)) {
		$db = mysql_open();
		$temp = new VMTemplate($req->_id);
		$ret = $temp->remove($db);
		if ($ret === TRUE) {
			$host = VMHost::anyHost($db);
			if ($host !== FALSE) {
				$host->removeTemplate($temp);
			}else {
				$req->error("No host!");
				return FALSE;
			}
		}else {
			$req->error($ret);
			return FALSE;
		}
	}else {
		$req->error("no id");
		return FALSE;
	}
	return TRUE;
}
$handler->register("DEL_VMTEMPLATE", "delTemplate");

function editTemplate(&$req) {
	if (isset($req->_id)) {
		$db = mysql_open();
		$temp = new VMTemplate($req->_id);
		$temp->initByValue($req->_vc_name, $req->_vc_os, $req->_vc_version, $req->_tiu_bits, 0, $req->_vc_lang);
		$ret = $temp->save($db);
		if ($ret === TRUE) {
		}else {
			$req->error($ret);
			return FALSE;
		}
	}else {
		$req->error("No id");
		return FALSE;
	}
	return TRUE;
}
$handler->register("EDIT_VMTEMPLATE", "editTemplate");

function addVMGuestNetworkPortmap(&$req) {
	$db = mysql_open();
	$portmap = new VMPortmap();
	$portmap->initByValue($req->_guest_net_id, $req->_vc_protocol, $req->_siu_pub_port, $req->_siu_pri_port);
	$ret = $portmap->save($db);
	if ($ret === TRUE) {
	}else {
		$req->error("save error!{$ret}");
		return FALSE;
	}
	return TRUE;
}
$handler->register("ADD_VMGUEST_NETPORT_MAP", "addVMGuestNetworkPortmap");

function delVMGuestNetworkPortmap(&$req) {
	$db = mysql_open();
	$portmap = new VMPortmap($req->_id);
	$ret = $portmap->remove($db);
	if ($ret === TRUE) {
	}else {
		$req->error($ret);
		return FALSE;
	}
	return TRUE;
}
$handler->register("DEL_VMGUEST_NETPORT_MAP", "delVMGuestNetworkPortmap");

function editVMGuestDisk(&$req) {
	$db = mysql_open();
	$disk = new VMGuestDisk($req->_id);
	$disk->initByDB($db);
	$disk->setValue($req->_vc_if, $req->_vc_cache, $req->_vc_aio);
	$ret = $disk->save($db);
	if ($ret === TRUE) {
	}else {
		$req->error($ret);
		return FALSE;
	}
	return TRUE;
}
$handler->register("EDIT_VMGUEST_DISK", "editVMGuestDisk");

function editVMGuestNIC(&$req) {
	$db = mysql_open();
	$nic = new VMGuestNIC($req->_id);
	$nic->initByDB($db);
	$nic->setValue($req->_vc_model, $req->_iu_sndbuf);
	$ret = $nic->save($db);
	if ($ret === TRUE) {
	}else {
		$req->error($ret);
		return FALSE;
	}
	return TRUE;
}
$handler->register("EDIT_VMGUEST_NIC", "editVMGuestNIC");

function addVMHostNetwork(&$req) {
	$db = mysql_open();
	$host_net = new VMHostNet();
	$host_net->initByValue($req->_host_id, $req->_net_id, $req->_vc_bridge, $req->_vc_eth);
	$ret = $host_net->save($db);
	if ($ret === TRUE) {
	}else {
		$req->error($ret);
		return FALSE;
	}
	return TRUE;
}
$handler->register("ADD_VMHOST_NETWORK", "addVMHostNetwork");

function delVMHostNetwork(&$req) {
	$db = mysql_open();
	$host_net = new VMHostNet($req->_id);
	$ret = $host_net->remove($db);
	if ($ret === TRUE) {
	}else {
		$req->error($ret);
		return FALSE;
	}
	return TRUE;
}
$handler->register("DEL_VMHOST_NETWORK", "delVMHostNetwork");

function editVMHostNetwork(&$req) {
	$db = mysql_open();
	$host_net = new VMHostNet($req->_id);
	$host_net->initByDB($db);
	$host_net->initByValue($req->_host_id, $req->_net_id, $req->_vc_bridge, $req->_vc_eth);
	$ret = $host_net->save($db);
	if ($ret === TRUE) {
	}else {
		$req->error($ret);
		return FALSE;
	}
	return TRUE;
}
$handler->register("EDIT_VMHOST_NETWORK", "editVMHostNetwork");


$handler->accept();

?>
