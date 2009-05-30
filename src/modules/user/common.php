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

function generateCommonMenu($user_name)
{
	global $tpl;
	global $config;

	$tpl->addBlockfile("common_menu", "common_menu", "common_menu.html");
	$tpl->setVariable("user", $user_name);

	$tpl->setVariable("edit_url",
		generateUrl(
			array("mm" => "edit", "mg" => "user", "user" => $user_name)
		)
	);

	$tpl->setVariable("info_url",
		generateUrl(
			array("mm" => "info", "mg" => "user", "user" => $user_name)
		)
	);

	$tpl->setVariable("acct_url",
		generateUrl(
			array("mg" => "acct", "mm" => "generic",
			"value" => $user_name, "search" => "login")
		)
	);

	if ($config["sql_user_extension"]) {
		$tpl->setVariable("user_realname",
			htmlentities(
				getUserRealName($user_name),
				ENT_QUOTES, "UTF-8"
			)
		);
		$tpl->setVariable("user_descr",
			htmlentities(
				getUserNotes($user_name),
				ENT_QUOTES, "UTF-8"
			)
		);
		$tpl->setVariable("personal_url",
			generateUrl(
				array("mm" => "personal", "mg" => "user",
				"user" => $user_name)
			)
		);
	}
}
?>
