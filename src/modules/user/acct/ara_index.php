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
if (!isset($_GET["user"]) || $_GET["user"] == "") {
	/* no user set, choose one */
	redirectPage(array("mm" => "find", "mg" => "misc", "nmm" => $mm,
	                   "nmg" => $mg ,"type" => "user"));
} else {
	redirectPage(array("mm" => "generic", "mg" => "acct",
		"search" => "login", "value" => $_GET["user"]));
}
?>
