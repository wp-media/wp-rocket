<?php

declare(strict_types=1);

return [
	'testShouldHandleRequestWhenEnabled'     => [
		'config'   => [
			'enabled'   => true,
			'query_var' => 'token',
		],
		'expected' => [
			'handled' => true,
		],
	],
	'testShouldNotHandleRequestWhenDisabled' => [
		'config'   => [
			'enabled'   => false,
			'query_var' => 'token',
		],
		'expected' => [
			'handled' => false,
		],
	],
];
