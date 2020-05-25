<?php

if ( ! class_exists( 'wpdb' ) ) {
	class wpdb {
		public $posts         = 'posts';
		public $term_taxonomy = 'terms';
		public $posts_results = [];
		public $terms_results = [];

		public function get_results( $sql ) {
			if ( $this->is_post( $sql ) ) {
				return $this->posts_results;
			}

			if ( $this->is_term( $sql ) ) {
				return $this->terms_results;
			}

			return [];
		}

		public function setTerms( $results ) {
			$this->term_taxonomy = 'terms';
			$this->terms_results = $results;
		}

		public function setPosts( $results ) {
			$this->posts         = 'posts';
			$this->posts_results = $results;
		}

		private function is_post( $sql ) {
			return $this->starts_with( $sql, 'SELECT MAX(ID) as ID, post_type' );
		}

		private function is_term( $sql ) {
			return $this->starts_with( $sql, 'SELECT MAX( term_id ) AS ID, taxonomy' );
		}

		private function starts_with( $string, $starting_string ) {
			$string = trim( $string );
			$len    = strlen( $starting_string );

			return ( substr( $string, 0, $len ) === $starting_string );
		}
	}
}
