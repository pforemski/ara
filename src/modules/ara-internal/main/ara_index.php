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


$tpl->setCurrentBlock("module_output");
$tpl->addBlockfile("module_generated", "foo", "template.html");

switch ($lang) {
	case "pl":
		$tpl->setVariable("welcome", "Witaj w Asn Radius Admin (ARA) v. " .
			ARA_VERSION . "!");
		$tpl->setVariable("license", "Program ten udostępniony jest na zasadach " .
			"licencji GPL v2, bądź późniejszej. Treść licencji dostępna jest w " .
			"pliku COPYING w archiwum z kodem programu, bądź na stronie " .
			"<a href=\"http://www.gnu.org/licenses/gpl.html\">projektu GNU</a>.");
		$tpl->setVariable("arainfo", "ARA tworzona jest przez " .
			"<a href=\"http://www.asn.pl/\">ASN</a>. Jeżeli jesteś programistą, " .
			"zapraszamy Cię na <a href=\"http://projects.asn.pl/ara/\"> stronę " .
			"naszego projektu</a>.");
		break;

	default:
		$tpl->setVariable("welcome", "Welcome in Asn Radius Admin (ARA) v. " .
			ARA_VERSION . "!");
		$tpl->setVariable("license", "This program is free software; you can " .
			"redistribute it and/or modify it under the terms of the GNU General " .
			"Public License. See the COPYING file and " .
			"<a href=\"http://www.gnu.org/licenses/gpl.html\">project GNU</a>.");
		$tpl->setVariable("arainfo", "ARA is being developed by " .
			"<a href=\"http://www.asn.pl/\">ASN</a>. If you're a developer, you're " .
			"welcome to visit <a href=\"http://projects.asn.pl/ara/\"> our " .
			"project's website</a>.");
		break;
}

?>
