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

/* check access */
if ($config["access_level"] < ARA_ACCESS_VIEW)
	throw new Exception($i18n["i18n_action_not_allowed"]);

$tpl->setCurrentBlock("module_output");
$tpl->addBlockfile("module_generated", "foo", "template.html");
$tpl->setCurrentBlock("nas_info");

$tpl->setGlobalVariable("current_group", $mg);
$tpl->setGlobalVariable("current_module", $mm);

$choosenNas = array();
if (!isset($_GET["nas"]) || $_GET["nas"] == "") {
	/* no user set, choose one */
	redirectPage(array("mm" => "find", "mg" => "misc", "nmm" => $mm,
	                   "nmg" => $mg, "type" => "nas"));
}
else if (!is_array($choosenNas = getNas($_GET["nas"]))) {
	redirectPage(array("mm" => "edit", "mg" => $mg, "nas" => $_GET["nas"]));
}
else {
	$nas_name = $_GET["nas"];
	$old_dir = getcwd();
	chdir("..");
	include("common.php");
	generateCommonMenu($nas_name);
	chdir($old_dir);

	$tpl->setVariable("ports_max", $choosenNas[SQL_NAS_COLUMN_PORTS]);

	/* SESSIONS */
	$activeSessions = getNasActiveSessions($choosenNas[SQL_NAS_COLUMN_IP]);

	$tpl->setVariable("active_sessions", count($activeSessions));

	foreach (array("weekly" => 7, "daily" => 1, "monthly" => 30)
	         as $periodStr => $period) {
		$transf = getNasTransferedOctets(
			$choosenNas[SQL_NAS_COLUMN_IP], $period
		);
		$tpl->setVariable($periodStr . "_upload", bytesToStr($transf[SQL_ACCT_COLUMN_UPLOAD]));
		$tpl->setVariable($periodStr . "_download", bytesToStr($transf[SQL_ACCT_COLUMN_DOWNLOAD]));
	}
}

?>
