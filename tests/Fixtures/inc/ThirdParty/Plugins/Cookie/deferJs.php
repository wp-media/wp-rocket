<?php

$html = <<<HTML
	<script src="http://example.org/wp-content/plugins/hello-world/script.js" id="hello-script"></script>
	<script data-cfasync="false" src="/javascript.js"></script>
	<script src="https://app.termly.io/resource-blocker/c3dccd2d-48a5-4b63-9109-2dc7068924df?autoBlock=on"></script>
HTML
;

$expected = <<<HTML
	<script src="http://example.org/wp-content/plugins/hello-world/script.js" id="hello-script" data-rocket-defer defer></script>
	<script data-cfasync="false" src="/javascript.js" data-rocket-defer defer></script>
	<script src="https://app.termly.io/resource-blocker/c3dccd2d-48a5-4b63-9109-2dc7068924df?autoBlock=on" data-rocket-defer defer></script>
HTML
;

$expected_exclusion = <<<HTML
	<script src="http://example.org/wp-content/plugins/hello-world/script.js" id="hello-script" data-rocket-defer defer></script>
	<script data-cfasync="false" src="/javascript.js" data-rocket-defer defer></script>
	<script src="https://app.termly.io/resource-blocker/c3dccd2d-48a5-4b63-9109-2dc7068924df?autoBlock=on"></script>
HTML
;

$exclusions_list = (object) [
	'defer_js_external_exclusions' => [
		'gist.github.com',
		'/api/scripts/lb_cs.js',
	],
];

return [
	'testShouldReturnUpdatedHTML' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => $html,
		'expected' => $expected,
	],

	'testShouldEvaluateRegexPatternInOptions' => [
		'config' => [
			'donotrocketoptimize' => false,
			'post_meta'           => false,
			'options'             => [
				'defer_all_js'      => 1,
				'exclude_defer_js'  => [
					'app.termly.io/resource-blocker/(.*)',
				],
			],
			'exclusions'          => $exclusions_list,
		],
		'html'     => $html,
		'expected' => $expected_exclusion,
	],
];
