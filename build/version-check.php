<?php
$version_checks = array(
	"$plugin_slug.php" => array(
		'@Version:\s+(.*)\n@' => 'header',
	),
	"readme.txt" => array(
		'@Stable tag:\s+(.*)\n@' => 'Readme.txt'
	),
	"public/class-featured-content-manager.php" => array(
		"@const VERSION = '(.*)'@" => 'Version CONSTANT',
	),
);
