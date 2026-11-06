<?php
return [
	'enableShouldInitInstance' => [
		'config' => [
			'is_enable' => true,
			'interval' => 60,
			'interval_filter' => true,
		]
	],
	'disableNotPendingShouldDoNothing' => [
		'config' => [
			'is_enable' => false,
			'is_pending' => false,
		]
	],
	'disablePendingShouldCancel' => [
		'config' => [
			'is_enable' => false,
			'is_pending' => true,
		]
	]
];
