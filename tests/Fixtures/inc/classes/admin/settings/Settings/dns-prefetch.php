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
	// Textarea with various values as a string.
	[
		[
			'dns_prefetch' => "http://google.com\nhttps://fonts.gstatic.com/\n//123456.rocketcdn.me\n \nhttp://facebook.com/sdk",
		],
		[
			'dns_prefetch' => [
				'//google.com',
				'//fonts.gstatic.com',
				'//123456.rocketcdn.me',
				'//facebook.com',
			],
		],
	],
	// Textarea with various values as a string and duplicates.
	[
		[
			'dns_prefetch' => "http://google.com\nhttps://fonts.gstatic.com/\n//123456.rocketcdn.me\n \nhttp://google.com\nhttp://facebook.com/sdk//123456.rocketcdn.me\n \n \//google.com\nhttps://123456.rocketcdn.me",
		],
		[
			'dns_prefetch' => [
				'//google.com',
				'//fonts.gstatic.com',
				'//123456.rocketcdn.me',
				'//facebook.com',
			],
		],
	],
	// Input as an array instead of a string.
	[
		[
			'dns_prefetch' => [
				'http://google.com',
				'https://fonts.gstatic.com/',
				'//123456.rocketcdn.me',
				' ',
				'http://facebook.com/sdk',
				'https://123456.rocketcdn.me',
				'//google.com'
			],
		],
		[
			'dns_prefetch' => [
				'//google.com',
				'//fonts.gstatic.com',
				'//123456.rocketcdn.me',
				'//facebook.com',
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
				'//123456.rocketcdn.me',
				'https://fonts.gstatic.com/',
			],
		],
		[
			'dns_prefetch' => [
				'//google.com',
				'//fonts.gstatic.com',
				'//123456.rocketcdn.me',
				'//facebook.com',
			],
		],
	],
];
