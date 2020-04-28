<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Subscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\Subscriber::rewrite_css_properties
 * @uses   \WP_Rocket\Engine\CDN\CDN::rewrite_css_properties
 * @uses   \WP_Rocket\Admin\Options_Data::get
 * @group  Subscriber
 */
class Test_RewriteCssProperties extends TestCase {

	public function testShouldRewriteCSSProperties() {
		update_option(
			'wp_rocket_settings',
			[
				'cdn'              => '1',
				'cdn_cnames'       => [
					'cdn.example.org',
				],
				'cdn_zone'         => [
					'all',
				],
				'cdn_reject_files' => [],
			]
		);

		$options        = new Options_Data( ( new Options( 'wp_rocket_' ) )->get( 'settings' ) );
		$cdn_subscriber = new CDNSubscriber( $options, new CDN( $options ) );

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/original.css' );
		$rewrite  = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/rewrite.css' );

		$this->assertSame(
			$rewrite,
			$cdn_subscriber->rewrite_css_properties( $original )
		);
	}

	public function testShouldReturnOriginalWhenFilterIsFalse() {
		update_option(
			'wp_rocket_settings',
			[
				'cdn'              => '1',
				'cdn_cnames'       => [
					'cdn.example.org',
				],
				'cdn_zone'         => [
					'all',
				],
				'cdn_reject_files' => [],
			]
		);

		$options        = new Options_Data( ( new Options( 'wp_rocket_' ) )->get( 'settings' ) );
		$cdn_subscriber = new CDNSubscriber( $options, new CDN( $options ) );

		add_filter( 'do_rocket_cdn_css_properties', '__return_false' );

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/original.css' );

		$this->assertSame(
			$original,
			$cdn_subscriber->rewrite_css_properties( $original )
		);
	}
}
