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
<div id='_change_admin_password'></div>
</td></tr></table>
<?php

if($_SESSION['_user'] == 'admin') {
?>
<script language='JavaScript'>
<!--

function init_change_admin_password_callback(msg) {
	if(isErrorMsg(msg)) {
		showAlert(msg);
	}else {
		showAsyncMsg('更改成功！');
		var field = document.getElementById('_old_password');
		if (field) {
			field.value = '';
		}
		field = document.getElementById('_new_password');
		if (field) {
			field.value = '';
		}
		field = document.getElementById('_new_password2');
		if (field) {
			field.value = '';
		}
	}
}

function init_change_admin_password_validate(form) {
	with(form) {
		if(!checkDOMNonempty(_new_password, "密码不能为空！")) {
			return false;
		}
		if(!checkDOMEqual(_new_password2, _new_password.value, "两次输入的新密码不一致！")) {
			return false;
		}
	}
	return true;
}

function init_change_admin_password(panel) {
	var form = newRPCForm({
		action: 'CHANGE_ADMIN_PASSWORD',
		callback: 'init_change_admin_password_callback',
		onsubmit: init_change_admin_password_validate
	}, panel);

	var tbl = newTableElement('', 0, 0, 2, '', 5, 2, ['right', 'left'], 'middle', form);

	tblCell(tbl, 0, 0).innerHTML = '管理员(admin)密码设置';
	tblCell(tbl, 0, 0).style.fontWeight = 'bold';
        tblCell(tbl, 0, 0).align = 'left';

	tblCell(tbl, 1, 0).innerHTML = '当前密码：';
	tblCell(tbl, 1, 1).appendChild(newInputElement('password', '_old_password', ''));

	tblCell(tbl, 2, 0).innerHTML = '新密码：';
	tblCell(tbl, 2, 1).appendChild(newInputElement('password', '_new_password', ''));

	tblCell(tbl, 3, 0).innerHTML = '重复新密码：';
	tblCell(tbl, 3, 1).appendChild(newInputElement('password', '_new_password2', ''));

	tblCell(tbl, 4, 1).appendChild(newInputElement('submit', '', '修改'));
}

EventManager.Add(window, 'load', function(ev, obj) {
	init_change_admin_password(document.getElementById('_change_admin_password'));
});

//-->
</script>
<?php
}

?>
