<?php

return [
	'testShouldBailOutNoPermissions' => [
		'config' => [
			'rocket_manage_options'          => false,
		],
		'update' => false,
	],
	'testShouldBailOutNoRegeneratePermissions' => [
		'config' => [
			'rocket_manage_options'          => true,
			'rocket_regenerate_critical_css' => false,
		],
		'update' => false,
	],
	'testShouldEnableMobileCpcss' => [
		'config' => [
			'rocket_manage_options'          => true,
			'rocket_regenerate_critical_css' => true,
		],
		'update' => true,
	],
];
