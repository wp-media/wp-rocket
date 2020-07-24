<?php
$wp_config_has_no_wp_cache = "<?php
/*
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to \"wp-config.php\" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */
";

$wp_config_has_no_wp_cache_expected = "<?php
define( 'WP_CACHE', true ); // Added by WP Rocket

/*
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to \"wp-config.php\" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */
";

$wp_config_has_wp_cache = "<?php
define('WP_CACHE', true); // Added by WP Rocket

 /*
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to \"wp-config.php\" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */
";

$wp_config_has_wp_cache_expected = "<?php
define('WP_CACHE', true); // Added by WP Rocket

 /*
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to \"wp-config.php\" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */
";

return [
	'vfs_dir' => 'public/',

	'test_data' => [
        'ShouldDoNothingWhenDoingAjax'     => [
            'config' => [
                'doing_ajax'     => true,
                'doing_autosave' => false,
                'original'       => $wp_config_has_no_wp_cache,
                'valid_key'      => true,
                'filter'         => true,
			],
			'expected' => $wp_config_has_no_wp_cache
        ],
        'ShouldDoNothingWhenDoingAutoSave' => [
            'config' => [
                'doing_ajax'     => false,
                'doing_autosave' => true,
                'original'       => $wp_config_has_no_wp_cache,
                'valid_key'      => true,
                'filter'         => true,
			],
			'expected' => $wp_config_has_no_wp_cache
        ],
        'ShouldDoNothingWhenWpCacheset'    => [
			'config' => [
                'doing_ajax'     => false,
                'doing_autosave' => false,
                'original'       => $wp_config_has_wp_cache,
                'valid_key'      => true,
                'filter'         => true,
			],
			'expected' => $wp_config_has_wp_cache_expected
        ],
        'ShouldDoNothingWhenFilterIsFalse' => [
			'config' => [
                'doing_ajax'     => false,
                'doing_autosave' => false,
                'original'       => $wp_config_has_no_wp_cache,
                'valid_key'      => true,
                'filter'         => false,
			],
			'expected' => $wp_config_has_no_wp_cache
		],
		'ShouldAddWpCache'                 => [
			'config' => [
                'doing_ajax'     => false,
                'doing_autosave' => false,
                'original'       => $wp_config_has_no_wp_cache,
                'valid_key'      => true,
                'filter'         => true,
			],
			'expected' => $wp_config_has_no_wp_cache_expected
		],
	],
];
