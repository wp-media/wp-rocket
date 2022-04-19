<?php
return [
	'testShouldExclude' => [
		'config' => [
			'excluded' => []
		],
		'expected' => [
			'/jquery-?[0-9.](.*)(.min|.slim|.slim.min)?.js',
			'/jquery-migrate(.min)?.js',
		]
	]
];
