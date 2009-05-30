<?php
/* Asn Radius Admin
 * Copyright (C) ASN http://www.asn.pl/ 2005
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


function generateUrl($parameters)
{
	if (!isset($parameters["mm"]))
		$parameters["mm"] = $_GET["mm"];

	if (!isset($parameters["mg"]))
		$parameters["mg"] = $_GET["mg"];

	return "index.php?" .
	       http_build_query($parameters);
}

$i18n = array();

function reload_translations($lang) {
	global $i18n;

	$old_i18n = $i18n;

	if (file_exists(dirname(__FILE__) . '/../lang/' . $lang . '.php'))
		include dirname(__FILE__) . '/../lang/' . $lang . '.php';

	$i18n = array_merge($old_i18n, $i18n);
}

function i18n($str) {
	global $i18n;
	if (array_key_exists("i18n_" . $str, $i18n)) {
		return $i18n["i18n_" . $str];
	}

	return $str . "?";
}

function bytesToStr($bytes)
{
	define("KILO", 1024);
	define("MEGA", KILO * KILO);
	define("GIGA", MEGA * KILO);
	define("TERA", GIGA * KILO);

	if (!isset($bytes))
		return "N/A";

	else if ($bytes < 2 * KILO) {
		$ret = $bytes;
		$unit = " B";
	} else if ($bytes < 2 * MEGA) {
		$ret = round($bytes / KILO, 2);
		$unit = "KB";
	} else if ($bytes < 2 * GIGA) {
		$ret = round($bytes / MEGA, 2);
		$unit = "MB";
	} else if ($bytes < 2 * TERA) {
		$ret = round($bytes / GIGA, 2);
		$unit = "GB";
	} else {
		$ret = round($bytes / TERA, 2);
		$unit = "TB";
	}

	return sprintf("%.2f", $ret) . $unit;
}

/* other format? */
function secToStr($elapsedTime)
{
	$elapsedTime = intval($elapsedTime);

	$sec = $elapsedTime % 60;
	$elapsedTime = ($elapsedTime - $sec) / 60;

	$min = $elapsedTime % 60;
	$h = ($elapsedTime - $min) / 60;

	if ($min < 10) {
		$min = '0' . $min;
	}
	if ($sec < 10) {
		$sec = '0' . $sec;
	}

	return $h . ':' . $min . '.' . $sec;
}

/* dpc - just another PHP hacker ;-) --pjf */
function smartCmp($a, $b)
{
	/* $a's and $b's length */
	$al = strlen($a);
	$bl = strlen($b);

	/* indexes: $i for $a, $j for $b */
	$i = 0;
	$j = 0;

	while ($i < $al && $j < $bl) {
		/* here we hold numbers from $a and $b */
		$an = 0;
		$bn = 0;

		/* read number in $a, starting at index $i */
		while ($i < $al && ctype_digit($a[$i]))
			$an = 10 * $an + $a[$i++];

		/* read number in $b, starting at index $j */
		while ($j < $bl && ctype_digit($b[$j]))
			$bn = 10 * $bn + $b[$j++];

		/* if we read some number */
		if ($bn != 0 || $an != 0) {
			/* if they're the same, we can't make decision */
			if ($an != $bn)
				return ($an < $bn) ? -1 : 1;
		}
		else {
			/* comment as above */
			if ($a[$i] != $b[$j])
				return ($a[$i] < $b[$j]) ? -1 : 1;

			/* move to next character */
			$i++;
			$j++;
		}
	}

	/* they're the same */
	return  0;
}

function redirectPage($params)
{
	$url = generateUrl($params);

	header("Location: " . str_replace("&amp;", "&", $url));
	exit();
}

function htmlize($string)
{
	return htmlentities(
		$string,
		ENT_QUOTES, "UTF-8"
	);
}
?>
