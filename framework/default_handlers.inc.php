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

session_start();

#if(empty($_SESSION['_user'])) {
#	die("Session expires!");
#}

function rawInit(&$req) {
	if(null === mysql_open($req->_mysql_host, $req->_mysql_user, $req->_mysql_password, $req->_mysql_db, $req->_mysql_port)) {
		$req->error("MySQL数据库设置错误！");
		return FALSE;
	}
	$fh = fopen(CUSTOM_CONFIG, "w");
	if(FALSE !== $fh) {
		fwrite($fh, "<?php\n");
		fwrite($fh, "\$_admin_password=\"".encrypt_password($req->_admin_password)."\";\n");
		fwrite($fh, "\$_mysql_host=\"{$req->_mysql_host}\";\n");
		fwrite($fh, "\$_mysql_user=\"{$req->_mysql_user}\";\n");
		fwrite($fh, "\$_mysql_password=\"{$req->_mysql_password}\";\n");
		fwrite($fh, "\$_mysql_port=\"{$req->_mysql_port}\";\n");
		fwrite($fh, "\$_mysql_db=\"{$req->_mysql_db}\";\n");
		fwrite($fh, "?>\n");
	}else {
		$req->error("不能写".CUSTOM_CONFIG);
		return FALSE;
	}
	return TRUE;
}
$handler->register("RAW_INIT", "rawInit");


function login(&$req) {
	global $_admin_password;
	if($req->_mag_user == 'admin') {
		if(encrypt_password($req->_mag_password) !== $_admin_password) {
			$req->error("密码不正确!");
			return FALSE;
		}
	}else {
		$db = mysql_open();
		if($db->get_item_count("user_tbl", "vc_user='{$req->_mag_user}' and vc_password='".encrypt_password($req->_mag_password)."'") == 0) {
			$req->error("密码不正确!");
			return FALSE;
		}
	}
	session_start();
	$_SESSION['_user'] = $req->_mag_user;
	return TRUE;
}
$handler->register("LOGIN", "login");


function change_admin_password(&$req) {
        $fh = fopen(CUSTOM_CONFIG, "w");
        if(FALSE !== $fh) {
                fwrite($fh, "<?php\n");
                fwrite($fh, "\$_admin_password=\"".encrypt_password($req->_admin_password)."\";\n");
                fwrite($fh, "\$_mysql_host=\"".MYSQL_DB_HOST."\";\n");
                fwrite($fh, "\$_mysql_user=\"".MYSQL_DB_USER."\";\n");
                fwrite($fh, "\$_mysql_password=\"".MYSQL_DB_PASS."\";\n");
                fwrite($fh, "\$_mysql_port=\"".MYSQL_DB_PORT."\";\n");
                fwrite($fh, "\$_mysql_db=\"".MYSQL_DB_NAME."\";\n");
                fwrite($fh, "?>\n");
        }else {
                $req->error("不能写".CUSTOM_CONFIG);
                return FALSE;
        }
	return TRUE;
}
$handler->register("CHANGE_ADMIN_PASSWORD", "change_admin_password");

function add_user(&$req) {
	$db = mysql_open();
	if($db->get_item_count("user_tbl", "vc_user='{$req->_vc_user}'") == 0) {
		$sql = "insert into user_tbl(vc_user, vc_password, vc_roles, iu_group, vc_groups, vc_name) values('{$req->_vc_user}', '".encrypt_password($req->_vc_password)."', '', 0, '', '{$req->_vc_name}')";
		if(!$db->query($sql)) {
			$req->error("插入用户数据出错！");
			return FALSE;
		}
	}else {
		$req->error("账号{$req->_vc_user}已经被人使用！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("ADD_USER", "add_user");

function del_user(&$req) {
	$db = mysql_open();
	if(!$db->delete("user_tbl", "id={$req->_id}")) {
		$req->error("账户不存在！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("DEL_USER", "del_user");

function update_user(&$req) {
	$db = mysql_open();
	if($db->get_item_count("user_tbl", "id!={$req->_id} and vc_user='{$req->_vc_user}'") > 0) {
		$req->error("该用户名已经被使用！");
		return FALSE;
	}else {
		if(!$db->update("vc_user='{$req->_vc_user}', vc_name='{$req->_vc_name}'", "user_tbl", "id={$req->_id}")) {
			$req->error("更新用户信息出错！"."vc_name='{$req->_vc_name}'"."id={$req->_id}");
			return FALSE;
		}
	}
	return TRUE;
}
$handler->register("UPDATE_USER", "update_user");

function update_user_password(&$req) {
	$db = mysql_open();
	$passwd = encrypt_password($req->_vc_password);
	if(!$db->update("vc_password='{$passwd}'", "user_tbl", "id={$req->_id}")) {
		$req->error("更新密码出错！");
		return FALSE;
	}
	return TRUE;
}
$handler->register("UPDATE_USER_PASSWD", "update_user_password");

?>
