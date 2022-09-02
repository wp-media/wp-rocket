<?php

return [
	'shouldReturnSameWhenNotOCD' => [
		'config' => [
			'expired' => false,
			'white_label' => false,
			'expire_date' => strtotime( 'now + 7 days' ),
			'renewal_url' => '',
			'auto_renew' => false,
			'ocd' => true,
		],
		'args' => [
			'id' => 'minify_css',
			'label' => 'Minify CSS',
		],
		'expected' => [
			'id' => 'minify_css',
			'label' => 'Minify CSS',
		],
	],
	'shouldReturnSameWhenLicenseNotExpired' => [
		'config' => [
			'expired' => false,
			'white_label' => false,
			'expire_date' => strtotime( 'now + 7 days' ),
			'renewal_url' => '',
			'auto_renew' => false,
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
		],
	],
	'shouldReturnSameWhenWLAndExpiredRecently' => [
		'config' => [
			'expired' => true,
			'white_label' => true,
			'expire_date' => strtotime( 'now - 7 days' ),
			'renewal_url' => '',
			'auto_renew' => false,
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
		],
	],
	'shouldReturnSameWhenWLAndExpiredRecentlyAndAutoRenewAndOCDDisabled' => [
		'config' => [
			'expired' => true,
			'white_label' => true,
			'expire_date' => strtotime( 'now - 3 days' ),
			'renewal_url' => '',
			'auto_renew' => true,
			'ocd' => false,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
		],
	],
	'shouldReturnSameWhenAutoRenewAndExpiredLessThan4Days' => [
		'config' => [
			'expired' => true,
			'white_label' => false,
			'expire_date' => strtotime( 'now - 2 days' ),
			'renewal_url' => '',
			'auto_renew' => true,
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
		],
	],
	'shouldReturnWarningWhenExpiredRecently' => [
		'config' => [
			'expired' => true,
			'white_label' => false,
			'expire_date' => strtotime( 'now - 7 days' ),
			'renewal_url' => '',
			'auto_renew' => false,
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery <span class="wpr-icon-important wpr-checkbox-warning">You need a valid license to continue using this feature. <a href="" target="_blank">Renew now</a> before losing access.</span>',
		],
	],
	'shouldReturnWarningWhenOCDDisabledAndExpiredRecently' => [
		'config' => [
			'expired' => true,
			'white_label' => false,
			'expire_date' => strtotime( 'now - 7 days' ),
			'renewal_url' => '',
			'auto_renew' => false,
			'ocd' => false,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery <span class="wpr-icon-important wpr-checkbox-warning">You need an active license to enable this option. <a href="" target="_blank">Renew now</a>.</span>',
		],
	],
	'shouldReturnWarningWhenExpiredLonger' => [
		'config' => [
			'expired' => true,
			'white_label' => false,
			'expire_date' => strtotime( 'now - 20 days' ),
			'renewal_url' => '',
			'auto_renew' => false,
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery <span class="wpr-icon-important wpr-checkbox-warning">You need an active license to enable this option. <a href="" target="_blank">Renew now</a>.</span>',
		],
	],
	'shouldReturnWarningWhenWLAndOCDDisabled' => [
		'config' => [
			'expired' => true,
			'white_label' => true,
			'expire_date' => strtotime( 'now - 7 days' ),
			'renewal_url' => '',
			'auto_renew' => false,
			'ocd' => false,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery <span class="wpr-icon-important wpr-checkbox-warning">You need an active license to enable this option. <a href="https://docs.wp-rocket.me/article/1711-what-happens-if-my-license-expires?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">More info</a>.</span>',
		],
	],
	'shouldReturnWarningWhenWLAndExpiredLonger' => [
		'config' => [
			'expired' => true,
			'white_label' => true,
			'expire_date' => strtotime( 'now - 20 days' ),
			'renewal_url' => '',
			'auto_renew' => false,
			'ocd' => true,
		],
		'args' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery',
		],
		'expected' => [
			'id' => 'optimize_css_delivery',
			'label' => 'Optimize CSS Delivery <span class="wpr-icon-important wpr-checkbox-warning">You need an active license to enable this option. <a href="https://docs.wp-rocket.me/article/1711-what-happens-if-my-license-expires?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">More info</a>.</span>',
		],
	],
];
