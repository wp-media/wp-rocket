<?php

use WP_Rocket\Engine\CDN\Context;

return [
	'testShouldReturnNothingWhenCdnDisabled'          => [
		'config'   => [
			'cdn' => 0,
		],
		'expected' => Context::CDN_STATE_NOTHING,
	],
	'testShouldReturnByocdnWhenCdnTypeIsNotRocketcdn' => [
		'config'   => [
			'cdn'      => 1,
			'cdn_type' => Context::BYOCDN_TYPE,
		],
		'expected' => Context::BYOCDN_TYPE,
	],
	'testShouldReturnByocdnWhenFreeStateHasNoToken'   => [
		'config'   => [
			'cdn'       => 1,
			'cdn_type'  => Context::ROCKETCDN_TYPE,
			'cdn_state' => Context::ROCKETCDN_FREE_TYPE,
			'has_token' => false,
		],
		'expected' => Context::BYOCDN_TYPE,
	],
	'testShouldReturnRocketcdnFreeWhenTokenExists'    => [
		'config'   => [
			'cdn'       => 1,
			'cdn_type'  => Context::ROCKETCDN_TYPE,
			'cdn_state' => Context::ROCKETCDN_FREE_TYPE,
			'has_token' => true,
		],
		'expected' => Context::ROCKETCDN_FREE_TYPE,
	],
	'testShouldReturnRocketcdnPaidUnchanged'          => [
		'config'   => [
			'cdn'       => 1,
			'cdn_type'  => Context::ROCKETCDN_TYPE,
			'cdn_state' => Context::ROCKETCDN_PAID_TYPE,
		],
		'expected' => Context::ROCKETCDN_PAID_TYPE,
	],
	'testShouldReturnNothingStateUnchanged'           => [
		'config'   => [
			'cdn'       => 1,
			'cdn_type'  => Context::ROCKETCDN_TYPE,
			'cdn_state' => Context::CDN_STATE_NOTHING,
		],
		'expected' => Context::CDN_STATE_NOTHING,
	],
	// One.com-shaped site: cdn = 1 (force-set on upgrade), cdn_type filtered to byocdn via
	// OneCom::disable_rocketcdn_tab() (pre_get_rocket_option_cdn_type), regardless of the
	// persisted cdn_state (which may still say rocketcdn_free from legacy_to_state()'s
	// no-token fallback, or anything else). The cdn_type short-circuit must win before
	// cdn_state or has_token() are ever consulted.
	'testShouldReturnByocdnForOneComShapedSiteWithFreeState' => [
		'config'   => [
			'cdn'       => 1,
			'cdn_type'  => Context::BYOCDN_TYPE,
			'cdn_state' => Context::ROCKETCDN_FREE_TYPE,
		],
		'expected' => Context::BYOCDN_TYPE,
	],
	'testShouldReturnByocdnForOneComShapedSiteWithNothingState' => [
		'config'   => [
			'cdn'       => 1,
			'cdn_type'  => Context::BYOCDN_TYPE,
			'cdn_state' => Context::CDN_STATE_NOTHING,
		],
		'expected' => Context::BYOCDN_TYPE,
	],
];
