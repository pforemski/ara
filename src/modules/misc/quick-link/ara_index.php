<?php
/* Asn Radius Admin
 * Copyright (C) ASN http://www.asn.pl 2005
 *           and Dawid Ciezarkiewicz <dpc@asn.pl> 2005
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later
 * version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software Foundation,
 * Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/* will not check access - it's only redirector */
if (!isset($_GET["type"]) || $_GET["type"] == "") {
	/* no type - redirect to main */
	redirectPage(array("mm" => "", "mg" => ""));
} else {
	switch ($_GET["type"]) {
	case "user-by-login":
		setcookie("quicksel", "qubsel_by_login");
		redirectPage(
			array("mm" => "list", "mg" => "user", "login" => $_GET["value"])
		);
		break;
	case "nas-by-name":
		setcookie("quicksel", "qubsel_nas_by_name");
		redirectPage(
			array("mm" => "info", "mg" => "nas", "nas" => $_GET["value"])
		);
		break;
	case "user-by-phone":
		setcookie("quicksel", "qubsel_by_phone");
		redirectPage(
			array("mm" => "list", "mg" => "user", "phone" => $_GET["value"])
		);
		break;
	case "user-by-name":
		setcookie("quicksel", "qubsel_by_name");
		redirectPage(
			array("mm" => "list", "mg" => "user", "name" => $_GET["value"])
		);
		break;
	case "user-by-mac":
		setcookie("quicksel", "qubsel_by_mac");
		redirectPage(
			array("mm" => "list", "mg" => "user", "mac" => $_GET["value"])
		);
		break;
	case "user-by-address":
		setcookie("quicksel", "qubsel_by_address");
		redirectPage(
			array("mm" => "list", "mg" => "user", "address" => $_GET["value"])
		);
		break;
	default:
		throw new Exception ("unknown type");
	}
}
?>
