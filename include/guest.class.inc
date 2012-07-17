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

define("VM_GUEST_STATE_FREE", 0);
define("VM_GUEST_STATE_RUNNING", 1);
define("VM_GUEST_STATE_LOCKED", 2);

function updateDhcpHostTable(&$db) {
	$rows = $db->get_arrays("a.vc_mac, a.vc_addr, b.vc_name", "vm_guest_net_tbl a, vm_guest_tbl b", "a.guest_id=b.id");
	if(!is_null($rows)) {
		$conf = "";
		for($i = 0; $i < count($rows); $i ++) {
			$conf .= "dhcp-host={$rows[$i][0]},{$rows[$i][2]},{$rows[$i][1]}\n";
			$conf .= "address=/{$rows[$i][2]}/{$rows[$i][1]}\n";
		}
		$ret = file_put_contents("/tmp/dhcp_hosts", $conf);
		if ($ret === FALSE) {
			return FALSE;
		}else {
			system("sudo mv /tmp/dhcp_hosts /etc/dnsmasq.d/dhcp_hosts > /dev/null 2>&1", $ret);
			if ($ret == 0) {
				system("sudo /etc/init.d/dnsmasq restart > /dev/null 2>&1", $ret);
				if ($ret == 0) {
					return TRUE;
				}else {
					return FALSE;
				}
			}else {
				return FALSE;
			}
		}
	}
}

class VMGuestNIC {
	private $__id;
	private $__guest_id;
	private $__net_id;
	private $__vc_mac;
	private $__vc_addr;
	private $__vc_model;
	private $__iu_sndbuf;
	private $__dt_created;

	public function __construct($id=0) {
		$this->__id = $id;
		$this->__vc_model = 'virtio';
		$this->__iu_sndbuf = 0;
	}

	public function initByDB(&$db) {
		$dbrow = $db->get_single_array("guest_id, net_id, vc_mac, vc_addr, vc_model, iu_sndbuf, dt_created", "vm_guest_net_tbl", "id={$this->__id}");
		if(!is_null($dbrow)) {
			list($this->__guest_id, $this->__net_id, $this->__vc_mac, $this->__vc_addr, $this->__vc_model, $this->__iu_sndbuf, $this->__dt_created) = $dbrow;
		}
	}

	public function initByValue($guest_id, $net_id, $mac, $addr) {
		$this->__guest_id = $guest_id;
		$this->__net_id   = $net_id;
		$this->__vc_mac   = $mac;
		$this->__vc_addr  = $addr;
	}

	public function setValue($model, $sndbuf) {
		$this->__vc_model = $model;
		$this->__iu_sndbuf = $sndbuf;
	}

	public function save(&$db) { // no check
		if($this->__id <= 0) { // insert
			$sql = "INSERT INTO vm_guest_net_tbl(guest_id, net_id, vc_mac, vc_addr, vc_model, iu_sndbuf, dt_created) VALUES({$this->__guest_id}, {$this->__net_id}, '{$this->__vc_mac}', '{$this->__vc_addr}', '{$this->__vc_model}', {$this->__iu_sndbuf}, NOW())";
			if($db->query($sql)) {
				$this->__id = $db->last_id();
				updateDhcpHostTable($db);
			}else {
				return "插入虚机网络设备失败！";
			}
		}else { // update
			if($db->update("guest_id={$this->__guest_id}, net_id={$this->__net_id}, vc_mac='{$this->__vc_mac}', vc_addr='{$this->__vc_addr}', vc_model='{$this->__vc_model}', iu_sndbuf={$this->__iu_sndbuf}", "vm_guest_net_tbl", "id={$this->__id}")) {
				#updateDhcpHostTable($db);
			}else {
				return "更新虚机网络设备失败！";
			}
		}
		return TRUE;
	}

	public function remove(&$db) {
		if($this->__id > 0) {
			if($db->delete("vm_guest_net_tbl", "id={$this->__id}")) {
				updateDhcpHostTable($db);
			}else {
				return "删除虚机网络设备失败！";
			}
		}
		return TRUE;
	}

	public function getID() {
		return $this->__id;
	}

	public function getMac() {
		return $this->__vc_mac;
	}

	public function getNetID() {
		return $this->__net_id;
	}

	public function getSendBufferLen() {
		return $this->__iu_sndbuf;
	}

