<?php

$fields = [
	'cdn'              => [
		'type'              => 'checkbox',
		'label'             => 'Enable Content Delivery Network',
		'helper'            => '',
		'section'           => 'cdn_section',
		'page'              => 'page_cdn',
		'default'           => 0,
		'sanitize_callback' => 'sanitize_checkbox',
	],
	'cdn_cnames'       => [
		'type'        => 'cnames',
		'label'       => 'CDN CNAME(s)',
		'description' => 'Specify the CNAME(s) below',
		'default'     => [],
		'section'     => 'cnames_section',
		'page'        => 'page_cdn',
	],
	'cdn_reject_files' => [
		'type'              => 'textarea',
		'description'       => 'Specify URL(s) of files that should not get served via CDN (one per line).',
		'helper'            => 'The domain part of the URL will be stripped automatically.<br>Use (.*) wildcards to exclude all files of a given file type located at a specific path.',
		'placeholder'       => '/wp-content/plugins/some-plugins/(.*).css',
		'section'           => 'exclude_cdn_section',
		'page'              => 'page_cdn',
		'default'           => [],
		'sanitize_callback' => 'sanitize_textarea',
	],
	'rocketcdn_token'  => [
		'type'            => 'text',
		'label'           => 'RocketCDN token',
		'description'     => 'The RocketCDN token used to send request to RocketCDN API',
		'default'         => '',
		'container_class' => [
			'wpr-rocketcdn-token',
			'wpr-isHidden',
		],
		'section'         => 'cnames_section',
		'page'            => 'page_cdn',
	],
];

return [

	'fields' => $fields,

	'test_data' => [
		'testShouldReturnDefaultFieldWhenWhiteLabel' => [
			'config' => [
				'white_label'      => true,
				'cdn_names'        => [],
				'rocketcdn_status' => [
					'subscription_status' => 'cancelled',
					'cdn_url'             => '',
				],
			],
			'expected_cdn_cnames' => $fields['cdn_cnames'],
		],

		'testShouldReturnDefaultFieldWhenRocketCDNNotActive' => [
			'config' => [
				'cdn_names'           => [],
				'rocketcdn_status'    => [
					'subscription_status' => 'cancelled',
					'cdn_url'             => '',
				],
			],
			'expected_cdn_cnames' => $fields['cdn_cnames'],
		],

		'testShouldReturnRocketCDNFieldWhenRocketCDNActive' => [
			'config' => [
				'cdn_names'           => [ 'example1.org' ],
				'rocketcdn_status'    => [
					'subscription_status' => 'running',
					'cdn_url'             => 'example1.org',
				],
			],
			'expected_cdn_cnames' => [
				'type'        => 'rocket_cdn',
				'label'       => 'CDN CNAME(s)',
				'description' => 'Specify the CNAME(s) below',
				'helper'      => 'Your RocketCDN subscription is currently active. <a href="https://docs.wp-rocket.me/article/1307-rocketcdn/?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="5e4c84bd04286364bc958833" rel="noopener noreferrer" target="_blank">More Info</a>',
				'default'     => '',
				'section'     => 'cnames_section',
				'page'        => 'page_cdn',
				'beacon'      => [
					'url' => 'https://docs.wp-rocket.me/article/1307-rocketcdn/?utm_source=wp_plugin&utm_medium=wp_rocket',
					'id'  => '5e4c84bd04286364bc958833',
				],
			],
		],

		'testShouldReturnRocketCDNFieldWithCNAMEWhenRocketCDNActiveAndCNamesEmpty' => [
			'config' => [
				'cdn_names'           => [],
				'rocketcdn_status'    => [
					'subscription_status' => 'running',
					'cdn_url'             => 'example1.org',
				],
			],
			'expected_cdn_cnames' => [
				'type'        => 'rocket_cdn',
				'label'       => 'CDN CNAME(s)',
				'description' => 'Specify the CNAME(s) below',
				'helper'      => 'To use RocketCDN, replace your CNAME with <code>example1.org</code>. <a href="https://docs.wp-rocket.me/article/1307-rocketcdn/?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="5e4c84bd04286364bc958833" rel="noopener noreferrer" target="_blank">More Info</a>',
				'default'     => '',
				'section'     => 'cnames_section',
				'page'        => 'page_cdn',
				'beacon'      => [
					'url' => 'https://docs.wp-rocket.me/article/1307-rocketcdn/?utm_source=wp_plugin&utm_medium=wp_rocket',
					'id'  => '5e4c84bd04286364bc958833',
				],
			],
		],

		'testShouldReturnRocketCDNFieldWithCNAMEWhenRocketCDNActiveAndCNames' => [
			'config' => [
				'cdn_names'           => [ 'example2.com' ],
				'rocketcdn_status'    => [
					'subscription_status' => 'running',
					'cdn_url'             => 'example1.org',
				],
			],
			'expected_cdn_cnames' => [
				'type'        => 'rocket_cdn',
				'label'       => 'CDN CNAME(s)',
				'description' => 'Specify the CNAME(s) below',
				'helper'      => 'To use RocketCDN, replace your CNAME with <code>example1.org</code>. <a href="https://docs.wp-rocket.me/article/1307-rocketcdn/?utm_source=wp_plugin&#038;utm_medium=wp_rocket" data-beacon-article="5e4c84bd04286364bc958833" rel="noopener noreferrer" target="_blank">More Info</a>',
				'default'     => '',
				'section'     => 'cnames_section',
				'page'        => 'page_cdn',
				'beacon'      => [
					'url' => 'https://docs.wp-rocket.me/article/1307-rocketcdn/?utm_source=wp_plugin&utm_medium=wp_rocket',
					'id'  => '5e4c84bd04286364bc958833',
				],
			],
		],
	],
];
