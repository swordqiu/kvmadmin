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

class VMPortmap {
	private $__id;
	private $__guest_net_id;
	private $__vc_protocol;
	private $__siu_pub_port;
	private $__siu_pri_port;
	private $__dt_created;

	public function __construct($id=0) {
		$this->__id = $id;
	}

	public function initByDB(&$db) {
		$dbrow = $db->get_single_array("guest_net_id, vc_protocol, siu_pub_port, siu_pri_port, dt_created", "vm_guest_net_portmap_tbl", "id={$this->__id}");
		if (!is_null($dbrow)) {
			list($this->__guest_net_id, $this->__vc_protocol, $this->__siu_pub_port, $this->__siu_pri_port, $this->__dt_created) = $dbrow;
			return TRUE;
		}
		return FALSE;
	}

	public function initByValue($guest_net_id, $protocol, $pub_port, $priv_port) {
		$this->__guest_net_id = $guest_net_id;
		$this->__vc_protocol = $protocol;
		$this->__siu_pub_port = $pub_port;
		$this->__siu_pri_port = $priv_port;
	}

	public function save(&$db) {
		if ($this->__id <= 0) { // insert
			$sql = "INSERT INTO vm_guest_net_portmap_tbl(guest_net_id, vc_protocol, siu_pub_port, siu_pri_port, dt_created) VALUES({$this->__guest_net_id}, '{$this->__vc_protocol}', {$this->__siu_pub_port}, {$this->__siu_pri_port}, NOW())";
			if ($db->query($sql)) {
				$this->__id = $db->last_id();
			}else {
				return "Failed to insert VM portmap";
			}
		}else { // update
			if ($db->update("guest_net_id={$this->__guest_net_id}, vc_protocol='{$this->__vc_protocol}', siu_pub_port={$this->__siu_pub_port}, siu_pri_port={$this->__siu_pri_port}", "vm_guest_net_portmap_tbl", "id={$this->__id}")) {
			}else {
				return "Failed to update VM portmap";
			}
		}
		return TRUE;
	}

	public function remove(&$db) {
		if ($this->__id > 0) {
			if ($db->delete("vm_guest_net_portmap_tbl", "id={$this->__id}")) {
			}else {
				return "Failed to remove VM portmap record";
			}
		}
		return TRUE;
	}

	public function getID() {
		return $this->__id;
	}

	public static function consumeOutput($stream) {
		while($line = fread($stream, 4096)) {
			# echo "???".$line."???";
			if (preg_match('/(\[|\<)MAG(\-[a-zA-Z0-9\/]+)?(\]|\>)$/', trim($line)) > 0) {
				break;
			}
		}
	}

	public static function commitConfig($cmds) {
		$conn = ssh2_connect(GATEWAY_ADDRESS, 22);
		if (ssh2_auth_password($conn, GATEWAY_ADMIN, GATEWAY_PASSWORD)) {
			$stream = ssh2_shell($conn, 'xterm');
			if ($stream === FALSE) {
				return FALSE;
			}
			stream_set_blocking($stream, TRUE);

			VMPortmap::consumeOutput($stream);

			fwrite($stream, "system-view\n");

			VMPortmap::consumeOutput($stream);

			fwrite($stream, "interface ethernet 0/0\n");

			VMPortmap::consumeOutput($stream);

			foreach($cmds as $cmd) {
				fwrite($stream, $cmd."\n");
				VMPortmap::consumeOutput($stream);
			}

			return TRUE;
		}
		return "Failed to authenticate";
	}

