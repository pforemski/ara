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

/* check access */
if ($config["access_level"] < ARA_ACCESS_VIEW)
	throw new Exception($i18n["i18n_action_not_allowed"]);

$tpl->setCurrentBlock("module_output");
$tpl->addBlockfile("module_generated", "foo", "template.html");

if (!isset($_GET["per_page"])) {
	if (isset($_COOKIE["list-users-per-page"])) {
		$_GET["per_page"] = $_COOKIE["list-users-per-page"];
	}
	else {
		$_GET["per_page"] = 25;
	}
}
else {
	setcookie("list-users-per-page", $_GET["per_page"]);
}

if (!isset($_GET["page_no"])) {
	$_GET["page_no"] = 0;
}

$args_array = array();
/* get & process needed information */
if (!isset($_GET["group"])) {
	$tpl->touchBlock("search_title");
}

$tpl->setVariable("date", date("m.d.y, H:i:s"));

if (isset($_GET["phone"])) {
	$users = getUsersByPhone($_GET["phone"]);
	$tpl->touchBlock("use_phone");
	$args_array["phone"] = $_GET["phone"];
} else if (isset($_GET["name"])) {
	$users = getUsersByName($_GET["name"]);
	$args_array["name"] = $_GET["name"];
} else if (isset($_GET["login"])) {
	$users = getUsersByLogin($_GET["login"]);
	$args_array["login"] = $_GET["login"];
} else if (isset($_GET["address"])) {
	$users = getUsersByAddress($_GET["address"]);
	$args_array["address"] = $_GET["address"];
	$tpl->touchBlock("use_address");
} else if (isset($_GET["mac"])) {
	$users = getUsersByMac($_GET["mac"]);
	$args_array["mac"] = $_GET["mac"];
	$tpl->touchBlock("use_mac");
} else if (isset($_GET["group"])) {
	$args_array["group"] = $_GET["group"];
	$old_dir = getcwd();
	chdir("../../group/");
	require_once("common.php");
	generateCommonMenu($_GET["group"]);
	chdir($old_dir);

	$users = getGroupUsers($_GET["group"]);
} else {
	$users = getUserNames();
}

usort($users, "smartCmp");

if ($config["sql_user_extension"]) {
	$tpl->touchBlock("use_real_name");
}

$toPrint = array();
foreach ($users as $user) {
	if ($user == "")
		continue;
	$toPrint[$user] = array("user_name" => $user);
}

/* print them out */

$tpl->setCurrentBlock("list");
$counter = 0;

$start = $_GET["page_no"] * $_GET["per_page"];
$end = ($_GET["page_no"] + 1 ) * $_GET["per_page"];

uksort($toPrint, "smartCmp");

foreach ($toPrint as $user) {
	++$counter;
	if ($counter > $start && $counter <= $end) {
		$tpl->setVariable($user);
		$tpl->setVariable("user_no", $counter);
		if ($config["sql_user_extension"]) {
			$user_name = $user["user_name"];
			$tpl->touchBlock("use_real_name2");
			$tpl->setVariable("user_realname",
				htmlize(getUserRealName($user_name))
			);
			
			$tpl->setVariable("user_descr",
				htmlize(getUserNotes($user_name))
			);

			$tpl->setVariable("view_url",
				generateUrl(array("mm" => "info", "user" => $user_name))
			);

			$tpl->setVariable("realname_url",
				generateUrl(array("mm" => "personal", "user" => $user_name))
			);

			if (isset($_GET["phone"])) {
				$tpl->touchBlock("use_phone2");
				$phone1 = getUserPhone1($user_name);
				if ($phone1 == "")
					$phone1 = "-";
				$tpl->setVariable("user_phone1",
					htmlize($phone1)
				);
				$phone2 = getUserPhone2($user_name);
				if ($phone2 == "")
					$phone2 = "-";

				$tpl->setVariable("user_phone2",
					htmlize($phone2)
				);
			}
			if (isset($_GET["address"])) {
				$tpl->touchBlock("use_address2");
				$address = getUserAddress($user_name);
				$tpl->setVariable("user_address",
					htmlize($address)
				);
			}
			if (isset($_GET["mac"])) {
				$tpl->touchBlock("use_mac2");
				$mac = getUserLastMacLike($user_name, $_GET["mac"]);
				if ($mac !== null) {
					$tpl->setVariable("user_mac",
						htmlize($mac)
					);
				} else {
					$tpl->setVariable("user_mac",
						htmlize("N/A")
					);
				}
			}
		}
		$tpl->parse("user_list");
	}
}

for ($i = 0; $i < count($toPrint) / $_GET["per_page"]; $i++) {
	$tpl->setVariable("page_no", $i);
	$args_array["per_page"] = $_GET["per_page"];
	$args_array["page_no"] = $i;
	$tpl->setVariable("page_url", generateUrl($args_array));
	$tpl->parse("page_list");
}

foreach (array(5, 25, 50, 100) as $per_page) {
	$tpl->setVariable("per_page", $per_page);
	$args_array["per_page"] = $per_page;
	$args_array["page_no"] = 0;
	$tpl->setVariable("per_page_url", generateUrl($args_array));
	$tpl->parse("per_page_list");
}

?>
