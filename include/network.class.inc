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

class VMNetwork {
	private $__id;
	private $__vc_name;
	private $__vc_gateway;
	private $__vc_public_ip;
	private $__tiu_netmask;
	private $__siu_vlan_id;
	private $__vc_addr_start;
	private $__vc_addr_end;
	private $__dt_created;

	public function __construct($id=0) {
		$this->__id = $id;
	}

	public function initByDB(&$db) {
		$dbrow = $db->get_single_array("vc_name, vc_gateway, vc_public_ip, tiu_netmask, siu_vlan_id, vc_addr_start, vc_addr_end, dt_created", "vm_network_tbl", "id={$this->__id}");
		if(!is_null($dbrow)) {
			list($this->__vc_name, $this->__vc_gateway, $this->__vc_public_ip, $this->__tiu_netmask, $this->__siu_vlan_id, $this->__vc_addr_start, $this->__vc_addr_end, $this->__dt_created) = $dbrow;
		}
	}

	public function initByValue($name, $gw, $pub_ip, $masklen, $vlan_id, $addr_start, $addr_end) {
		$this->__vc_name = $name;
		$this->__vc_gateway = $gw;
		$this->__vc_public_ip = $pub_ip;
		$this->__tiu_netmask = $masklen;
		if (empty($vlan_id) || is_null($vlan_id)) {
			$vlan_id = 0;
		}
		$this->__siu_vlan_id = $vlan_id;
		$this->__vc_addr_start = $addr_start;
		$this->__vc_addr_end   = $addr_end;
	}

	public function save(&$db) {
		if ($this->__id <= 0) { // insert
			if($db->get_item_count("vm_network_tbl", "vc_name='{$this->__vc_name}'") == 0) {
				$sql = "INSERT INTO vm_network_tbl(vc_name, vc_gateway, vc_public_ip, tiu_netmask, siu_vlan_id, vc_addr_start, vc_addr_end, dt_created) VALUES('{$this->__vc_name}', '{$this->__vc_gateway}', '{$this->__vc_public_ip}', {$this->__tiu_netmask}, {$this->__siu_vlan_id}, '{$this->__vc_addr_start}', '{$this->__vc_addr_end}', NOW())";
				if ($db->query($sql)) {
					$this->__id = $db->last_id();
				}else {
					return "插入主机信息失败！".$sql;
				}
			}else {
				return "同名网络已经存在";
			}
		}else { // update
			if($db->get_item_count("vm_network_tbl", "(vc_name='{$this->__vc_name}') AND (id<>{$this->__id})") == 0) {
				if($db->update("vc_name='{$this->__vc_name}', vc_gateway='{$this->__vc_gateway}', vc_public_ip='{$this->__vc_public_ip}', tiu_netmask={$this->__tiu_netmask}, siu_vlan_id={$this->__siu_vlan_id}, vc_addr_start='{$this->__vc_addr_start}', vc_addr_end='{$this->__vc_addr_end}'", "vm_network_tbl", "id={$this->__id}")) {
				}else {
					return "更新主机信息失败！".$sql;
				}
			}else {
				return "同名网络已经存在";
			}
		}
		return TRUE;
	}

	public function getGuestCount(&$db) {
		if ($this->__id > 0) {
			return $db->get_item_count("vm_guest_net_tbl", "net_id={$this->__id}");
		}else {
			return 0;
		}
	}

	public function remove(&$db) {
		if ($this->__id > 0) {
			if ($this->getGuestCount($db) > 0) {
				return "此网络还有虚拟机使用，无法删除！";
			}else {
				if ($db->delete("vm_network_tbl", "id={$this->__id}")) {
				} else {
					return "删除网络信息出错！";
				}
			}
		}
		return TRUE;
	}

	private function getNextAddress($addr) {
		$parts = explode(".", $addr);
		$i = 3;
		while($i >= 0) {
			$parts[$i] += 1;
			if ($parts[$i] >= 256) {
				$parts[$i] = 0;
				$i--;
			}else {
				break;
			}
		}
		return implode(".", $parts);
	}

	public function getSpareAddress(&$db) {
		$dbrows = $db->get_arrays("vc_addr", "vm_guest_net_tbl", "net_id={$this->__id}");
		if (!is_null($dbrows)) {
			$used_addr = array();
			for($i = 0; $i < count($dbrows); $i ++) {
				$used_addr[] = $dbrows[$i][0];
			}
			$addr = $this->__vc_addr_start;
			while(TRUE) {
				if(in_array($addr, $used_addr)) {
					if($addr == $this->__vc_addr_end) {
						return FALSE;
					}else {
						$addr = $this->getNextAddress($addr);
					}
				}else {
					return $addr;
				}
			}
		}
		return FALSE;
	}

	public function getVLANID() {
		return $this->__siu_vlan_id;
	}

	public function getID() {
		return $this->__id;
	}
}

?>
