<?php

return [
	'active_sitewide_plugins' => [
		'bbpress/bbpress.php'         => 1,
		'ninja-forms/ninja-forms.php' => 1,
		'wordpress-seo/wp-seo.php'    => 1,
		'wp-rocket/wp-rocket.php'     => 1,
	],

	'test_data' => [
		[
			'wp-rocket/wp-rocket.php',
			true,
		],
		[
			'imagify/imagify.php',
			false,
		],
		[
			'gravity-forms/gravity-forms.php',
			false,
		],
		[
			'ninja-forms/ninja-forms.php',
			true,
		],
		[
			'wpforms-lites/wpforms.php',
			false,
		],
		[
			'woocommerce/woocommerce.php',
			false,
		],
		[
			'bbpress/bbpress.php',
			true,
		],
		[
			'wordpress-seo/wp-seo.php',
			true,
		],
	],
];
