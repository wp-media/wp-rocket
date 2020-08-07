<?php

if ( ! class_exists( 'WP_Theme' ) ) {
	class WP_Theme {
		private $theme_root;
		private $stylesheet;
		private $template;
		private $name;

		public function __construct( $theme_dir, $theme_root, $_child = null ) {
			$this->theme_root = $theme_root;
			$this->stylesheet = $theme_dir;
			$this->template   = $theme_dir;
			$this->name       = 'WordPress Default';
		}

		public function set_name( $name ) {
			$this->name = (string) $name;
		}

		public function set_template( $template ) {
			$this->template = (string) $template;
		}

		public function set_stylesheet( $stylesheet ) {
			$this->stylesheet = (string) $stylesheet;
		}

		public function get_template() {
			return $this->template;
		}

		public function get_stylesheet() {
			return $this->stylesheet;
		}

		public function get( $value ) {
			switch ( $value ) {
				case 'Name':
					return $this->name;
				case 'Template':
					return $this->template;
				default:
					return null;
			}
		}
	}
}
