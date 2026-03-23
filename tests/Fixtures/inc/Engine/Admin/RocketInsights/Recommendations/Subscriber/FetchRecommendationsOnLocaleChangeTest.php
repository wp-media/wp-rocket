<?php

return [
	'test_data' => [
		'shouldTriggerFetchWhenLocaleMetaUpdated' => [
			'config'   => [
				'meta_key'          => 'locale',
				'initial_transient' => [
					'status'          => 'success',
					'recommendations' => [ 'some_recommendation' ],
					'metadata'        => [],
					'timestamp'       => time(),
				],
			],
			'expected' => [
				'should_trigger_fetch' => true,
				'transient_after'      => null, // Not checked when should_trigger_fetch is true.
			],
		],

		'shouldNotTriggerFetchWhenOtherMetaUpdated' => [
			'config'   => [
				'meta_key'          => 'nickname',
				'initial_transient' => [
					'status'          => 'success',
					'recommendations' => [ 'some_recommendation' ],
					'metadata'        => [],
					'timestamp'       => time(),
				],
			],
			'expected' => [
				'should_trigger_fetch' => false,
				'transient_after'      => [
					'status'          => 'success',
					'recommendations' => [ 'some_recommendation' ],
					'metadata'        => [],
					'timestamp'       => time(),
				],
			],
		],

		'shouldTriggerFetchEvenWithEmptyTransient' => [
			'config'   => [
				'meta_key'          => 'locale',
				'initial_transient' => null,
			],
			'expected' => [
				'should_trigger_fetch' => true,
				'transient_after'      => null,
			],
		],

		'shouldNotTriggerFetchWhenFirstNameMetaUpdated' => [
			'config'   => [
				'meta_key'          => 'first_name',
				'initial_transient' => null,
			],
			'expected' => [
				'should_trigger_fetch' => false,
				'transient_after'      => false,
			],
		],
	],
];
