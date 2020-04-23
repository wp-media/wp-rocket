<?php

if ( ! class_exists( 'WP_Theme') ) {
	class WP_Theme {
		private $theme_root;
		private $stylesheet;
		private $template;
		private $name;

		public function __construct( $theme_dir, $theme_root, $_child = null ) {
			$this->theme_root = $theme_root;
			$this->stylesheet = $theme_dir;
			$this->template = $theme_dir;
			$this->name = 'WordPress Default';
		}

		public function get_template() {
			return $this->template;
		}

		public function get_stylesheet() {
			return $this->stylesheet;
		}

		public function get( $value ) {
			if ( 'Name' === $value ) {
				return $this->name;
			}
		}
	}
}
