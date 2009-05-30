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


require_once "sql/" . $config["sql_driver"] . ".php";

/*
 * all functions throws simple Exception with
 * description of error
 */

define("SQL_NAS_COLUMN_NAME", "shortname");
define("SQL_NAS_COLUMN_IP", "nasname");
define("SQL_NAS_COLUMN_TYPE", "type");
define("SQL_NAS_COLUMN_PORTS", "ports");
define("SQL_NAS_COLUMN_SECRET", "secret");
define("SQL_NAS_COLUMN_COMMUNITY", "community");
define("SQL_NAS_COLUMN_DESCRIPTION", "description");

define("SQL_ACCT_COLUMN_STARTTIME", "AcctStartTime");
define("SQL_ACCT_COLUMN_STOPTIME", "AcctStopTime");
define("SQL_ACCT_COLUMN_NASIP", "NASIPAddress");
define("SQL_ACCT_COLUMN_USERIP", "FramedIPAddress");
define("SQL_ACCT_COLUMN_USER", "UserName");
define("SQL_ACCT_COLUMN_DOWNLOAD", "AcctOutputOctets");
define("SQL_ACCT_COLUMN_UPLOAD", "AcctInputOctets");
define("SQL_ACCT_COLUMN_SESSIONTIME", "AcctSessionTime");
/* fake column for time from stat to now() */
/* TODO: rename to ActiveTime or something */
define("SQL_ACCT_COLUMN_ACTIVETIME", "RealSessionTime");
define("SQL_ACCT_COLUMN_CALLINGSTATION", "CallingStationId");

define("SQL_RADCHECK_COLUMN_USER", "UserName");
define("SQL_RADREPLY_COLUMN_USER", "UserName");
define("SQL_RADGROUPCHECK_COLUMN_GROUP", "GroupName");
define("SQL_RADGROUPREPLY_COLUMN_GROUP", "GroupName");
define("SQL_USERGROUP_COLUMN_USER", "UserName");
define("SQL_USERGROUP_COLUMN_GROUP", "GroupName");

if ($config["sql_user_extension"]) {
	global $config;
	require 'sql-user-ext/' . $config["sql_user_extension_name"] . ".php";
}

/*
 * HINTS:
 * don't sort - simple sort is almost always not enough so don't bother
 *
 */

/* returns plain array of all user */
function getUserNames()
{
	global $config;

	$tables = array(
		array("usergroup", SQL_USERGROUP_COLUMN_USER),
		array("radcheck", SQL_RADCHECK_COLUMN_USER),
		array("radreply", SQL_RADREPLY_COLUMN_USER),
	);
	$output = array();

	foreach ($tables as $table) {
		$table_name = $table[0];
		$table_column = $table[1];
		$list = sqlQuery(
			"SELECT DISTINCT " . $table_column . " " .
			"FROM `" . $config["sql_table_" . $table_name] . "`"
		);

		foreach ($list as $attr)
			$output[] = $attr[$table_column];
	}

	return $output;
}

function getGroupUsers($group)
{
	global $config;

	$output = array();

	$list = sqlQuery(
		"SELECT DISTINCT " . SQL_USERGROUP_COLUMN_USER ." " .
			"FROM `" . $config["sql_table_usergroup"] . "` " .
		"WHERE " .
			SQL_USERGROUP_COLUMN_GROUP . " = '" . sqlEscape($group) . "'"
	);

	foreach ($list as $attr)
		$output[] = $attr[SQL_USERGROUP_COLUMN_USER];

	return $output;
}

/**
 * Returns:
 * AA of format $user => (array of groups)
 */
function getGroupUsersPairs()
{
	global $config;

	$tables = array(
		array("usergroup", SQL_USERGROUP_COLUMN_USER),
		array("radcheck", SQL_RADCHECK_COLUMN_USER),
		array("radreply", SQL_RADREPLY_COLUMN_USER),
	);
	$output = array();

	foreach ($tables as $table) {
		$table_name = $table[0];
		$table_column = $table[1];
		$list = sqlQuery(
			"SELECT " . $table_column . " " .
			"FROM `" . $config["sql_table_" . $table_name] . "`"
		);

		foreach ($list as $attr) {
			if (isset($attr[$table_column]) && $attr[$table_column] != "")
				$output[$attr[$table_column]] = array();
		}
	}

	$list = sqlQuery(
		"SELECT " . SQL_USERGROUP_COLUMN_USER . ", " .
			SQL_USERGROUP_COLUMN_GROUP . " " .
		"FROM `" . $config["sql_table_usergroup"] . "`"
	); 

	foreach ($list as $attr)
		$output[$attr[SQL_USERGROUP_COLUMN_USER]][]
			= $attr[SQL_USERGROUP_COLUMN_GROUP];

	return $output;
}

/* returns plain array of all groups */
function getGroupNames()
{
	global $config;

	$tables = array(
		array("usergroup", SQL_USERGROUP_COLUMN_GROUP),
		array("radgroupcheck", SQL_RADGROUPCHECK_COLUMN_GROUP),
		array("radgroupreply", SQL_RADGROUPREPLY_COLUMN_GROUP),
	);
	$output = array();

	foreach ($tables as $table) {
		$table_name = $table[0];
		$table_column = $table[1];
		$list = sqlQuery(
			"SELECT " . $table_column . " " .
			"FROM `" . $config["sql_table_" . $table_name] . "`"
		);

		foreach ($list as $attr)
			$output[] = trim($attr[$table_column]);
	}

	$output = array_unique($output);
	sort($output);
	return $output;
}

