<?php

$html = '<html>
<head>
	<script src="http://example.org/wp-includes/js/jquery/jquery.min.js?ver=3.5.1"></script>
	<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">< / script>
	<script src="https://tests.local/wp-includes/js/wp-embed.min.js?ver=5.7" id="wp-embed-js"></script>
	<script type="application/ld+json" class="yoast-schema-graph">{"@context":"https://schema.org","@graph":[{"@type":"WebSite","@id":"http://example.org/#website","url":"http://example.org/"]}</script>
	<script>var et_site_url=\'http://example.org\';var et_post_id=\'2\';function et_core_page_resource_fallback(a,b){"undefined"===typeof b&&(b=a.sheet.cssRules&&0===a.sheet.cssRules.length);b&&(a.onerror=null,a.onload=null,a.href?a.href=et_site_url+"/?et_core_page_resource="+a.id+et_post_id:a.src&&(a.src=et_site_url+"/?et_core_page_resource="+a.id+et_post_id))}
	</script>
</head>
<body>
<script>if(navigator.userAgent.match(/MSIE|Internet Explorer/i)||navigator.userAgent.match(/Trident\/7\..*?rv:11/i)){var href=document.location.href;if(!href.match(/[?&]nowprocket/)){if(href.indexOf("?")==-1){if(href.indexOf("#")==-1){document.location.href=href+"?nowprocket=1"}else{document.location.href=href.replace("#","?nowprocket=1#")}}else{if(href.indexOf("#")==-1){document.location.href=href+"&nowprocket=1"}else{document.location.href=href.replace("#","&nowprocket=1#")}}}}</script>
<script>var screenReaderText = {"expand":"expand child menu","collapse":"collapse child menu"};</script>
<script src="http://example.org/wp-includes/js/comment-reply.min.js?ver=5.7" id="comment-reply-js"></script>
<script type="module" src="http://example.org/wp-content/plugins/module/test.js"></script>
<script id="astra-theme-js-js-extra">var astra = {"break_point:"921","isRtl:""};</script>
</body>
</html>';

$delay_html_upgrade = '<html>
<head>
	<script src="http://example.org/wp-includes/js/jquery/jquery.min.js?ver=3.5.1"></script>
	<script type="rocketlazyloadscript" data-rocket-type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">< / script>
	<script src="https://tests.local/wp-includes/js/wp-embed.min.js?ver=5.7" id="wp-embed-js"></script>
	<script type="application/ld+json" class="yoast-schema-graph">{"@context":"https://schema.org","@graph":[{"@type":"WebSite","@id":"http://example.org/#website","url":"http://example.org/"]}</script>
	<script>var et_site_url=\'http://example.org\';var et_post_id=\'2\';function et_core_page_resource_fallback(a,b){"undefined"===typeof b&&(b=a.sheet.cssRules&&0===a.sheet.cssRules.length);b&&(a.onerror=null,a.onload=null,a.href?a.href=et_site_url+"/?et_core_page_resource="+a.id+et_post_id:a.src&&(a.src=et_site_url+"/?et_core_page_resource="+a.id+et_post_id))}
	</script>
</head>
<body>
<script>if(navigator.userAgent.match(/MSIE|Internet Explorer/i)||navigator.userAgent.match(/Trident\/7\..*?rv:11/i)){var href=document.location.href;if(!href.match(/[?&]nowprocket/)){if(href.indexOf("?")==-1){if(href.indexOf("#")==-1){document.location.href=href+"?nowprocket=1"}else{document.location.href=href.replace("#","?nowprocket=1#")}}else{if(href.indexOf("#")==-1){document.location.href=href+"&nowprocket=1"}else{document.location.href=href.replace("#","&nowprocket=1#")}}}}</script>
<script type="rocketlazyloadscript">var screenReaderText = {"expand":"expand child menu","collapse":"collapse child menu"};</script>
<script src="http://example.org/wp-includes/js/comment-reply.min.js?ver=5.7" id="comment-reply-js"></script>
<script type="module" src="http://example.org/wp-content/plugins/module/test.js"></script>
<script id="astra-theme-js-js-extra">var astra = {"break_point:"921","isRtl:""};</script>
</body>
</html>';

$delay_html = '<html>
<head>
	<script type="rocketlazyloadscript" src="http://example.org/wp-includes/js/jquery/jquery.min.js?ver=3.5.1"></script>
	<script type="rocketlazyloadscript" data-rocket-type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.5.0/js/bootstrap.js">< / script>
	<script src="https://tests.local/wp-includes/js/wp-embed.min.js?ver=5.7" id="wp-embed-js"></script>
	<script type="application/ld+json" class="yoast-schema-graph">{"@context":"https://schema.org","@graph":[{"@type":"WebSite","@id":"http://example.org/#website","url":"http://example.org/"]}</script>
	<script>var et_site_url=\'http://example.org\';var et_post_id=\'2\';function et_core_page_resource_fallback(a,b){"undefined"===typeof b&&(b=a.sheet.cssRules&&0===a.sheet.cssRules.length);b&&(a.onerror=null,a.onload=null,a.href?a.href=et_site_url+"/?et_core_page_resource="+a.id+et_post_id:a.src&&(a.src=et_site_url+"/?et_core_page_resource="+a.id+et_post_id))}
	</script>
</head>
<body>
<script>if(navigator.userAgent.match(/MSIE|Internet Explorer/i)||navigator.userAgent.match(/Trident\/7\..*?rv:11/i)){var href=document.location.href;if(!href.match(/[?&]nowprocket/)){if(href.indexOf("?")==-1){if(href.indexOf("#")==-1){document.location.href=href+"?nowprocket=1"}else{document.location.href=href.replace("#","?nowprocket=1#")}}else{if(href.indexOf("#")==-1){document.location.href=href+"&nowprocket=1"}else{document.location.href=href.replace("#","&nowprocket=1#")}}}}</script>
<script type="rocketlazyloadscript">var screenReaderText = {"expand":"expand child menu","collapse":"collapse child menu"};</script>
<script src="http://example.org/wp-includes/js/comment-reply.min.js?ver=5.7" id="comment-reply-js"></script>
<script type="rocketlazyloadscript" data-rocket-type="module" src="http://example.org/wp-content/plugins/module/test.js"></script>
<script id="astra-theme-js-js-extra">var astra = {"break_point:"921","isRtl:""};</script>
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

		'testShouldDelayJSWithUpgradeExclusions' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'post-excluded'        => false,
				'delay_js'             => 1,
				'delay_js_exclusions'  => [
					'(?:/wp-content|/wp-includes/)(.*)',
					'/jquery-?[0-9.]*(.min|.slim|.slim.min)?.js',
					'js-(before|after)',
				],
			],
			'html'     => $html,
			'expected' => $delay_html_upgrade,
		],

		'testShouldDelayJS' => [
			'config'   => [
				'bypass'               => false,
				'donotoptimize'        => false,
				'post-excluded'        => false,
				'delay_js'             => 1,
				'delay_js_exclusions'  => [
					'/wp-includes/js/comment-reply.min.js?ver=5.7',
					'js-(after|extra)',
				],
			],
			'html'     => $html,
			'expected' => $delay_html,
		],
	]
];
