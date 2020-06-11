<?php

return [
	'testShouldDoNothingWhenNotWistia' => [
		'tag'      => '<script src="http://example.org/wp-includes/js/jquery.js"></script>',
		'handle'   => 'jquery',
		'expected' => '<script src="http://example.org/wp-includes/js/jquery.js"></script>',
	],
	'testShouldAddAsyncAttributeWhenWistia' => [
		'tag'      => '<script src="https://fast.wistia.com/assets/external/E-v1.js"></script>',
		'handle'   => 'wistia-e-v1',
		'expected' => '<script async src="https://fast.wistia.com/assets/external/E-v1.js"></script>',
	],
];
