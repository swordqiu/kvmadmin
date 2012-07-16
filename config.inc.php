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

$_mysql_host = "127.0.0.1";
$_mysql_user = "root";
$_mysql_password = "";
$_mysql_port = "3306";
$_mysql_db   = "kvm";

define("LOCAL_CONFIG_DIR", dirname(__FILE__).DIRECTORY_SEPARATOR."local");
define("CUSTOM_CONFIG", LOCAL_CONFIG_DIR. DIRECTORY_SEPARATOR. "custom_config.php");

if (file_exists(CUSTOM_CONFIG)) {
	include_once(CUSTOM_CONFIG);
}

define("VERSION", "0.1");

define("MYSQL_DB_HOST", $_mysql_host);
define("MYSQL_DB_USER", $_mysql_user);
define("MYSQL_DB_PASS", $_mysql_password);
define("MYSQL_DB_PORT", $_mysql_port);

define("MYSQL_DB_NAME", $_mysql_db);

define("SITE_BASE_DIR", dirname(__FILE__));

if(!defined("SITE_BASE_URL")) {
	if(isset($_SERVER) && array_key_exists('HTTP_HOST', $_SERVER) && !empty($_SERVER['HTTP_HOST'])) {
		define("SITE_BASE_URL", dirname($_SERVER['SCRIPT_NAME']));
	}
}

define("JIALIB_PATH", SITE_BASE_DIR."/../jialib0.3/");

define("LOG_DIR", LOCAL_CONFIG_DIR.DIRECTORY_SEPARATOR."log".DIRECTORY_SEPARATOR);

define("LIBUI_USER_SCRIPTS_DIR", SITE_BASE_DIR."/scripts");

if (defined("SITE_BASE_URL")) {
	define("LIBUI_DB_QUERY_SCRIPT", SITE_BASE_URL."/framework/db_query.php");
	define("LIBUI_REQUEST_HANDLER_SCRIPT", SITE_BASE_URL."/request_handler.php");
	define("LIBUI_RPC_SCRIPT", SITE_BASE_URL."/rpc_handler.php");
	define("LIBUI_USER_SCRIPTS_DIR_URL", SITE_BASE_URL."/scripts");
}

/*$_os_list = array(
"Debian Linux 6.0 64bit"    => array("iso"=>"/home/vm/iso/debian-604-amd64-CD-1.iso", "image"=>"/home/vm/repository/debian60_64.img",    "disk_size"=>8, "nic"=>"e1000"),
"Debian Linux 5.0 64bit"    => array("iso"=>"/home/vm/iso/debian-506-amd64-CD-1.iso", "image"=>"/home/vm/repository/debian50_64.img",    "disk_size"=>8, "nic"=>"e1000"),
"Windows Server 2003 32bit" => array("iso"=>"/home/vm/iso/winsrv03sp2.iso", "image"=>"/home/vm/repository/winsrv03chs_32.img", "disk_size"=>16, "nic"=>"rtl8139"),
"Windows Server 2003 R2 32bit EN" => array("iso"=>"/home/vm/iso/winsrv03r2en.iso", "image"=>"/home/vm/repository/winsrv03en_32.img", "disk_size"=>16, "nic"=>"rtl8139"),
"Windows Server 2008 R2 64bit" => array("iso"=>"/home/vm/iso/winsrv08r2x64.iso", "image"=>"/home/vm/repository/winsrv08_64.img", "disk_size"=>30, "nic"=>"rtl8139"),
#"Windows Server 2003 32bit with Exchange" => array("iso"=>"/home/vm/iso/winsrv03sp2.iso", "image"=>"/home/vm/repository/winsrv03chs_exchg_32.img", "disk_size"=>16, "nic"=>"rtl8139")
);*/

# 是否将 jialib 置于 Debug 状态
define("LIBUI_AJAX_DEBUG", FALSE);
define("LIBUI_SCRIPTS_EMBEDDED", TRUE);
define("LIBDB_REMOVE_EMPTY_FIELDS", FALSE);
define("LIBUI_DB_QUERY_DEBUG", FALSE);

define("SSH_USERNAME", "vm");
define("SSH_PRIVATE_KEY_PATH", SITE_BASE_DIR.DIRECTORY_SEPARATOR."etc".DIRECTORY_SEPARATOR."id_rsa");
define("SSH_PUBLIC_KEY_PATH", SSH_PRIVATE_KEY_PATH.".pub");

define("VM_START_SCRIPT", SITE_BASE_DIR.DIRECTORY_SEPARATOR."etc".DIRECTORY_SEPARATOR."vmstart");
define("VM_STATUS_SCRIPT", SITE_BASE_DIR.DIRECTORY_SEPARATOR."etc".DIRECTORY_SEPARATOR."vmstatus");
define("VM_STOP_SCRIPT", SITE_BASE_DIR.DIRECTORY_SEPARATOR."etc".DIRECTORY_SEPARATOR."vmstop");
define("BR0_START_SCRIPT", SITE_BASE_DIR.DIRECTORY_SEPARATOR."etc".DIRECTORY_SEPARATOR."ovs-ifup");
define("BR0_STOP_SCRIPT", SITE_BASE_DIR.DIRECTORY_SEPARATOR."etc".DIRECTORY_SEPARATOR."ovs-ifdown");

define("KVM_COMMAND", "qemu-system-x86_64");
define("KVM_IMAGE_COMMAND", "qemu-img");

define("VNC_PROXY_COMMAND", SITE_BASE_DIR.DIRECTORY_SEPARATOR."etc".DIRECTORY_SEPARATOR."vncproxy");

define("GATEWAY_ADDRESS", "192.168.0.1");
define("GATEWAY_ADMIN", "magadmin");
define("GATEWAY_PASSWORD", "123@openmag");

?>
