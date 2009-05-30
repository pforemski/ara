<?php
/* Asn Radius Admin
 * Copyright (C) ASN http://www.asn.pl 2005
 *           and Dawid Ciezarkiewicz <arael@asn.pl> 2005
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

if ($config["access_level"] < ARA_ACCESS_VIEW)
	throw new Exception($i18n["i18n_action_not_allowed"]);

$tpl->setCurrentBlock("module_output");
$tpl->addBlockfile("module_generated", "foo", "template.html");
$tpl->setCurrentBlock("user_stats");

$tpl->setGlobalVariable("current_group", $mg);
$tpl->setGlobalVariable("current_module", $mm);

if (isset($_GET["limit"])) {
	$limit = $_GET["limit"];
} else {
	$limit = 10;
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

if ($config["sql_user_extension"]) {
	$tpl->touchBlock("use_real_name");
}

/* print rates */
$rates = getTopUserTransferedOctets($days, $limit, $sort_by, $reverse);
$tpl->setCurrentBlock("user_list");

$count = 1;

foreach ($rates as $user) {
	$user_name = $user["UserName"];
	$tpl->setVariable("user_name", $user_name);
	$tpl->setVariable("user_no", $count);
	$count++;
	$tpl->setVariable("stats_url", generateUrl(
		array("user" => $user[SQL_ACCT_COLUMN_USER],
		"mm" => "info", "mg" => "user"))
	);
	if ($config["sql_user_extension"]) {
		$tpl->setVariable("user_realname",
			htmlentities(
				getUserRealName($user_name),
				ENT_QUOTES, "UTF-8"
			)
		);
		$tpl->setVariable("user_realname_url",
			generateUrl(
				array("mg" => "user", "mm" => "personal",
				"user" => $user_name)
			)
		);
		$tpl->setVariable("user_descr",
			htmlentities(
				getUserNotes($user_name),
				ENT_QUOTES, "UTF-8"
			)
		);
	}

	$tpl->setVariable("user_upload", bytesToStr($user[SQL_ACCT_COLUMN_UPLOAD]));
	$tpl->setVariable("user_download", bytesToStr($user[SQL_ACCT_COLUMN_DOWNLOAD]));
	$tpl->parse("user_list");
}

?>
