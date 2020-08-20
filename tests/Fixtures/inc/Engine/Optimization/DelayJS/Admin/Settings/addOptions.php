<?php
$list = [
	'getbutton.io',
	'//a.omappapi.com/app/js/api.min.js',
	'feedbackcompany.com/includes/widgets/feedback-company-widget.min.js',
	'snap.licdn.com/li.lms-analytics/insight.min.js',
	'static.ads-twitter.com/uwt.js',
	'platform.twitter.com/widgets.js',
	'twq(',
	'/sdk.js#xfbml',
	'static.leadpages.net/leadbars/current/embed.js',
	'translate.google.com/translate_a/element.js',
	'widget.manychat.com',
	'google.com/recaptcha/api.js',
	'xfbml.customerchat.js',
	'static.hotjar.com/c/hotjar-',
	'smartsuppchat.com/loader.js',
	'grecaptcha.execute',
	'Tawk_API',
	'shareaholic',
	'sharethis',
	'simple-share-buttons-adder',
	'addtoany',
	'font-awesome',
	'wpdiscuz',
	'cookie-law-info',
	'cookie-notice',
	'pinit.js',
	'/gtag/js',
	'gtag(',
	'/gtm.js',
	'/gtm-',
	'fbevents.js',
	'fbq(',
	'google-analytics.com/analytics.js',
	'ga( \'',
	'ga(\'',
	'adsbygoogle',
	'ShopifyBuy',
	'widget.trustpilot.com',
	'ft.sdk.min.js',
	'apps.elfsight.com/p/platform.js',
	'livechatinc.com/tracking.js',
	'LiveChatWidget',
	'/busting/facebook-tracking/',
];

return [
	'shouldReturnValidOptionsWithEmptyOptions' => [
		'input' => [
			'options' => [],
		],
		'expected' => [
			'delay_js'         => 1,
			'delay_js_scripts' => $list,
		]
	],
	'shouldReturnValidOptionsWithOptionsNotArray' => [
		'input' => [
			'options' => 'test_option',
		],
		'expected' => [
			'test_option',
			'delay_js'         => 1,
			'delay_js_scripts' => $list,
		]
	],
	'shouldOverrideOptions' => [
		'input' => [
			'options' => [
				'delay_js'         => 0,
				'delay_js_scripts' => [
					'any value'
				]
			],
		],
		'expected' => [
			'delay_js'         => 1,
			'delay_js_scripts' => $list,
		]
	],
	'shouldNotOverrideOtherOptions' => [
		'input' => [
			'options' => [
				'test_option'      => 1,
				'delay_js'         => 0,
				'delay_js_scripts' => [
					'any value'
				]
			],
		],
		'expected' => [
			'test_option'      => 1,
			'delay_js'         => 1,
			'delay_js_scripts' => $list,
		]
	],
];
