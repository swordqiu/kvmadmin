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

class VMHostNet {
	private $__id;
	private $__host_id;
	private $__net_id;
	private $__vc_bridge;
	private $__vc_eth;
	private $__dt_created;

	public function __construct($id=0) {
		$this->__id = $id;
	}

	public function initByHostNet(&$db, &$host, &$net) {
		$this->__host_id = $host->getID();
		$this->__net_id  = $net->getID();
		$dbrow = $db->get_single_array("id, vc_bridge, vc_eth, dt_created", "vm_host_net_tbl", "host_id={$this->__host_id} AND net_id={$this->__net_id}");
		if (!is_null($dbrow)) {
			list($this->__id, $this->__vc_bridge, $this->__vc_eth, $this->__dt_created) = $dbrow;
			return TRUE;
		}else {
			return FALSE;
		}
	}

	public function initByDB(&$db) {
		$dbrow = $db->get_single_array("host_id, net_id, vc_bridge, vc_eth, dt_created", "vm_host_net_tbl", "id={$this->__id}");
		if (!is_null($dbrow)) {
			list($this->__host_id, $this->__net_id, $this->__vc_bridge, $this->__vc_eth, $this->__dt_created) = $dbrow;
			return TRUE;
		}else {
			return FALSE;
		}
	}

	public function initByValue($host_id, $net_id, $bridge, $eth) {
		$this->__host_id = $host_id;
		$this->__net_id  = $net_id;
		$this->__vc_bridge = $bridge;
		$this->__vc_eth    = $eth;
	}

	public function save(&$db) {
		if ($this->__id <= 0) { // insert
			$sql = "INSERT INTO vm_host_net_tbl(host_id, net_id, vc_bridge, vc_eth, dt_created) VALUES({$this->__host_id}, {$this->__net_id}, '{$this->__vc_bridge}', '{$this->__vc_eth}', NOW())";
			if ($db->query($sql)) {
				$this->__id = $db->last_id();
			}else {
				return "Failed to insert host net record";
			}
		}else {
			if ($db->update("host_id={$this->__host_id}, net_id={$this->__net_id}, vc_bridge='{$this->__vc_bridge}', vc_eth='{$this->__vc_eth}'", "vm_host_net_tbl", "id={$this->__id}")) {
			}else {
				return "Failed to update host net record";
			}
		}
		return TRUE;
	}

	public function remove(&$db) {
		if ($this->__id > 0) {
			if ($db->delete("vm_host_net_tbl", "id={$this->__id}")) {
				return TRUE;
			}else {
				return "Failed to delete records";
			}
		}else {
			return "Not a valid record";
		}
	}

	public function getBridge() {
		return $this->__vc_bridge;
	}
}

class VMHost {
	private $__id;
	private $__vc_name;
	private $__vc_address;
	private $__siu_memory_size;
	private $__siu_disk_size;
	private $__vc_bridge_name;
	private $__vc_vm_dir;
	private $__vc_template_dir;
	private $__vc_ios_dir;
	private $__dt_created;

	public function __construct($id=0) {
		$this->__id = $id;
	}

	public function initByDB(&$db) {
		$dbrow = $db->get_single_array("vc_name, vc_address, siu_memory, siu_disk_size, vc_bridge_name, vc_vm_dir, vc_template_dir, vc_iso_dir, dt_created", "vm_host_tbl", "id={$this->__id}");
		if (!is_null($dbrow)) {
			list($this->__vc_name, $this->__vc_address, $this->__siu_memory, $this->__siu_disk_size, $this->__vc_bridge_name, $this->__vc_vm_dir, $this->__vc_template_dir, $this->__vc_iso_dir, $this->__dt_created) = $dbrow;
			return TRUE;
		}else {
			return FALSE;
		}
	}

	public function initByValue($name, $addr, $mem_sz, $disk_sz, $br_name, $vmdir, $tempdir, $isodir) {
		$this->__vc_name = $name;
		$this->__vc_address = $addr;
		$this->__siu_memory = $mem_sz;
		$this->__siu_disk_size = $disk_sz;
		$this->__vc_bridge_name = $br_name;
		$this->__vc_vm_dir = $vmdir;
		$this->__vc_template_dir = $tempdir;
		$this->__vc_iso_dir = $isodir;
	}

