<?php

$html = '<html>
<head>
<link rel="stylesheet" href="http://example.org/stylesheet.css">
</head>
<body>
<img src="http://example.org/image.jpg" alt="">
<form action="http://example.org/post.php"></form>
<script src="http://example.org/script.js"></script>
</body>
</html>';

$updated = '<html>
<head>
<link rel="stylesheet" href="//example.org/stylesheet.css">
</head>
<body>
<img src="//example.org/image.jpg" alt="">
<form action="//example.org/post.php"></form>
<script src="//example.org/script.js"></script>
</body>
</html>';

return [
	'testShouldDoNothingWhenCfDisabled' => [
		'config' => [
			'cloudflare' => 0,
			'rewrite' => 1,
			'filter' => true,
		],
		'value' => $html,
		'expected' => $html,
	],
	'testShouldDoNothingWhenRewriteAndFilterDisabled' => [
		'config' => [
			'cloudflare' => 1,
			'rewrite' => 0,
			'filter' => false,
		],
		'value' => $html,
		'expected' => $html,
	],
	'testShouldRewriteWhenRewriteEnabled' => [
		'config' => [
			'cloudflare' => 1,
			'rewrite' => 1,
			'filter' => false,
		],
		'value' => $html,
		'expected' => $updated,
	],
	'testShouldRewriteWhenFilterEnabled' => [
		'config' => [
			'cloudflare' => 1,
			'rewrite' => 0,
			'filter' => true,
		],
		'value' => $html,
		'expected' => $updated,
	],
];
