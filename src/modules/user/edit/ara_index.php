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

function parseCommonAttributes($tpl, $commonAttrList)
{
	$tpl->setCurrentBlock("common_attributes");
	$tpl->setVariable("common_attribute", "");
	$tpl->parse("common_attributes");
	foreach ($commonAttrList as $attr) {
		$tpl->setVariable("common_attribute", $attr);
		$tpl->parse("common_attributes");
	}
}

/* parse all groups, but not current one */
function parseCommonGroups($tpl, $commonGroupsList, $curGroup)
{
	$tpl->setCurrentBlock("common_groups");
	foreach ($commonGroupsList as $group) {
		if ($group == $curGroup)
			continue;
		$tpl->setVariable("common_group", $group);
		$tpl->parse("common_groups");
	}
}

/* check access */
if ($config["access_level"] < ARA_ACCESS_VIEW)
	throw new Exception($i18n["i18n_action_not_allowed"]);

$tpl->setCurrentBlock("module_output");
$tpl->addBlockfile("module_generated", "foo", "template.html");
$tpl->setCurrentBlock("edit_user");

$tpl->setGlobalVariable("current_group", $mg);
$tpl->setGlobalVariable("current_module", $mm);

if (isset($_POST["action"])) {
	/* check access */
	if ($config["access_level"] < ARA_ACCESS_EDIT)
		throw new Exception($i18n["i18n_action_not_allowed"]);

	try {
		switch ($_POST["action"]) {
			case "table_row_save":
				modUserTableRow(
					$_GET["user"],
					$_POST["table_type"],
					$_POST["id"],
					$_POST["attribute"],
					$_POST["operator"],
					$_POST["value"]
				);
				break;

			case "table_row_add":
				addUserTableRow(
					$_GET["user"],
					$_POST["table_type"],
					$_POST["attribute"],
					$_POST["operator"],
					$_POST["value"]
				);
				break;

			case "table_row_delete":
				delUserTableRow(
					$_GET["user"],
					$_POST["table_type"],
					$_POST["id"]
				);
				break;

			case "group_row_save":
				changeUserGroup($_GET["user"], $_POST["group"],
				                $_POST["common_group"]);
				break;

			case "group_row_delete":
				deleteUserGroup($_GET["user"], $_POST["group"]);
				break;

			case "group_row_add":
				addUserGroup($_GET["user"], $_POST["common_group"]);
				break;

			case "group_row_edit":
				$tpl->setCurrentBlock("redirection");
				redirectPage(
					array("mm" => "edit", "mg" => "group", "group" => $_POST["group"])
				);
				break;

			case "change_user_name":
				if ($config['allow_login_renaming'])
					changeUserName($_GET["user"], $_POST["user_name"]);
				$_GET["user"] = $_POST["user_name"];
				break;

			case "clone_user":
				cloneUser($_GET["user"], $_POST["user_name"]);
				$_GET["user"] = $_POST["user_name"];
				break;

			case "delete_user":
				echo $_POST["refs_too"];
				deleteUser($_GET["user"]);
				break;
		}
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
	$tpl->setGlobalVariable("user_name", $_GET["user"]);

	$old_dir = getcwd();
	chdir("..");
	include("common.php");
	generateCommonMenu($_GET["user"]);
	chdir($old_dir);

	if (userExists($_GET["user"]))
		$tpl->touchBlock("exists");
	else
		$tpl->touchBlock("exists_not");
	
	if ($config['allow_login_renaming']) {
		$tpl->touchBlock('allow_login_renaming');
	}

	$tpl->setGlobalVariable("current_user", $_GET["user"]);
	$tpl->setCurrentBlock("edit_user");
	$base_form_url = generateUrl(array("user" => $_GET["user"]));
	$tpl->setGlobalVariable("base_form_url", $base_form_url);

	/* prepare "common attributes poplist" */
	$commonAttributeList = getCommonAttributes();

	foreach (array("reply", "check") as $tableType) {
		$tpl->setCurrentBlock("table_list");
		$tpl->setGlobalVariable("table_type", $tableType);

		/* get user replies/checks */
		switch ($tableType) {
			case "reply":
				$replies = getUserReplies($_GET["user"]);
				break;

			case "check":
				$replies = getUserChecks($_GET["user"]);
				break;

			default:
				die("assert error");
				break;
		}

		$toPrint = array();

		foreach ($replies as $reply) {
			$toPrint[] = array(
				"id" => htmlentities($reply["id"], ENT_QUOTES, "UTF-8"),
				"attribute" => htmlentities($reply["attribute"], ENT_QUOTES, "UTF-8"),
				"operator" => htmlentities($reply["op"], ENT_QUOTES, "UTF-8"),
				"value" => htmlentities($reply["value"], ENT_QUOTES, "UTF-8")
			);
		}

		/* draw attrs already assigned to user */
		foreach ($toPrint as $reply) {
			parseCommonAttributes($tpl, $commonAttributeList);
			$tpl->setCurrentBlock($tableType . "_list_row");

			if (in_array($reply["attribute"], $config["passwd_attributes"]) &&
			    $config["access_level"] < ARA_ACCESS_VIEW_ALL) {
				$reply["value"] = "N/A";
			}

			$tpl->setVariable($reply);
			$tpl->touchBlock("save_and_remove");

			if (in_array($reply["attribute"], $config["masq_attributes"])) {
				$tpl->touchBlock("value_unhider");
				$tpl->setVariable("value_class", "none");
			}

			$tpl->parse("table_list_row");
		}

		/* draw attr adding to user */
		parseCommonAttributes($tpl, $commonAttributeList);
		$tpl->setCurrentBlock($tableType . "_list_row");
		$tpl->setVariable("id", "add");
		$tpl->touchBlock("add");
		$tpl->parse("table_list_row");

		$tpl->parse("tables_list");
	}

	/* draw groups user belongs to */
	$tpl->setCurrentBlock("group_list_row");
	$commonGroups = getGroupNames();

	$userGroups = getUserGroups($_GET["user"]);
	$numberOfGroups = count($userGroups);

	foreach ($userGroups as $group) {
		$tpl->setVariable("group_name", $group);
		parseCommonGroups($tpl, $commonGroups, $group);
		$tpl->touchBlock("save_and_remove_group_row");
		$tpl->touchBlock("remove_group_row");
		$tpl->parse("groups_list_row");
	}

	parseCommonGroups($tpl, $commonGroups, "");
	$tpl->touchBlock("add_group_row");
	$tpl->parse("groups_list_row");
}

?>
