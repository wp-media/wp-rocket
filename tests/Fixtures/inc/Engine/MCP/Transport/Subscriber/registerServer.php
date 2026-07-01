<?php

declare(strict_types=1);

return [
	'testShouldRegisterServerWhenEnabled'     => [
		'config'   => [
			'enabled' => true,
		],
		'expected' => [
			'registered' => true,
		],
	],
	'testShouldNotRegisterServerWhenDisabled' => [
		'config'   => [
			'enabled' => false,
		],
		'expected' => [
			'registered' => false,
		],
	],
];
