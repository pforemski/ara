<?php
/* Asn Radius Admin
 * Copyright (C) ASN http://www.asn.pl 2005 - 2006
 *           and Dawid Ciezarkiewicz <dpc@asn.pl> 2005 - 2006
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
$tpl->setCurrentBlock("sessions");
$tpl->touchBlock("sessions");

/* resonably defaults */

if (!isset($_GET["from"])) {
	if (isset($_GET["search"]) && $_GET["search"] == "nas-name") {
		$_GET["from"] = 1;
	}
	else {
		$_GET["from"] = 7;
	}
} else {
	$_GET["from"] = intval($_GET["from"]);
}

if (!isset($_GET["to"])) {
	$_GET["to"] = 0;
} else {
	$_GET["to"] = intval($_GET["to"]);
}

$tpl->setVariable("from", $_GET["from"]);
$tpl->setVariable("to", $_GET["to"]);

if ($_GET["to"] > $_GET["from"]) {
	$tmp = $_GET["to"];
	$_GET["to"] = $_GET["from"];
	$_GET["from"] = $tmp;
	$reverse = 0;
} else {
	$reverse = 1;
}


$nas_names = getNasNames();

$nas_ip_to_name = array();
$nas_name_to_ip = array();
$nas_ip_to_descr = array();

foreach ($nas_names as $nas_name) {
	$nas = getNas($nas_name);
	$nas_ip_to_name[$nas[SQL_NAS_COLUMN_IP]] =
		$nas[SQL_NAS_COLUMN_NAME];
	$nas_ip_to_descr[$nas[SQL_NAS_COLUMN_IP]] =
		$nas[SQL_NAS_COLUMN_DESCRIPTION];
	$nas_name_to_ip[$nas[SQL_NAS_COLUMN_NAME]] =
		$nas[SQL_NAS_COLUMN_IP];
}

if (file_exists('../../../lib/pdf/tcpdf/fonts/')) {
	$tpl->touchBlock('pdf_output_support');
}

switch ($_GET["format"]) {
	case 'pdf':
		global $config;
		define('FPDF_FONTPATH','../../../lib/pdf/tcpdf/fonts/');
		require('../../../lib/pdf/tcpdf/tcpdf.php');

		$pdf_font =
			$config['pdf_font'] ? $config['pdf_font'] : 'FreeMono';

		$pdf_font_size =
			$config['pdf_font_size'] ? $config['pdf_font_size'] : 9;

		$pdf_header_title =
			$config['pdf_header_title'] ? $config['pdf_header_title'] : 'ARA';

		$pdf_left_margin =
			$config['pdf_left_margin'] ? $config['pdf_left_margin'] : 30;
		$pdf_top_margin =
			$config['pdf_top_margin'] ? $config['pdf_top_margin'] : 20;
		/* width of columns */
		$w = $config['pdf_column_size'] ?
			$config['pdf_column_size'] : array(15, 38, 20, 30, 20, 20);


		class PDF extends TCPDF {
			public function Footer() {
				global $pdf_font_size;
				global $pdf_font;
				$this->SetY(-25);
				$this->SetFont($pdf_font, 'I', $pdf_font_size * 1.2);

				$this->Cell(
					0, 10,
					ucwords(i18n('page')) . ' ' . $this->PageNo().'/{nb}',
					0, 0, 'C'
				);
				$this->Ln();
				$this->SetFont($pdf_font, 'I', $pdf_font_size * 0.7);
				$this->Cell(
					0, 10,
					'generated by ASN Radius Admin',
					0, 0, 'C'
				);
				$this->Ln();
			}

			public function Header() {
				global $pdf_font_size;
				global $pdf_font;
				global $pdf_header_title;
				//$this->SetY(-15);
				$this->SetFont($pdf_font, 'I', $pdf_font_size * 2);

				$this->Cell(0, 10, $pdf_header_title ,0,0,'C');
			}
		}
		$pdf = new PDF();
		$pdf->AliasNbPages();
		
		if ($config['pdf_lang']) {
			reload_translations($config['pdf_lang']);
		}
		$pdf->SetFont($pdf_font, '', $pdf_font_size);
		$pdf->SetLeftMargin($pdf_left_margin);
		$pdf->SetRightMargin($pdf_left_margin);
		$pdf->AddPage();

		/* top margin */
		$pdf->Cell($pdf_left_margin, $pdf_top_margin, '', '', 0, 'C', 0);
		$pdf->Ln();
		break;
	default:
}

