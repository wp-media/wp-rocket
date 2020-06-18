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

$wp_config_has_no_wp_cache_comment_on_first_line = "<?php /*
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

$wp_config_has_no_wp_cache_comment_on_first_line_expected = "<?php
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
		'ShouldAddWpCache' => [
			'config' => [
				'original'  => $wp_config_has_no_wp_cache,
				'valid_key' => true,
			],
			'expected' => $wp_config_has_no_wp_cache_expected
		],
		'ShouldAddWpCacheWhenCommentInFirstLine' => [
			'config' => [
				'original'  => $wp_config_has_no_wp_cache_comment_on_first_line,
				'valid_key' => true,
			],
			'expected' => $wp_config_has_no_wp_cache_comment_on_first_line_expected
		],
		'ShouldNotAddWpCache' => [
			'config' => [
				'original'  => $wp_config_has_wp_cache,
				'valid_key' => true,
			],
			'expected' => $wp_config_has_wp_cache_expected
		],
		'ShouldBailOutWhenNotValidKey' => [
			'config' => [
				'original'  => $wp_config_has_no_wp_cache,
				'valid_key' => false,
			],
			'expected' => $wp_config_has_no_wp_cache
		]
	],
];