	public static function getCurrentConfig() {
		$conn = ssh2_connect(GATEWAY_ADDRESS, 22);
		if (ssh2_auth_password($conn, GATEWAY_ADMIN, GATEWAY_PASSWORD)) {
			$stream = ssh2_shell($conn, 'xterm');
			if ($stream === FALSE) {
				return FALSE;
			}
			stream_set_blocking($stream, TRUE);

			VMPortmap::consumeOutput($stream);

			fwrite($stream, "display nat server\n");
			
			$buf = "";
			while($line = fread($stream, 4096)) {
				#echo "???".$line."???";
				$buf .= $line;
				if (preg_match('/\-\-\-\-$/', trim($line)) > 0) {
					fwrite($stream, " ");
				}else if (preg_match('/\<MAG\>$/', trim($line)) > 0) {
					break;
				}
			}
			$buf = trim(preg_replace('/[[:cntrl:]]+/', '', $buf));
			$buf = str_replace('---- More ----', '', $buf);
			$buf = str_replace('[16D', '', $buf);
			#echo $buf;
			$pattern = '/Protocol\s*\:\s*\d+\((?P<proto>[a-z]+?)\)\s*Global\s*:\s*(?P<pubaddr>[0-9.]+?)\s*\:\s*(?P<pubport>[0-9]+?)(\([a-zA-Z0-9]+\))?\s*Local\s*\:\s*(?P<priaddr>[0-9.]+?)\s*\:\s*(?P<priport>[0-9]+?)(\([a-zA-Z0-9]+\))?\s+/';
			if (preg_match_all($pattern, $buf, $matches) > 0) {
				#var_dump($matches);
				$conf = array();
				for($i = 0; $i < count($matches['proto']); $i ++) {
					$proto = $matches['proto'][$i];
					$pubaddr = $matches['pubaddr'][$i];
					$priaddr = $matches['priaddr'][$i];
					$pubport = $matches['pubport'][$i];
					$priport = $matches['priport'][$i];
					if ($pubport > 0 && $pubport < 50000) {

						$key = "{$proto}/{$pubaddr}/{$priaddr}/{$pubport}/{$priport}";
						#echo $key;
						$conf[$key] = 1;
					}
				}
				return $conf;
			}
			return FALSE;
		} else {
			return FALSE;
		}
	}

	public static function getLocalConfig(&$db) {
		$dbrow = $db->get_arrays("p.vc_protocol, p.siu_pub_port, p.siu_pri_port, n.vc_addr, t.vc_public_ip", "vm_guest_net_portmap_tbl p left join vm_guest_net_tbl n on p.guest_net_id = n.id left join vm_network_tbl t on n.net_id=t.id", "");
		if (!is_null($dbrow)) {
			$local_conf = array();
			foreach($dbrow as $row) {
				list($proto, $pub_port, $pri_port, $pri_addr, $pub_addr) = $row;
				$key = "{$proto}/{$pub_addr}/{$pri_addr}/{$pub_port}/{$pri_port}";
				$local_conf[$key] = 1;
			}
			return $local_conf;
		}
		return FALSE;
	}

	public static function syncConfig(&$db) {
		$local_conf = VMPortmap::getLocalConfig($db);
		if ($local_conf === FALSE) {
			return "Cannot get local config";
		}
		$current_conf = VMPortmap::getCurrentConfig();
		if ($current_conf === FALSE) {
			return "Cannot get current config";
		}
		$add = 0;
		$del = 0;
		$cmds = array();
		foreach($current_conf as $key=>$val) {
			if(!array_key_exists($key, $local_conf)) {
				list($proto, $pubaddr, $priaddr, $pubport, $priport) = explode("/", $key);
				$cmds[] = "undo nat server protocol {$proto} global {$pubaddr} {$pubport} inside {$priaddr} {$priport}";
				$del++;
			}
		}
		foreach($local_conf as $key=>$val) {
			if(!array_key_exists($key, $current_conf)) {
				list($proto, $pubaddr, $priaddr, $pubport, $priport) = explode("/", $key);
				$cmds[] = "nat server protocol {$proto} global {$pubaddr} {$pubport} inside {$priaddr} {$priport}";
				$add++;
			}
		}

		#var_dump($cmds);
		if (count($cmds) > 0) {
			VMPortmap::commitConfig($cmds);
		}

		$db->update("tiu_state=1", "vm_guest_net_portmap_tbl", "");

		$ret = array("add"=>$add, "del"=>$del);

		return $ret;
	}

}

?>