/* returns array of associative arrays descibing attributes */
function getUserChecks($user)
{
	global $config;

	return sqlQuery(
		"SELECT * " .
			"FROM `" . $config["sql_table_radcheck"] . "` " .
		"WHERE " .
			SQL_RADCHECK_COLUMN_USER . " = '" . sqlEscape($user) . "'"
	);
}

/* return associative array describing nas */

function getNasBy($column, $value)
{
	global $config;

	$nas = sqlQuery(
		"SELECT * FROM " . $config["sql_table_nas"] . " " .
			"WHERE `" . $column . "` = '" . sqlEscape($value) . "'"
	);

	if (is_array($nas)) {
		return $nas[0];
	}
	else {
		return NULL;
	}
}

function getNas($name)
{
	return getNasBy(SQL_NAS_COLUMN_NAME, $name);
}

/* returns plain array of nas shornames */
function getNasNames()
{
	global $config;
	$output = array();

	$list = sqlQuery(
		"SELECT `" . SQL_NAS_COLUMN_NAME . "` " .
		"FROM " . $config["sql_table_nas"]
	);
	
	foreach ($list as $attr)
		$output[] = $attr[SQL_NAS_COLUMN_NAME];

	return $output;
}

function getGroupChecks($group)
{
	global $config;

	return sqlQuery(
		"SELECT * " .
			"FROM `" . $config["sql_table_radgroupcheck"] . "` " .
		"WHERE " .
			SQL_RADGROUPCHECK_COLUMN_GROUP . " = '" . sqlEscape($group) . "'"
	);
}

/* returns array of associative arrays descibing attributes */
function getUserReplies($user)
{
	global $config;

	return sqlQuery(
		"SELECT * " .
			"FROM `" . $config["sql_table_radreply"] . "` " .
		"WHERE " .
			"UserName = '" . sqlEscape($user) . "'"
	);
}

function getGroupReplies($group)
{
	global $config;

	return sqlQuery(
		"SELECT * " .
			"FROM `" . $config["sql_table_radgroupreply"] . "` " .
		"WHERE " .
			"GroupName = '" . sqlEscape($group) . "'"
	);
}


/* *UserTableRow:
 * they can be used to radreply and radcheck tables 
 * $tableName should be "check" or "reply"
 */


/* returns void */
function modUserTableRow($user, $tableName, $reId, $reAttr, $reOp, $reVal)
{
	global $config;

	foreach (array($user, $tableName, $reId, $reAttr, $reOp, $reVal) as $arg)
		if ($arg == "") throw new Exception("empty argument");

	sqlQuery(
		"UPDATE `" . $config["sql_table_rad" . $tableName] . "` " .
		"SET " .
			"`Attribute` = '" . sqlEscape($reAttr) . "', " .
			"`op` = '" . sqlEscape($reOp) ."', " .
			"`Value` = '" . sqlEscape($reVal) ."' " .
		"WHERE " .
			"`id` = " . sqlEscape($reId) . " AND " .
			"`UserName` = '" . sqlEscape($user) . "' " .
		"LIMIT 1"
	);
}

/* returns void */
function addUserTableRow($user, $tableName, $reAttr, $reOp, $reVal)
{
	global $config;

	foreach (array($user, $tableName, $reAttr, $reOp, $reVal) as $arg)
		if ($arg == "") throw new Exception("empty argument");

	sqlQuery(
		"INSERT INTO `" . $config["sql_table_rad" . $tableName] . "` " .
			"(UserName, Attribute, op, Value) " .
		"VALUES (" .
			"'" . sqlEscape($user) . "', " .
			"'" . sqlEscape($reAttr) . "', " .
			"'" . sqlEscape($reOp) . "', " .
			"'" . sqlEscape($reVal) .
		"')"
	);
}

/* returns void */
function delUserTableRow($user, $tableName, $reId)
{
	global $config;

	sqlQuery("DELETE FROM `" . $config["sql_table_rad" . $tableName] .
	         "` WHERE `id` = '" . sqlEscape($reId) . "' LIMIT 1");
}


/* *GroupTableRow:
 * they can be used to radgroupreply and radgroupcheck tables 
 * $tableName should be "check" or "reply"
 */

/* returns void */
function modGroupTableRow($group, $tableName, $reId, $reAttr, $reOp, $reVal)
{
	global $config;

	foreach (array($group, $tableName, $reId, $reAttr, $reOp, $reVal) as $arg)
		if ($arg == "") throw new Exception("empty argument");

	sqlQuery(
		"UPDATE `" . $config["sql_table_radgroup" . $tableName] . "` " .
		"SET " .
			"`Attribute` = '" . sqlEscape($reAttr) . "', " .
			"`op` = '" . sqlEscape($reOp) ."', " .
			"`Value` = '" . sqlEscape($reVal) ."' " .
		"WHERE " .
			"`id` = " . sqlEscape($reId) . " AND " .
			"`GroupName` = '" . sqlEscape($group) . "' " .
		"LIMIT 1"
	);
}

/* returns void */
function addGroupTableRow($group, $tableName, $reAttr, $reOp, $reVal)
{
	global $config;

	foreach (array($group, $tableName, $reAttr, $reOp, $reVal) as $arg)
		if ($arg == "") throw new Exception("empty argument");

	sqlQuery(
		"INSERT INTO `" . $config["sql_table_radgroup" . $tableName] . "` " .
			"(GroupName, Attribute, op, Value) " .
		"VALUES (" .
			"'" . sqlEscape($group) ."', " .
			"'" . sqlEscape($reAttr) . "', " .
			"'" . sqlEscape($reOp)   . "', " .
			"'" . sqlEscape($reVal)  . "'" .
		")"
	);
}

