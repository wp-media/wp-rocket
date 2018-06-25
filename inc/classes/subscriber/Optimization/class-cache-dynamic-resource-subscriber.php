<?php
namespace WP_Rocket\Subscriber\Optimization;

use WP_Rocket\Optimization\Cache_Dynamic_Resource;
use WP_Rocket\Event_Management\Subscriber_Interface;

/**
 * Hooks into WordPress to replace dynamic php file by static files
 *
 * @since 3.1
 * @author Remy Perona
 */
class Cache_Dynamic_Resource_Subscriber implements Subscriber_Interface {
	/**
	 * Cache dynamic resource instance.
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @var Cache_Dynamic_Resource
	 */
	protected $cache_resource;

	/**
	 * Constructor
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param Cache_Dynamic_Resource $cache_resource Cache dynamic resource instance.
	 */
	public function __construct( Cache_Dynamic_Resource $cache_resource ) {
		$this->cache_resource = $cache_resource;
	}

	/**
	 * @inheritDoc
	 */
	public static function get_subscribed_events() {
		return [
			'style_loader_src'  => [ 'cache_dynamic_resource', 16 ],
			'script_loader_src' => [ 'cache_dynamic_resource', 16 ],
		];
	}

	/**
	 * Filters the source dynamic php file to replace it with a static file
	 *
	 * @since 3.1
	 * @author Remy Perona
	 *
	 * @param string $src source URL.
	 * @return string
	 */
	public function cache_dynamic_resource( $src ) {
		if ( ! $this->cache_resource->is_allowed() ) {
			return $src;
		}

		switch ( current_filter() ) {
			case 'script_loader_src':
				$this->cache_resource->set_extension( 'js' );
				break;
			case 'style_loader_src':
				$this->cache_resource->set_extension( 'css' );
				break;
		}

		if ( $this->cache_resource->is_excluded_file( $src ) ) {
			return $src;
		}

		return $this->cache_resource->replace_url( $src );
	}
}
