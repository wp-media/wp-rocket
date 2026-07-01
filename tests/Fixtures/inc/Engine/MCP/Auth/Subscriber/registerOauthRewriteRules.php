<?php

declare(strict_types=1);

return [
	'testShouldRegisterRewriteRulesWhenEnabled'     => [
		'config'   => [
			'enabled' => true,
		],
		'expected' => [
			'called' => 5,
		],
	],
	'testShouldNotRegisterRewriteRulesWhenDisabled' => [
		'config'   => [
			'enabled' => false,
		],
		'expected' => [
			'called' => 0,
		],
	],
];
