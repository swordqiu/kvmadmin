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

$title = "<b>日志记录</b>";

$cond = "";

if($_SESSION['_user'] != 'admin') {
	$cond .= "vm_log_tbl.vc_user='{$_SESSION['_user']}'";
}

if(!empty($_host_id)) {
	if(!empty($cond)) {
		$cond .= " and ";
	}
	$cond .= "vm_log_tbl.host_id={$_host_id}";
}

if(!empty($_vmname)) {
	if(!empty($cond)) {
		$cond .= " and ";
	}
	$cond .= "vm_log_tbl.vc_name='{$_vmname}'";
}

showPanel2(0, '_kvmlogs', $title, PANEL_UNEXPANDABLE, null, 'showKvmLogs', 
array(
'query_vars'       => 'vm_log_tbl.id, vm_log_tbl.dt_when, vm_log_tbl.host_id, vm_host_tbl.vc_name host_name, vm_log_tbl.vc_name as guest_name, vm_log_tbl.vc_user, vm_log_tbl.notes',
'query_tables'     => '(vm_log_tbl left join vm_guest_tbl on vm_log_tbl.host_id=vm_guest_tbl.host_id and vm_log_tbl.vc_name=vm_guest_tbl.vc_name) left join vm_host_tbl on vm_log_tbl.host_id=vm_host_tbl.id',
'query_conditions' => $cond,
'query_order'      => 'vm_log_tbl.dt_when desc'
),
array('limit'=>25, 'position'=>PAGE_POSITION_BOTH));

?>
