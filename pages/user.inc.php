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
<script language='JavaScript'>
<!--

var SESSION_USER = '<?php echo $_SESSION['_user']; ?>';

function showRoles(records, panel, conf) {
}

function showGroups(records, panel, conf) {
}

function showUsers(records, panel, conf) {
	if(records.length == 0) {
		panel.innerHTML = "No records!";
	}else {
		var headers = [
			{
				title: "#",
				value: function(c, r) {
					c.innerHTML = r.id;
				}
			},
			{
				title: "Account",
				value: function(c, r) {
					c.innerHTML = r.vc_user;
				}
			},
			{
				title: "Name",
				value: function(c, r) {
					c.innerHTML = r.vc_name;
				}
			},
			{
				title: "",
				value: function(c, r) {
					var update_btn = newInputElement('button', '', '更新信息');
					c.appendChild(update_btn);
					update_btn.rec = r;
					update_btn.conf = conf;
					update_btn.__destruct = function() {
						this.rec = null;
						this.conf = null;
					};
					EventManager.Add(update_btn, 'click', function(ev, obj) {
						var p = getContentPanelConfigArea(obj.conf);
						var form = newDefaultRPCForm(p, 'UPDATE_USER', obj.conf.name, updateUserInfoValidate);
						form.appendChild(newInputElement('hidden', '_id', obj.rec.id));
						var tbl = newTableElement('', 0, 0, 2, '', 3, 2, 'left', 'middle', form);
						tblCell(tbl, 0, 0).innerHTML = '账号：';
						if(SESSION_USER != 'admin') {
							form.appendChild(newInputElement('hidden', '_vc_user', obj.rec.vc_user));
							tblCell(tbl, 0, 1).innerHTML = obj.rec.vc_user;
						}else {
							tblCell(tbl, 0, 1).appendChild(newInputElement('text', '_vc_user', obj.rec.vc_user));
						}
						tblCell(tbl, 1, 0).innerHTML = '姓名：';
						tblCell(tbl, 1, 1).appendChild(newInputElement('text', '_vc_name', obj.rec.vc_name));
						tblCell(tbl, 2, 1).appendChild(newInputElement('submit', '', '保存'));
						tblCell(tbl, 2, 1).appendChild(newCancelConfigButton(conf));
					});
					var passwd_btn = newInputElement('button', '', '修改密码');
					c.appendChild(passwd_btn);
					passwd_btn.rec = r;
					passwd_btn.conf = conf;
					passwd_btn.__destruct = function() {
						this.rec = null;
						this.conf = null;
					};
					EventManager.Add(passwd_btn, 'click', function(ev, obj) {
						var p = getContentPanelConfigArea(obj.conf);
						var form = newDefaultRPCForm(p, 'UPDATE_USER_PASSWD', obj.conf.name, updateUserPasswordValidate);
						form.appendChild(newInputElement('hidden', '_id', obj.rec.id));
						var tbl = newTableElement('', 0, 0, 2, '', 4, 2, 'left', 'middle', form);
						tblCell(tbl, 0, 0).innerHTML = '账号：';
						tblCell(tbl, 0, 1).innerHTML = obj.rec.vc_user;
						tblCell(tbl, 1, 0).innerHTML = '密码：';
						tblCell(tbl, 1, 1).appendChild(newInputElement('password', '_vc_password', ''));
						tblCell(tbl, 2, 0).innerHTML = '重复密码：';
						tblCell(tbl, 2, 1).appendChild(newInputElement('password', '_vc_password2', ''));
						tblCell(tbl, 3, 1).appendChild(newInputElement('submit', '', '修改'));
						tblCell(tbl, 3, 1).appendChild(newCancelConfigButton(conf));
					});
					var del_btn = newInputElement('button', '', '删除');
					c.appendChild(del_btn);
					del_btn.rec = r;
					del_btn.conf = conf;
					del_btn.__destruct = function() {
						this.rec = null;
						this.conf = null;
					};
					EventManager.Add(del_btn, 'click', function(ev, obj) {
						showConfirm({
							msg: '是否确定删除账户' + obj.rec.vc_user + '?',
							onOK: function() { updatePanelContentRPC(obj.conf.name, 'DEL_USER', {_id: obj.rec.id}); return true;},
							onCancel: function() {return true;}
						});
					});
				}
			}
		];
		newDBGrid(records, headers, "", panel);
	}
}