/* returns void */
function delGroupTableRow($user, $tableName, $reId)
{
	global $config;

	sqlQuery("DELETE FROM `" . $config["sql_table_radgroup" . $tableName] .
	         "` WHERE `id` = '" . sqlEscape($reId) . "' LIMIT 1");
}

/* returns void */
function changeUserName($name, $newName)
{
	global $config;

	if (userExists($newName))
		throw new Exception("user exists");

	if ($name == "" || $newName == "")
		throw new Exception("empty argument");

	sqlQuery(
		"UPDATE `" . $config["sql_table_usergroup"] . "` " .
		"SET " .
			"`UserName` = '" . sqlEscape($newName) . "' " .
		"WHERE " .
			"`UserName` = '" . sqlEscape($name) . "'"
		);

		$tables = array("radacct", "radcheck", "radreply");

		foreach ($tables as $table) {
			sqlQuery(
				"UPDATE `" . $config["sql_table_" . $table] . "` " .
					"SET " .
						"`UserName` = '" . sqlEscape($newName) . "' " .
					"WHERE " .
						"`UserName` = '" . sqlEscape($name) . "'"
			);
		}
}

/* returns void */
function changeGroupName($name, $newName)
{
	global $config;

	if (groupExists($newName))
		throw new Exception("group exists");

	if ($name == "" || $newName == "")
		throw new Exception("empty argument");

	sqlQuery(
		"UPDATE `" . $config["sql_table_usergroup"] . "` " .
		"SET " .
			"`GroupName` = '" . sqlEscape($newName) . "' " .
		"WHERE " .
			"`GroupName` = '" . sqlEscape($name) . "'"
	);

		$tables = array( "radgroupcheck", "radgroupreply");

		foreach ($tables as $table) {
			sqlQuery(
				"UPDATE `" . $config["sql_table_" . $table] . "` " .
				"SET " .
					"`GroupName` = '" . sqlEscape($newName) . "' " .
				"WHERE " .
					"`GroupName` = '" . sqlEscape($name) . "'"
			);
		}
}

/* returns void */
function cloneUser($oldName, $newName)
{
	global $config;

	if (userExists($newName))
		throw new Exception("user exists");

	if ($oldName == "" || $newName == "")
		throw new Exception("empty argument");

	sqlQuery(
		"INSERT INTO `" . $config["sql_table_usergroup"] . "` " .
			"(UserName, GroupName) " .
		"SELECT " .
			"'" . sqlEscape($newName) . "', GroupName " .
		"FROM " .
			"`" . $config["sql_table_usergroup"] . "` " .
		"WHERE " .
			"`UserName` = '" . sqlEscape($oldName) . "'"
	);

	sqlQuery(
		"INSERT INTO `" . $config["sql_table_radreply"] . "` " .
			"(UserName, Attribute, op, Value) " .
		"SELECT " .
			"'" . sqlEscape($newName) . "', Attribute, op, Value " .
		"FROM " .
			"`" . $config["sql_table_radreply"] . "` " .
		"WHERE " .
			"`UserName` = '" . sqlEscape($oldName) . "'"
	);

	sqlQuery(
		"INSERT `" . $config["sql_table_radcheck"] . "` " .
			"(UserName, Attribute, op, Value) " .
		"SELECT " .
			"'" . sqlEscape($newName) . "', Attribute, op, Value " .
		"FROM " .
			"`" . $config["sql_table_radcheck"] . "` " .
		"WHERE " .
			"`UserName` = '" . sqlEscape($oldName) . "'"
	);
}

/* returns void */
function cloneGroup($oldName, $newName)
{
	global $config;

	if (groupExists($newName))
		throw new Exception("group exists: ". $newName);

	if ($oldName == "" or $newName == "")
		throw new Exception("empty argument");

	/* TODO: should we do that?
	 * pjf: probably yes, or as an option; even better - there should be an option
	 *      which lets op to assign all users from given group to another one
	 *                 (op = operator) */

	/* ugly code below ;-P
	sqlQuery("INSERT `" . $config["sql_table_usergroup"] .
		"` (UserName, GroupName)  SELECT " .
		"'" . sqlEscape($newName) . "' , GroupName" .
		" FROM `" . $config["sql_table_usergroup"] . "` WHERE `GroupName`='" . sqlEscape($oldName) . "'");
	*/

	sqlQuery(
		"INSERT `" . $config["sql_table_radgroupreply"] . "` " .
			"(GroupName, Attribute, op, Value) " .
		"SELECT " .
			"'" . sqlEscape($newName) . "', Attribute, op, Value " .
		"FROM " .
			"`" . $config["sql_table_radgroupreply"] . "` " .
		"WHERE " .
			"`GroupName` = '" . sqlEscape($oldName) . "'"
	);

	sqlQuery(
		"INSERT `" . $config["sql_table_radgroupcheck"] . "` " .
			"(GroupName, Attribute, op, Value) " .
		"SELECT " .
			"'" . sqlEscape($newName) . "', Attribute, op, Value " .
		"FROM " .
			"`" . $config["sql_table_radgroupcheck"] . "` " .
		"WHERE " .
			"`GroupName` = '" . sqlEscape($oldName) . "'"
	);
}

/* deletes user from database
 * it does not delete accounting associated with user */
/* returns void */
function deleteUser($name)
{
	global $config;

	if ($name == "")
		throw new Exception("empty argument");

	$tables = array("usergroup", "radcheck", "radreply");

	foreach ($tables as $table) {
		sqlQuery(
			"DELETE " .
			"   FROM `" . $config["sql_table_" . $table] . "` " .
			"WHERE " .
			"   `UserName` = '" . sqlEscape($name) . "'"
		);
	}
}

