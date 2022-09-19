<?php

if ( ! class_exists( 'ActionScheduler_StoreSchema' ) ) {
    class ActionScheduler_StoreSchema {
        public function register_tables( $force_update = false ) {
            return true;
        }
    }
}