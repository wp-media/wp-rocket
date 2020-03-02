<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\CDN\CDNSubscriber;

use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\CDN\CDN;
use WP_Rocket\Subscriber\CDN\CDNSubscriber;

abstract class TestCase extends BaseTestCase {

	protected function getSubscriberInstance() {
		$options = new Options_Data( ( new Options( 'wp_rocket_' ) )->get( 'settings' ) );

		return new CDNSubscriber( $options, new CDN( $options ) );
	}
}
