<?php

return [
	'test_data' => [
		'shouldNotDisplayForAdministratorWithoutMcpUsage' => [
			'role' => 'administrator',
		],
		'shouldNotDisplayForEditor'                       => [
			'role' => 'editor',
		],
	],
];
