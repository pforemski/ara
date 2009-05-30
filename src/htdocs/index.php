<?php
/* Asn Radius Admin
 * Copyright (C) ASN http://www.asn.pl 2007
 *           and Dawid Ciezarkiewicz <dpc@asn.pl> 2007
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

/* CHANGEME: path to ARA, relative to dir of index.php */
define("ARA_PATH", "../");

/* ARA version */
define("ARA_VERSION", "0.6");

/* access levels */
define("ARA_ACCESS_NONE",     1); /* guess :) */
define("ARA_ACCESS_VIEW",     2); /* can view (without valuable information */
define("ARA_ACCESS_VIEW_ALL", 3); /* can view everything */
define("ARA_ACCESS_EDIT",     4); /* saving of forms */
define("ARA_ACCESS_ALL",      5); /* Charlie Root */

/* go directly to our dir */
chdir(ARA_PATH);

/* verify passed arguments ASAP */
define("ARA_ARG_REGEXP", '/^[a-z0-9_\-]+$/');

if (isset($_GET["mg"]) && !preg_match(ARA_ARG_REGEXP, $_GET["mg"]))
	die("Invalid mg.");

if (isset($_GET["mm"]) && !preg_match(ARA_ARG_REGEXP, $_GET["mg"]))
	die("Invalid mm.");

if (isset($_GET["nmg"]) && !preg_match(ARA_ARG_REGEXP, $_GET["nmg"]))
	die("Invalid nmg.");

if (isset($_GET["nmm"]) && !preg_match(ARA_ARG_REGEXP, $_GET["nmm"]))
	die("Invalid nmm.");

/* we include config/users/PHP_AUTH_USER, so better check it */
if (isset($_SERVER['PHP_AUTH_USER']) &&
    !preg_match(ARA_ARG_REGEXP, $_SERVER['PHP_AUTH_USER']))
	die("Invalid characters in HTTP username.");

/* include config */
require_once "config/config.php";

/* now we know whether debugging is enabled */
if ($config["debug"])
	error_reporting(E_ALL | E_STRICT);
else
	error_reporting(E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR);

/* include common funcs */
require_once "lib/common.php";

/* init templates engine */
require_once "HTML/Template/Sigma.php";


$tpl = new HTML_Template_Sigma("./");
$tpl->loadTemplateFile("template.html");
$tpl->touchBlock('quick');
if ($config["sql_user_extension"]) {
	$tpl->touchBlock('quick_user_by_phone');
}

/* set default language */
if (isset($_COOKIE["lang"]))
	$lang = $_COOKIE["lang"];
else
	$lang = "en";

/* load translations */
reload_translations('en');
reload_translations($lang);

/* save current dir */
$currentDir = getcwd();

/* date when we started */
$tpl->setGlobalVariable('date_and_time', date("m.d.y, H:i:s"));