	public function save(&$db) {
		if($this->__id <= 0) {
			if($db->get_item_count("vm_host_tbl", "vc_name='{$this->__vc_name}' or vc_address='{$this->__vc_address}'") == 0) {
				$sql = "INSERT INTO vm_host_tbl(vc_name, vc_address, siu_memory, siu_disk_size, vc_bridge_name, vc_vm_dir, vc_template_dir, vc_iso_dir, dt_created) VALUES('{$this->__vc_name}', '{$this->__vc_address}', {$this->__siu_memory}, {$this->__siu_disk_size}, '{$this->__vc_bridge_name}', '{$this->__vc_vm_dir}', '{$this->__vc_template_dir}', '{$this->__vc_iso_dir}', NOW())";
				if ($db->query($sql)) {
					$this->__id = $db->last_id();
				}else {
					return "插入主机信息失败！".$sql;
				}
			}else {
				return "同名或同IP主机已经存在！";
			}
		}else {
			if($db->get_item_count("vm_host_tbl", "(vc_name='{$this->__vc_name}' or vc_address='{$this->__vc_address}') AND (id<>{$this->__id})") == 0) {
				if ($db->update("vc_name='{$this->__vc_name}', vc_address='{$this->__vc_address}', siu_memory={$this->__siu_memory}, siu_disk_size={$this->__siu_disk_size}, vc_bridge_name='{$this->__vc_bridge_name}', vc_vm_dir='{$this->__vc_vm_dir}', vc_template_dir='{$this->__vc_template_dir}', vc_iso_dir='{$this->__vc_iso_dir}'", "vm_host_tbl", "id={$this->__id}")) {
				}else {
					return "更新主机信息失败！";
				}
			}else {
				return "同名或同IP主机已经存在！";
			}
		}
		return TRUE;
	}

	public function remove(&$db) {
		if ($this->__id > 0) {
			if ($db->delete("vm_host_tbl", "id={$this->__id}")) {
			}else {
				return "删除主机信息失败！";
			}
		}
		return TRUE;
	}

	private function ssh2_connect(&$errmsg) {
		$conn = ssh2_connect($this->__vc_address, 22, array('hostkey'=>'ssh-rsa'));
		if ($conn === FALSE) {
			$errmsg = "无法创建到{$this->__vc_address}的SSH连接";
			return FALSE;
		}
		if (ssh2_auth_pubkey_file($conn, SSH_USERNAME, SSH_PUBLIC_KEY_PATH, SSH_PRIVATE_KEY_PATH)) {
			return $conn;
		}else {
			$errmsg = "ssh认证失败";
			return FALSE;
		}
	}

	public static function ssh2_exec_internal($conn, $cmd, &$errmsg) {
		$stream = ssh2_exec($conn, $cmd);
		if ($stream === FALSE) {
			$errmsg = "执行命令{$cmd}失败";
			return FALSE;
		}
		stream_set_blocking($stream, TRUE);
		$data = "";
		while($buf = fread($stream, 4096)) {
			$data .= $buf;
		}
		fclose($stream);
		return $data;
	}

	public function ssh2_exec($cmd, &$errmsg) {
		$conn = $this->ssh2_connect($errmsg);
		if ($conn !== FALSE) {
			return VMHost::ssh2_exec_internal($conn, $cmd, $errmsg);
		}else {
			return FALSE;
		}
	}

	public function ssh2_send($localfile, $remotefile) {
		$conn = $this->ssh2_connect($errmsg);
		if ($conn !== FALSE) {
			$ret = ssh2_scp_send($conn, $localfile, $remotefile);
			if ($ret === TRUE) {
				return TRUE;
			}else {
				return "Failed to send file";
			}
		}else {
			return $errmsg;
		}
	}

	public function cpu() {
		$result = $this->ssh2_exec('vmstat', $errmsg);
		if($result !== FALSE) {
			$stat = array();
			$lines = explode("\n", $result);
			$keys = preg_split('/\s+/', trim($lines[1]));
			$vals = preg_split('/\s+/', trim($lines[2]));
			for($i = 0; $i < count($keys); $i ++) {
				$stat[$keys[$i]] = $vals[$i];
			}
			$ret = array();
			$ret['cpu_usr'] = $stat['us'];
			$ret['cpu_sys'] = $stat['sy'];
			$ret['cpu_idl'] = $stat['id'];
			$ret['cpu_iow'] = $stat['wa'];
			return $ret;
		}else {
			return $errmsg;
		}
	}