	public function getModel() {
		return $this->__vc_model;
	}
}

class VMGuestDisk {
	private $__id;
	private $__guest_id;
	private $__siu_disk_sz;
	private $__vc_if;
	private $__vc_cache;
	private $__vc_aio;
	private $__dt_created;

	public function __construct($id=0) {
		$this->__id = $id;
		$this->__vc_if = 'virtio';
		$this->__vc_cache = 'writethrough';
		$this->__vc_aio = 'native';
	}

	public function initByDB(&$db) {
		$dbrow = $db->get_single_array("guest_id, siu_disk_sz, vc_if, vc_cache, vc_aio, dt_created", "vm_guest_disk_tbl", "id={$this->__id}");
		if (!is_null($dbrow)) {
			list($this->__guest_id, $this->__siu_disk_sz, $this->__vc_if, $this->__vc_cache, $this->__vc_aio, $this->__dt_created) = $dbrow;
		}
	}

	public function initByValue($guest_id, $disksz) {
		$this->__guest_id = $guest_id;
		$this->__siu_disk_sz = $disksz;
	}

	public function setValue($if, $cache, $aio) {
		$this->__vc_if = $if;
		$this->__vc_cache = $cache;
		$this->__vc_aio = $aio;
	}

	public function save(&$db) {
		if ($this->__id <= 0) { // insert
			$sql = "INSERT INTO vm_guest_disk_tbl(guest_id, siu_disk_sz, vc_if, vc_cache, vc_aio, dt_created) VALUES({$this->__guest_id}, {$this->__siu_disk_sz}, '{$this->__vc_if}', '{$this->__vc_cache}', '{$this->__vc_aio}', NOW())";
			if ($db->query($sql)) {
				$this->__id = $db->last_id();
			}else {
				return "插入虚机磁盘记录失败！".$sql;
			}
		}else { // update
			if ($db->update("guest_id={$this->__guest_id}, siu_disk_sz={$this->__siu_disk_sz}, vc_if='{$this->__vc_if}', vc_cache='{$this->__vc_cache}', vc_aio='{$this->__vc_aio}'", "vm_guest_disk_tbl", "id={$this->__id}")) {
			}else {
				return "更新虚机磁盘记录失败！";
			}
		}
		return TRUE;
	}

	public function remove(&$db) {
		if ($this->__id > 0) {
			if($db->delete("vm_guest_disk_tbl", "id={$this->__id}")) {
			}else {
				return "Failed to delete VM disk record";
			}
		}
		return TRUE;
	}

	public function getID() {
		return $this->__id;
	}

	public function getGBSize() {
		return $this->__siu_disk_sz;
	}

	public function getGuestID() {
		return $this->__guest_id;
	}

	public function getIF() {
		return $this->__vc_if;
	}

	public function getCache() {
		return $this->__vc_cache;
	}

	public function getAIO() {
		return $this->__vc_aio;
	}
}

class VMGuest {
	private $__id;
	private $__host_id;
	private $__host;
	private $__vc_name;
	private $__siu_memory;
	private $__siu_vnc_port;
	private $__tiu_state;
	private $__vc_iso;
	private $__vc_user;
	private $__vc_bootorder;
	private $__dt_created;
	private $__nic_list;
	private $__disk_list;

	public function __construct($id=0) {
		$this->__id = $id;
	}

