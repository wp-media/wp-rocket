<?php

return [
	'test_data' => [
		'shouldClearTransientWhenLocaleMetaUpdated' => [
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
				'should_clear'     => true,
				'transient_after'  => null, // Not checked when should_clear is true.
			],
		],

		'shouldNotClearTransientWhenOtherMetaUpdated' => [
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
				'should_clear'     => false,
				'transient_after'  => [
					'status'          => 'success',
					'recommendations' => [ 'some_recommendation' ],
					'metadata'        => [],
					'timestamp'       => time(),
				],
			],
		],

		'shouldNotClearTransientWhenUserMetaUpdatedIsNotCurrentUser' => [
			'config'   => [
				'meta_key'          => 'locale',
				'user_id'           => 999,
				'initial_transient' => [
					'status'          => 'success',
					'recommendations' => [ 'some_recommendation' ],
					'metadata'        => [],
					'timestamp'       => time(),
				],
			],
			'expected' => [
				'should_clear'     => false,
				'transient_after'  => [
					'status'          => 'success',
					'recommendations' => [ 'some_recommendation' ],
					'metadata'        => [],
					'timestamp'       => time(),
				],
			],
		],
	],
];
