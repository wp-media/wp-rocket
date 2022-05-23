<?php

namespace WP_Rocket\Tests\Integration;

use WP_Theme;

/**
 * Integration testing framework for themes (and child themes).
 *
 * @since 3.8
 */
abstract class WPThemeTestcase extends FilesystemTestCase {

	protected $theme;

	protected $child_theme;

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );
	}

	public function tear_down() {
		remove_filter( 'pre_option_stylesheet_root', [ $this, 'set_stylesheet_root' ] );

		parent::tear_down();
	}

	/**
	 * @param string $stylesheet The name of the dir in wp-content/themes/ where the theme resides.
	 * @param string $name       The Theme's actual name ("Divi", "Genesis", "Twenty Nineteen").
	 *
	 * @return WPThemeTestcase This instance, for chaining on a child theme.
	 */
	protected function set_theme( $stylesheet, $name ) {
		$this->filesystem->mkdir( 'vfs://public/wp-content/themes/' . $stylesheet );
		$this->filesystem->put_contents(
			'vfs://public/wp-content/themes/' . $stylesheet . '/style.css',
			'Theme Name: ' . $name
		);
		$this->filesystem->put_contents(
			'vfs://public/wp-content/themes/' . $stylesheet . '/index.php',
			'/** Silence is golden. */'
		);

		$this->theme = new WP_Theme( $stylesheet, 'vfs://public/wp-content/themes/' );

		return $this;
	}

	/**
	 * @param string $stylesheet The name of the dir in wp-content/themes/ where the child theme resides.
	 * @param string $name       The theme's actual name ("Divi Child", "Genesis Child").
	 * @param string $template   The name of the dir in wp-content/themes/ where the parent theme resides.
	 *
	 * @return void
	 */
	protected function set_child_theme( $stylesheet, $name, $template ) {

		$this->filesystem->mkdir( 'vfs://public/wp-content/themes/' . $stylesheet );
		$this->filesystem->put_contents(
			'vfs://wp-content/themes/' . $stylesheet . '/style.css',
			'Theme Name: ' . $name . "\nTemplate: " . $template
		);
		$this->filesystem->put_contents(
			'vfs://public/wp-content/themes/' . $stylesheet . '/index.php',
			'/** Silence is golden. */'
		);

		$this->child_theme = new WP_Theme( $stylesheet, 'vfs://public/wp-content/themes/' );
	}

	public function set_stylesheet_root() {
		global $wp_theme_directories;

		$wp_theme_directories['virtual'] = $this->filesystem->getUrl( 'wp-content/themes/' );

		return $this->filesystem->getUrl( 'wp-content/themes/' );
	}
}
