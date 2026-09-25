<?php

namespace WP_Rocket\Tests\Integration\inc\Addon\MaxCache;

/**
 * The stand every one of these tests needs: the add-on driven in place of the container's, against
 * the real .htaccess of the install, from a licensed admin request.
 */
trait AddOnStandTrait {
	/**
	 * The add-on the test drives, in place of the one the container built.
	 *
	 * @var MaxCacheStandIn
	 */
	private $maxcache;

	/**
	 * The subscriber whose callbacks replace the container's for the length of the test.
	 *
	 * @var \WP_Rocket\Addon\MaxCache\Subscriber
	 */
	private $stand_in;

	/**
	 * The callbacks the container registered, to be put back afterwards.
	 *
	 * @var \WP_Rocket\Addon\MaxCache\Subscriber|null
	 */
	private $registered;

	/**
	 * The plugin's own reader and writer of the settings row.
	 *
	 * @var \WP_Rocket\Admin\Options
	 */
	private $options_api;

	/**
	 * Absolute path to the file under test.
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * What that file held before the test.
	 *
	 * @var string|null
	 */
	private $original;

	/**
	 * Server type as the plugin reads it.
	 *
	 * @var bool|null
	 */
	private $was_apache;

	/**
	 * Who the request was made by before the test.
	 *
	 * @var int
	 */
	private $was_user;

	/**
	 * The read filters that make this a licensed site, to be taken off afterwards.
	 *
	 * @var array
	 */
	private $stand_filters = [];

