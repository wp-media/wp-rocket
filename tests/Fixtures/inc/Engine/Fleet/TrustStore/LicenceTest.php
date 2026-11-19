<?php

return [
	'testShouldReadTheLicenceWhenNestedUnderData' => [
		'response' => (object) [ 'data' => (object) [ 'fleet' => (object) [ 'licence_id' => '291089' ] ] ],
		'expected' => '291089',
	],
	// The shape on a cache hit: the inner `data` itself.
	'testShouldReadTheLicenceFromACachedResponse' => [
		'response' => (object) [ 'fleet' => (object) [ 'licence_id' => '291089' ] ],
		'expected' => '291089',
	],
	// wp-rocket.me before it served the field: no grant can be accepted.
	'testShouldBeEmptyWhenNotServed'              => [
		'response' => (object) [ 'data' => (object) [ 'fleet' => (object) [ 'issuer' => 'grn:fleet' ] ] ],
		'expected' => '',
	],
	// A number is not the string the grant carries, so it is not trusted.
	'testShouldBeEmptyWhenNotAString'             => [
		'response' => (object) [ 'data' => (object) [ 'fleet' => (object) [ 'licence_id' => 291089 ] ] ],
		'expected' => '',
	],
	'testShouldBeEmptyWhenThereIsNoResponse'      => [
		'response' => false,
		'expected' => '',
	],
];