	public function memory() {
		$result = $this->ssh2_exec('free', $errmsg);
		if($result !== FALSE) {
			$stat = array();
			$lines = explode("\n", $result);
			$mem_line = preg_split('/\s+/', trim($lines[1]));
			$swp_line = preg_split('/\s+/', trim($lines[3]));
			$stat['mem_used'] = $mem_line[2];
			$stat['mem_free'] = $mem_line[3];
			$stat['swp_used'] = $swp_line[2];
			$stat['swp_free'] = $swp_line[3];
			return $stat;
		}else {
			return $errmsg;
		}
	}

	public function ifconfig() {
		$result = $this->ssh2_exec('/sbin/ifconfig', $errmsg);
		if($result !== FALSE) {
			$ifs = array();
			echo $result;
			return $ifs;
		}else {
			return $errmsg;
		}
	}


	private function getDirList($dir) {
		$result = $this->ssh2_exec('ls -l '.$dir, $errmsg);
		if ($result !== FALSE) {
			$iso_list = array();
			$lines = explode("\n", $result);
			for($i = 1; $i < count($lines); $i ++) {
				$dat = preg_split('/\s+/', $lines[$i]);
				if(count($dat) > 8) {
					$iso_list[] = $dat[8];
				}
			}
			return $iso_list;
		}else {
			return $errmsg;
		}
	}

	public function getISOList() {
		return $this->getDirList($this->__vc_iso_dir);
	}

	/*public function getTemplateList() {
		return $this->getDirList($this->__vc_template_dir);
	}*/

	public function getGuestDir(&$guest) {
		return $this->__vc_vm_dir."/vm".$guest->getID();
	}

	/*public function getTemplateSize($tempname) {
		$cmd = "stat {$this->__vc_template_dir}/{$tempname}";
		$result = $this->ssh2_exec($cmd, $errmsg);
		if ($result !== FALSE) {
			$lines = explode("\n", $result);
			if (count($lines) > 1) {
				$dat = preg_split('/\s+/', $lines[1]);
				if (count($dat) > 1) {
					return $dat[1];
				}
			}
		}
		return -1;
	}*/

	public function makeVM(&$guest) {
		$conn = $this->ssh2_connect($errmsg);
		if($conn !== FALSE) {
			$guest_dir = $this->getGuestDir($guest);
			$cmd = "rm -fr {$guest_dir}; mkdir -p {$guest_dir}; echo $?";
			$result = VMHost::ssh2_exec_internal($conn, $cmd, $errmsg);
			if ($result !== FALSE) {
				if (trim($result) == '0') {
					if (ssh2_scp_send($conn, VM_START_SCRIPT, $guest_dir."/vmstart")
						&& ssh2_scp_send($conn, VM_STATUS_SCRIPT, $guest_dir."/vmstatus")
						&& ssh2_scp_send($conn, VM_STOP_SCRIPT, $guest_dir."/vmstop")
						&& ssh2_scp_send($conn, BR0_START_SCRIPT, $guest_dir."/ovs-ifup")
						&& ssh2_scp_send($conn, BR0_STOP_SCRIPT, $guest_dir."/ovs-ifdown")
					) {
						return TRUE;
					}else {
						$errmsg = "Unable to copy VM scripts";
						return $errmsg;
					}
				}else {
					return "failed to create VM directory";
				}
			}else {
				return $errmsg;
			}
		}else {
			return $errmsg;
		}
	}

	public function destroyVM(&$guest) {
		$cmd = "rm -fr {$this->getGuestDir($guest)};echo $?";
		$result = $this->ssh2_exec($cmd, $errmsg);
		if ($result !== FALSE) {
			if (trim($result) == '0') {
				return TRUE;
			}else {
				return "Failed to destroy VM directory";
			}
		}else {
			return $errmsg;
		}
	}

