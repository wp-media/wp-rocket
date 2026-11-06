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
			'beacon'           => 'https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&utm_medium=wp_rocket',
		],
		'expected' => '
<div id="wpr-mobile_cpcss_view" class="wpr-tools">
<div class="wpr-tools-col">
<div class="wpr-title3 wpr-tools-label wpr-icon-stack">
Load CSS asynchronously for mobile</div>
<div class="wpr-field-description wpr-hide-on-click">
Your website currently uses the same Critical Path CSS for both desktop and mobile.</div>
<div class="wpr-field-description wpr-hide-on-click">
Click the button to enable mobile-specific CPCSS for your site.</div>
<div class="wpr-field-description wpr-hide-on-click">
This is a one-time action and this button will be removed afterwards. <a href="https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a>
</div>
<div class="wpr-field-description wpr-field wpr-isHidden wpr-show-on-click">
Your site is now using mobile-specific critical path CSS. <a href="https://docs.wp-rocket.me/article/1266-optimize-css-delivery/?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="5d52144c0428631e94f94ae2" target="_blank" rel="noopener noreferrer">More info</a></div>
</div>
<div class="wpr-tools-col">
<button id="wpr-action-rocket_enable_mobile_cpcss" class="wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-refresh">
Generate Mobile Specific CPCSS</button>
</div>
</div>',
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