/* returns void */
function deleteGroup($name)
{
	global $config;

	if ($name)
		throw new Exception("empty argument");

	sqlQuery(
		"DELETE " .
			"FROM `" . $config["sql_table_usergroup"] . "` " .
		"WHERE " .
			"`GroupName` = '" . sqlEscape($name) . "'"
	);

	$tables = array("radgroupcheck", "radgroupreply");
	foreach ($tables as $table) {
		sqlQuery(
			"DELETE " .
				"FROM `" . $config["sql_table_" . $table] . "` " .
			"WHERE " .
				"`GroupName` = '" . sqlEscape($name) . "'"
		);
	}
}

/* returns simple array of atributes names */
function getCommonAttributes()
{
	global $config;

	$tables = array("radcheck", "radreply", "radgroupreply", "radgroupcheck");
	$output = array();

	foreach ($tables as $tableName) {
		$list = sqlQuery(
		        	"SELECT DISTINCT Attribute " .
		        		"FROM `" . $config["sql_table_" . $tableName] . "`"
		        );

		foreach ($list as $id => $attr)
			$output[] = trim($attr["Attribute"]);

	}
	
	$output = array_unique($output);
	sort($output);

	return $output;
}

/* returns simple array of groups names */
function getUserGroups($user)
{
	global $config;

	$output = array();

	$list = sqlQuery(
		"SELECT DISTINCT " . SQL_USERGROUP_COLUMN_GROUP . " " .
		"FROM " .
			"`" . $config["sql_table_usergroup"] . "` " .
		"WHERE " .
			"`" . SQL_USERGROUP_COLUMN_USER . "` = '". sqlEscape($user) . "'"
	);

	foreach ($list as $id  => $attr)
		$output[] = $attr[SQL_USERGROUP_COLUMN_GROUP];

	return $output;
}

/* returns void */
function changeUserGroup($user, $oldGroupName, $newGroupName)
{
	global $config;

	if (!userExists($user))
		throw new Exception("user does not exists");

	if ($user == "" || $oldGroupName == "" || $newGroupName == "")
		throw new Exception("empty argument");

	if (in_array($newGroupName, getUserGroups($user)))
		throw new Exception("user is already in that group");

	sqlQuery(
		"UPDATE `" . $config["sql_table_usergroup"] . "` " .
		"SET " .
			"`" . SQL_USERGROUP_COLUMN_GROUP . "` = '" .
				sqlEscape($newGroupName) . "' " .
		"WHERE " .
			"`" . SQL_USERGROUP_COLUMN_USER . "` = '" .
				sqlEscape($user) . "' AND " .
			"`" . SQL_USERGROUP_COLUMN_GROUP . "` = '" .
				sqlEscape($oldGroupName) . "' " .
		"LIMIT 1"
	);
}

/* returns void */
function deleteUserGroup($user, $group)
{
	global $config;

	if (!userExists($user))
		throw new Exception("user does not exists");

	if ($user == "" || $group == "")
		throw new Exception("empty argument");

	sqlQuery(
		"DELETE " .
			"FROM `" . $config["sql_table_usergroup"] . "` " .
		"WHERE " .
			"`" .SQL_USERGROUP_COLUMN_USER . "` = '" .
				sqlEscape($user) . "' AND " .
			"`" . SQL_USERGROUP_COLUMN_GROUP. "` = '" .
				sqlEscape($group) . "' " .
		"LIMIT 1"
	);
}

/* returns void */
function addUserGroup($user, $group)
{
	global $config;

	if (in_array($group, getUserGroups($user)))
		throw new Exception("user is already in that group");

	if ($user == "" || $group == "")
		throw new Exception("empty argument");

	sqlQuery(
		"INSERT INTO `" . $config["sql_table_usergroup"] . "` " .
			"(" . SQL_USERGROUP_COLUMN_USER . ", " .
			SQL_USERGROUP_COLUMN_GROUP . ") " .
		"VALUES (" .
			"'" . sqlEscape($user) ."', " .
			"'" . sqlEscape($group) . "'" .
		")"
	);
}

/* returns bool */
function userExists($name)
{
	global $config;
	return in_array($name, getUserNames());
}

/* returns bool */
function groupExists($name)
{
	global $config;
	return in_array($name, getGroupNames());
}

/* returns array of associative arrays describing session */
function getActiveSessions()
{
	global $config;
	return sqlQuery(
		"SELECT *, " .
			"(unix_timestamp(NOW()) - unix_timestamp(`" .
			SQL_ACCT_COLUMN_STARTTIME . "`)) AS `" .
			SQL_ACCT_COLUMN_ACTIVETIME ."` " .
			"FROM `" .  $config["sql_table_radacct"]. "` " .
		"WHERE " .
			"`" . SQL_ACCT_COLUMN_STOPTIME . "` = 0"
	);
}

/* returns array of associative arrays describing unclosed sessions of $user */
function getUserActiveSessions($user)
{
	global $config;
	return sqlQuery(
		"SELECT *, (unix_timestamp(NOW()) - unix_timestamp(`AcctStartTime`)) AS `RealSessionTime` " .
			"FROM `" . $config["sql_table_radacct"] . "` " .
		"WHERE " .
			"AcctStopTime = 0 AND " .
			"UserName = '" . sqlEscape($user). "' " .
		"ORDER BY AcctStartTime DESC"
	);
}

