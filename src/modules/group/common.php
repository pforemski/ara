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

function generateCommonMenu($group_name)
{
	global $tpl;

	$tpl->addBlockfile("common_menu", "common_menu", "common_menu.html");
	$tpl->setVariable("group", $group_name);

	$tpl->setVariable("edit_url",
		generateUrl(array("mm" => "edit",  "mg" => "group",
		"group" => $group_name))
	);

	$tpl->setVariable("users_url",
		generateUrl(array("mm" => "list", "mg" => "user",
		"group" => $group_name))
	);

}
?>
