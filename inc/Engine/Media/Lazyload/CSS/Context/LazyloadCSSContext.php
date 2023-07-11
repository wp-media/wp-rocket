<?php

namespace WP_Rocket\Engine\Media\Lazyload\CSS\Context;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Common\Cache\CacheInterface;
use WP_Rocket\Engine\Common\Context\AbstractContext;

class LazyloadCSSContext extends AbstractContext {


	/**
	 * Cache instance.
	 *
	 * @var CacheInterface
	 */
	protected $cache;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data   $options WPR options.
	 * @param CacheInterface $cache Cache instance.
	 */
	public function __construct( Options_Data $options, CacheInterface $cache ) {
		parent::__construct( $options );
		$this->cache = $cache;
	}

	/**
	 * Determine if the action is allowed.
	 *
	 * @param array $data Data to pass to the context.
	 * @return bool
	 */
	public function is_allowed( array $data = [] ): bool {
		$is_allowed = $this->run_common_checks(
			[
				'do_not_optimize'    => false,
				'bypass'             => false,
				'option'             => 'lazyload_css_bg_img',
				'password_protected' => false,
				'post_excluded'      => 'disable_css_bg_img_lazyload',
				'logged_in'          => false,
			]
			);
		if ( ! $is_allowed ) {
			return false;
		}

		return $this->cache->is_accessible();
	}
}
