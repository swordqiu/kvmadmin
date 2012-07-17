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

print_header("KVM Administration Platform - 初始配置");

?>
<h1 align='center' style='padding:30px;'>KVM管理平台初始化设置</h1>
<div id='_raw_init_panel' align='center'></div>
<script language='JavaScript'>
<!--

function init_raw_panel_callback(msg) {
	if(isErrorMsg(msg)) {
		showAlert(msg);
	}else {
		refresh();
	}
}

function init_raw_panel_validate(form) {
	with(form) {
		if(!checkDOM(_admin_password, ".+", '必须输入管理员密码！')) {
			return false;
		}
		if(!checkDOM(_admin_password2, "^" + _admin_password.value + "$", '管理员密码不一致！')) {
			return false;
		}
		if(!checkDOM(_mysql_host, ".+", '请输入MySQL服务器地址！')) {
			return false;
		}
		if(!checkDOM(_mysql_user, ".+", '请输入MySQL账号！')) {
			return false;
		}
		if(!checkDOM(_mysql_password, ".+", '请输入MySQL账号密码！')) {
			return false;
		}
		if(!checkDOM(_mysql_port, ".+", '请输入MySQL服务器端口号！')) {
			return false;
		}
		if(!checkDOM(_mysql_db, ".+", '请输入MySQL数据库名称！')) {
			return false;
		}
	}
	return true;
}

function init_raw_panel(panel) {

	var form = newRPCForm({
		action: 'RAW_INIT',
		callback: 'init_raw_panel_callback',
		onsubmit: init_raw_panel_validate
	}, panel);

	var tbl = newTableElement("", 0, 0, 2, '', 8, 2, 'right', 'middle', form);

	tblCell(tbl, 0, 0).innerHTML = '管理员密码：';
	tblCell(tbl, 0, 1).appendChild(newInputElement('password', '_admin_password', ''));
	tblCell(tbl, 1, 0).innerHTML = '重复密码：';
	tblCell(tbl, 1, 1).appendChild(newInputElement('password', '_admin_password2', ''));

	tblCell(tbl, 2, 0).innerHTML = 'MySQL服务器地址：';
	tblCell(tbl, 2, 1).appendChild(newInputElement('text', '_mysql_host', '<?php echo $_mysql_host; ?>'));
	tblCell(tbl, 3, 0).innerHTML = 'MySQL账号：';
	tblCell(tbl, 3, 1).appendChild(newInputElement('text', '_mysql_user', '<?php echo $_mysql_user; ?>'));
	tblCell(tbl, 4, 0).innerHTML = 'MySQL账号密码：';
	tblCell(tbl, 4, 1).appendChild(newInputElement('password', '_mysql_password', ''));
	tblCell(tbl, 5, 0).innerHTML = 'MySQL端口号：';
	tblCell(tbl, 5, 1).appendChild(newInputElement('text', '_mysql_port', '<?php echo $_mysql_port; ?>'));
	tblCell(tbl, 6, 0).innerHTML = 'MySQL数据库：';
	tblCell(tbl, 6, 1).appendChild(newInputElement('text', '_mysql_db', '<?php echo $_mysql_db; ?>'));

	tblCell(tbl, 7, 1).align = 'center';
	tblCell(tbl, 7, 1).appendChild(newInputElement('submit', '', '提交'));
}

EventManager.Add(window, 'load', function(ev, obj) {
	init_raw_panel(document.getElementById('_raw_init_panel'));
});

//-->
</script>
