<?php

if ( ! class_exists( 'WP_Error') ) {
	class WP_Error {
		private $code;
		private $message;
		private $error_data;

		public function __construct( $code = '', $message = '', $error_data = '' ) {
			$this->code = $code;
			$this->message = $message;
			$this->error_data = $error_data;
		}

		public function get_error_code() {
			return $this->code;
		}

		public function get_error_message() {
			return $this->message;
		}

		public function get_error_data() {
			return $this->error_data;
		}
	}
}
