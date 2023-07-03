<?php
$new = <<<HTML
<link href='//123456.rocketcdn.me' rel='preconnect' />
<link href='https://my-cdn.cdnservice.com' rel='preconnect' />
<link href='http://cdn.example.com' rel='preconnect' />
<link href='//8901.wicked-fast-cdn.com' rel='preconnect' />
<link href='https://another.cdn.com' rel='preconnect' />
HTML;

return [
	'shouldAddPreconnectLinkForCdn' => [
		'cdn-cnames' => [
			'123456.rocketcdn.me',
			'https://my-cdn.cdnservice.com/',
			'http://cdn.example.com',
			'/some/path/with/no/domain',
			'test/tests',
			'8901.wicked-fast-cdn.com/path/to/my/files',
			'https://another.cdn.com/with/a/path',
		],
		'expected'   => $new,
	],
];