/* returns array of associative arrays describing unclosed sessions on
 * with $nas_ip */
function getNasActiveSessions($nas_ip)
{
	global $config;
	return sqlQuery(
		"SELECT *, (unix_timestamp(NOW()) - unix_timestamp(`AcctStartTime`)) AS `RealSessionTime` " .
			"FROM `" . $config["sql_table_radacct"] . "` " .
		"WHERE " .
			"AcctStopTime = 0 AND " .
			"`" . SQL_ACCT_COLUMN_NASIP . "` = '" . sqlEscape($nas_ip). "' " .
		"ORDER BY AcctStartTime DESC"
	);
}
/* returns array of associative arrays describing unclosed sessions of $user */
function getUserLastSession($user)
{
	global $config;

	$out = sqlQuery(
		"SELECT * " .
			"FROM `" . $config["sql_table_radacct"] . "` " .
		"WHERE " .
			"UserName = '" . sqlEscape($user) . "' " .
		"ORDER BY AcctStartTime DESC " .
		"LIMIT 1"
	);

	if (isset($out[0]))
		return $out[0];

	/* XXX: pjf: what about "else"? */
}

/* returns formated string */
function getUserOnlineTime($user)
{
	global $config;

	$out = sqlQuery(
	       	"SELECT " .
	       		"(unix_timestamp(NOW()) - unix_timestamp(`AcctStartTime`)) " .
	       	"FROM `" . $config["sql_table_radacct"] . "` " .
	       	"WHERE " .
	       		"AcctStopTime = 0 AND " .
	       		"UserName = '" . sqlEscape($user). "' " .
	       	"ORDER BY AcctStartTime DESC " .
	       	"LIMIT 1"
	       );

	return $out[0][0];
}

/* returns formated string */
function getUserOfflineTime($user)
{
	global $config;

	$out = sqlQuery(
	       	"SELECT " .
	       		"(unix_timestamp(NOW()) - unix_timestamp(`AcctStartTime`)) " .
	       	"FROM `" . $config["sql_table_radacct"] . "` " .
	       	"WHERE " .
	       		"AcctStopTime != 0 AND " .
	       		"UserName = '" . sqlEscape($user). "' " .
	       	"ORDER BY AcctStartTime DESC " .
	       	"LIMIT 1"
	       );

	if (isset($out[0][0]))
		return $out[0][0];

	/* XXX: pjf: what about "else"? */
}

/* from in days and to in days AGO */
function getUserAccounting($user, $from, $to)
{
	global $config;

	$out = sqlQuery(
	       	"SELECT * " .
	       		"FROM `" . $config["sql_table_radacct"] . "` " .
	       	"WHERE " .
	       		"`UserName` = '" . sqlEscape($user) . "' AND " .
	       		"(" .
	       			"DATE_SUB(" .
	       				"CURDATE(), INTERVAL " . sqlEscape($from) . " DAY" .
	       			") < `AcctStartTime` AND " .
	       			"DATE_SUB(" .
	       				"CURDATE(), INTERVAL " . sqlEscape($to) . " DAY" .
	       			") > `AcctStartTime`" .
	       		") " .
	       	"ORDER BY `AcctStartTime` DESC"
	       );

	return $out;
}
/* from in days and to in days AGO */
function getNasAccounting($nas_ip, $from, $to)
{
	global $config;

	$out = sqlQuery(
	       	"SELECT * " .
	       		"FROM `" . $config["sql_table_radacct"] . "` " .
	       	"WHERE " .
	       		"`NASIPAddress` = '" . sqlEscape($nas_ip) . "' AND " .
	       		"(" .
	       			"DATE_SUB(" .
	       				"CURDATE(), INTERVAL " . sqlEscape($from) . " DAY" .
	       			") < `AcctStartTime` AND " .
	       			"DATE_SUB(" .
	       				"CURDATE(), INTERVAL " . sqlEscape($to) . " DAY" .
	       			") > `AcctStartTime`" .
	       		") " .
	       	"ORDER BY `AcctStartTime` DESC"
	       );

	return $out;
}

/* from in days and to in days AGO */
function getAccounting($arg_name, $like, $from, $to) {
	global $config;

	$out = sqlQuery(
	       	"SELECT * " .
	       		"FROM `" . $config["sql_table_radacct"] . "` " .
	       	"WHERE " .
	       		"`" . sqlEscape($arg_name) . "` = '" . sqlEscape($like) . "' AND " .
	       		"(" .
	       			"DATE_SUB(" .
	       				"CURDATE(), INTERVAL " . sqlEscape($from) . " DAY" .
	       			") < `AcctStartTime` AND " .
	       			"DATE_SUB(" .
	       				"CURDATE(), INTERVAL " . sqlEscape($to) . " DAY" .
	       			") > `AcctStartTime`" .
	       		") " .
	       	"ORDER BY `AcctStartTime` DESC"
	       );

	return $out;
}

/* get users online at specific time */
function getAccountingAtTime($time) {
	global $config;

	$out = sqlQuery(
		"SELECT * " .
			"FROM `" . $config["sql_table_radacct"] . "` " .
		"WHERE " .
			"(" .
			"'" . sqlEscape($time) . "'" . " > `AcctStartTime` AND " .
			"(" .
				"'" . sqlEscape($time) .  "'" . " < `AcctStopTime` OR " .
				"`AcctStopTime` = 0 " .
			") " .
			") " .
			"ORDER BY `AcctStartTime` DESC"
		);

	return $out;
}

