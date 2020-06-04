<?php

if ( ! class_exists( 'WP_Admin_Bar' ) ) {
	class WP_Admin_Bar {
		private $nodes = [];

		public function add_menu( $args ) {
			$defaults = [
				'id'     => false,
				'title'  => false,
				'parent' => false,
				'href'   => false,
				'group'  => false,
				'meta'   => [],
			];

			$args = array_merge( $defaults, $args );

			$this->_set_node( $args );
		}

		public function _set_node( $args ) {
			$this->nodes[ $args['id'] ] = (object) $args;
		}

		public function get_node( $id ) {
			if ( isset( $this->nodes[ $id ] ) ) {
				return $this->nodes[ $id ];
			}
		}
	}
}
