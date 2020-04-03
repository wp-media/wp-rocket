<?php

return [
	'active_plugins'          => [
		'akismet/akismet.php',
		'imagify/imagify.php',
		'woocommerce/woocommerce.php',
		'wp-rocket/wp-rocket.php',
	],
	'active_sitewide_plugins' => [
		'bbpress/bbpress.php'         => 1,
		'ninja-forms/ninja-forms.php' => 1,
		'wordpress-seo/wp-seo.php'    => 1,
	],

	'test_data' => [

		'non_multisite' => [
			[
				'akismet/akismet.php',
				true,
			],
			[
				'imagify/imagify.php',
				true,
			],
			[
				'woocommerce/woocommerce.php',
				true,
			],
			[
				'wp-rocket/wp-rocket.php',
				true,
			],

			// Multisite.
			[
				'bbpress/bbpress.php',
				false,
			],
			[
				'ninja-forms/ninja-forms.php',
				false,
			],
			[
				'wordpress-seo/wp-seo.php',
				false,
			],

			// These don't exist.
			[
				'classic-editor/classic-editor.php',
				false,
			],
			[
				'gravity-forms/gravity-forms.php',
				false,
			],
			[
				'jetpack/jetpack.php',
				false,
			],
			[
				'wpforms-lites/wpforms.php',
				false,
			],
		],

		'multisite' => [
			[
				'akismet/akismet.php',
				true,
			],
			[
				'imagify/imagify.php',
				true,
			],
			[
				'woocommerce/woocommerce.php',
				true,
			],
			[
				'wp-rocket/wp-rocket.php',
				true,
			],

			// Multisite.
			[
				'bbpress/bbpress.php',
				true,
				'is_multisite_active' => true,
			],
			[
				'ninja-forms/ninja-forms.php',
				true,
				'is_multisite_active' => true,
			],
			[
				'wordpress-seo/wp-seo.php',
				true,
				'is_multisite_active' => true,
			],

			// These don't exist.
			[
				'classic-editor/classic-editor.php',
				false,
			],
			[
				'gravity-forms/gravity-forms.php',
				false,
			],
			[
				'jetpack/jetpack.php',
				false,
			],
			[
				'wpforms-lites/wpforms.php',
				false,
			],
		],
	],
];
