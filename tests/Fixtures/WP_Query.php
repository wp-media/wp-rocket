<?php

if ( ! class_exists( 'WP_Query' ) ) {
    class WP_Query {
        public static $set_posts = [];
        public $posts = [];

        public static $have_posts = false;

        public function __construct( array $arg ) {
            $this->posts();
        }
        
        public function posts() {
            $this->posts = self::$set_posts;
        }

        public function have_posts() : bool {
            return self::$have_posts;
        }
    }
}