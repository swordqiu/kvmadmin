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

print_header("KVM Administration Platform - 登录");

?>
<center><table border=0 width=240><tr><td><h1 align=center>OpenMAG Cloud</h1></td></tr></table></center>
<div id='_login_panel' align='center'></div>
<script language='JavaScript'>
<!--

function init_login_panel_callback(msg) {
	if(isErrorMsg(msg)) {
		showAlert(msg);
	}else {
		refresh();
	}
}

function init_login_panel(panel) {

        var form = newRPCForm({
		action: 'LOGIN',
		callback: 'init_login_panel_callback'
        }, panel);

        var tbl = newTableElement("", 0, 0, 2, '', 3, 2, 'right', 'middle', form);

        tblCell(tbl, 0, 0).innerHTML = '账号：';
        tblCell(tbl, 0, 1).appendChild(newHintTextInput('_mag_user', 'admin', 'MAG管理账号', 150));
        tblCell(tbl, 1, 0).innerHTML = '密码：';
        tblCell(tbl, 1, 1).appendChild(newHintPasswordInput('_mag_password', 'MAG管理账号密码', 150));

	tblCell(tbl, 2, 1).align = 'center';
        tblCell(tbl, 2, 1).appendChild(newInputElement('submit', '', '提交'));
}

EventManager.Add(window, 'load', function(ev, obj) {
	init_login_panel(document.getElementById('_login_panel'));
});


//-->
</script>