function getUserTransferedOctets($user, $days)
{
	global $config;

	$out = sqlQuery(
	       	"SELECT " .
				"SUM(`" . SQL_ACCT_COLUMN_DOWNLOAD . "`) AS `" . SQL_ACCT_COLUMN_DOWNLOAD . "`, " .
				"SUM(`" . SQL_ACCT_COLUMN_UPLOAD . "`) AS `" . SQL_ACCT_COLUMN_UPLOAD . "` " .
	       	"FROM `" . $config["sql_table_radacct"] . "` " .
	       	"WHERE " .
	       		"`UserName` = '" . sqlEscape($user) . "' AND " .
	       		"(" .
	       			"DATE_SUB(" .
	       				"CURDATE(), INTERVAL " . sqlEscape($days) . " DAY" .
	       			") < `AcctStopTime` OR " .
	       			"`AcctStopTime` = 0" .
	       		")"
	       );

	if(!isset($out[0][0]))
		$out[0][0] = 0;

	if(!isset($out[0][1]))
		$out[0][1] = 0;

	if(!isset($out[0][SQL_ACCT_COLUMN_DOWNLOAD]))
		$out[0][SQL_ACCT_COLUMN_DOWNLOAD] = 0;

	if(!isset($out[0][SQL_ACCT_COLUMN_UPLOAD]))
		$out[0][SQL_ACCT_COLUMN_UPLOAD] = 0;

	return $out[0];
}

function getNasTransferedOctets($nas_ip, $days)
{
	global $config;

	$out = sqlQuery(
			"SELECT " .
				"SUM(`" . SQL_ACCT_COLUMN_UPLOAD . "`) AS `" . SQL_ACCT_COLUMN_UPLOAD . "`, " .
				"SUM(`" . SQL_ACCT_COLUMN_DOWNLOAD . "`) AS `" . SQL_ACCT_COLUMN_DOWNLOAD . "` " .
			"FROM `" . $config["sql_table_radacct"] . "` " .
			"WHERE " .
				" `" . SQL_ACCT_COLUMN_NASIP . "` = '" .
				sqlEscape($nas_ip) . "' AND " .
				"(" .
					"DATE_SUB(" .
					"CURDATE(), INTERVAL " . sqlEscape($days) . " DAY" .
					") < `AcctStopTime` OR " .
					"`AcctStopTime` = 0" .
				")"
		);

	if(!isset($out[0][0]))
		$out[0][0] = 0;

	if(!isset($out[0][1]))
		$out[0][1] = 0;


	if(!isset($out[0][SQL_ACCT_COLUMN_DOWNLOAD]))
		$out[0][SQL_ACCT_COLUMN_DOWNLOAD] = 0;

	if(!isset($out[0][SQL_ACCT_COLUMN_UPLOAD]))
		$out[0][SQL_ACCT_COLUMN_UPLOAD] = 0;

		return $out[0];
}

function getTodayTransferedOctets()
{
	global $config;
	
	/* NEW VERSION: mysql >= 4.1.1 */
/*	$out = sqlQuery("SELECT SUM(`AcctInputOctets`) AS `Input`, SUM(`AcctOutputOctets`) AS `Output` " .
		"FROM " . $config["sql_table_radacct"] . 
		" WHERE   DATE(AcctStartTime) = CURDATE()"); */

	$out = sqlQuery(
	       	"SELECT " .
				"SUM(`" . SQL_ACCT_COLUMN_UPLOAD . "`) AS `" . SQL_ACCT_COLUMN_UPLOAD . "`, " .
				"SUM(`" . SQL_ACCT_COLUMN_DOWNLOAD . "`) AS `" . SQL_ACCT_COLUMN_DOWNLOAD . "` " .
	       	"FROM `" . $config["sql_table_radacct"] . "` " .
	       	"WHERE " .
	       		"DATE_FORMAT(AcctStartTime, '%Y-%m-%d') = CURDATE() OR " .
	       		"`AcctStopTime` = 0"
	       );

	if(!isset($out[0][SQL_ACCT_COLUMN_DOWNLOAD]))
		$out[0][SQL_ACCT_COLUMN_DOWNLOAD] = 0;

	if(!isset($out[0][SQL_ACCT_COLUMN_UPLOAD]))
		$out[0][SQL_ACCT_COLUMN_UPLOAD] = 0;

	return $out[0];
}

function getGlobalTransferedOctets($days)
{
	global $config;

	$out = sqlQuery(
	       	"SELECT " .
				"SUM(`" . SQL_ACCT_COLUMN_UPLOAD . "`) AS `" . SQL_ACCT_COLUMN_UPLOAD . "`, " .
				"SUM(`" . SQL_ACCT_COLUMN_DOWNLOAD . "`) AS `" . SQL_ACCT_COLUMN_DOWNLOAD . "` " .
	       	"FROM `" . $config["sql_table_radacct"] . "` " .
	       	"WHERE ".
	       		"DATE_SUB(" .
	       			"CURDATE(), INTERVAL " . sqlEscape($days) . " DAY" .
	       		") < `AcctStopTime` OR " .
	       		"`AcctStopTime` = 0"
	       );

	if(!isset($out[0][0]))
		$out[0][0] = 0;

	if(!isset($out[0][1]))
		$out[0][1] = 0;

	if(!isset($out[0][SQL_ACCT_COLUMN_DOWNLOAD]))
		$out[0][SQL_ACCT_COLUMN_DOWNLOAD] = 0;

	if(!isset($out[0][SQL_ACCT_COLUMN_UPLOAD]))
		$out[0][SQL_ACCT_COLUMN_UPLOAD] = 0;

	return $out[0];
}

