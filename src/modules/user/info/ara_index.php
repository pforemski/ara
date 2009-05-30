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
$tpl->setCurrentBlock("user_stats");

$tpl->setGlobalVariable("current_group", $mg);
$tpl->setGlobalVariable("current_module", $mm);

if (!isset($_GET["user"]) || $_GET["user"] == "") {
	/* no user set, choose one */
	redirectPage(array("mm" => "find", "mg" => "misc", "nmm" => $mm,
	                   "nmg" => $mg ,"type" => "user"));
}
else {
	$old_dir = getcwd();
	chdir("..");
	include("common.php");
	generateCommonMenu($_GET["user"]);
	chdir($old_dir);

	$user = $_GET["user"];
	$tpl->setGlobalVariable("current_user", $user);
	$tpl->setCurrentBlock("user_stats");
	$tpl->setGlobalVariable("page_title" , $i18n["i18n_user_stats"] .
		" : " . $_GET["user"]);

	/* BASIC INFO */
	$first = true;
	foreach (getUserGroups($_GET["user"]) as $group) {
		if ($first) {
			$first = false;
			$tpl->setVariable("first_group_name", $group);
			continue;
		}
		$tpl->setVariable("group_name", $group);
		$tpl->parse("user_groups");
	}
	
	/* LAST SESSION */
	$activeSessions = getUserActiveSessions($user);
	$lastSession = getUserLastSession($user);

	if (count($activeSessions) > 0) {
		$tpl->setVariable("session_type", i18n("active_session"));
		$tpl->setVariable("time_class", "highlight");

		$tpl->setVariable("state", $i18n["i18n_online"]);

		if(count($activeSessions) > 1) {
			$tpl->setVariable("times", count($activeSessions));
		}

		$tpl->setVariable("since", $activeSessions[0]["AcctStartTime"]);
		$tpl->setVariable("for", secToStr(getUserOnlineTime($user)));

		if ($config["use_user_killer"]) {
			$tpl->setVariable("user_killer_url", $config["user_killer_url"]);
			$tpl->setVariable("value_name",
				$config["user_killer_value_name"]);
			$tpl->setVariable("value", $user);
		}

	}
	else {
		$tpl->setVariable("session_type", i18n("last_session"));
		$tpl->setVariable("state", $i18n["i18n_offline"]);
		$tpl->setVariable("since", $lastSession["AcctStopTime"]);
		$tpl->setVariable("for", secToStr(getUserOfflineTime($user)));
	}

	if (!isset($lastSession)) {
		$tpl->setVariable("current_workstation", "N/A");
		$tpl->setVariable("current_ip", "N/A");
		$tpl->setVariable("server", "N/A");
		$tpl->setVariable("server_port", "N/A");
		$tpl->setVariable("since", "N/A");
	} else {
		$tpl->setVariable("server", $lastSession["NASIPAddress"]);
		$tpl->setVariable("server_port", $lastSession["NASPortId"]);
		if ($lastSession["CallingStationId"] == "")
			$lastSession["CallingStationId"] = "&nbsp;";
		$tpl->setVariable("current_workstation", $lastSession["CallingStationId"]);
		$tpl->setVariable("current_ip", $lastSession[SQL_ACCT_COLUMN_USERIP]);

		$nas_names = getNasNames();        /* $nas_names["IP"] == "name"; */
		$nas_ip_to_name = array();
		$nas_name_to_ip = array();

		foreach($nas_names as $nas_name) {
			$nas = getNas($nas_name);
			$nas_ip_to_name[$nas[SQL_NAS_COLUMN_IP]] =
				$nas[SQL_NAS_COLUMN_NAME];
			$nas_name_to_ip[$nas[SQL_NAS_COLUMN_NAME]] =
				$nas[SQL_NAS_COLUMN_IP];
		}

		$nas_name = $nas_ip_to_name[$lastSession[SQL_ACCT_COLUMN_NASIP]];
		
		if ($nas_name != "") {
			$tpl->setVariable("curr_nas_url",
				generateUrl(
					array(
						"nas" => $nas_name,
						"mm" => "info",
						"mg" => "nas"
					)
				)
			);
			$tpl->setVariable("curr_nas", $nas_name);
			$nas = getNas($nas_name);
			if (is_array($nas)) {
				$tpl->setVariable("curr_nas_title",
				$nas[SQL_NAS_COLUMN_DESCRIPTION]);
			}
		}
	}
	
	/* TRANSFERS */
	$tpl->setVariable("current_download",
	                  bytesToStr($lastSession[SQL_ACCT_COLUMN_DOWNLOAD]));
	$tpl->setVariable("current_upload",
	                  bytesToStr($lastSession[SQL_ACCT_COLUMN_UPLOAD]));

	foreach (array("weekly" => 7, "daily" => 1, "monthly" => 30)
	         as $periodStr => $period) {
		$transf = getUserTransferedOctets($user,$period);
		$tpl->setVariable($periodStr . "_upload", bytesToStr($transf[SQL_ACCT_COLUMN_UPLOAD]));
		$tpl->setVariable($periodStr . "_download", bytesToStr($transf[SQL_ACCT_COLUMN_DOWNLOAD]));
	}
}

?>
