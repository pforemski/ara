#!/usr/bin/env php
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

if (php_sapi_name() != "cli")
	die("!");

declare(ticks = 1);

/* signal handler function */
function sig_handler($signo)
{
	global $proces;
	proc_terminate($proces);
	die("timeout/signal");
}

require_once "../config.php";

if ($cmd == "")
	die("$cmd not set");

pcntl_signal(SIGALRM, "sig_handler");
pcntl_alarm(15);

$descriptorspec = array(
	0 => array("pipe", "r"),
	1 => array("pipe", "w"),
	2 => array("pipe", "w")
);

if ($run_through_sudo == true) {
	$process = proc_open("$sudo_path -S -u $sudo_run_as $cmd",
	                     $descriptorspec, $pipes);

	if (is_resource($proc)) {
		$first = fread($pipes[2], 9);
		if ($first == "Password:" ||
		    $first == "\nWe trust") {
			fwrite($pipes[0], "$sudo_pass\n");
		}
		else {
			$ret .= $first;
		}
	} else {
		fwrite(STDERR, "problem starting sudo command: " .
		               "$sudo_path -S -u $sudo_run_as $cmd\n");
		exit(1);
	}

} /* sudo mode */
else {
	$process = proc_open($cmd, $descriptorspec, $pipes);
}

fclose($pipes[0]);

$output = stream_get_contents($pipes[1]);
fclose($pipes[1]);

$err = stream_get_contents($pipes[2]);
fclose($pipes[2]);

echo $output;

if (proc_close($process) != 0) {
	fwrite(STDERR, $err);
	exit(1);
}

?>