function getTopNasTransferedOctets($days, $limit, $sort_by, $reverse)
{
	global $config;
	$query =
			"SELECT " .
				"SUM(`" . SQL_ACCT_COLUMN_UPLOAD . "`) AS `" . SQL_ACCT_COLUMN_UPLOAD . "`, " .
				"SUM(`" . SQL_ACCT_COLUMN_DOWNLOAD . "`) AS `" . SQL_ACCT_COLUMN_DOWNLOAD . "`, " .
				"`" . SQL_ACCT_COLUMN_NASIP . "` " .
			"FROM `" . $config["sql_table_radacct"] . "` " .
			"WHERE ".
				"DATE_SUB(" .
					"CURDATE(), INTERVAL " . sqlEscape($days) . " DAY" .
				") < `AcctStopTime` OR " .
				"`AcctStopTime` = 0 " .
			"GROUP BY `" . SQL_ACCT_COLUMN_NASIP . "` " .
			"ORDER BY `" . $sort_by . "` ";

	if ($reverse) {
		$query .= " ASC ";
	} else {
		$query .= " DESC ";
	}
	$query .= " LIMIT " . sqlEscape($limit);

	$out = sqlQuery($query);

	return $out;
}


function getTopUserTransferedOctets($days, $limit, $sort_by, $reverse)
{
	global $config;
	$query =
			"SELECT " .
				"SUM(`" . SQL_ACCT_COLUMN_UPLOAD . "`) AS `" . SQL_ACCT_COLUMN_UPLOAD . "`, " .
				"SUM(`" . SQL_ACCT_COLUMN_DOWNLOAD . "`) AS `" . SQL_ACCT_COLUMN_DOWNLOAD . "`, " .
				"`" . SQL_ACCT_COLUMN_USER . "` " .
			"FROM `" . $config["sql_table_radacct"] . "` " .
			"WHERE ".
				"DATE_SUB(" .
					"CURDATE(), INTERVAL " . sqlEscape($days) . " DAY" .
				") < `AcctStopTime` OR " .
				"`AcctStopTime` = 0 " .
				"GROUP BY `" . SQL_ACCT_COLUMN_USER . "` " .
			"ORDER BY `" . $sort_by . "` ";

	if ($reverse) {
		$query .= " ASC ";
	} else {
		$query .= " DESC ";
	}
	$query .= " LIMIT " . sqlEscape($limit);

	$out = sqlQuery($query);

	return $out;
}

function addNas($name, $longName, $type, $ports,
                $secret, $community, $description)
{
	global $config;

	if ($name == "")
		throw new Exception("empty argument");

	sqlQuery(
		"INSERT INTO `" . $config["sql_table_nas"] . "` " .
			"(nasname, shortname, type, ports, secret, community, description) " .
		"VALUES (" .
			"'" . sqlEscape($longName) ."', " .
			"'" . sqlEscape($name) ."', " .
			"'" . sqlEscape($type) ."', " .
			"'" . sqlEscape($ports) ."', " .
			"'" . sqlEscape($secret) ."', " .
			"'" . sqlEscape($community) ."', " .
			"'" . sqlEscape($description) . "'" .
		")"
	);
}

function modNas($name, $longName, $type, $ports,
                $secret, $community, $description)
{
	global $config;

	if ($name == "")
		throw new Exception("empty argument");

	sqlQuery(
		"UPDATE `" . $config["sql_table_nas"] . "` " .
		"SET " .
			"`nasname` = '" . sqlEscape($longName) ."', " .
			"`shortname` = '" . sqlEscape($name) ."', " .
			"`type` = '" . sqlEscape($type) ."', " .
			"`ports` = '" . sqlEscape($ports) ."', " .
			"`secret` = '" . sqlEscape($secret) ."', " .
			"`community` = '" . sqlEscape($community) ."', " .
			"`description` = '" . sqlEscape($description) . "' " .
		"WHERE " .
			"`shortname` = '" . sqlEscape($name) . "' " .
		"LIMIT 1"
	);
}

function deleteNas($name)
{
	global $config;

	if ($name == "")
		throw new Exception("empty argument");

	sqlQuery(
		"DELETE " .
			"FROM `" . $config["sql_table_nas"] . "` " .
		"WHERE " .
			"`shortname` = '" . sqlEscape($name) . "' " .
		"LIMIT 1"
	);
}

function cloneNas($oldName, $newName)
{
	global $config;

	if ($oldName == "" || $newName == "")
		throw new Exception("empty argument");

	sqlQuery(
		"INSERT `" . $config["sql_table_nas"] . "` " .
			"(shortname, `type`, ports, secret, community, description) " .
		"SELECT " .
			"'" . sqlEscape($newName) . "' , `type`, ports, secret, community, description " .
		"FROM `" . $config["sql_table_nas"] . "` " .
		"WHERE " .
			"`shortname` = '" . sqlEscape($oldName) . "'"
	);
}

function changeNasName($oldName, $newName)
{
	global $config;

	if ($oldName == "" || $newName == "")
		throw new Exception("empty argument");

	sqlQuery(
		"UPDATE `" . $config["sql_table_nas"] . "` " .
		"SET " .
			"`shortname` = '" . sqlEscape($newName) . "' " .
		"WHERE " .
			"`shortname` = '" . sqlEscape($oldName) . "' " .
		"LIMIT 1");
}

