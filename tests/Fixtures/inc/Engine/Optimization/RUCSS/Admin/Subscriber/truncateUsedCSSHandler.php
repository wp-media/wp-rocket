<?php

return [
	'test_data' => [

		'shouldNotTruncateUnusedCSSWithOptionDisabled' => [
			'input' => [
				'remove_unused_css' => false,
				'nonce'
			],
			'expected' => [
				'truncated' => false,
			],
		],

	],
];