function updateUserInfoValidate(form) {
	with(form) {
		if(!checkDOM(_vc_name, /.+/, '姓名不能为空！')) {
			return false;
		}
	}
	return true;
}

function updateUserPasswordValidate(form) {
	with(form) {
		if(!checkDOM(_vc_password, /.+/, '新密码不能为空！')) {
			return false;
		}
		if(!checkDOM(_vc_password2, "^" + _vc_password.value + "$", '密码不一致！')) {
			return false;
		}
	}
	return true;
}

function userRecordValidation(form) {
	with(form) {
		if(!checkDOM(_vc_user, /.+/, '必须输入账号！')) {
			return false;
		}
		if(!checkDOM(_vc_password, /.+/, '必须输入密码！')) {
			return false;
		}
		if(!checkDOM(_vc_password2, "^" + _vc_password.value + "$", '密码不一致！')) {
			return false;
		}
	}
	return true;
}

function addUserRecord(conf, menuText, panel) {
	var form = newDefaultRPCForm(panel, 'ADD_USER', conf.name, userRecordValidation);

	var tbl = newTableElement('', 0, 0, 2, '', 5, 2, 'left', 'middle');
	form.appendChild(tbl);

	tblCell(tbl, 0, 0).innerHTML = '账号：';
	tblCell(tbl, 0, 1).appendChild(newInputElement('text', '_vc_user', ''));
	tblCell(tbl, 1, 0).innerHTML = '密码：';
	tblCell(tbl, 1, 1).appendChild(newInputElement('password', '_vc_password', ''));
	tblCell(tbl, 2, 0).innerHTML = '重复密码：';
	tblCell(tbl, 2, 1).appendChild(newInputElement('password', '_vc_password2', ''));

	tblCell(tbl, 3, 0).innerHTML = '姓名：';
	tblCell(tbl, 3, 1).appendChild(newInputElement('text', '_vc_name', ''));

	tblCell(tbl, 4, 1).appendChild(newInputElement('submit', '', '提交'));
	tblCell(tbl, 4, 1).appendChild(newCancelConfigButton(conf));
}

//-->
</script>
<table width=100% border=0><tr>
<td width=30% valign='top'>
<?php

showPanel2(0, '_group_panel', '组', PANEL_UNEXPANDABLE, null, 'showGroups', 
array(
'query_vars'       => '',
'query_tables'     => 'group_tbl',
'query_conditions' => '',
'query_order'      => ''
),
null);

?>
</td><td width=30% valign='top'>
<?

showPanel2(0, '_role_panel', '角色', PANEL_UNEXPANDABLE, null, 'showRoles', 
array(
'query_vars'       => '',
'query_tables'     => 'role_tbl',
'query_conditions' => '',
'query_order'      => ''
),
null);

?>
</td><td width=40% valign='top'>
<?

$config = array('menus'=>array('添加'), 'func'=>'addUserRecord');

if($_SESSION['_user'] !== 'admin') {
	$cond = "vc_user='{$_SESSION['_user']}'";
}else {
	$cond = '';
}

showPanel2(0, '_user_panel', '用户', PANEL_UNEXPANDABLE, $config, 'showUsers', 
array(
'query_vars'       => "id, vc_user, vc_name",
'query_tables'     => 'user_tbl',
'query_conditions' => $cond,
'query_order'      => 'id'
),
array('limit'=>25, 'position'=>PAGE_POSITION_BOTH));

?>
</td></tr></table>

<?

if($_SESSION['_user'] == 'admin') {
	include "admin_pass.inc.php";
}

?>
