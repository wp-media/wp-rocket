<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\Subscriber::rewrite
 * @uses   \WP_Rocket\Engine\CDN\CDN::rewrite
 * @group  Subscriber
 */
class Test_Rewrite extends TestCase {

	public function testShouldRewriteURL() {
		update_option(
			'wp_rocket_settings',
			[
				'cdn'              => '1',
				'cdn_cnames'       => [ 'cdn.example.org' ],
				'cdn_zone'         => [ 'all' ],
				'cdn_reject_files' => [],
			]
		);

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/original.html' );

		$this->assertSame(
			file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/rewrite.html' ),
			$this->getSubscriberInstance()->rewrite( $original )
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
			$this->getSubscriberInstance()->rewrite( $original )
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
			$this->getSubscriberInstance()->rewrite( $original )
		);
	}
}
