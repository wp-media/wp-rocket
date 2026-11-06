<?php

return [
	'shouldPurgeUrl' => [
		'config' => [
			'url' => ['https://example.com/asd'],
			'post'    => [
				'post_id' 	   => 123,
				'post_title'   => 'Lorem ipsum',
				'post_content' => 'Lorem ipsum dolor sit amet',
				'post_status'  => 'publish',
				'post_date'    => '2020-03-01',
			],

			'path' => ['/asd'],
			'parsed_url' => [
				'scheme' => 'https',
				'host' => 'example.com',
				'path' => '/asd',
			],
		],
		'expected' => [
		]
	]
];
