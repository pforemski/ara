<?php
/*
 * Asn Radius Admin
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

/*
 * all functions throws simple Exception with
 * description of error
 */

define("SQL_USER_TABLE", "userinfo");
define("SQL_USER_COLUMN_USER", "UserName");
define("SQL_USER_COLUMN_REALNAME", "Name");
define("SQL_USER_COLUMN_HOMEPHONE", "HomePhone");
define("SQL_USER_COLUMN_WORKPHONE", "WorkPhone");
define("SQL_USER_COLUMN_MOBILEPHONE", "Mobile");
define("SQL_USER_COLUMN_NOTES", "notes");
define("SQL_USER_COLUMN_ADDRESS", "Department");
define("SQL_USER_COLUMN_EMAIL", "Mail");

define("SQL_USER_COLUMN_PHONE1", "HomePhone");
define("SQL_USER_COLUMN_PHONE2", "Mobile");

function getUserExtInfo_Impl($user_name, $column)
{
	global $config;

	$output = array();

	$list = sqlQuery(
		"SELECT `" . $column . "` " .
		" FROM `" . SQL_USER_TABLE . "`" .
		" WHERE " .
		" `" . SQL_USER_COLUMN_USER . "`  = '" .
		sqlEscape($user_name) . "'"
	);

	if (count($list) > 0)
		return $list[0][0];

	return "-";
}

function getUsersByExtInfo_Impl($string, $column)
{
	global $config;

	$output = array();

	$list = sqlQuery(
		"SELECT DISTINCT (`" . SQL_USER_COLUMN_USER . "`) " .
		"FROM `" . SQL_USER_TABLE . "`" .
		" WHERE " .
		" `" . $column . "` LIKE '%" .
		sqlEscape($string) . "%'"
	);

	foreach ($list as $attr)
		$output[] = $attr[SQL_USER_COLUMN_USER];

	return ($output);
}

function modUserExtInfo_Impl($user, $name, $address, $email,
	$phone1, $phone2, $notes)
{
	global $config;

	sqlQuery("DELETE FROM `" . SQL_USER_TABLE . "` " .
		"WHERE " .
			" `" . SQL_USER_COLUMN_USER . "` = '" .
			sqlEscape($user) . "'");

	sqlQuery(
		"INSERT INTO `" . SQL_USER_TABLE . "` " .
		"SET " .
		"`" . SQL_USER_COLUMN_USER . "` = '" .
			sqlEscape($user) . "', " .
		"`" . SQL_USER_COLUMN_REALNAME . "` = '" .
			sqlEscape($name) . "', " .
		"`" . SQL_USER_COLUMN_ADDRESS . "` = '" .
			sqlEscape($address) . "', " .
		"`" . SQL_USER_COLUMN_EMAIL . "` = '" .
			sqlEscape($email) . "', " .
		"`" . SQL_USER_COLUMN_PHONE1 . "` = '" .
			sqlEscape($phone1) . "', " .
		"`" . SQL_USER_COLUMN_PHONE2 . "` = '" .
			sqlEscape($phone2) . "', " .
		"`" . SQL_USER_COLUMN_NOTES . "` = '" .
			sqlEscape($notes) . "' "
	);
}

