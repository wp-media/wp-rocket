<?php
return [
	'versionInferiorShouldDoNothing' => [
		'config' => [
			'new_version' => '3.14.0',
			'old_version' => '3.13.0',
		],
		'result' => false,
	],
	'versionSuperiorShouldReload' => [
		'config' => [
			'new_version' => '3.12.2',
			'old_version' => '3.11.0',
		],
		'result' => true,
	],
	'versionExactlyShouldReload' => [
		'config' => [
			'new_version' => '3.12.0.3',
			'old_version' => '3.12.0.2',
		],
		'result' => true,
	],
];
