<?php

if ( ! class_exists( 'WP_Error') ) {
	class WP_Error {
		private $code;
		private $message;
		private $data;

		public function __construct( $code, $message, $data = null ) {
			$this->code = $code;
			$this->message = $message;
			$this->data = $data;
		}

		public function get_error_code() {
			return $this->code;
		}

		public function get_error_message() {
			return $this->message;
		}

		public function get_error_data() {
			return $this->data;
		}
	}
}
