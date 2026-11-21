<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::rewrite_preload_links
 *
 * @uses \WP_Rocket\Engine\CDN\CDN::rewrite_preload_links
 *
 * @group CDN
 */
class Test_RewritePreloadLinks extends TestCase {
	private $preload_tag = '';

	public function set_up() {
		parent::set_up();

		add_filter( 'rocket_disable_meta_generator', '__return_true' );
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'running' ] );

		$this->unregisterAllCallbacksExceptMulti(
			'rocket_buffer',
			[
				2  => 'rewrite',
				3  => 'rewrite_srcset',
				19 => 'rewrite_preload_links',
			]
		);
	}

	public function tear_down() {
		remove_filter( 'rocket_buffer', [ $this, 'injectPreloadTag' ], 17 );
		remove_filter( 'content_url', [ $this, 'setContentURL' ] );
		remove_filter( 'includes_url', [ $this, 'setIncludesURL' ] );
		remove_filter( 'pre_get_rocket_option_cdn', '__return_zero' );
		remove_filter( 'rocket_disable_meta_generator', '__return_true' );
		delete_transient( 'rocketcdn_status' );
		unset( $GLOBALS['post'] );

		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
	}

	/**
	 * Stands in for the Optimize Critical Images injection, which runs at priority 17.
	 *
	 * @param string $html HTML content.
	 * @return string
	 */
	public function injectPreloadTag( $html ) {
		return str_replace( '</title>', '</title>' . $this->preload_tag, $html );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRewritePreloadLinks( $config, $original, $expected ) {
		if ( $config['excluded'] ) {
			$post_id = self::factory()->post->create( [ 'meta_input' => [ '_rocket_exclude_cdn' => 1 ] ] );

			$this->go_to( get_permalink( $post_id ) );
			$GLOBALS['post'] = get_post( $post_id );
		}

		$this->cnames      = $config['cnames'];
		$this->cdn_zone    = $config['cdn_zone'];
		$this->preload_tag = $config['preload'];

		$this->home_url     = 'http://example.org';
		$this->content_url  = 'http://example.org/wp-content/';
		$this->includes_url = 'http://example.org/wp-includes/';

		add_filter( 'home_url', [ $this, 'setHomeURL' ] );
		add_filter( 'content_url', [ $this, 'setContentURL' ] );
		add_filter( 'includes_url', [ $this, 'setIncludesURL' ] );

		if ( $config['cdn'] ) {
			add_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true' ] );
		} else {
			add_filter( 'pre_get_rocket_option_cdn', '__return_zero' );
		}

		add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'setCnames' ] );
		add_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'setCDNZone' ] );
		add_filter( 'rocket_buffer', [ $this, 'injectPreloadTag' ], 17 );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( apply_filters( 'rocket_buffer', $original ) )
		);
	}
}
