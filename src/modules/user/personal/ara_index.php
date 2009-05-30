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
$tpl->setCurrentBlock("edit_user");

$tpl->setGlobalVariable("current_group", $mg);
$tpl->setGlobalVariable("current_module", $mm);

if (isset($_POST["name"])) {
	/* check access */
	if ($config["access_level"] < ARA_ACCESS_EDIT)
		throw new Exception($i18n["i18n_action_not_allowed"]);

	try {
		modUserExtInfo(
			$_GET["user"],
			$_POST["name"],
			$_POST["address"],
			$_POST["email"],
			$_POST["phone1"],
			$_POST["phone2"],
			$_POST["notes"]
		);
	} catch(Exception $e) {
		$tpl->setCurrentBlock("module_error");
		$tpl->setVariable("error_message", $e->getMessage());
	}
}

if (!isset($_GET["user"]) || $_GET["user"] == "") {
	/* no user set, choose one */
	redirectPage(
		array(
			"mm" => "find", "mg" => "misc", "nmm" => $mm,
			"nmg" => $mg ,"type" => "user"
		)
	);
}
else {
	$user = $_GET["user"];
	$old_dir = getcwd();
	chdir("..");
	include("common.php");
	generateCommonMenu($user);
	chdir($old_dir);


	$tpl->setVariable("base_form_url",
		generateUrl(array("user" => $_GET["user"])));

	$tpl->setVariable("user_realname",
		htmlize(getUserRealName($user))
	);
	$tpl->setVariable("user_notes",
		htmlize(getUserNotes($user))
	);
	$tpl->setVariable("user_email",
		htmlize(getUserEmail($user))
	);
	$tpl->setVariable("user_address",
		htmlize(getUserAddress($user))
	);
	$tpl->setVariable("user_phone1",
		htmlize(getUserPhone1($user))
	);
	$tpl->setVariable("user_phone2",
		htmlize(getUserPhone2($user))
	);
}

?>
