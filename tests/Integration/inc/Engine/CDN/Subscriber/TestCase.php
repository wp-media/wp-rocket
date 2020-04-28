<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

use WPMedia\PHPUnit\Integration\TestCase as BaseTestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Subscriber;

abstract class TestCase extends BaseTestCase {

	protected function getSubscriberInstance() {
		$options = new Options_Data( ( new Options( 'wp_rocket_' ) )->get( 'settings' ) );

		return new Subscriber( $options, new CDN( $options ) );
	}
}
