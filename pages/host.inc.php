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
<script language='JavaScript'>
<!--

var _vm_net_records = <?php
$db = mysql_open();
$data = $db->get_assocs("id, vc_name", "vm_network_tbl", "", "dt_created");
if (!is_null($data)) {
        echo varPHP2JS($data);
}else {
        echo "[]";
}
?>;

//-->
</script>
<?php

$title = "<b>物理主机列表</b>";

if($_SESSION['_user'] === 'admin') {
	$config = array('menus'=>array('添加'), 'func'=>'addVMHostRecord');
}else {
	$config = null;
}

showPanel2(0, '_host_list', $title, PANEL_UNEXPANDABLE, $config, 'showHostList',
array(
'query_vars'       => 'vm_host_tbl.id, vc_name, vc_address, siu_memory, used_mem, live_vm_count, dt_created, vm_count, siu_disk_size, vm_disk_sz, vc_bridge_name, vc_vm_dir, vc_template_dir, vc_iso_dir',
'query_tables'     => 'vm_host_tbl left join vm_host_used_res_view on vm_host_tbl.id=vm_host_used_res_view.host_id left join vm_host_alloc_view on vm_host_tbl.id=vm_host_alloc_view.host_id',
'query_conditions' => '',
'query_order'      => 'vm_host_tbl.dt_created desc'
),
array('limit'=>25, 'position'=>PAGE_POSITION_BOTH));

?>
<div id='_vm_host_detailed_edit_pane'></div>
