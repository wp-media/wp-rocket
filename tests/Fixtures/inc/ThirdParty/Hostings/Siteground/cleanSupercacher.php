<?php
return [
	'notActiveShouldDoNothing' => [
		'config' => [
			'is_active' => false,
			'version' => '4.0.0',
			'is_version_superior' => false,
		],
	],
	'activeAndInferiorShouldClean' => [
		'config' => [
			'is_active' => true,
			'version' => '4.0.0',
			'is_version_superior' => false,
		],
	],
	'activeAndSuperiorShouldClean' => [
		'config' => [
			'is_active' => true,
			'version' => '6.0.0',
			'is_version_superior' => true,
		],
	]
];
