<?php

declare(strict_types=1);

return [
	'testShouldHandleRequestWhenEnabled'     => [
		'config'   => [
			'enabled' => true,
		],
		'expected' => [
			'handled' => true,
		],
	],
	'testShouldNotHandleRequestWhenDisabled' => [
		'config'   => [
			'enabled' => false,
		],
		'expected' => [
			'handled' => false,
		],
	],
];
