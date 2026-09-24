<?php

return [
	'test_data' => [
		'returns definition and binds container' => [
			[
				'add'       => 'foo',
				'get'       => 'foo',
				'exception' => false,
				'message'   => '',
			],
		],
		'unknown id throws NotFoundException'    => [
			[
				'add'       => null,
				'get'       => 'unknown',
				'exception' => true,
				'message'   => 'Alias (unknown) is not being handled as a definition.',
			],
		],
		'normalises leading backslash on lookup' => [
			[
				'add'       => '\\Foo\\Bar',
				'get'       => 'Foo\\Bar',
				'exception' => false,
				'message'   => '',
			],
		],
	],
];
