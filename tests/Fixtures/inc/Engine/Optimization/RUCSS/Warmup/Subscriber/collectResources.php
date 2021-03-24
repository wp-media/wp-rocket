<?php

return [

	'test_data' => [

		'shouldBailoutWhenDONOTROCKETOPTIMIZEEnabled' => [
			'input' => [
				'html' => 'any html',
				'DONOTROCKETOPTIMIZE' => true,
			],
			'expected' => [
				'allowed' => false,
			],
		],

		'shouldBailoutWhenBypassRocket' => [
			'input' => [
				'html' => 'any html',
				'DONOTROCKETOPTIMIZE' => false,
				'rocket_bypass' => true,
			],
			'expected' => [
				'allowed' => false,
			],
		],

		'shouldBailoutWhenOptionDisabled' => [
			'input' => [
				'html' => 'any html',
				'DONOTROCKETOPTIMIZE' => false,
				'rocket_bypass' => false,
				'remove_unused_css' => false,
			],
			'expected' => [
				'allowed' => false,
			],
		],

		'shouldBailoutWhenMetaboxOptionExcluded' => [
			'input' => [
				'html' => 'any html',
				'DONOTROCKETOPTIMIZE' => false,
				'rocket_bypass' => false,
				'remove_unused_css' => true,
				'post_metabox_option_excluded' => true,
			],
			'expected' => [
				'allowed' => false,
			],
		],

		'shouldCallResourceFetcher' => [
			'input' => [
				'html' => 'any html',
				'DONOTROCKETOPTIMIZE' => false,
				'rocket_bypass' => false,
				'remove_unused_css' => true,
			],
			'expected' => [
				'allowed' => true,
			],
		],

	],

];
