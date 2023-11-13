<?php
return [
	'NotSuperiorShouldGenerate' => [
		'config' => [
			'new_version' => '3.15',
			'old_version' => '3.14',
			'is_superior' => false
		],
	],
	'SuperiorShouldNotGenerate' => [
		'config' => [
			'new_version' => '3.17',
			'old_version' => '3.16',
			'is_superior' => true
		],
	],
];
