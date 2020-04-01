<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\CDN\CDNSubscriber;

/**
 * @covers \WP_Rocket\Subscriber\CDN\CDNSubscriber::rewrite_srcset
 * @uses   \WP_Rocket\CDN\CDN::rewrite_srcset
 * @group  Subscriber
 */
class Test_RewriteSrcset extends TestCase {

	public function testShouldRewriteSrcsetURLs() {
		update_option(
			'wp_rocket_settings',
			[
				'cdn'              => '1',
				'cdn_cnames'       => [ 'cdn.example.org' ],
				'cdn_zone'         => [ 'images' ],
				'cdn_reject_files' => [],
			]
		);

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/original.html' );

		$this->assertSame(
			file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/srcset/rewrite.html' ),
			$this->getSubscriberInstance()->rewrite_srcset( $original )
		);
	}

	public function testShouldReturnOriginalWhenCDNDisabled() {
		update_option(
			'wp_rocket_settings',
			[
				'cdn'              => '0',
				'cdn_cnames'       => [ 'cdn.example.org' ],
				'cdn_zone'         => [ 'all' ],
				'cdn_reject_files' => [],
			]
		);

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/original.html' );

		$this->assertSame(
			$original,
			$this->getSubscriberInstance()->rewrite_srcset( $original )
		);
	}

	public function testShouldReturnOriginalWhenNoCNAME() {
		update_option(
			'wp_rocket_settings',
			[
				'cdn'              => '1',
				'cdn_cnames'       => [],
				'cdn_zone'         => [],
				'cdn_reject_files' => [],
			]
		);

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/original.html' );

		$this->assertSame(
			$original,
			$this->getSubscriberInstance()->rewrite_srcset( $original )
		);
	}
}
