<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Themes\Avada;

use WP_Rocket\Tests\Integration\FilesystemTestCase;

abstract class TestCase extends FilesystemTestCase {
	protected $container;
	protected $event;
	protected $subscriber;

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		add_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );

		$this->container = apply_filters( 'rocket_container', '' );
		$this->event = $this->container->get( 'event_manager' );
	}

	public function tear_down() {
		global $wp_theme_directories;
		unset( $wp_theme_directories['virtual'] );

		$this->event->remove_subscriber( $this->subscriber );

		remove_filter( 'pre_option_stylesheet', [ $this, 'set_stylesheet' ] );
		remove_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );
		parent::tear_down();
	}

	public function set_stylesheet() {
		return 'avada';
	}

	public function set_stylesheet_root() {
		global $wp_theme_directories;

		$wp_theme_directories['virtual'] = $this->filesystem->getUrl( 'wp-content/themes/' );

		return $this->filesystem->getUrl( 'wp-content/themes/' );
	}
}
