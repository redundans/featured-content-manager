<?php
$version_checks = array(
	"$plugin_slug.php" => array(
		'@Version:\s+(.*)\n@' => 'header',
		"@'version' 	=> '(.*)'@" => 'EDD updater',
	),
	"readme.txt" => array(
		'@Stable tag:\s+(.*)\n@' => 'Readme.txt'
	),
);
