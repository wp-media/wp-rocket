<?php

return [
	'testGateFalseDoesNotCallRegister' => [
		'config'   => [
			'is_enabled' => false,
		],
		'expected' => [
			'register_called' => false,
		],
	],
	'testGateTrueCallsRegister'        => [
		'config'   => [
			'is_enabled' => true,
		],
		'expected' => [
			'register_called' => true,
		],
	],
];
