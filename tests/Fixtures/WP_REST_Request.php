<?php

if ( ! class_exists( 'WP_REST_Request' ) ) {

	class WP_REST_Request implements ArrayAccess {
		public $params = [];

		public function offsetExists( $key ) {
			return array_key_exists( $key, $this->params );
		}

		public function offsetGet( $key ) {
			if ( $this->offsetExists( $key ) ) {
				return $this->params[ $key ];
			}
		}

		public function offsetSet( $key, $value ) {
			$this->params[ $key ] = $value;
		}

		public function offsetUnset( $key ) {
			unset( $this->params[ $key ] );
		}

		public function has_param( $key ) {
			return $this->offsetExists( $key );
		}

		public function get_param( $key ) {
			return $this->offsetGet( $key );
		}

		public function set_param( $key, $value ) {
			$this->offsetSet( $key, $value );
		}
	}
}
