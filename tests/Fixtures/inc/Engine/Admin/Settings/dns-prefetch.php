<?php
return [
	// Empty WP Rocket options array.
	[
		[
		],
		[
			'dns_prefetch' => [],
		],
	],
	// Empty textarea for DNS prefetch.
	[
		[
			'dns_prefetch' => '',
		],
		[
			'dns_prefetch' => [],
		],
	],
	// Textarea with various values as a string and duplicates.
	[
		[
			'dns_prefetch' => "http://google.com\nhttps://fonts.gstatic.com/\n//123456.rocketcdn.me\n \nhttp://google.com\nhttp://facebook.com/sdk\n//123456.rocketcdn.me\n \n//google.com\nhttps://123456.rocketcdn.me\nhttp:///wp-rocket.me",
		],
		[
			'dns_prefetch' => [
				'//google.com',
				'//fonts.gstatic.com',
				'//123456.rocketcdn.me',
				'//facebook.com',
				'//wp-rocket.me',
			],
		],
	],
	// Input as an array with duplicates.
	[
		[
			'dns_prefetch' => [
				'http://google.com',
				'https://fonts.gstatic.com/',
				'//123456.rocketcdn.me',
				' ',
				'http://google.com',
				'http://facebook.com/sdk',
				'https://123456.rocketcdn.me',
				'https://fonts.gstatic.com/',
				'http:///wp-rocket.me',
			],
		],
		[
			'dns_prefetch' => [
				'//google.com',
				'//fonts.gstatic.com',
				'//123456.rocketcdn.me',
				'//facebook.com',
				'//wp-rocket.me',
			],
		],
	],
];