	public function startVM(&$guest, &$db) {
		$cmd = "echo '{$guest->getVMStartScript($db)}' > {$this->getGuestDir($guest)}/vmstart_script";
		$result = $this->ssh2_exec($cmd, $errmsg);
		if ($result !== FALSE) {
			$cmd = "cd {$this->getGuestDir($guest)};sh vmstart;";
			$result = $this->ssh2_exec($cmd, $errmsg);
			if ($result !== FALSE) {
				return TRUE;
			}else {
				return $errmsg;
			}
		}else {
			return $errmsg;
		}
	}

	public function stopVM(&$guest, &$db) {
		$cmd = "echo '{$guest->getVMStopScript($db)}' > {$this->getGuestDir($guest)}/vmstop_script";
		$result = $this->ssh2_exec($cmd, $errmsg);
		if ($result !== FALSE) {
			$cmd = "cd {$this->getGuestDir($guest)};sh vmstop;";
			$result = trim($this->ssh2_exec($cmd, $errmsg));
			if ($result == "succ" || $result == "noop") {
				return TRUE;
			}else {
				return FALSE;
			}
		}else {
			return FALSE;
		}
	}

	public function isVMRunning(&$guest) {
		$cmd = "cd {$this->getGuestDir($guest)};sh vmstatus;";
		$result = $this->ssh2_exec($cmd, $errmsg);
		if ($result === FALSE) {
			return $errmsg;
		}else {
			if (trim($result) == "running") {
				return TRUE;
			}else {
				return FALSE;
			}
		}
	}

	public function makeVMDiskOfSize(&$guest, &$disk) {
		$cmd = KVM_IMAGE_COMMAND." create -f raw {$this->getGuestDir($guest)}/disk{$disk->getID()}.img {$disk->getGBSize()}G";
		$result = $this->ssh2_exec($cmd, $errmsg);
		if ($result === FALSE) {
			return $errmsg;
		}else {
			return TRUE;
		}
	}

	public function createTemplate(&$guest, &$disk, &$template) {
		$guest_dir = $this->getGuestDir($guest);
		$cmd = "cp -f {$this->getGuestDir($guest)}/disk{$disk->getID()}.img {$this->__vc_template_dir}/template{$template->getID()}.img; echo $?";
		$result = $this->ssh2_exec($cmd, $errmsg);
		if ($result !== FALSE) {
			if (trim($result) == '0') {
				return TRUE;
			}else {
				return "Fail to copy: {$result}";
			}
		}else {
			return $errmsg;
		}
	}

	public function removeTemplate(&$template) {
		$cmd = "rm -fr {$this->__vc_template_dir}/template{$template->getID()}.img; echo $?";
		$result = $this->ssh2_exec($cmd, $errmsg);
		if ($result !== FALSE) {
			if (trim($result) == '0') {
				return TRUE;
			}else {
				return "Fail to rm: {$result}";
			}
		}else {
			return $errmsg;
		}
	}

	public function makeVMDiskFromTemplate(&$guest, &$template, &$disk) {
		$cmd = "cp -fr {$this->__vc_template_dir}/template{$template->getID()}.img {$this->getGuestDir($guest)}/disk{$disk->getID()}.img; echo $?";
		$result = $this->ssh2_exec($cmd, $errmsg);
		if ($result !== FALSE) {
			if (trim($result) == '0') {
				return TRUE;
			}else {
				return "Fail to copy template!";
			}
		}else {
			return $errmsg;
		}
	}

	public function removeGuestDisk(&$guest, &$disk) {
		$cmd = "rm -fr {$this->getGuestDir($guest)}/disk{$disk->getID()}.img";
		$result = $this->ssh2_exec($cmd, $errmsg);
		if ($result === FALSE) {
			return $ermsg;
		}else {
			return TRUE;
		}
	}

	public function getID() {
		return $this->__id;
	}

	public function getISODir() {
		return $this->__vc_iso_dir;
	}

	public static function anyHost(&$db) {
		$dbrow = $db->get_single_array("id", "vm_host_tbl", "");
		if (!is_null($dbrow)) {
			$id = $dbrow[0];
			$host = new VMHost($id);
			$host->initByDB($db);
			return $host;
		}
		return FALSE;
	}

	public function getIPAddress() {
		return $this->__vc_address;
	}

}

?>
