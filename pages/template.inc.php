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

$title = "<b>模板列表</b>";

$config = null;

showPanel2(0, '_network_list', $title, PANEL_UNEXPANDABLE, $config, 'showTemplateList',
array(
'query_vars'       => 'id, vc_name, vc_os, vc_version, tiu_bits, siu_size, vc_lang, dt_created',
'query_tables'     => 'vm_template_tbl',
'query_conditions' => '',
'query_order'      => 'dt_created desc'
),
array('limit'=>25, 'position'=>PAGE_POSITION_BOTH));

?>
