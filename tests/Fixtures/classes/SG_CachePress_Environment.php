<?php
if ( ! class_exists( 'SG_CachePress_Environment' ) ) {
	class SG_CachePress_Environment {
		public function cache_is_enabled() {
			return false;
		}
	}
}
