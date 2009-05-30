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
$tpl->setCurrentBlock("nas_transfers");

$tpl->setGlobalVariable("current_group", $mg);
$tpl->setGlobalVariable("current_module", $mm);


if (isset($_GET["limit"])) {
	$limit = $_GET["limit"];
} else {
	$limit = 30;
}

if (isset($_GET["days"])) {
	$days = $_GET["days"];
} else {
	$days = 7;
}

if (isset($_GET["reverse"])) {
	$reverse = true;
} else {
	$reverse = false;
}

if (isset($_GET["sort-by"]) && $_GET["sort-by"] == "upload") {
	$sort_by = SQL_ACCT_COLUMN_UPLOAD;
} else {
	$sort_by = SQL_ACCT_COLUMN_DOWNLOAD;
}

$base_form_url = generateUrl(array());
$tpl->setGlobalVariable("base_form_url", $base_form_url);
$tpl->setGlobalVariable("limit", $limit);
$tpl->setGlobalVariable("days", $days);

/* print rates */
$rates = getTopNasTransferedOctets($days, $limit, $sort_by, $reverse);
$tpl->setCurrentBlock("nas_list");

$count = 1;
foreach ($rates as $nas) {
	$choosenNas = getNasBy(SQL_NAS_COLUMN_IP, $nas[SQL_ACCT_COLUMN_NASIP]);

	if (!$choosenNas) {
		continue;
	}

	$tpl->setVariable("nas_no", $count);
	$count++;
	$tpl->setVariable("nas_name", $choosenNas[SQL_NAS_COLUMN_NAME]);
	$tpl->setVariable("nas_name_title", $choosenNas[SQL_NAS_COLUMN_DESCRIPTION]);
	$tpl->setVariable("nas_name_url", generateUrl(
		array( "mm" => "info", "mg" => "nas",
		"nas" => $choosenNas[SQL_NAS_COLUMN_NAME])
	));
	$tpl->setVariable("nas_ports_max", $choosenNas[SQL_NAS_COLUMN_PORTS]);

	$activeSessions = getNasActiveSessions($choosenNas[SQL_NAS_COLUMN_IP]);

	$tpl->setVariable("nas_active_sessions", count($activeSessions));

	$tpl->setVariable("nas_upload", bytesToStr($nas[SQL_ACCT_COLUMN_UPLOAD]));
	$tpl->setVariable("nas_download", bytesToStr($nas[SQL_ACCT_COLUMN_DOWNLOAD]));
	$tpl->parse("nas_list");
}

?>
