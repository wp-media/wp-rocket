<?php

return [
	'shouldReturnSameWhenNotOCD' => [
		'config' => [
			'white_label' => false,
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now + 7 days' ),
				'renewal_url' => '',
				'has_auto_renew' => false,
			] ) ),
			'ocd' => true,
		],
		'args' => [
			'id' => 'minify_css',
			'label' => 'Minify CSS',
			'value' => 0,
		],
		'expected' => [
			'id' => 'minify_css',
			'label' => 'Minify CSS',
			'value' => 0,
		],
	],
	'shouldReturnSameWhenLicenseNotExpired' => [
		'config' => [
			'white_label' => false,
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now + 7 days' ),
				'renewal_url' => '',
				'has_auto_renew' => false,
			] ) ),
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
			'value' => 1,
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
			'value' => 1,
		],
	],
	'shouldReturnSameWhenAutoRenewAndExpiredSinceLessThan4Days' => [
		'config' => [
			'white_label' => true,
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 3 days' ),
				'renewal_url' => '',
				'has_auto_renew' => true,
			] ) ),
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
			'value' => 1,
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
			'value' => 1,
		],
	],
	'shouldReturnSameWhenWLAndExpiredRecentlyAndAutoRenewAndOCDDisabled' => [
		'config' => [
			'white_label' => true,
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 3 days' ),
				'renewal_url' => '',
				'has_auto_renew' => true,
			] ) ),
			'ocd' => false,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
			'value' => 1,
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
			'value' => 1,
		],
	],
	'shouldReturnSameWhenWLAndExpiredRecently' => [
		'config' => [
			'white_label' => true,
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 7 days' ),
				'renewal_url' => '',
				'has_auto_renew' => false,
			] ) ),
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
			'value' => 1,
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
			'value' => 1,
		],
	],
	'shouldReturnWarningWhenExpiredRecently' => [
		'config' => [
			'white_label' => false,
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 7 days' ),
				'renewal_url' => '',
				'has_auto_renew' => false,
			] ) ),
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
			'value' => 1,
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery <span class="wpr-icon-important wpr-checkbox-warning">You need a valid license to continue using this feature. <a href="" target="_blank">Renew now</a> before losing access.</span>',
			'value' => 1,
		],
	],
	'shouldReturnWarningWhenExpiredLonger' => [
		'config' => [
			'white_label' => false,
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 20 days' ),
				'renewal_url' => '',
				'has_auto_renew' => false,
			] ) ),
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
			'value' => 1,
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery <span class="wpr-icon-important wpr-checkbox-warning">You need an active license to enable this option. <a href="" target="_blank">Renew now</a>.</span>',
			'value' => 1,
		],
	],
	'shouldReturnWarningWhenOCDDisabledAndExpiredRecently' => [
		'config' => [
			'white_label' => false,
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 7 days' ),
				'renewal_url' => '',
				'has_auto_renew' => false,
			] ) ),
			'ocd' => false,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
			'value' => 1,
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery <span class="wpr-icon-important wpr-checkbox-warning">You need an active license to enable this option. <a href="" target="_blank">Renew now</a>.</span>',
			'value' => 1,
		],
	],
	'shouldReturnWarningWhenWLAndOCDDisabled' => [
		'config' => [
			'white_label' => true,
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 7 days' ),
				'renewal_url' => '',
				'has_auto_renew' => false,
			] ) ),
			'ocd' => false,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
			'value' => 1,
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery <span class="wpr-icon-important wpr-checkbox-warning">You need an active license to enable this option. <a href="https://docs.wp-rocket.me/article/1711-what-happens-if-my-license-expires?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">More info</a>.</span>',
			'value' => 1,
		],
	],
	'shouldReturnWarningWhenWLAndExpiredLonger' => [
		'config' => [
			'white_label' => true,
			'transient' => json_decode( json_encode( [
				'licence_expiration' => strtotime( 'now - 20 days' ),
				'renewal_url' => '',
				'has_auto_renew' => false,
			] ) ),
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
			'value' => 1,
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery <span class="wpr-icon-important wpr-checkbox-warning">You need an active license to enable this option. <a href="https://docs.wp-rocket.me/article/1711-what-happens-if-my-license-expires?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">More info</a>.</span>',
			'value' => 1,
		],
	],
];
