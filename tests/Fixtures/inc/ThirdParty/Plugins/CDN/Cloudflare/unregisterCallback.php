<?php
return [
	'intKeyedCallbackShouldBeIgnored'               => [
		'config'   => [
			// Mimics the int array key PHP produces from the purely-numeric
			// string _wp_filter_build_unique_id() returns for a Closure/
			// invokable object hooked directly onto the priority.
			'key' => 12345,
		],
		'expected' => [
			'removed' => false,
		],
	],
	'stringKeyedNonMatchingCallbackShouldBeIgnored' => [
		'config'   => [
			'key' => 'SomeClass::someOtherMethod',
		],
		'expected' => [
			'removed' => false,
		],
	],
	'stringKeyedMatchingCallbackShouldBeRemoved'    => [
		'config'   => [
			'key' => 'SomeClass::purgeCacheOnPostStatusChange',
		],
		'expected' => [
			'removed' => true,
		],
	],
];
