<?php

declare(strict_types=1);

return [
	'testShouldAddRewriteRulesWhenEnabled'     => [
		'config'   => [
			'enabled' => true,
		],
		'expected' => [
			'added' => true,
		],
	],
	'testShouldNotAddRewriteRulesWhenDisabled' => [
		'config'   => [
			'enabled' => false,
		],
		'expected' => [
			'added' => false,
		],
	],
];
