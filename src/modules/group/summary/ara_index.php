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

$users = getGroupUsersPairs();

$toPrint = array();
$toPrint2 = array();

foreach ($users as $user => $groups) {
	if (count($groups) == 0)
		$toPrint2[$user] = $user;
	else
		foreach ($groups as $group) {
		if (isset($toPrint[$group]))
			$toPrint[$group]++;
		else
			$toPrint[$group] = 1;
	}
}

/* print them out */
$tpl->setCurrentBlock("list");
$counter = 1;
uksort($toPrint, "smartCmp");

foreach ($toPrint as $group => $count) {
	$tpl->setVariable("group_name", $group);

	$tpl->setVariable("no", $counter);
	$counter++;

	$tpl->setVariable("user_count", $count);
	$tpl->setVariable("users_url", generateUrl(
		array("mm" => "list", "mg" => "user", "group" => $group)));
	$tpl->setVariable("edit_url", generateUrl(
		array( "mm" => "edit", "mg" => "group", "group" => $group)));

	$tpl->parse("user_list");
}

$tpl->setCurrentBlock("ungrouped");
uksort($toPrint, "smartCmp");

foreach ($toPrint2 as $user) {
	$tpl->setVariable("user_name", $user);
	$tpl->setVariable(
		"edit_url",
		generateUrl( array( "mm" => "edit", "mg" => "user", "user" => $user))
	);

	$tpl->parse("ungrouped_list");
}

?>
