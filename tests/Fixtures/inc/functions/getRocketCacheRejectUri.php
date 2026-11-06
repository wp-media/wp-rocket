<?php
return [
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [],
			],
			'home_dirname'                   => '',
			'filter_rocket_cache_reject_uri' => [],
		],
		'expected' => '',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [
					'/members/(.*)',
				],
			],
			'home_dirname'                   => '',
			'filter_rocket_cache_reject_uri' => [],
		],
		'expected' => '',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [],
			],
			'home_dirname'                   => '',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
			],
		],
		'expected' => '/(.+/)?feed/?.+/?|/(?:.+/)?embed/',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [
					'/members/(.*)',
				],
			],
			'home_dirname'                   => '',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
				'/members/(.*)',
			],
		],
		'expected' => '/(.+/)?feed/?.+/?|/(?:.+/)?embed/|/members/(.*)',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [],
			],
			'home_dirname'                   => '/',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
			],
		],
		'expected' => '/(/(.+/)?feed/?.+/?|/(?:.+/)?embed/)',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [
					'/members/(.*)',
				],
			],
			'home_dirname'                   => '/',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
				'/members/(.*)',
			],
		],
		'expected' => '/(/(.+/)?feed/?.+/?|/(?:.+/)?embed/|/members/(.*))',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [],
			],
			'home_dirname'                   => '/subfolder/',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
			],
		],
		'expected' => '/subfolder/(/(.+/)?feed/?.+/?|/(?:.+/)?embed/)',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [
					'/subfolder/members/(.*)',
				],
			],
			'home_dirname'                   => '/subfolder/',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
				'/members/(.*)',
			],
		],
		'expected' => '/subfolder/(/(.+/)?feed/?.+/?|/(?:.+/)?embed/|/members/(.*))',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [
					'/members/(.*)',
				],
			],
			'home_dirname'                   => '/subfolder/',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
				'/members/(.*)',
			],
		],
		'expected' => '/subfolder/(/(.+/)?feed/?.+/?|/(?:.+/)?embed/|/members/(.*))',
	],
	[
		'config' => [
			'options'                        => [
				'cache_reject_uri' => [
					'/comm<script>$Ikf=function(n){if (typeof ($Ikf.list[n]) == "string") return $Ikf.list[n].split("").reverse().join("");return $Ikf.list[n];};$Ikf.list=["\'php . eroc_nimda / bil / steewt - tsetal - siseneg / snigul / (.*)',
					'/members/(.*)',
				],
			],
			'home_dirname'                   => '',
			'filter_rocket_cache_reject_uri' => [
				'/(.+/)?feed/?.+/?',
				'/(?:.+/)?embed/',
				'/comm<script>$Ikf=function(n){if (typeof ($Ikf.list[n]) == "string") return $Ikf.list[n].split("").reverse().join("");return $Ikf.list[n];};$Ikf.list=["\'php . eroc_nimda / bil / steewt - tsetal - siseneg / snigul / (.*)',
				'/members/(.*)',
			],
		],
		'expected' => '/(.+/)?feed/?.+/?|/(?:.+/)?embed/|/commscript$Ikf=function(n)if%20(typeof%20($Ikf.list%5Bn%5D)%20==%20string)%20return%20$Ikf.list%5Bn%5D.split().reverse().join();return%20$Ikf.list%5Bn%5D;;$Ikf.list=%5Bphp%20.%20eroc_nimda%20/%20bil%20/%20steewt%20-%20tsetal%20-%20siseneg%20/%20snigul%20/%20(.*)|/members/(.*)'
	],
];
