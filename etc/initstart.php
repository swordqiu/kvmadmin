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
#!/usr/bin/php
<?php

$KVMADMIN_HOME = dirname(__FILE__)."/../";
include_once($KVMADMIN_HOME."config.inc.php");
define("LIBUI_DB_QUERY_SCRIPT", "");
define("LIBUI_REQUEST_HANDLER_SCRIPT", "");
define("LIBUI_RPC_SCRIPT", "");
include_once(JIALIB_PATH."/jialib.inc");
include_once($KVMADMIN_HOME."include/host.class.inc");
include_once($KVMADMIN_HOME."include/network.class.inc");
include_once($KVMADMIN_HOME."include/template.class.inc");
include_once($KVMADMIN_HOME."include/guest.class.inc");
include_once($KVMADMIN_HOME."include/portmap.class.inc");

$db = mysql_open();

VMGuest::startAllVMGuest($db);

?>
