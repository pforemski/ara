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

/*
 * this module is clumsy and unreadable
 * it qualifiets for being rewritten but
 * works well, so...
 *
 *                "...only this?" :PPP --pjf
*/

/* check access */
if ($config["access_level"] < ARA_ACCESS_VIEW)
	throw new Exception($i18n["i18n_not_allowed"]);

$tpl->setCurrentBlock("module_output");
$tpl->addBlockfile("module_generated", "foo", "template.html");

$tpl->touchBlock("refreshing");

/* get & process needed information */

$tpl->setVariable("date", date("m.d.y, H:i:s"));
$sessions = getActiveSessions();

$toPrint1 = array();
$toPrint2 = array();

if (isset($_GET["nas"]) && $nasName != $_GET["nas"]) {
	$old_dir = getcwd();
	chdir("../../nas/");
	include("common.php");
	generateCommonMenu($_GET["nas"]);
	chdir($old_dir);
} else {
	$tpl->setGlobalVariable('top_header', i18n('online__users_'));
}

foreach ($sessions as $session) {
	$toPrint1[$session["NASIPAddress"]][] = array(
		"user_name" => $session["UserName"],
		"user_time" => secToStr($session["RealSessionTime"]),
		"user_ip" => $session["FramedIPAddress"],
		"user_mac" => $session["CallingStationId"]
	);
}

/* print them out */
$nas_names = getNasNames();

foreach ($nas_names as $nas_name) {
	$nas = getNas($nas_name);
	$toPrint2[$nas_name]["users"] = array();
	$toPrint2[$nas_name]["ip"] = $nas[SQL_NAS_COLUMN_IP];
	$toPrint2[$nas_name]["desc"] = $nas[SQL_NAS_COLUMN_DESCRIPTION];
	$toPrint2[$nas_name]["short"] = $nas[SQL_NAS_COLUMN_NAME];
	$toPrint2[$nas_name]["ports"] = $nas[SQL_NAS_COLUMN_PORTS];
}

foreach ($toPrint1 as $nasIP => $users) {
	$nas["ip"] = $nasIP;
	$found = false;

	foreach ($nas_names as $nas_name) {
		$nas = getNas($nas_name);
		if ($nasIP == $nas[SQL_NAS_COLUMN_IP]) {
			$found = true;
			$toPrint2[$nas_name]["users"] = $users;
			break;
		}
	}

	if (!$found) {
		$toPrint2[$nasIP]["users"] = $users;
	}
}

$tpl->setCurrentBlock("nas_list");
uksort($toPrint2, "smartCmp");

function userCmp($a, $b)
{
	return smartCmp($a["user_name"], $b["user_name"]);
}

$portsTotal = 0;
$usersTotal = 0;

foreach ($toPrint2 as $nasName => $nas_params) {
	if (isset($_GET["nas"]) && $nasName != $_GET["nas"]) {
		continue;
	}

	if ($config["sql_user_extension"]) {
		$tpl->touchBlock("use_real_name");
	}

	if (isset($nas_params["short"])) {
		$tpl->setVariable("nas_short", $nas_params["short"]);
	}
	else {
		$tpl->setVariable("nas_short", $nasName);
	}

	$tpl->setVariable("nas_desc", $nas_params["desc"]);
	$tpl->setVariable("nas_ip", $nas_params["ip"]);
	$tpl->setVariable("nas_edit_url", generateUrl(
		array( "mm" => "edit", "mg" => "nas",
		"nas" => $nas_params["short"])
	));

	if (isset($config["sql_use_nat_table"])) {
		$tpl->setCurrentBlock("nas_param");
		$tpl->setVariable("nas_desc", htmlentities($nas_params["desc"],
		                  ENT_QUOTES, "UTF-8"));
		$tpl->setVariable("nas_ip", $nas_params["ip"]);
	}
	if (isset($nas_params["ports"])) {
		$tpl->setVariable("ports_no", $nas_params["ports"]);
	}
	else  {
		$tpl->setVariable("ports_no", "N/A");
	}
	$portsTotal += $nas_params["ports"];
	$counter = 0;

	usort($toPrint2[$nasName]["users"], "userCmp");
	foreach ($toPrint2[$nasName]["users"] as $user) {
		foreach ($user as $name => &$arg) {
			if($arg == "") {
				$arg = "N/A";
			}
		}

		$tpl->setCurrentBlock("session_list");
		$tpl->setVariable($user);
		$tpl->setVariable("view_url", generateUrl(
			array( "mm" => "info", "mg" => "user",
			"user" => $user["user_name"])
		));
		if ($config["sql_user_extension"]) {
			$tpl->setVariable("user_realname",
				htmlentities(
					getUserRealName($user["user_name"]),
					ENT_QUOTES, "UTF-8"
				)
			);
			$tpl->setVariable("user_realname_url",
				generateUrl(
					array("mg" => "user", "mm" => "personal",
					"user" => $user["user_name"])
				)
			);
			$tpl->setVariable("user_descr",
				htmlentities(
					getUserNotes($user["user_name"]),
					ENT_QUOTES, "UTF-8"
				)
			);
		}
		$tpl->setVariable("user_no",++$counter);
		$tpl->parse("session_list");
	}

	$usersTotal += $counter;
	$tpl->setVariable("online_no", $counter);
	$tpl->parse("nas_list");
	if (!isset($_GET["nas"])) {
		$tpl->setCurrentBlock("total");
		$tpl->setVariable("users_total", $usersTotal);
		$tpl->setVariable("ports_total", $portsTotal);
	}
}

?>
