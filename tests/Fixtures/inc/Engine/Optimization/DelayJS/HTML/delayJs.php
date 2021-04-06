<?php

$html = '<html>
<head>
	<script src="http://example.org/wp-includes/js/jquery/jquery.min.js?ver=3.5.1"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">< / script>
</head>
<body>
<script>if(navigator.userAgent.match(/MSIE|Internet Explorer/i)||navigator.userAgent.match(/Trident\/7\..*?rv:11/i)){var href=document.location.href;if(!href.match(/[?&]nowprocket/)){if(href.indexOf("?")==-1){if(href.indexOf("#")==-1){document.location.href=href+"?nowprocket=1"}else{document.location.href=href.replace("#","?nowprocket=1#")}}else{if(href.indexOf("#")==-1){document.location.href=href+"&nowprocket=1"}else{document.location.href=href.replace("#","&nowprocket=1#")}}}}</script>
<script>var screenReaderText = {"expand":"expand child menu","collapse":"collapse child menu"};</script>
<script src="http://example.org/wp-includes/js/comment-reply.min.js?ver=5.7" id="comment-reply-js"></script>
<script type="module" src="http://example.org/wp-content/plugins/module/test.js"></script>
</body>
</html>';

$delay_html = '<html>
<head>
	<script type="rocketlazyloadscript" src="http://example.org/wp-includes/js/jquery/jquery.min.js?ver=3.5.1"></script>
	<script type="rocketlazyloadscript" data-rocket-type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">< / script>
</head>
<body>
<script>if(navigator.userAgent.match(/MSIE|Internet Explorer/i)||navigator.userAgent.match(/Trident\/7\..*?rv:11/i)){var href=document.location.href;if(!href.match(/[?&]nowprocket/)){if(href.indexOf("?")==-1){if(href.indexOf("#")==-1){document.location.href=href+"?nowprocket=1"}else{document.location.href=href.replace("#","?nowprocket=1#")}}else{if(href.indexOf("#")==-1){document.location.href=href+"&nowprocket=1"}else{document.location.href=href.replace("#","&nowprocket=1#")}}}}</script>
<script type="rocketlazyloadscript">var screenReaderText = {"expand":"expand child menu","collapse":"collapse child menu"};</script>
<script src="http://example.org/wp-includes/js/comment-reply.min.js?ver=5.7" id="comment-reply-js"></script>
<script type="rocketlazyloadscript" data-rocket-type="module" src="http://example.org/wp-content/plugins/module/test.js"></script>
</body>
</html>';

return [
	'test_data' => [
		'testShouldNotDelayJSWhenBypass' => [
			'config'   => [
				'bypass'               => true,
				'donotoptimize'        => false,
				'post-excluded'        => false,
				'delay_js'             => 1,
				'delay_js_exclusions'  => [],
			],
			'html'     => $html,
			'expected' => $html,
		],

		'testShouldDoNothingWhenDoNotOptimizeEnabled' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => true,
				'post-excluded'        => false,
				'delay_js'             => 1,
				'delay_js_exclusions'  => [],
			],
			'html'     => $html,
			'expected' => $html,
		],

		'testShouldDoNothingWhenPostExcluded' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'post-excluded'        => true,
				'delay_js'             => 1,
				'delay_js_exclusions'  => [],
			],
			'html'     => $html,
			'expected' => $html,
		],

		'testShouldDoNothingWhenDelaySettingDisabled' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'post-excluded'        => false,
				'delay_js'             => 0,
				'delay_js_exclusions'  => [],
			],
			'html'     => $html,
			'expected' => $html,
		],

		'testShouldDelayJS' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'post-excluded'        => false,
				'delay_js'             => 1,
				'delay_js_exclusions'  => [
					'/wp-includes/js/comment-reply.min.js',
				],
			],
			'html'     => $html,
			'expected' => $delay_html,
		],
	]
];
