<?php

return [
	'testGateFalseDoesNotRegisterCategory' => [
		'config'   => [
			'is_enabled' => false,
		],
		'expected' => [
			'wp_register_ability_category_called' => false,
		],
	],
	'testGateTrueRegistersCategory'        => [
		'config'   => [
			'is_enabled' => true,
		],
		'expected' => [
			'wp_register_ability_category_called' => true,
		],
	],
];