function getUsersByMac($mac)
{
	global $config;

	$output = array();

	$list1 = sqlQuery(
		"SELECT DISTINCT " . SQL_ACCT_COLUMN_USER .
		" FROM `" . $config["sql_table_radacct"] . "` " .
		" WHERE " .
		" `" . SQL_ACCT_COLUMN_CALLINGSTATION . "` LIKE '%" .
		sqlEscape($mac) . "%'"
	);

	foreach ($list1 as $attr) {
		$output[] = $attr[0];
	}

	$output = array_unique($output);
	return $output;
}

/* returns last mac like $mac that user with $login used 
 * if nothing found - returns null */
function getUserLastMacLike($login, $mac)
{
	global $config;

	$output = array();

	$list1 = sqlQuery(
		"SELECT " . SQL_ACCT_COLUMN_USER . " , " .
		SQL_ACCT_COLUMN_CALLINGSTATION . " " .
		" FROM `" . $config["sql_table_radacct"] . "` " .
		" WHERE " .
		" `" . SQL_ACCT_COLUMN_USER. "` = '" .
		sqlEscape($login) . "' AND " .
		" `" . SQL_ACCT_COLUMN_CALLINGSTATION . "` LIKE '%" .
		sqlEscape($mac) . "%'" .
		"LIMIT 1"
	);

	foreach ($list1 as $attr) {
		return $attr[SQL_ACCT_COLUMN_CALLINGSTATION];
	}
	return null;
}

function getUsersByLogin($login)
{
	global $config;

	$output = array();

	$list1 = sqlQuery(
		"SELECT DISTINCT " . SQL_USERGROUP_COLUMN_USER .
		" FROM `" . $config["sql_table_usergroup"] . "` " .
		" WHERE " .
		" `" . SQL_USERGROUP_COLUMN_USER . "` LIKE '%" .
		sqlEscape($login) . "%'"
	);

	$list2 = sqlQuery(
		"SELECT DISTINCT " . SQL_RADCHECK_COLUMN_USER .
		" FROM `" . $config["sql_table_radcheck"] . "` " .
		" WHERE " .
		" `" . SQL_RADCHECK_COLUMN_USER . "` LIKE '%" .
		sqlEscape($login) . "%'"
	);

	$list3 = sqlQuery(
		"SELECT DISTINCT " . SQL_RADREPLY_COLUMN_USER .
		" FROM `" . $config["sql_table_radreply"] . "` " .
		" WHERE " .
		" `" . SQL_RADREPLY_COLUMN_USER . "` LIKE '%" .
		sqlEscape($login) . "%'"
	);

	foreach ($list1 as $attr) {
		$output[] = $attr[0];
	}
	foreach ($list2 as $attr) {
		$output[] = $attr[0];
	}
	foreach ($list3 as $attr) {
		$output[] = $attr[0];
	}

	$output = array_unique($output);
	return $output;
}

function getUserRealName($user_name)
{
	if (function_exists('getUserExtInfo_Impl')) {
		return getUserExtInfo_Impl($user_name, SQL_USER_COLUMN_REALNAME);
	}
	return null;
}

function getUserAddress($user_name)
{
	if (function_exists('getUserExtInfo_Impl')) {
		return getUserExtInfo_Impl($user_name, SQL_USER_COLUMN_ADDRESS);
	}
	return null;
}

function getUserEmail($user_name)
{
	if (function_exists('getUserExtInfo_Impl')) {
		return getUserExtInfo_Impl($user_name, SQL_USER_COLUMN_EMAIL);
	}
	return null;
}

function getUserHomePhone($user_name)
{
	if (function_exists('getUserExtInfo_Impl')) {
		return getUserExtInfo_Impl($user_name, SQL_USER_COLUMN_HOMEPHONE);
	}
	return null;
}

function getUserNotes($user_name)
{
	if (function_exists('getUserExtInfo_Impl')) {
		return getUserExtInfo_Impl($user_name, SQL_USER_COLUMN_NOTES);
	}
	return null;
}

function getUserPhone1($user_name)
{
	if (function_exists('getUserExtInfo_Impl')) {
		return getUserExtInfo_Impl($user_name, SQL_USER_COLUMN_PHONE1);
	}
	return null;
}

function getUserPhone2($user_name)
{
	if (function_exists('getUserExtInfo_Impl')) {
		return getUserExtInfo_Impl($user_name, SQL_USER_COLUMN_PHONE2);
	}

	return null;
}

/*
 * returns plain array of all users with $phone
 * where $phone may be part of a phone number
 */
function getUsersByPhone($phone)
{
	if (function_exists('getUsersByExtInfo_Impl')) {
		return array_merge(
			getUsersByExtInfo_Impl($phone, SQL_USER_COLUMN_PHONE1),
			getUsersByExtInfo_Impl($phone, SQL_USER_COLUMN_PHONE2)
		);
	}
	return array();
}

/*
 * returns plain array of all users with $address
 * where $address may be part of a address
 */
function getUsersByAddress($address)
{
	if (function_exists('getUsersByExtInfo_Impl')) {
		return getUsersByExtInfo_Impl($address, SQL_USER_COLUMN_ADDRESS);
	}
	return array();
}

/*
 * returns plain array of all users with $name
 * where $name may be part of a user full name (not login)
 */
function getUsersByName($name)
{
	if (function_exists('getUsersByExtInfo_Impl')) {
		return getUsersByExtInfo_Impl($name, SQL_USER_COLUMN_REALNAME);
	}
	return array();
}

function modUserExtInfo($user, $name, $address, $email,
	$phone1, $phone2, $notes)
{
	if (function_exists('modUserExtInfo_Impl')) {
		modUserExtInfo_Impl($user, $name, $address, $email,
			$phone1, $phone2, $notes);
	}
	else
		throw new Exception("no modUserExtInfo_Impl");
}

?>
