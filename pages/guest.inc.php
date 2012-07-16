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
var _vm_host_records = <?php
$db = mysql_open();
$data = $db->get_assocs("id, vc_name", "vm_host_tbl", "", "dt_created");
if(!is_null($data)) {
	echo varPHP2JS($data);
}else {
	echo "[]";
}
?>;
var _vm_net_records = <?php
$data = $db->get_assocs("id, vc_name", "vm_network_tbl", "", "dt_created");
if (!is_null($data)) {
	echo varPHP2JS($data);
}else {
	echo "[]";
}
?>;
var _vm_template_records = <?php
$data = $db->get_assocs("id, CONCAT(vc_os, '_', vc_version, '-', vc_name, '-', tiu_bits, 'bits_', vc_lang) as name", "vm_template_tbl", "", "dt_created");
if (!is_null($data)) {
	echo varPHP2JS($data);
}else {
	echo "[]";
}
?>;
//-->
</script>
<?php

$title = "<b>虚拟主机列表</b>";

$cond = "";

if($_SESSION['_user'] != 'admin') {
	$cond = "g.vc_user='{$_SESSION['_user']}'";
}

$config = array('menus'=>array('添加'), 'func'=>'addVMGuestRecord');

showPanel2(0, '_guest_list', $title, PANEL_UNEXPANDABLE, $config, 'showGuestList', 
array(
'query_vars'       => 'g.id, g.host_id, h.vc_name as host_name, g.vc_name, g.siu_memory, g.siu_vnc_port, g.tiu_state, g.vc_user, g.dt_created, n.vc_addr, n.vc_mac, d.siu_disk_sz, g.vc_iso',
'query_tables'     => 'vm_guest_tbl g left join vm_guest_nic_view n on g.id=n.guest_id left join vm_guest_disk_view d on g.id=d.guest_id left join vm_host_tbl h on g.host_id=h.id',
'query_conditions' => $cond,
'query_order'      => 'g.dt_created asc'
),
array('limit'=>25, 'position'=>PAGE_POSITION_BOTH));

?>
<div id='_vm_guest_detailed_edit_pane'></div>
