<?php

namespace WP_Rocket\Tests\Fixtures\Kinsta;

class Kinsta_Cache
{
	/**
	 * Cache_Admin instance
	 *
	 * @var Cache_Admin
	 */
	public $kinsta_cache_admin;

	/**
	 * Cache_Purge instance
	 *
	 * @var Cache_Purge
	 */
	public $kinsta_cache_purge;

	/**
	 * Backward compatible Cache_Purge instance
	 * WP Rocket plugin's 3.0.1 version caused fatal error without this
	 *
	 * @var Cache_Purge
	 */
	public $KinstaCachePurge; // phpcs:ignore

	/**
	 * The cache configuration
	 *
	 * @see ./cache.php
	 * @var array
	 */
	public $config;

	/**
	 * The cache settings
	 *
	 * @see Kinsta/Cache()->set_settings()
	 * @var array
	 */
	public $settings;

	/**
	 * The cache default settings
	 *
	 * @see ./cache.php
	 * @var array
	 */
	public $default_settings;

	/**
	 * Whether the Object cache is enabled
	 *
	 * @var bool
	 */
	public $has_object_cache;
}

class Cache_Purge {

	/**
	 * ID of the Page assigned to display the Blog Posts Index.
	 *
	 * @var int
	 */
	public $posts_page_id;

	/**
	 * URL of the Page assigned to display the Blog Posts Index.
	 *
	 * @var string
	 */
	public $posts_page_url;

	/**
	 * Kinsta Cache Object
	 *
	 * @var object
	 */
	public $kinsta_cache;

	/**
	 * Number of pages at home or archive page to purge
	 *
	 * @var int
	 */
	public $immediate_depth;

	/**
	 * Defines if a single purge action happened
	 *
	 * @var boolean
	 */
	public $purge_single_happened;

	/**
	 * Defines if the all purge action happened
	 *
	 * @var boolean
	 */
	public $purge_all_happened;

	/**
	 * Initiate selective purge
	 *
	 * @version 1.1
	 * @author Daniel Pataki
	 * @author Laci <laszlo@kinsta.com>
	 *
	 * @param int    $post_id the post id.
	 * @param string $event the initiate event.
	 *
	 * @return array the result of the wp_remote_post action
	 **/
	public function initiate_purge( $post_id, $event ) {}

	/**
	 * Purge object cache and page cache
	 *
	 * @return void
	 */
	public function purge_complete_caches() {}
}