if (isset($_GET["search"]) && !$_GET["search"] == "") {
	
	switch ($_GET["search"]) {
	case "ip":
		$tpl->setGlobalVariable('top_header', i18n('accounting'));
		$sessions = getAccounting(SQL_ACCT_COLUMN_USERIP,
			$_GET["value"], $_GET["from"], $_GET["to"] - 1);
		break;

	case "nas-ip":
		$tpl->setGlobalVariable('top_header', i18n('accounting'));
		$sessions = getAccounting(SQL_ACCT_COLUMN_NASIP,
			$_GET["value"], $_GET["from"], $_GET["to"] - 1);
		break;

	case "nas-name":
		$old_dir = getcwd();
		chdir("../../nas/");
		include("common.php");
		generateCommonMenu($_GET["value"]);
		chdir($old_dir);
		$sessions = getAccounting(SQL_ACCT_COLUMN_NASIP,
			$nas_name_to_ip[$_GET["value"]], $_GET["from"], $_GET["to"] - 1);
		break;

	case "workstation":
		$tpl->setGlobalVariable('top_header', i18n('accounting'));
		$sessions = getAccounting(SQL_ACCT_COLUMN_CALLINGSTATION,
			$_GET["value"], $_GET["from"], $_GET["to"] - 1);
		break;

	case "login":
		$_GET['user'] = $_GET['value'];
		switch ($_GET['format']) {
			case 'pdf':
			$w_info = array(20, 30);
			$pdf->SetFont($pdf_font, 'B', $pdf_font_size);
			$pdf->Cell($w_info[0], 5, i18n('login') . ':', '', '', 'R', $fill);
			$pdf->SetFont($pdf_font, '', $pdf_font_size);
			$pdf->Cell($w_info[1], 5, $_GET['user'], '', '', 'L', $fill);
			$pdf->Ln();
			if ($name = getUserRealName($_GET['user'])) {
				$pdf->SetFont($pdf_font, 'B', $pdf_font_size);
				$pdf->Cell($w_info[0], 5, i18n('user') . ':', '', '', 'R', $fill);
				$pdf->SetFont($pdf_font, '', $pdf_font_size);
				$pdf->Cell($w_info[1], 5, $name, '', '', 'L', $fill);
				$pdf->Ln();
			}
			$pdf->Ln();
				break;
			default:
		}
		$old_dir = getcwd();
		chdir("../../user/");
		include("common.php");
		generateCommonMenu($_GET["value"]);
		chdir($old_dir);
		$sessions = getAccounting(SQL_ACCT_COLUMN_USER,
			$_GET["value"], $_GET["from"], $_GET["to"] - 1);
		break;

	case "online-at":
		$tpl->setGlobalVariable('top_header', i18n('accounting'));
		$_GET["time"] = $_GET["value"];
		$sessions = getAccountingAtTime($_GET["time"]);
		break;

	default:
		$tpl->setGlobalVariable('top_header', i18n('accounting'));
		$sessions = getAccounting(SQL_ACCT_COLUMN_USER,
			$_GET["value"], $_GET["from"], $_GET["to"] - 1);
		break;
	}

	if (isset($_GET["unique"]) && !$_GET["unique"] == "") {
		switch($_GET["unique"]) {
			case "login":
				$unique_table=SQL_ACCT_COLUMN_USER;
				break;
			case "workstation":
				$unique_table = SQL_ACCT_COLUMN_CALLINGSTATION;
				break;
			case "nas-name":
				$_GET["unique"] = $nas_name_to_ip[$_GET["value"]];
				/* fall */
			case "nas-ip":
				$unique_table = SQL_ACCT_COLUMN_NASIP;
				break;
			case "ip":
				$unique_table = SQL_ACCT_COLUMN_USERIP;
				break;
			default:
				throw new Exception("assert error");
		}
	}

	$toPrint = array();
	$unique = array();

	$summary_time = 0;
	$summary_upload = 0;
	$summary_download = 0;

	foreach($sessions as $session) {
		if ($unique_table != "" && in_array($session[$unique_table], $unique)) {
			continue;
		}
		$unique[] = $session[$unique_table];
		$toPrint[] = array(
			"start" => $session[SQL_ACCT_COLUMN_STARTTIME],
			"end" => $session[SQL_ACCT_COLUMN_STOPTIME],
			"login" => $session[SQL_ACCT_COLUMN_USER],
			"login_url" => generateUrl(
				array(
					"from" => $_GET["from"],
					"to" => $_GET["to"],
					"search" => "login",
					"value" => $session[SQL_ACCT_COLUMN_USER]
				)
			),

			"time" => secToStr($session[SQL_ACCT_COLUMN_SESSIONTIME]),
			"time_class" => ($session[SQL_ACCT_COLUMN_STOPTIME] == 0) ?
				"highlight" : "",
			"download" => bytesToStr($session[SQL_ACCT_COLUMN_DOWNLOAD]),
			"upload" => bytesToStr($session[SQL_ACCT_COLUMN_UPLOAD]),
			"workstation" => $session["CallingStationId"],
			"workstation_url" => generateUrl(
				array(
					"from" => $_GET["from"],
					"to" => $_GET["to"],
					"search" => "workstation",
					"value" => $session["CallingStationId"]
				)
			),
			"acct_nas" => $session["NASIPAddress"],
			"acct_nas_descr" => $nas_ip_to_descr[$session["NASIPAddress"]],
			"port" => $session["NASPortId"],
		    "nas_short" => $nas_ip_to_name[$session["NASIPAddress"]] == "" ?
								$session["NASIPAddress"] :
								$nas_ip_to_name[$session["NASIPAddress"]],
			"nas_url" => generateUrl(
				array(
					"from" => $_GET["from"],
					"to" => $_GET["to"],
					"search" => "nas-ip",
					"value" => $session["NASIPAddress"]
				)
			),
			"online_at_start_url" => generateUrl(
				array(
					"from" => $_GET["from"],
					"to" => $_GET["to"],
					"search" => "online-at",
					"value" => $session[SQL_ACCT_COLUMN_STARTTIME]
				)
			),
			"ip" => $session["FramedIPAddress"],
			"ip_url" => generateUrl(
				array(
					"from" => $_GET["from"],
					"to" => $_GET["to"],
					"search" => "ip",
					"value" => $session["FramedIPAddress"]
				)
			)
		);
		$summary_time += $session[SQL_ACCT_COLUMN_SESSIONTIME];
		$summary_download += $session[SQL_ACCT_COLUMN_DOWNLOAD];
		$summary_upload += $session[SQL_ACCT_COLUMN_UPLOAD];
	}

	switch ($_GET["format"]) {
		case 'pdf':
			$fill = 0;
			$pdf->setFillColor(240, 240, 240);
			$last_start_time = '';
			$num = 1;

			/* TABLE HEADING */
			$pdf->SetFont($pdf_font, 'B', $pdf_font_size);
			$pdf->Cell($w[0], 5, '', '', '', 'C', $fill);
			$pdf->Cell($w[1], 5, i18n('log_in') . '', 'B', '', 'C', $fill);
			$pdf->Cell($w[2], 5, i18n('time'), 'B', 0, 'C', $fill);
			$pdf->Cell($w[3], 5, i18n('ip'), 'B', 0, 'C', $fill);
			$pdf->Cell($w[4], 5, i18n('upload'), 'B', 0, 'C', $fill);
			$pdf->Cell($w[5], 5, i18n('download'), 'B', 0, 'C', $fill);
			$pdf->Ln();
			$pdf->SetFont($pdf_font, '', $pdf_font_size);
			break;
		default:
			$tpl->setCurrentBlock("session_list");
	}

	if ($reverse) {
		$toPrint = array_reverse($toPrint);
	}

	foreach ($toPrint as $arg) {
		switch ($_GET["format"]) {
			case 'pdf':
				/* TABLE ROWS */
				$words = split('[ ]+', $arg['start']);

				$pdf->Cell($w[0], 4, $num++, '', 0, 'R', 0);

				if ($words[0] != $last_start_time) {
					$border = '';
					//$border = 'T';
					$pdf->Cell($w[1], 4, $arg['start'], $border, 0, 'C', $fill);
					$last_start_time = $words[0];
				} else {
					$border = '';
					$pdf->Cell($w[1], 4,
					/* 11 spaces */
					'           ' . $words[1],
						$border, 0, 'C', $fill
					);
				}
				$pdf->Cell($w[2], 4, $arg['time'], $border, 0, 'R', $fill);
				$pdf->Cell($w[3], 4, $arg['ip'], $border, 0, 'C', $fill);
				$pdf->Cell($w[4], 4, $arg['upload'], $border, 0, 'R', $fill);
				$pdf->Cell($w[5], 4, $arg['download'], $border, 0, 'R', $fill);
				$pdf->Ln();
				$fill=!$fill;
				break;

			default:
				$tpl->setVariable($arg);
				$tpl->parse("session_list");
		}
	}
	switch ($_GET["format"]) {
		case 'pdf':
				/* TABLE SUMMARY */
				$fill = 0;
				$pdf->SetFont($pdf_font, 'B', $pdf_font_size);
				$pdf->Cell($w[0], 5, '', '', '', 'C', $fill);
				$pdf->Cell($w[1], 5, i18n('summary') . ':', 'T', '', 'C', $fill);
				$pdf->Cell($w[2], 5,
					secToStr($summary_time),
					'T', 0, 'R', $fill
				);
				$pdf->Cell($w[3], 5, '', 'T', 0, 'L', $fill);
				$pdf->Cell($w[4], 5,
					bytesToStr($summary_upload),
					'T', 0, 'R', $fill
				);
				$pdf->Cell($w[5], 5,
					bytesToStr($summary_download),
					'T', 0, 'R', $fill
				);
				$pdf->Ln();
			break;
		default:
	}
	
	switch ($_GET["format"]) {
		case 'pdf':
			$pdf->Output();
			break;
		default:
			$tpl->setVariable("value", $_GET["value"]);
			$tpl->setVariable("search", $_GET["search"]);
			$tpl->parse("search_bar_look_for");
	}
} else {
	$tpl->setGlobalVariable('top_header', i18n('accounting'));
}
$tpl->setVariable("summary_time", secToStr($summary_time));
$tpl->setVariable("summary_upload",
	bytesToStr($summary_upload)
);
$tpl->setVariable("summary_download",
	bytesToStr($summary_download)
);

switch ($_GET["format"]) {
	case 'pdf':
		exit(0);
		break;
	default:
		if (isset($_GET["unique"]) && !$_GET["unique"] == "") {
			$tpl->setVariable("unique", $_GET["unique"]);
			$tpl->parse("search_bar_unique");
		}
		$tpl->setVariable("unique", "");
		$tpl->parse("search_bar_unique");

		foreach (
			array(
				"login", "nas-name", "nas-ip",
				"ip", "workstation", "online-at"
			) as $arg) {
				if ($arg == $_GET["search"]) {
					continue;
				}

				$tpl->setVariable("search", $arg);
				$tpl->parse("search_bar_look_for");

				if ($arg != "online-at" && $arg != $_GET["unique"]) {
					$tpl->setVariable("unique", $arg);
					$tpl->parse("search_bar_unique");
				}
		}
}
?>