	public function initByDB(&$db) {
		$dbrow = $db->get_single_array("host_id, vc_name, siu_memory, siu_vnc_port, tiu_state, vc_iso, vc_user, vc_bootorder, dt_created", "vm_guest_tbl", "id={$this->__id}");
		if(!is_null($dbrow)) {
			list($this->__host_id, $this->__vc_name, $this->__siu_memory, $this->__siu_vnc_port, $this->__tiu_state, $this->__vc_iso, $this->__vc_user, $this->__vc_bootorder, $this->__dt_created) = $dbrow;
			$this->__host = new VMHost($this->__host_id);
			$this->__host->initByDB($db);
			$this->__nic_list = array();
			$dbrows = $db->get_arrays("id, guest_id, net_id, vc_mac, vc_addr, vc_model, iu_sndbuf", "vm_guest_net_tbl", "guest_id={$this->__id}", "dt_created asc");
			if (!is_null($dbrows) && count($dbrows) > 0) {
				for($i = 0; $i < count($dbrows); $i ++) {
					list($nic_id, $guest_id, $net_id, $mac, $addr, $model, $sndbuf) = $dbrows[$i];
					$nic = new VMGuestNIC($nic_id);
					$nic->initByValue($guest_id, $net_id, $mac, $addr);
					$nic->setValue($model, $sndbuf);
					$this->__nic_list[] = $nic;
				}
			}
			$this->__disk_list = array();
			$dbrows = $db->get_arrays("id, guest_id, siu_disk_sz, vc_if, vc_cache, vc_aio", "vm_guest_disk_tbl", "guest_id={$this->__id}", "dt_created asc");
			if (!is_null($dbrows) && count($dbrows) > 0) {
				for($i = 0; $i < count($dbrows); $i ++) {
					list($disk_id, $guest_id, $disksz, $if, $cache, $aio) = $dbrows[$i];
					$disk = new VMGuestDisk($disk_id);
					$disk->initByValue($guest_id, $disksz);
					$disk->setValue($if, $cache, $aio);
					$this->__disk_list[] = $disk;
				}
			}
		}
	}

	public function initByValue($host, $name, $memsz, $iso, $user, $bootorder) {
		$this->__host_id = $host->getID();
		$this->__host = $host;
		$this->__vc_name = $name;
		$this->__siu_memory = $memsz;
		$this->__vc_iso = $iso;
		$this->__vc_user = $user;
		$this->__vc_bootorder = $bootorder;
		$this->__nic_list = array();
		$this->__disk_list = array();
		$this->__tiu_state = VM_GUEST_STATE_FREE;
	}

	public function save(&$db) {
		if ($this->__id <= 0) { // insert
			$sql = "INSERT INTO vm_guest_tbl(host_id, vc_name, siu_memory, siu_vnc_port, tiu_state, vc_iso, vc_user, vc_bootorder, dt_created) VALUES({$this->__host_id}, '{$this->__vc_name}', {$this->__siu_memory}, 0, 0, '{$this->__vc_iso}', '{$this->__vc_user}', '{$this->__vc_bootorder}', NOW())";
			if ($db->query($sql)) {
				$this->__id = $db->last_id();
			}else {
				return "Failed to insert VM guest record";
			}
		}else { // update
			if ($db->update("vc_name='{$this->__vc_name}', siu_memory={$this->__siu_memory}, siu_vnc_port={$this->__siu_vnc_port}, tiu_state={$this->__tiu_state}, vc_iso='{$this->__vc_iso}', vc_bootorder='{$this->__vc_bootorder}'", "vm_guest_tbl", "id={$this->__id}")) {
			}else {
				return "Failed to update VM guest record";
			}
		}
		return TRUE;
	}

	private function randHEX() {
		return sprintf("%02X", rand(0, 127)*2);
	}

	private function genRandomMac() {
		return "00:22:".($this->randHEX()).":".($this->randHEX()).":".($this->randHEX()).":".($this->randHEX());
	}

	private function getSpareMac(&$db) {
		$mac = $this->genRandomMac();
		while($db->get_item_count("vm_guest_tbl", "vc_mac='{$mac}'") > 0) {
			$mac = $this->genRandomMac();
		}
		return $mac;
	}

	public function tryLock(&$db) {
		$ret = TRUE;
		if ($db->query("LOCK TABLES vm_guest_tbl WRITE")) {
			$dbrow = $db->get_single_array("tiu_state", "vm_guest_tbl", "id={$this->__id}");
			if (!is_null($dbrow)) {
				$this->__tiu_state = $dbrow[0];
				if ($this->__tiu_state != VM_GUEST_STATE_LOCKED) {
					$this->__tiu_state = VM_GUEST_STATE_LOCKED;
					if ($db->update("tiu_state={$this->__tiu_state}", "vm_guest_tbl", "id={$this->__id}")) {
					}else {
						$ret = "Failed to update state";
					}
				}else {
					$ret = "Table has been locked";
				}
			}else {
				$ret = "Failed to get state";
			}
			$db->query("UNLOCK TABLES");
		}
		return $ret;
	}

