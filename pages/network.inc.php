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

var _vm_guest_net_records = <?php

$db = mysql_open();
$dbrows = $db->get_assocs("n.id as val, CONCAT(g.vc_name, '(', n.vc_addr, '->', m.vc_public_ip, ')') as txt", "vm_guest_net_tbl n left join vm_guest_tbl g on n.guest_id=g.id left join vm_network_tbl m on n.net_id=m.id", "m.vc_public_ip!='' or m.vc_public_ip is not null", "n.dt_created asc");
if (!is_null($dbrows)) {
	echo varPHP2JS($dbrows);
}else {
	echo "[]";
}

?>;

//-->
</script>
<?php

$title = "<b>网络列表</b>";

if($_SESSION['_user'] === 'admin') {
	$config = array('menus'=>array('添加'), 'func'=>'addNetworkRecord');
}else {
	$config = null;
}

showPanel2(0, '_network_list', $title, PANEL_UNEXPANDABLE, $config, 'showNetworkList',
array(
'query_vars'       => 'id, vc_name, vc_gateway, vc_public_ip, tiu_netmask, siu_vlan_id, vc_addr_start, vc_addr_end, dt_created',
'query_tables'     => 'vm_network_tbl',
'query_conditions' => '',
'query_order'      => 'dt_created asc'
),
array('limit'=>25, 'position'=>PAGE_POSITION_BOTH));


if($_SESSION['_user'] === 'admin') {
	$config = array('menus'=>array('添加', '部署端口映射'), 'func'=>'networkPortmapConfig');
}else {
	$config = null;
}

showPanel2(0, '_network_port_map_list', '网络端口映射配置', PANEL_UNEXPANDABLE, $config, 'showNetworkPortMapList',
array(
'query_vars'       => 'p.id, p.vc_protocol, p.siu_pub_port, p.siu_pri_port, n.vc_addr, t.vc_public_ip, p.dt_created, g.vc_name, t.vc_name as pub_name, p.tiu_state',
'query_tables'     => 'vm_guest_net_portmap_tbl p left join vm_guest_net_tbl n on p.guest_net_id = n.id left join vm_network_tbl t on n.net_id=t.id left join vm_guest_tbl g on n.guest_id=g.id',
'query_conditions' => '',
'query_order'      => 'p.dt_created asc'
),
array('limit'=>25, 'position'=>PAGE_POSITION_BOTH));

?>
