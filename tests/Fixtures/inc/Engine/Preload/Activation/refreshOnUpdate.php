<?php
return [
	'versionInferiorShouldDoNothing' => [
		'config' => [
			'new_version' => '3.12.0',
			'old_version' => '3.13.0',
		],
	],
	'versionSuperiorShouldReload' => [
		'config' => [
			'new_version' => '3.11.0',
			'old_version' => '3.12.0',
		],
	]
];
