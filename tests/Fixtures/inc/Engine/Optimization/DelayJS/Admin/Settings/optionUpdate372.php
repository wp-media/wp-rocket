<?php

return [
	'ShouldNotUpdateOptionWithVersionAbove3.7.2' => [
		'config'        => [
			'old_version'   => '3.7.3',
			'valid_version' => false,
			'initial_list'  => [
				'getbutton.io',
				'//a.omappapi.com/app/js/api.min.js',
				'twq(',
				'/sdk.js#xfbml',
				'static.leadpages.net/leadbars/current/embed.js',
				'addtoany',
				'font-awesome',
				'wpdiscuz',
				'fbq(',
				'google-analytics.com/analytics.js',
				'ga( \'',
				'ga(\'',
				'adsbygoogle',
				'a-script-the-customer-added',
			],
		],
		'expected_list' => [
			'getbutton.io',
			'//a.omappapi.com/app/js/api.min.js',
			'twq(',
			'/sdk.js#xfbml',
			'static.leadpages.net/leadbars/current/embed.js',
			'addtoany',
			'font-awesome',
			'wpdiscuz',
			'fbq(',
			'google-analytics.com/analytics.js',
			'ga( \'',
			'ga(\'',
			'adsbygoogle',
			'a-script-the-customer-added',
		],
	],
	'ShouldAppendPixelCaffeineWhenFbqIsPresent'  => [
		'config'        => [
			'old_version'   => '3.5',
			'valid_version' => true,
			'initial_list'  => [
				'getbutton.io',
				'//a.omappapi.com/app/js/api.min.js',
				'twq(',
				'/sdk.js#xfbml',
				'static.leadpages.net/leadbars/current/embed.js',
				'addtoany',
				'font-awesome',
				'wpdiscuz',
				'fbq(',
				'google-analytics.com/analytics.js',
				'ga( \'',
				'ga(\'',
				'adsbygoogle',
				'a-script-the-customer-added',
			],
		],
		'expected_list' => [
			'getbutton.io',
			'//a.omappapi.com/app/js/api.min.js',
			'twq(',
			'/sdk.js#xfbml',
			'static.leadpages.net/leadbars/current/embed.js',
			'addtoany',
			'font-awesome',
			'wpdiscuz',
			'fbq(',
			'google-analytics.com/analytics.js',
			'ga( \'',
			'ga(\'',
			'adsbygoogle',
			'a-script-the-customer-added',
			'pixel-caffeine/build/frontend.js',
		],
	],
	'ShouldNotAddPixelCaffeineWhenFbqNotPresent' => [
		'config'        => [
			'old_version'   => '3.5',
			'valid_version' => true,
			'initial_list'  => [
				'getbutton.io',
				'//a.omappapi.com/app/js/api.min.js',
				'twq(',
				'/sdk.js#xfbml',
				'static.leadpages.net/leadbars/current/embed.js',
				'addtoany',
				'font-awesome',
				'wpdiscuz',
				'google-analytics.com/analytics.js',
				'ga( \'',
				'ga(\'',
				'adsbygoogle',
				'a-script-the-customer-added',
			],
		],
		'expected_list' => [
			'getbutton.io',
			'//a.omappapi.com/app/js/api.min.js',
			'twq(',
			'/sdk.js#xfbml',
			'static.leadpages.net/leadbars/current/embed.js',
			'addtoany',
			'font-awesome',
			'wpdiscuz',
			'google-analytics.com/analytics.js',
			'ga( \'',
			'ga(\'',
			'adsbygoogle',
			'a-script-the-customer-added',
		],
	],
	'ShouldNotAddDuplicatePixelCaffeineWhenAlreadyInList' => [
		'config'        => [
			'old_version'   => '3.5',
			'valid_version' => true,
			'initial_list'  => [
				'getbutton.io',
				'//a.omappapi.com/app/js/api.min.js',
				'twq(',
				'/sdk.js#xfbml',
				'static.leadpages.net/leadbars/current/embed.js',
				'addtoany',
				'font-awesome',
				'wpdiscuz',
				'fbq(',
				'pixel-caffeine/build/frontend.js',
				'google-analytics.com/analytics.js',
				'ga( \'',
				'ga(\'',
				'adsbygoogle',
				'a-script-the-customer-added',
			],
		],
		'expected_list' => [
			'getbutton.io',
			'//a.omappapi.com/app/js/api.min.js',
			'twq(',
			'/sdk.js#xfbml',
			'static.leadpages.net/leadbars/current/embed.js',
			'addtoany',
			'font-awesome',
			'wpdiscuz',
			'fbq(',
			'pixel-caffeine/build/frontend.js',
			'google-analytics.com/analytics.js',
			'ga( \'',
			'ga(\'',
			'adsbygoogle',
			'a-script-the-customer-added',
		],
	],
];
