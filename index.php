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

error_reporting(E_ALL|E_STRICT);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
ini_set('session.gc_probability', '0');
ini_set('default_socket_timeout', '1200');
ini_set('mysql.connect_timeout', '-1');


if (!file_exists(dirname(__file__) . "/config.inc.php"))
{
    echo "No config.inc.php found!";
    exit(-1);
}

if (defined("SITE_BASE_URL") && preg_match('/127\.0\.0\.\d+|localhost|localdomain/', $_SERVER['HTTP_HOST']) > 0) {
	echo "Please do not visit this site with local address (127.0.0.1, localhost, ::1, etc.)!";
	exit(-1);
}

include_once("config.inc.php");
include_once(JIALIB_PATH."/jialib.inc");
include_once("include/format.inc.php");
include_once("framework/db_init.inc.php");

session_start();

if (empty($_SESSION['_user']))
{
    if (!isset($_admin_password))
    {
	$ret = verify_env();
	if ($ret !== TRUE) {
            echo $ret;
	}else {
            $_SESSION['_state'] = "raw_config";
            include "write_custom_config.php";
        }
    }
    else
    {
        include "login.php";
    }
}
else
{
    if (empty($_menu))
    {
        $_menu = $_default_menu;
    }

    print_title($_menu);

    include_once(dirname(__FILE__)."/pages/".$_menu_config[$_menu]["PAGE"]);

}

print_footnote();

?>
