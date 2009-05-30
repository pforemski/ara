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

$tpl->setCurrentBlock("module_output");
$tpl->addBlockfile("module_generated", "foo", "template.html");

$tpl->setCurrentBlock("edit_user");
$tpl->setGlobalVariable("current_group", $mg);
$tpl->setGlobalVariable("current_module", $mm);

/* take an action? */
if (isset($_POST["action"])) {
	try {
		switch ($_POST["action"]) {
			case "change_nas_name":
				changeNasName($_GET["nas"], $_POST["nas_name"]);
				$_GET["nas"] = $_POST["nas_name"];
				break;

			case "clone_nas":
				cloneNas($_GET["nas"], $_POST["nas_name"]);
				$_GET["nas"] = $_POST["nas_name"];
				break;

			case "delete_nas":
				deleteNas($_GET["nas"]);
				break;

			case "change_nas":
				if($_POST["new"] == "yes") {
					addNas($_GET["nas"], $_POST["nas_long_name"],
					       $_POST["nas_type"], $_POST["nas_ports"],
					       $_POST["nas_secret"], $_POST["nas_community"],
					       $_POST["nas_description"]
					);
				}
				else {
					modNas($_GET["nas"], $_POST["nas_long_name"],
					       $_POST["nas_type"], $_POST["nas_ports"],
					       $_POST["nas_secret"], $_POST["nas_community"],
					       $_POST["nas_description"]
					);
				}
				break;
		}
	} catch(Exception $e) {
		$tpl->setCurrentBlock("module_error");
		$tpl->setVariable("error_message", $e->getMessage());
	}
}

if (!isset($_GET["nas"]) || $_GET["nas"] == "") {
	redirectPage(
		array("mm" => "find", "mg" => "misc", "nmm" => $mm ,
		      "nmg" => $mg ,"type" => "nas"
		)
	);
}
else {
	$old_dir = getcwd();
	chdir("..");
	include("common.php");
	generateCommonMenu($_GET["nas"]);
	chdir($old_dir);

	$tpl->setCurrentBlock("edit_nas");
	$base_form_url = generateUrl(array("nas" => $_GET["nas"]));
	$tpl->setGlobalVariable("base_form_url", $base_form_url);
	$tpl->setGlobalVariable("nas_name", $_GET["nas"]);

	$choosenNasRecord = getNas($_GET["nas"]);
	
	$found = isset($choosenNasRecord);
	$tpl->setCurrentBlock("nas_form");
	$tpl->touchBlock("nas_form");

	if ($found) {
		$tpl->touchBlock("nas_name_changing");
		$tpl->setVariable("nas_long_name", $choosenNasRecord["nasname"]);
		$tpl->setVariable("nas_type", $choosenNasRecord["type"]);
		$tpl->setVariable("nas_ports", $choosenNasRecord["ports"]);
		$tpl->setVariable("nas_secret", $choosenNasRecord["secret"]);
		$tpl->setVariable("nas_community", $choosenNasRecord["community"]);
		$tpl->setVariable("nas_description", $choosenNasRecord["description"]);
	}
	else {
		$tpl->touchBlock("nas_noname_changing");
		$tpl->setVariable("nas_is_new", "yes");
	}
}

?>
