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

$tpl->setCurrentBlock("find");
$tpl->touchBlock("find");

$tpl->setGlobalVariable("item_type", $_GET["type"]);
$tpl->setGlobalVariable("mg", $mg);
$tpl->setGlobalVariable("mm", $mm);
$tpl->setGlobalVariable("nmg", $_GET["nmg"]);
$tpl->setGlobalVariable("nmm", $_GET["nmm"]);
$tpl->setGlobalVariable("action_url", generateUrl(array()));

try {
	$toPrint = array();
	if (!isset($_GET["type"]))
		throw new Exception("no `type=' set");

		/* like to search for */
	switch ($_GET["type"]) {
	case "group":
		$tpl->setVariable("select_what", i18n("group"));
		$items = getGroupNames();
		break;

	case "user":
		$tpl->setVariable("select_what", i18n("user"));
		$items = getUserNames();
		break;

	case "nas":
		$tpl->setVariable("select_what", i18n("nas"));
		$items = getNasNames();
		break;

	default:
		throw new Exception("unsupported type=");
	}

	foreach ($items as $item) {
		$toPrint[$item] = array(
			"item_name" => $item
		);
	}

	uksort($toPrint, "smartCmp");
	foreach ($toPrint as $itemid => $item) {
		$tpl->setVariable($item);
		$tpl->parse("all");
	}
} catch (Exception $e) {
	/* TODO: draw errors (no "type=" set etc) */
	$tpl->setCurrentBlock("module_error");
	$tpl->setVariable("error_message", $e->getMessage());
}

?>
