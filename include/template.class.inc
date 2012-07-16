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

class VMTemplate {
	private $__id;
	private $__vc_name;
	private $__vc_os;
	private $__vc_version;
	private $__tiu_bits;
	private $__siu_size;
	private $__vc_lang;
	private $__dt_created;

	public function __construct($id=0) {
		$this->__id = $id;
	}

	public function initByDB(&$db) {
		$dbrow = $db->get_single_array("vc_name, vc_os, vc_version, tiu_bits, siu_size, vc_lang, dt_created", "vm_template_tbl", "id={$this->__id}");
		if (!is_null($dbrow)) {
			list($this->__vc_name, $this->__vc_os, $this->__vc_version, $this->__tiu_bits, $this->__siu_size, $this->__vc_lang, $this->__dt_created) = $dbrow;
			return TRUE;
		}
		return FALSE;
	}

	public function initByValue($name, $os, $ver, $bits, $size_in_gb, $lang) {
		$this->__vc_name = $name;
		$this->__vc_os   = $os;
		$this->__vc_version = $ver;
		$this->__tiu_bits = $bits;
		$this->__siu_size = $size_in_gb;
		$this->__vc_lang = $lang;
	}

	public function save(&$db) {
		if ($this->__id <= 0) { // insert
			$sql = "INSERT INTO vm_template_tbl(vc_name, vc_os, vc_version, tiu_bits, siu_size, vc_lang, dt_created) VALUES('{$this->__vc_name}', '{$this->__vc_os}', '{$this->__vc_version}', {$this->__tiu_bits}, {$this->__siu_size}, '{$this->__vc_lang}', NOW())";
			if ($db->query($sql)) {
				$this->__id = $db->last_id();
			}else {
				return "Failed to insert VM template";
			}
		}else { // update
			if ($db->update("vc_name='{$this->__vc_name}', vc_os='{$this->__vc_os}', vc_version='{$this->__vc_version}', tiu_bits={$this->__tiu_bits}, vc_lang='{$this->__vc_lang}'", "vm_template_tbl", "id={$this->__id}")) {
			}else {
				return "Failed to update VM template";
			}
		}
		return TRUE;
	}

	public function remove(&$db) {
		if ($this->__id > 0) {
			if ($db->delete("vm_template_tbl", "id={$this->__id}")) {
			}else {
				return "Failed to remove VM template record";
			}
		}
		return TRUE;
	}

	public function getSize() {
		return $this->__siu_size;
	}

	public function getID() {
		return $this->__id;
	}
}

?>
