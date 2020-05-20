<?php

return [
	'testShouldBailOutWithNoCapability' => [
		'config'   => [
			'current_user_can' => false,
			'options'          => [
				'async_css'               => 1,
				'cache_mobile'            => 1,
				'do_caching_mobile_files' => 1,
				'async_css_mobile'        => 1,
			],
		],
		'expected' => '',
	],

	'testShouldBailOutWithNoAsyncCss' => [
		'config'   => [
			'current_user_can' => true,
			'options'          => [
				'async_css'               => 0,
				'cache_mobile'            => 1,
				'do_caching_mobile_files' => 1,
				'async_css_mobile'        => 0,
			],
		],
		'expected' => '',
	],

	'testShouldBailOutWithNoCacheMobile' => [
		'config'   => [
			'current_user_can' => true,
			'options'          => [
				'async_css'               => 1,
				'cache_mobile'            => 0,
				'do_caching_mobile_files' => 1,
				'async_css_mobile'        => 0,
			],
		],
		'expected' => '',
	],

	'testShouldBailOutWithNoDoCacheMobileFiles' => [
		'config'   => [
			'current_user_can' => true,
			'options'          => [
				'async_css'               => 1,
				'cache_mobile'            => 1,
				'do_caching_mobile_files' => 0,
				'async_css_mobile'        => 0,
			],
		],
		'expected' => '',
	],

	'testShouldBailOutWithNoOption' => [
		'config'   => [
			'current_user_can' => true,
			'options'          => [
				'async_css'               => 0,
				'cache_mobile'            => 0,
				'do_caching_mobile_files' => 0,
				'async_css_mobile'        => 0,
			],
		],
		'expected' => '',
	],

	'testSucceedWithAllOptionsEnabledAndAsyncMobileNotActive' => [
		'config'   => [
			'current_user_can' => true,
			'options'          => [
				'async_css'               => 1,
				'cache_mobile'            => 1,
				'do_caching_mobile_files' => 1,
				'async_css_mobile'        => 0,
			],
		],
		'expected' => '<div id="wpr-mobile_cpcss_view" class="wpr-tools">
<div class="wpr-tools-col">
<div class="wpr-title3 wpr-tools-label wpr-icon-check2">
Enable CPCSS for mobiles text</div>
<div class="wpr-field-description">
Description for CPCSS for mobiles. Probably some doc link.</div>
</div>
<div class="wpr-tools-col">
<button id="wpr-action-rocket_enable_mobile_cpcss" class="wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-refresh">
Enable CPCSS for mobile</button>
</div>
</div>
',
	],

	'testBailoutAsyncMobileAlreadyActive' => [
		'config'   => [
			'current_user_can' => true,
			'options'          => [
				'async_css'               => 0,
				'cache_mobile'            => 0,
				'do_caching_mobile_files' => 0,
				'async_css_mobile'        => 1,
			],
		],
		'expected' => '',
	],

	'testBailoutWithAllOptionsAndAsyncMobileAlreadyActive' => [
		'config'   => [
			'current_user_can' => true,
			'options'          => [
				'async_css'               => 1,
				'cache_mobile'            => 1,
				'do_caching_mobile_files' => 1,
				'async_css_mobile'        => 1,
			],
		],
		'expected' => '',
	],
];
