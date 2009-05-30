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

function generateCommonMenu($nas_name)
{
	global $tpl;
	global $config;

	$tpl->addBlockfile("common_menu", "common_menu", "common_menu.html");
	$tpl->setVariable("nas", $nas_name);

	$tpl->setVariable("edit_url",
		generateUrl(
			array("mg" => "nas", "mm" => "edit", "nas" => $nas_name)
		)
	);

	$tpl->setVariable("info_url",
		generateUrl(
			array("mg" => "nas", "mm" => "info", "nas" => $nas_name)
		)
	);

	$tpl->setVariable("acct_url",
		generateUrl(
			array("mg" => "acct", "mm" => "generic",
			"value" => $nas_name, "search" => "nas-name")
		)
	);

	$tpl->setVariable("online_url",
			generateUrl(
				array("mm" => "users-online", "mg" => "nas",
				"nas" => $nas_name)
			)
		);

	$nas_info = getNas($nas_name);
	if ($nas_info) {
		$tpl->setVariable("nas_common_nas_descr",
			htmlentities(
				$nas_info[SQL_NAS_COLUMN_DESCRIPTION],
				ENT_QUOTES, "UTF-8"
			)
		);
	}
}
?>
