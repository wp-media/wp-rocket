<?php

return [
	'test_data' => [
		'WithCommentLineOnly' => [
			'htaccess' => '
			# Your document html
			',
			'expected' => '
			',
		],


		'WithHtaccessLineOnly' => [
			'htaccess' => '
			ExpiresByType text/html "access plus 0 seconds"
			',
			'expected' => '
			',
		],

		'WithBothCommentAndHtaccessLine' => [
			'htaccess' => '
			# Your document html
			ExpiresByType text/html "access plus 0 seconds"
			',
			'expected' => '
			',
		],
	]
];
