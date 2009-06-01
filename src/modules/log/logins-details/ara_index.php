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

/* check access */
if ($config["access_level"] < ARA_ACCESS_VIEW)
	throw new Exception($i18n["i18n_action_not_allowed"]);

$tpl->setCurrentBlock("module_output");
$tpl->addBlockfile("module_generated", "foo", "template.html");

$tpl->setCurrentBlock("bad_logins");
$tpl->touchBlock("bad_logins");
$tpl->setVariable("user", $_GET["user"]);   /* value checked in index.php */

$old_dir = getcwd();
chdir("../../user/");
require_once("common.php");
generateCommonMenu($_GET["user"]);
chdir($old_dir);

function sig_handler($signo)
{
	global $process;
	global $pipes;

	if ($signo == SIGALRM) {
		fclose($pipes[1]);
		proc_close($process);
	}
}

require_once "../config.php";

$descriptorspec = array(
	0 => array("pipe", "r"),
	1 => array("pipe", "w"),
	2 => array("pipe", "w")
);

$process = proc_open($config["php_bin"] . " ../getlog.php",
                     $descriptorspec, $pipes, getcwd());

if (!is_resource($process))
	throw new Exception($i18n["i18n_couldnt_spawn_process"]);

fclose($pipes[0]);

$output = stream_get_contents($pipes[1]);
fclose($pipes[1]);

$err = stream_get_contents($pipes[2]);
fclose($pipes[2]);

if (proc_close($process) != 0)
	throw new Exception($i18n["i18n_command_didnt_returned_zero"] .
	                     "`" . $err . "'");

$out = array();

foreach (explode("\n", $output) as $line) {
	/* TODO is this good? */

	preg_match("/cli (.*)\)$/i", $line, $workstation);
	preg_match("/\[([^\/\]]+)/i", $line, $user);
	preg_match("/^.* :/i", $line, $date);

	$date = preg_replace("/ :$/i", "", $date);

	if (isset($user[1]) && $user[1] == $_GET["user"]) {
		$out[] = array(
			"date" => $date[0],
			"workstation" => $workstation[1]
		);
	}
}

$tpl->setCurrentBlock("bad_logins_list");
foreach ($out as $log) {
	$tpl->setVariable($log);
	$tpl->parse("bad_logins_list");
}

?>
