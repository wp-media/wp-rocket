<?php

return [
	'testShouldBailoutIfIsNotAmpEndpoint'      => [
		'config'  => [
			'is_amp_endpoint' => false,
			'amp_options'     => [ 'theme_support' => null ],
		],
		'expected' => [
			'bailout' => true,
		],
	],
	'testShouldBailOutIfNotAmpEndpointAndWebStoriesNotActive' => [
		'config'   => [
			'is_amp_endpoint'             => false,
			'is-web-story'                => false,
			'do_cloudflare'               => 0,
			'cloudflare_protocol_rewrite' => -1, // -1 - means Expect never
			'do_rocket_protocol_rewrite'  => -1, // -1 - means Expect never
			'amp_options'                 => ['theme_support' => 'standard'],
		],
		'expected' => [
			'bailout'       => true,
		]
	],
	'testShouldDisableOptionsForWebStory' => [
		'config' => [
			'is_amp_endpoint' => false,
			'is-web-story' => true,
			'do_cloudflare'               => 0,
			'cloudflare_protocol_rewrite' => -1, // -1 - means Expect never
			'do_rocket_protocol_rewrite'  => -1, // -1 - means Expect never
			'amp_options' => [ 'theme_support' => 'standard' ],
		],
		'expected' => [
			'bailout' => false,
			'remove_filter' => false,
		]
	],
	'testShouldDisableOptionForAmpExceptImageSrcSet'      => [
		'config'  => [
			'is_amp_endpoint'             => true,
			'do_cloudflare'               => 0,
			'cloudflare_protocol_rewrite' => -1, // -1 - means Expect never
			'do_rocket_protocol_rewrite'  => -1, // -1 - means Expect never
			'amp_options'                 => [ 'theme_support' => null ],
		],
		'expected' => [
			'bailout'       => false,
			'remove_filter' => false,
		],
	],
	'testShouldDisableOptionForAmpWhenCloudflareEnabled'      => [
		'config'  => [
			'is_amp_endpoint'             => true,
			'do_cloudflare'               => 1,
			'cloudflare_protocol_rewrite' => 1,
			'do_rocket_protocol_rewrite'  => -1, // -1 - means Expect never
			'amp_options'                 => [ 'theme_support' => null ],
		],
		'expected' => [
			'bailout'       => false,
			'remove_filter' => true,
		],
	],
	'testShouldDisableOptionForAmpWhenCloudflareEnabledAndFilterProtocolRewrite'      => [
		'config'  => [
			'is_amp_endpoint'             => true,
			'do_cloudflare'               => 1,
			'cloudflare_protocol_rewrite' => 0,
			'do_rocket_protocol_rewrite'  => 1,
			'amp_options'                 => [ 'theme_support' => null ],
		],
		'expected' => [
			'bailout'       => false,
			'remove_filter' => true,
		],
	],
	'testShouldDisableOptionForAmpExceptImageSrcSetAndThemeSupport'      => [
		'config'  => [
			'is_amp_endpoint'             => true,
			'do_cloudflare'               => 0,
			'cloudflare_protocol_rewrite' => -1, // -1 - means Expect never
			'do_rocket_protocol_rewrite'  => -1, // -1 - means Expect never
			'amp_options'                 => [ 'theme_support' => 'standard' ],
		],
		'expected' => [
			'bailout'       => false,
			'remove_filter' => false,
		],
	],
	'testShouldDisableOptionForAmpWhenCloudflareEnabledAndThemeSupport'      => [
		'config'  => [
			'is_amp_endpoint'             => true,
			'do_cloudflare'               => 1,
			'cloudflare_protocol_rewrite' => 1,
			'do_rocket_protocol_rewrite'  => -1, // -1 - means Expect never
			'amp_options'                 => [ 'theme_support' => 'transitional' ],
		],
		'expected' => [
			'bailout'       => false,
			'remove_filter' => true,
		],
	],
	'testShouldDisableOptionForAmpWhenCloudflareEnabledAndFilterProtocolRewriteAndThemeSupport'      => [
		'config'  => [
			'is_amp_endpoint'             => true,
			'do_cloudflare'               => 1,
			'cloudflare_protocol_rewrite' => 0,
			'do_rocket_protocol_rewrite'  => 1,
			'amp_options'                 => [ 'theme_support' => 'reader' ],
		],
		'expected' => [
			'bailout'       => false,
			'remove_filter' => true,
		],
	],
];
