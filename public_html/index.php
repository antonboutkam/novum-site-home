<?php
$aParts = array_reverse(explode('.', $_SERVER['HTTP_HOST']));

$sPath = dirname($_SERVER['SCRIPT_FILENAME'], 3);

require_once $sPath . '/index.php';
