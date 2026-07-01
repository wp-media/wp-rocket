<?php

declare(strict_types=1);

return [
	'testShouldActivateWhenEnabled'     => [
		'config'   => [
			'enabled' => true,
		],
		'expected' => [
			'activated' => true,
		],
	],
	'testShouldNotActivateWhenDisabled' => [
		'config'   => [
			'enabled' => false,
		],
		'expected' => [
			'activated' => false,
		],
	],
];
