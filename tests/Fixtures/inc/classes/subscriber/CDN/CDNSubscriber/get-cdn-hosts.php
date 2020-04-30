<?php
return [
	// Empty CDN list.
	[
		[],
		[ 'all' ],
		[],
		[],
	],
	// CDN list + original host.
	[
		[ 'cdn5.example.org' ],
		[ 'all' ],
		[
			'http://cdn.example.org',
			'//cdn2.example.org',
			'https://cdn3.example.org',
			'cdn4.example.org',
		],
		[
			'cdn5.example.org',
			'cdn.example.org',
			'cdn2.example.org',
			'cdn3.example.org',
			'cdn4.example.org',
		],
	],
	// CDN list with invalid URL, duplicate entries, URL with path.
	[
		[],
		[ 'all' ],
		[
			'http://cdn.example.org/path',
			'//cdn2.example.org',
			'//cdn2.example.org',
			'/subdir/',
			'https://cdn3.example.org/path/subdir/',
		],
		[
			'cdn.example.org/path',
			'cdn2.example.org',
			'cdn3.example.org/path/subdir',
		],
	],
];