	/**
	 * Puts the stand up.
	 *
	 * @return void
	 */
	private function given_the_add_on_stand() {
		$container         = apply_filters( 'rocket_container', null );
		$this->registered  = $container->get( 'maxcache_subscriber' );
		$this->options_api = $container->get( 'options_api' );

		// All three, not two: is_htaccess_needed() answers true for anyone before it looks at anything,
		// so a callback left on the filter would answer for the host this suite runs on.
		remove_filter( 'after_rocket_htaccess_rules', [ $this->registered, 'add_rules' ] );
		remove_filter( 'rocket_htaccess_mod_rewrite', [ $this->registered, 'maybe_remove_rewrite_rules' ], 100 );
		remove_filter( 'rocket_htaccess_needed_without_apache', [ $this->registered, 'is_htaccess_needed' ], 10 );

		$this->maxcache = new MaxCacheStandIn( $this->options_api );
		$this->stand_in = new \WP_Rocket\Addon\MaxCache\Subscriber( $this->maxcache, $this->options_api );

		add_filter( 'after_rocket_htaccess_rules', [ $this->stand_in, 'add_rules' ] );
		add_filter( 'rocket_htaccess_mod_rewrite', [ $this->stand_in, 'maybe_remove_rewrite_rules' ], 100 );
		add_filter( 'rocket_htaccess_needed_without_apache', [ $this->stand_in, 'is_htaccess_needed' ], 10, 2 );

		// A licensed site with the switch on offer, said through the plugin's own read filters: the
		// row cannot say the first part, because Options_Data::get() answers consumer_key and
		// consumer_email from the WP_ROCKET_* constants this suite's bootstrap defines.
		$email               = 'integration@example.org';
		$this->stand_filters = [
			'pre_get_rocket_option_consumer_key'   => function () {
				return '12345678';
			},
			'pre_get_rocket_option_consumer_email' => function () use ( $email ) {
				return $email;
			},
			'pre_get_rocket_option_secret_key'     => function () use ( $email ) {
				return hash( 'crc32', $email );
			},
			'rocket_maxcache_available'            => '__return_true',
		];

		foreach ( $this->stand_filters as $hook => $answer ) {
			add_filter( $hook, $answer );
		}

		// Everything here runs from an admin request: without the capability the add-on's own gate
		// declines to act, and every assertion would then pass on a method that did nothing.
		self::setAdminCap();
		$this->was_user = get_current_user_id();
		wp_set_current_user( 1 );

		$this->was_apache     = $GLOBALS['is_apache'] ?? null;
		$GLOBALS['is_apache'] = true;

		if ( ! function_exists( 'get_home_path' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$this->filename = get_home_path() . '.htaccess';
		$this->original = rocket_direct_filesystem()->exists( $this->filename )
			? rocket_direct_filesystem()->get_contents( $this->filename )
			: null;
	}

	/**
	 * Takes it down again.
	 *
	 * @return void
	 */
	private function then_the_stand_comes_down() {
		if ( null === $this->original ) {
			rocket_direct_filesystem()->delete( $this->filename );
		} else {
			$this->given_a_file( $this->original );
		}

		if ( null === $this->was_apache ) {
			unset( $GLOBALS['is_apache'] );
		} else {
			$GLOBALS['is_apache'] = $this->was_apache;
		}

		wp_set_current_user( $this->was_user );
		self::resetAdminCap();

		foreach ( $this->stand_filters as $hook => $answer ) {
			remove_filter( $hook, $answer );
		}

		remove_filter( 'rocket_htaccess_needed_without_apache', [ $this->stand_in, 'is_htaccess_needed' ], 10 );
		remove_filter( 'after_rocket_htaccess_rules', [ $this->stand_in, 'add_rules' ] );
		remove_filter( 'rocket_htaccess_mod_rewrite', [ $this->stand_in, 'maybe_remove_rewrite_rules' ], 100 );

		add_filter( 'after_rocket_htaccess_rules', [ $this->registered, 'add_rules' ] );
		add_filter( 'rocket_htaccess_mod_rewrite', [ $this->registered, 'maybe_remove_rewrite_rules' ], 100 );
		add_filter( 'rocket_htaccess_needed_without_apache', [ $this->registered, 'is_htaccess_needed' ], 10, 2 );
	}

	/**
	 * Stores the add-on's switch, with a configuration it accepts around it.
	 *
	 * @param int $answer What the site has answered.
	 *
	 * @return void
	 */
	private function given_the_switch( int $answer ) {
		$this->options_api->set(
			'settings',
			array_merge(
				(array) $this->options_api->get( 'settings', [] ),
				[
					'maxcache'                => $answer,
					'cache_mobile'            => 1,
					'do_caching_mobile_files' => 0,
					'cache_ssl'               => 1,
					'secret_cache_key'        => 'integration',
				]
			)
		);
	}

	/**
	 * Writes the file the test works on.
	 *
	 * @param string $contents Contents to write.
	 *
	 * @return void
	 */
	private function given_a_file( string $contents ) {
		// Mode given rather than left to FS_CHMOD_FILE, which WP_Filesystem() defines and nothing
		// here has called.
		rocket_direct_filesystem()->put_contents( $this->filename, $contents, 0644 );
	}

	/**
	 * Returns what the file holds now.
	 *
	 * @return string
	 */
	private function file_contents(): string {
		return (string) rocket_direct_filesystem()->get_contents( $this->filename );
	}
}

/**
 * The add-on with the two things a test cannot have answered for it.
 *
 * The module on disk, which a test says is there or gone, and a wp-config that sets WP_CACHE.
 */
class MaxCacheStandIn extends \WP_Rocket\Addon\MaxCache\MaxCache {
	/**
	 * What the server-mode probe would find.
	 *
	 * @var string
	 */
	private $mode_found = 'apache';

	/**
	 * Says what the probe would find from here on.
	 *
	 * @param string $mode Server mode.
	 *
	 * @return void
	 */
	public function answer_mode( string $mode ) {
		$this->mode_found = $mode;
	}

	/**
	 * Returns that answer.
	 *
	 * @return string
	 */
	public function get_mode(): string {
		return $this->mode_found;
	}

	/**
	 * Whether the plugin is set up to write cache files at all.
	 *
	 * @return bool
	 */
	public function plugin_writes_cache_files(): bool {
		return true;
	}
}
