<?php

$html = <<<HTML
	<script src="http://example.org/wp-includes/js/jquery/jquery.js?v=3.1.15" id="jquery-core"></script>
	<script src="http://example.org/wp-content/plugins/hello-world/script.js" id="hello-script"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=GOOGLE_API_KEY&#038;language=en&#038;ver=1"></script>
	<script integrity="sha512-VtmdOFNyOniRUIHzkfL4GAe+yuAhoWzJIWYW/9elcd+7zNu12OKscWFIe9PRQ6VBy4djrwGVzK6MLD3oTpLpRQ==" crossorigin="anonymous" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js" defer></script>
	<script data-cfasync="false" src="/javascript.js"></script>
	<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-51f58c4473f92506"></script>
HTML;

$expected = <<<HTML
	<script src="http://example.org/wp-includes/js/jquery/jquery.js?v=3.1.15" id="jquery-core" defer></script>
	<script src="http://example.org/wp-content/plugins/hello-world/script.js" id="hello-script" defer></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=GOOGLE_API_KEY&#038;language=en&#038;ver=1" defer></script>
	<script integrity="sha512-VtmdOFNyOniRUIHzkfL4GAe+yuAhoWzJIWYW/9elcd+7zNu12OKscWFIe9PRQ6VBy4djrwGVzK6MLD3oTpLpRQ==" crossorigin="anonymous" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js" defer></script>
	<script data-cfasync="false" src="/javascript.js" defer></script>
	<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-51f58c4473f92506" defer></script>
HTML;

$expected_exclusion = <<<HTML
	<script src="http://example.org/wp-includes/js/jquery/jquery.js?v=3.1.15" id="jquery-core" defer></script>
	<script src="http://example.org/wp-content/plugins/hello-world/script.js" id="hello-script"></script>
	<script src="https://maps.googleapis.com/maps/api/js?key=GOOGLE_API_KEY&#038;language=en&#038;ver=1" defer></script>
	<script integrity="sha512-VtmdOFNyOniRUIHzkfL4GAe+yuAhoWzJIWYW/9elcd+7zNu12OKscWFIe9PRQ6VBy4djrwGVzK6MLD3oTpLpRQ==" crossorigin="anonymous" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js" defer></script>
	<script data-cfasync="false" src="/javascript.js" defer></script>
	<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-51f58c4473f92506" defer></script>
HTML;

return [
	'testShouldReturnOriginalWhenConstantSet' => [
		'config' => [
			'donotrocketoptimize' => true,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
		],
		'html'     => $html,
		'expected' => $html,
	],
	'testShouldReturnOriginalWhenOptionDisabled' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 0,
				'exclude_defer_js'  => [],
			],
		],
		'html'     => $html,
		'expected' => $html,
	],
	'testShouldReturnOriginalWhenDisabledByPostMeta' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => true,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
		],
		'html'     => $html,
		'expected' => $html,
	],
	'testShouldReturnUpdatedHTML' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
		],
		'html'     => $html,
		'expected' => $expected,
	],
	'testShouldReturnUpdatedHTMLWhenExcludedValue' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [
					'/wp-content/plugins/hello-world/script.js',
				],
			],
		],
		'html'     => $html,
		'expected' => $expected_exclusion,
	],
];