	public function unlock(&$db, $state=VM_GUEST_STATE_FREE) {
		$ret = TRUE;
		if ($db->query("LOCK TABLES vm_guest_tbl WRITE")) {
			$this->__tiu_state = $state;
			if ($db->update("tiu_state={$this->__tiu_state}", "vm_guest_tbl", "id={$this->__id}")) {
			}else {
				$ret = "Failed to update state";
			}
			$db->query("UNLOCK TABLES");
		}else {
			$ret = "Failed to lock table";
		}
		return $ret;
	}

	public function createNIC($netid, &$db) {
		$ret = $this->tryLock($db);
		if ($ret === TRUE) {
			$net = new VMNetwork($netid);
			$net->initByDB($db);
			$nic = new VMGuestNIC();
			$mac = $this->getSpareMac($db);
			$addr = $net->getSpareAddress($db);
			if($addr === FALSE) {
				$ret = "No available IP address in network!";
			}else {
				$nic->initByValue($this->__id, $netid, $mac, $addr);
				$nic->save($db);
				$this->__nic_list[] = $nic;
				$ret = TRUE;
			}
			$this->unlock($db);
		}else {
			$ret = "Failed to lock guest:".$ret;
		}
		return $ret;
	}

	public function createDiskOfSize($sz, &$db) {
		$ret = $this->tryLock($db);
		if ($ret === TRUE) {
			$disk = new VMGuestDisk();
			$disk->initByValue($this->__id, $sz);
			$ret = $disk->save($db);
			if ($ret === TRUE) {
				$host = $this->getHost();
				$ret = $host->makeVMDiskOfSize($this, $disk);
				if ($ret === TRUE) {
					$this->__disk_list[] = $disk;
				}
			}
			$this->unlock($db);
		}else {
			$ret = "failed to lock guest: ".$ret;
		}
		return $ret;
	}

	public function createDiskFromTemplate($temp_id, &$db) {
		$ret = $this->tryLock($db);
		if ($ret === TRUE) {
			$disk = new VMGuestDisk();
			$template = new VMTemplate($temp_id);
			$template->initByDB($db);
			$disk->initByValue($this->__id, $template->getSize());
			$ret = $disk->save($db);
			if ($ret === TRUE) {
				$ret = $this->__host->makeVMDiskFromTemplate($this, $template, $disk);
				if ($ret === TRUE) {
					$this->__disk_list[] = $disk;
					$ret = TRUE;
				}
			}
			$this->unlock($db);
		}else {
			$ret = "Failed to lock guest: ".$ret;
		}
		return $ret;
	}

	public function getID() {
		return $this->__id;
	}

	private function getSpareVNCPort(&$db) {
		$port = 0;
		while($db->get_item_count("vm_guest_tbl", "host_id={$this->__host_id} and siu_vnc_port={$port} and tiu_state=1") > 0) {
			$port++;
		}
		return $port;
	}

	public function getVNCPort() {
		return 5900 + $this->__siu_vnc_port;
	}

