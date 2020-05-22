<?php

return [
	'testShouldBailOutNoPermissions' => [
		'config' => [
			'rocket_manage_options' => false,
		],
		'update' => false,
	],
	'testShouldEnableMobileCpcss' => [
		'config' => [
			'rocket_manage_options' => true,
		],
		'update' => true,
	],
];
