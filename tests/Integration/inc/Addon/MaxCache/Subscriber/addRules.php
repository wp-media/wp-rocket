<?php

namespace WP_Rocket\Tests\Integration\inc\Addon\MaxCache\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering the .htaccess block the MAx Cache add-on contributes.
 *
 * The subject is the assembled marker rather than any single callback: what matters is that exactly
 * one component ends up owning delivery, and that is only visible once every filter on the file has
 * run.
 *
 * @group MaxCache
 * @group Addon
 */
class Test_AddRules extends TestCase {
	/**
	 * The add-on the test drives, in place of the one the container built.
	 *
	 * @var \WP_Rocket\Addon\MaxCache\MaxCache
	 */
	private $maxcache;

	/**
	 * The callbacks the container registered, to be put back afterwards.
	 *
	 * @var \WP_Rocket\Addon\MaxCache\Subscriber|null
	 */
	private $registered;

	/**
	 * The stand-in whose callbacks replace them for the length of the test.
	 *
	 * @var \WP_Rocket\Addon\MaxCache\Subscriber|null
	 */
	private $stand_in;

	/**
	 * The plugin's own reader and writer of the settings row.
	 *
	 * @var \WP_Rocket\Admin\Options
	 */
	private $options_api;

	/**
	 * Sets the stage for each test.
	 *
	 * Two things about a real host cannot be had here: the module on disk, and a wp-config that sets
	 * WP_CACHE. Both are answered by a stand-in built on the add-on itself, so everything else —
	 * every option, every filter on the file, the plugin's own rules — stays real. The callbacks the
	 * container registered are swapped for the stand-in's, since the marker is assembled by whatever
	 * is on those hooks.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		$container         = apply_filters( 'rocket_container', null );
		$this->registered  = $container->get( 'maxcache_subscriber' );
		$this->options_api = $container->get( 'options_api' );

		remove_filter( 'after_rocket_htaccess_rules', [ $this->registered, 'add_rules' ] );
		remove_filter( 'rocket_htaccess_mod_rewrite', [ $this->registered, 'maybe_remove_rewrite_rules' ], 100 );

		$this->maxcache = new class( $this->options_api ) extends \WP_Rocket\Addon\MaxCache\MaxCache {
			public function get_mode(): string {
				return 'apache';
			}

			public function plugin_writes_cache_files(): bool {
				return true;
			}
		};

		$this->stand_in = new \WP_Rocket\Addon\MaxCache\Subscriber( $this->maxcache, $this->options_api );

		add_filter( 'after_rocket_htaccess_rules', [ $this->stand_in, 'add_rules' ] );
		add_filter( 'rocket_htaccess_mod_rewrite', [ $this->stand_in, 'maybe_remove_rewrite_rules' ], 100 );
	}

	/**
	 * Puts the container's own callbacks back.
	 *
	 * Anything later in the same process that builds the marker or applies rocket_htaccess_mod_rewrite
	 * would otherwise run against this test's stand-in, and its result would depend on the order the
	 * tests happened to run in. The settings row needs nothing here: SettingsTrait keeps the original
	 * and restores it after the class.
	 *
	 * @return void
	 */
	public function tear_down() {
		remove_filter( 'after_rocket_htaccess_rules', [ $this->stand_in, 'add_rules' ] );
		remove_filter( 'rocket_htaccess_mod_rewrite', [ $this->stand_in, 'maybe_remove_rewrite_rules' ], 100 );

		add_filter( 'after_rocket_htaccess_rules', [ $this->registered, 'add_rules' ] );
		add_filter( 'rocket_htaccess_mod_rewrite', [ $this->registered, 'maybe_remove_rewrite_rules' ], 100 );

		parent::tear_down();
	}

	/**
	 * Builds the marker for a given set of options and checks what ended up in it.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Plugin options for the case.
	 * @param array $expected Fragments the marker must and must not carry.
	 *
	 * @return void
	 */
	public function testShouldReturnExpectedMarker( $config, $expected ) {
		$this->options_api->set(
			'settings',
			array_merge(
				[
					// A configuration the add-on accepts: separate mobile files off,
					// mobile caching on, HTTPS caching on.
					'cache_mobile'            => 1,
					'do_caching_mobile_files' => 0,
					'cache_ssl'               => 1,
					'secret_cache_key'        => 'integration',
				],
				$config
			)
		);

		$marker = get_rocket_htaccess_marker();

		foreach ( $expected['contains'] as $fragment ) {
			$this->assertStringContainsString( $fragment, $marker );
		}

		foreach ( $expected['not_contains'] as $fragment ) {
			$this->assertStringNotContainsString( $fragment, $marker );
		}
	}
}
