<?php
return [
	'disableShouldStopProcess' => [
		'config' => [
			'is_enabled' => false,
			'remaining' => false,
			'have_pending' => false,
		]
	],
	'enableAndStillTaskShouldRecreate' => [
		'config' => [
			'is_enabled' => true,
			'remaining' => true,
			'have_pending' => true,
		]
	],
	'enableAndNoTaskShouldStopProcess' => [
		'config' => [
			'is_enabled' => true,
			'remaining' => false,
			'have_pending' => false,
		]
	]
];
