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

$_menu_config = array(
	"HOME" => array(
		"TITLE"=>"系统状态",
		"MENU"=>"首页",
		"PAGE"=>"home.inc.php"),
	"HOST" => array(
		"TITLE"=>"主机列表",
		"MENU"=>"主机",
		"PAGE"=>"host.inc.php"),
	"NETWORK" => array(
		"TITLE" => "网络列表",
		"MENU"=>"网络",
		"PAGE"=>"network.inc.php"),
	"TEMPLATE" => array(
		"TITLE" => "模板列表",
		"MENU" => "模板",
		"PAGE" => "template.inc.php"),
	"GUEST" => array(
		"TITLE"=>"虚拟机列表",
		"MENU"=>"虚拟机", 
		"PAGE"=>"guest.inc.php"),
	"LOG" => array(
		"TITLE"=>"管理日志",
		"MENU"=>"日志",
		"PAGE"=>"log.inc.php"),
	"USER" => array(
		"TITLE"=>"用户设置",
		"MENU"=>"",
		"PAGE"=>"user.inc.php")
	);

$_default_menu = "HOME";
if (array_key_exists('_menu', $_REQUEST)) {
	$_current_menu = $_REQUEST["_menu"];
} else {
	$_current_menu = '';
}
if(empty($_current_menu)) {
	$_current_menu = $_default_menu;
}

function get_menu($mid, $txt) {
	global $_menu_config, $_default_menu, $_current_menu;
	if($_current_menu == $mid) {
		return "<font color=white>{$txt}</font>";
	}else {
		return "<a href='{$_SERVER["PHP_SELF"]}?_menu={$mid}'>{$txt}</a>";
	}
}

function print_title() {
	global $_menu_config, $_menu;

	$title = $_menu_config[$_menu]["TITLE"];

	print_header("Anhe KVM Administration Platform - {$title}");
	echo "<table border=0 width='100%'>";
	echo "<tr><td style='border-bottom: 1px solid #000000;'>";
	echo "<ul class='menu_list'>";
	echo "<li><a href='logout.php'>退出</a></li>";
	echo "<li>".get_menu("USER", "用户设置")."</li>";
	echo "</ul>";
	echo "</td></tr>";
	echo "<tr><td align='center'><h1 style='padding: 10px'>OpenMAG云平台管理与配置</h1></td></tr>";
	echo "<tr><td align='left' bgcolor='#00af10'><table border=0><tr><td align=left><table border=0 cellspacing=5><tr>";
	foreach($_menu_config as $key=>$val) {
		echo "<td align=center width=60>";
		if(!empty($val["MENU"])) {
			echo get_menu($key, $val["MENU"]);
		}
		echo "</td>";
	}
	echo "</tr></table></td>";
	echo "<td align=right></td>";
	echo "</tr></table></td></tr>";
	echo "</table>";
}

function print_footnote() {
	echo "<hr><p align='center' style='font-size:12px;'><a href='http://openmag.mobi'>OpenMAG.mobi版权所有（2012/6）  版本: ".VERSION."</p><p align='center' style='font-size:14px;'><a href='/ganglia/'>Ganglia</a></p>\n";
	print_footer();
}

?>
