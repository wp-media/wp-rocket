<?php

declare( strict_types=1 );

$original_html = <<<HTML
<!doctype html>
<html lang="en">
<head>
<script src="http://example.com/wp-includes/js/jquery/jquery.min.js?ver=3.6.0" id="jquery-core-js"></script>
</head>
<body>
<script src="http://example.com/wp-content/plugins/xl-woocommerce-sales-triggers/assets/js/wcst_combined.min.js?ver=2.11.0" id="wcst_public_js-js"></script>
</body>
</html>
HTML;

$delay_js_html = <<<DELAYJSHTML
<!doctype html>
<html lang="en">
<head>
<script>if(navigator.userAgent.match(/MSIE|Internet Explorer/i)||navigator.userAgent.match(/Trident\/7\..*?rv:11/i)){var href=document.location.href;if(!href.match(/[?&]nowprocket/)){if(href.indexOf("?")==-1){if(href.indexOf("#")==-1){document.location.href=href+"?nowprocket=1"}else{document.location.href=href.replace("#","?nowprocket=1#")}}else{if(href.indexOf("#")==-1){document.location.href=href+"&nowprocket=1"}else{document.location.href=href.replace("#","&nowprocket=1#")}}}}</script>
<script type="rocketlazyloadscript" src="http://example.com/wp-includes/js/jquery/jquery.min.js?ver=3.6.0" id="jquery-core-js"></script>
</head>
<body>
<script type="rocketlazyloadscript" src="http://example.com/wp-content/plugins/xl-woocommerce-sales-triggers/assets/js/wcst_combined.min.js?ver=2.11.0" id="wcst_public_js-js"></script>
</body>
</html>
DELAYJSHTML;

$delay_with_exclusions_html = <<<DELAYEXCLUDEDHTML
<!doctype html>
<html lang="en">
<head>
<script>if(navigator.userAgent.match(/MSIE|Internet Explorer/i)||navigator.userAgent.match(/Trident\/7\..*?rv:11/i)){var href=document.location.href;if(!href.match(/[?&]nowprocket/)){if(href.indexOf("?")==-1){if(href.indexOf("#")==-1){document.location.href=href+"?nowprocket=1"}else{document.location.href=href.replace("#","?nowprocket=1#")}}else{if(href.indexOf("#")==-1){document.location.href=href+"&nowprocket=1"}else{document.location.href=href.replace("#","&nowprocket=1#")}}}}</script>
<script src="http://example.com/wp-includes/js/jquery/jquery.min.js?ver=3.6.0" id="jquery-core-js"></script>
</head>
<body>
<script src="http://example.com/wp-content/plugins/xl-woocommerce-sales-triggers/assets/js/wcst_combined.min.js?ver=2.11.0" id="wcst_public_js-js"></script>
</body>
</html>
DELAYEXCLUDEDHTML;


return [
	'test_data' => [

		'shouldNotExcludeFromDelayJsWhenSingleButNotIsProduct' => [
			'is_product' => false,
			'initial_html'        => $original_html,
			'expected_html'       => $delay_js_html
		],

		'shouldExcludeFromDelayJsWhenIsProduct' => [
			'is_product' => true,
			'initial_html'        => $original_html,
			'expected_html'       => $delay_with_exclusions_html
		]
	]
];
