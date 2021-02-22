<?php

return [
	'shouldAddPreconnectLinkForCdn' => [
		'cdn-cnames'    => [
			'https://123456.rocketcdn.me',
			'https://my-cdn.cdnservice.com',
		],
		'expected-html' => <<<HTML
<link rel='dns-prefetch' href='//s.w.org' />
<link rel='dns-prefetch' href='//123456.rocketcdn.me' />
<link rel='dns-prefetch' href='//my-cdn.cdnservice.com' />
<link href='https://123456.rocketcdn.me' rel='preconnect' />
<link href='https://my-cdn.cdnservice.com' rel='preconnect' />
HTML
	],
];
