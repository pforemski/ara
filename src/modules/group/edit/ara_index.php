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
$tpl->setCurrentBlock("edit");

$tpl->setGlobalVariable("current_group", $mg);
$tpl->setGlobalVariable("current_module", $mm);

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

function parseCommonGroups($tpl, $commonGroupsList)
{
	$tpl->setCurrentBlock("common_groups");

	foreach ($commonGroupsList as $group) {
		$tpl->setVariable("common_group", $group);
		$tpl->parse("common_groups");
	}
}

/* take an action? */
if (isset($_POST["action"])) {
	if ($config["access_level"] < ARA_ACCESS_EDIT)
		throw new Exception($i18n["i18n_action_not_allowed"]);

	try{
		switch ($_POST["action"]){
		case "table_row_save":
			modGroupTableRow(
				$_GET["group"],
				$_POST["table_type"],
				$_POST["id"],
				$_POST["attribute"],
				$_POST["operator"],
				$_POST["value"]
			);
			break;

		case "table_row_add":
			addGroupTableRow(
				$_GET["group"],
				$_POST["table_type"],
				$_POST["attribute"],
				$_POST["operator"],
				$_POST["value"]
			);
			break;

		case "table_row_delete":
			delGroupTableRow(
				$_GET["group"],
				$_POST["table_type"],
				$_POST["id"]
			);
			break;

		case "change_group_name":
			changeGroupName($_GET["group"], $_POST["group_name"]);
			$_GET["group"] = $_POST["group_name"];
			break;

		case "clone_group":
			cloneGroup($_GET["group"], $_POST["group_name"]);
			$_GET["group"] = $_POST["group_name"];
			break;

		case "delete_group":
			deleteGroup($_GET["group"]);
			$_GET["group"] = "";
			break;
		}

	} catch(Exception $e) {
		$tpl->setCurrentBlock("module_error");
		$tpl->setVariable("error_message", $e->getMessage());
	}
}

if (!isset($_GET["group"]) || $_GET["group"] == "") {
	/* no group set, choose one */
	redirectPage(
		array("mm" => "find", "mg" => "misc", "nmm" => $mm, "nmg"  => $mg ,
		      "type" => "group")
	);
}
else {
	$tpl->setGlobalVariable("group_name", $_GET["group"]);
	$tpl->setGlobalVariable("current_group", $_GET["group"]);
	$tpl->setCurrentBlock("edit");

	if (groupExists($_GET["group"]))
		$tpl->touchBlock("exists");
	else
		$tpl->touchBlock("exists_not");

	require_once("../common.php");
	$old_dir = getcwd();
	chdir("..");
	generateCommonMenu($_GET["group"]);
	chdir($old_dir);

	$base_form_url = generateUrl(
		array("mm" => $mm, "mg" => $mg, "group" => $_GET["group"]));

	$tpl->setGlobalVariable("base_form_url", $base_form_url);
	$tpl->setGlobalVariable("group_list_url",
		generateUrl(array("mm" => "list", "group" => $_GET["group"]))
	);

	/* prepare "common attributes poplist" */
	$commonAttributeList = getCommonAttributes();
	sort($commonAttributeList);

	foreach (array("reply", "check") as $tableType) {
		$tpl->setCurrentBlock("table_list");
		$tpl->setGlobalVariable("table_type", $tableType);

		/* get group replies/checks */
		switch ($tableType) {
			case "reply":
				$replies = getGroupReplies($_GET["group"]);
				break;

			case "check":
				$replies = getGroupChecks($_GET["group"]);
				break;

			default:
				die("assert error");
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

			$tpl->setCurrentBlock($tableType ."_list_row");
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

		$tpl->parse("table_list");
	}
}

?>
