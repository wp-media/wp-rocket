<?php

return [
	// Not JSON at all. A caller sending form-encoded data, or an empty body.
	'testShouldRefuseABodyThatIsNotJson'           => [
		'config'   => [ 'body' => 'option_name=cache_logged_user' ],
		'expected' => [ 'message' => 'Send a JSON object.' ],
	],

	'testShouldRefuseAnEmptyBody'                  => [
		'config'   => [ 'body' => '' ],
		'expected' => [ 'message' => 'Send a JSON object.' ],
	],

	// Valid JSON, but a scalar rather than an object.
	'testShouldRefuseAJsonScalar'                  => [
		'config'   => [ 'body' => '"cache_logged_user"' ],
		'expected' => [ 'message' => 'Send a JSON object.' ],
	],

	// An explicit empty batch. Answering 200 with no results would read as
	// "all your changes were applied".
	'testShouldRefuseAnEmptyOptionsArray'          => [
		'config'   => [ 'body' => '{"options":[]}' ],
		'expected' => [
			'message' => 'Send option_name and option_value, or a non-empty options array.',
		],
	],

	// `options` present but not a list.
	'testShouldRefuseOptionsThatIsNotAnArray'      => [
		'config'   => [ 'body' => '{"options":"cache_logged_user"}' ],
		'expected' => [
			'message' => 'Send option_name and option_value, or a non-empty options array.',
		],
	],

	'testShouldRefuseAChangeWithNoOptionName'      => [
		'config'   => [ 'body' => '{"option_value":1}' ],
		'expected' => [ 'message' => 'Every change needs an option_name.' ],
	],

	// A change with no value at all. Distinct from a value of null, which is a
	// request we answer — see testShouldAcceptAnOptionValueOfNull.
	'testShouldRefuseAChangeWithNoOptionValue'     => [
		'config'   => [ 'body' => '{"option_name":"cache_logged_user"}' ],
		'expected' => [ 'message' => 'Every change needs an option_value.' ],
	],

	// One bad entry in an otherwise valid batch refuses the whole batch. The
	// alternative is applying the good half, which leaves the customer's
	// configuration in a state neither side asked for.
	'testShouldRefuseAWholeBatchForOneBadEntry'    => [
		'config'   => [
			'body' => '{"options":[{"option_name":"cache_logged_user","option_value":1},{"option_value":0}]}',
		],
		'expected' => [ 'message' => 'Every change needs an option_name.' ],
	],

	// An entry that is not an object at all.
	'testShouldRefuseABatchEntryThatIsNotAnObject' => [
		'config'   => [ 'body' => '{"options":["cache_logged_user"]}' ],
		'expected' => [ 'message' => 'Every change needs an option_name.' ],
	],
];