	public function getVMStartScript(&$db) {
		$host = $this->getHost();
		$dir = $host->getGuestDir($this);
		$this->__siu_vnc_port = $this->getSpareVNCPort($db);
		$this->save($db);
		$cmd = KVM_COMMAND." -enable-kvm -smp 1 -cpu host";
		$cmd .= " -vga std -vnc :{$this->__siu_vnc_port} -k en-us";
		$cmd .= " -rtc base=utc,clock=host,driftfix=none";
		$cmd .= " -usb -device usb-tablet";
		$cmd .= " -daemonize";
		$cmd .= " -nodefaults -nodefconfig";
		$cmd .= " -no-kvm-pit-reinjection";
		$cmd .= " -no-shutdown";
		$cmd .= " -name {$this->__vc_name}";
		$cmd .= " -m {$this->__siu_memory}";
		$cmd .= " -boot order={$this->__vc_bootorder}";
		$netdev_pci_offset = 3;
		for($i = 0; $i < count($this->__nic_list); $i ++) {
			$nic = $this->__nic_list[$i];
			$net = new VMNetwork($nic->getNetID());
			$net->initByDB($db);
			$addr = dechex($netdev_pci_offset + $i);
			$host_net = new VMHostNet();
			$ret = $host_net->initByHostNet($db, $host, $net);
			if ($ret === TRUE) {
				$upscript   = "/etc/openvswitch/ovs-ifup-{$host_net->getBridge()}";
				$downscript = "/etc/openvswitch/ovs-ifdown-{$host_net->getBridge()}";
				$cmd .= " -net nic,vlan={$net->getVLANID()},macaddr={$nic->getMac()},model={$nic->getModel()},name=eth{$i},addr=0x{$addr}";
				$cmd .= " -net tap,vlan={$net->getVLANID()},ifname=vnet{$nic->getID()},vhost=on,sndbuf={$nic->getSendBufferLen()},script={$upscript},downscript={$downscript}";
			}
		}
		$drive_pci_offset=10;
		for($i = 0; $i < count($this->__disk_list); $i ++) {
			$disk = $this->__disk_list[$i];
			$addr = dechex($drive_pci_offset + $i);
			$cmd .= " -drive file={$dir}/disk{$disk->getID()}.img,if={$disk->getIF()},format=raw,cache={$disk->getCache()},aio={$disk->getAIO()},media=disk";
			if ($disk->getIF() == 'virtio') {
				$cmd.=",addr=0x{$addr}";
			}
			#if($i == 0) {
			#	$cmd .= ",boot=on";
			#}
			#$cmd .= " -device virtio-blk-pci,bus=pci.0,addr={$addr},drive=drive-virtio-disk{$i},id=virtio-disk{$i}";
		}
		$iso_file = "";
		if (strlen($this->__vc_iso) > 0) {
			$iso_file = "file={$host->getISODir()}/{$this->__vc_iso},";
		}
		$cmd .= " -drive {$iso_file}if=ide,media=cdrom";
		#$cmd .= " -device ide-cd,bus=ide.0,drive=drive-cdrom0,id=cdrom0";
		$cmd .= " -runas vm";
		$cmd .= " -pidfile {$dir}/vm.pid";
		return $cmd;
	}

	public function getVMStopScript(&$db) {
		$host = $this->getHost();
		$dir = $host->getGuestDir($this);
		$cmd = "";
		for($i = 0; $i < count($this->__nic_list); $i ++) {
			$nic = $this->__nic_list[$i];
			$net = new VMNetwork($nic->getNetID());
			$net->initByDB($db);
			$host_net = new VMHostNet();
			$ret = $host_net->initByHostNet($db, $host, $net);
			if ($ret === TRUE) {
				$downscript = "/etc/openvswitch/ovs-ifdown-{$host_net->getBridge()}";
				$cmd .= "sh {$downscript} vnet{$nic->getID()}\n";
			}
		}
		return $cmd;
	}

	public function getHost() {
		return $this->__host;
	}

	public function remove(&$db) {
		$ret = TRUE;
		if ($this->__id > 0) {
			// first lock table
			if ($db->query("LOCK TABLES vm_guest_net_tbl WRITE, vm_guest_disk_tbl WRITE, vm_guest_tbl WRITE")) {
				// make sure the vm_guest is free
				if ($db->get_item_count("vm_guest_tbl", "id={$this->__id} AND tiu_state=".VM_GUEST_STATE_FREE) > 0) {
					// first delete nics
					if ($db->delete("vm_guest_net_tbl", "guest_id={$this->__id}")) {
						// second delete disks
						if ($db->delete("vm_guest_disk_tbl", "guest_id={$this->__id}")) {
							// third delete guest
							if ($db->delete("vm_guest_tbl", "id={$this->__id}")) {
							}else {
								$ret = "Failed to delete guest record";
							}
						}else {
							$ret = "Failed to delete Disks";
						}
					}else {
						$ret = "Failed to delete NICs";
					}
				}else {
					$ret = "Guest is not free";
				}
				$db->query("UNLOCK TABLES");
			}else {
				$ret = "Failed to lock table";
			}
		}else {
			$ret = "Not a valid guest";
		}
		return $ret;
	}

	public static function startAllVMGuest(&$db) {
		$dbrows = $db->get_arrays("id", "vm_guest_tbl", "tiu_state=1");
		if (!is_null($dbrows)) {
			for($i = 0; $i < count($dbrows); $i ++) {
				$guest = new VMGuest($dbrows[$i][0]);
				$guest->initByDB($db);
				$host = $guest->getHost();
				$ret = $host->isVMRunning($guest);
				if ($ret === FALSE) {
					$ret = $host->startVM($guest, $db);
					if ($ret !== TRUE) {
						echo $ret;
					}
				}
			}
		}
	}

}

?>
