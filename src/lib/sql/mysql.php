<?php
/* Asn Radius Admin
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

if (!function_exists("mysql_connect")) {
	    throw new Exception("mysql php extension not installed (bad php.ini?)");
}

$mysql_connection = @mysql_connect($config["sql_server_host"] . ":" .
                       $config["sql_server_port"],
                       $config["sql_username"],
                       $config["sql_passwd"]);

if ($mysql_connection === FALSE)
	throw new Exception("cannot connect to DB");

mysql_query("SET NAMES '" . $config["sql_encoding"] . "'", $mysql_connection);

$db_selected = @mysql_select_db($config["sql_db"], $mysql_connection);
if ($db_selected === FALSE)
	throw new Exception("cannot select DB");

/*!
 * @short returns array made from query result
*/
function sqlQuery($query, $quiet = FALSE)
{
	global $config;
	global $mysql_connection;

	if ($config["sql_debug"])
		echo "DEBUG[sql]: $query<br/>";

	$resource = mysql_query($query, $mysql_connection);
	if ($resource === FALSE) {
		if ($quiet) return FALSE;
		throw new Exception("query `" . $query . "' failed");
	}

	$result = array();

	if (is_resource($resource) && mysql_num_rows($resource) > 0)
		while ($row = mysql_fetch_array($resource, MYSQL_BOTH))
			$result[] = $row;

	return $result;
}

/*!
 * @short returns array made from query result and do nothing on error
*/
function sqlQuietQuery($query)
{
	return sqlQuery($query, TRUE);
}

function sqlClose()
{
	return @mysql_close($mysql_connection);
}

function sqlEscape($string)
{
	return @mysql_real_escape_string($string);
}

?>
