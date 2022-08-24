<?php
return [
	'versionInferiorShouldReload' => [
		'config' => [
			'new_version' => '3.12.0',
			'old_version' => '3.13.0',
		],
	],
	'versionSuperiorShouldDoNothing' => [
		'config' => [
			'new_version' => '3.11.0',
			'old_version' => '3.12.0',
		],
	]
];
