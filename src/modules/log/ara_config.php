<?php
$excludeFromMenu = false;
if (!file_exists("./modules/log/config.php")) {
	$excludeFromMenu = true;
}
$groupLongName = i18n("logs");
$prio = "089"
?>