/* catch critical errors */
try {
	/* auth at all? */
	if ($config["use_auth"]) {
		if (!isset($_SERVER["PHP_AUTH_USER"])) {
			header("WWW-Authenticate: Basic realm=\"Ara\"");
			header("HTTP/1.0 401 Unauthorized");
			exit();
		}

		if (file_exists("./config/users/" .
			$_SERVER["PHP_AUTH_USER"] .  ".php")) {
				require "./config/users/" . $_SERVER["PHP_AUTH_USER"] . ".php";
			}
		else {
			if ($config["force_user_file"]) {
				throw new Exception ($i18n["i18n_not_authorized"]);
			}
		}


		if (isset($ara_user["pass"])) {
			if ($ara_user["pass"] != $_SERVER["PHP_AUTH_PW"]) {
				throw new Exception ($i18n["i18n_not_authorized"]);
			}
		}
		else {
			if (!$config["allow_user_file_without_pass"]) {
				throw new Exception ($i18n["i18n_not_authorized"]);
			}
		}
	}

	/* set selected quicksel */
	if (isset($_COOKIE['quicksel']) && $_COOKIE['quicksel'] != '') {
		$tpl->setGlobalVariable($_COOKIE['quicksel'], 'selected');
	}

	/* default is "welcome page" */
	if (!isset($_GET["mg"]) && !isset($_GET["mm"])) {
		$_GET["mg"] = "ara-internal";
		$_GET["mm"] = "main";
	}

	/* if set, draw requested module output */
	if (isset($_GET["mg"]) && isset($_GET["mm"])) {

		$mg = $_GET["mg"];
		$mm = $_GET["mm"];

		if (!file_exists("./modules/" . $mg . "/" . $mm))
			throw new Exception($i18n["i18n_wrong_module"]);

		/* do not try to load database if module does not need it */
		$needsDatabase = true;
		require './modules/' . $mg . '/' . $mm . '/ara_config.php';
		if ($needsDatabase == true) {
				require_once 'lib/' . $config['radius_storage'] . '.php';
		}
		$tpl->setGlobalVariable('page_title', $moduleLongName);

		/*  check permisions to access this module by current user */
		if ($config['default_access'] == true) {
			foreach ($config['forbidden_modules'] as $forbiddenModule){
				$arr = explode('/', $forbiddenModule);
				if ($arr[0] == $mg && $arr[1] == $mm) {
					throw new Exception($i18n["i18n_not_allowed"]);
				}
			}
		}
		else {
			$ok = FALSE;

			foreach ($config["allowed_modules"] as $allowedModule) {
				$arr = explode("/", $allowedModule);
				if ($arr[0] == $mg && $arr[1] == $mm) {
					$ok = TRUE;
					break;
				}
			}

			if(!$ok)
				throw new Exception($i18n["i18n_not_allowed"]);
		}

		/* go to module dir and run it */
		chdir("./modules/" . $mg . "/" . $mm);
		$tpl->setCurrentBlock("module_output");
		if (!file_exists("./ara_index.php"))
			die("defected module");

		include_once "./ara_index.php";
		chdir($currentDir);
	}

$tpl->setVariable("edit_user_url", generateUrl(array("mm"=> "edit", "mg" => "user")));

$groupsDir = opendir("./modules");

if ($groupsDir === FALSE)
	die("Error reading ./modules");

$tpl->setCurrentBlock("mg_list");

/* read group dirs, remove unused, sort */
while (FALSE !== ($groupEntry = readdir($groupsDir))) {
	/* skip hidden, "." and ".." dirs */
	if ($groupEntry[0] == '.') continue;

	$groupDirName = "./modules/" . $groupEntry;
	$groupLongName = $groupEntry;

	if (!is_dir($groupDirName) ||
	    !file_exists($groupDirName . "/ara_config.php"))
		continue;

	$prio = "";
	$excludeFromMenu = FALSE;
	require $groupDirName . "/ara_config.php";

	if($excludeFromMenu) continue;

	if($prio == "")
		throw new Exception("prio not set in " . $groupDirName . "/ara_config.php");

	$groupDirs[$prio . $groupEntry] = $groupEntry;
}

ksort($groupDirs);

/* draw sorted groups */
foreach ($groupDirs as $prio => $groupEntry) {
	$groupDirName = "./modules/" . $groupEntry;
	$groupLongName = $groupEntry;

	/* read lisf of modules in group, remove usnnecessary, sort */
	$modulesDir = opendir($groupDirName);
	if ($modulesDir === FALSE)
		die("Error reading " . $groupDirName);
	unset($moduleDirs);

	while (FALSE !== ($moduleEntry = readdir($modulesDir))) {
		if ($moduleEntry[0] == '.')
			continue;

		$moduleDirName = $groupDirName . "/" . $moduleEntry;
		if (!is_dir($moduleDirName))
			continue;

		if (!file_exists($moduleDirName . "/ara_config.php"))
			continue;

		$prio = "";
		$excludeFromMenu = FALSE;
		require $moduleDirName . "/ara_config.php";
		if($excludeFromMenu)
			continue;

		if($prio == "")
			throw Exception("prior not set in " . $moduleDirName . "/ara_config.php");

		$moduleDirs[$prio . $moduleEntry] = $moduleEntry;
	}

	ksort($moduleDirs);

	foreach($moduleDirs as $prio => $moduleEntry) {
		$moduleDirName = $groupDirName . "/" . $moduleEntry;
		$moduleLongName = $moduleEntry;
		$moduleAccesskey = "";
		$moduleUrl = generateUrl(
			array("mg" => $groupEntry ,"mm" => $moduleEntry)
		);

		global $i18n;

		require $groupDirName . "/ara_config.php";
		require $moduleDirName . "/ara_config.php";

		$tpl->setVariable("module_url", $moduleUrl);
		$tpl->setVariable("mm_name", $moduleLongName);
		if ($moduleAccesskey != "") {
			$tpl->setVariable("module_accesskey", $moduleAccesskey);
		}
		$tpl->parse("mm_list");
	}

	$tpl->setVariable("mg_name", $groupLongName);
	$tpl->parse("mg_list");
}

if ($config["use_quick_stats"]) {
	require_once "lib/" . $config["radius_storage"] . ".php";
	$stats = getTodayTransferedOctets();
	$tpl->setCurrentBlock("quick_stats");
	$tpl->setVariable("quick_stats_download", bytesToStr($stats["Output"]));
	$tpl->setVariable("quick_stats_upload", bytesToStr($stats["Input"]));
}
} catch (Exception $e) {
	/* draw critical error message */
	chdir($currentDir);
	$tpl->setCurrentBlock("fatal_error");
	$tpl->setVariable("msg", $e->getMessage());
}
/* replace all i18n strings */
//require "./lang/en.php";
$tpl->setGlobalVariable("mm", $mm);
$tpl->setGlobalVariable("mg", $mg);
$tpl->setGlobalVariable($i18n);

//$tpl->setGlobalVariable($i18n);
//if ($lang != "en") {
//	include "./lang/" . $lang . ".php";
//	$tpl->setGlobalVariable($i18n);
//}



/* draw template and say "bye, bye" ;-) */
$tpl->show();

?>
