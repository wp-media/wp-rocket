<?php

if ( ! class_exists( 'ActionScheduler_LoggerSchema' ) ) {
    class ActionScheduler_LoggerSchema {
        public function register_tables( $force_update = false ) {
            return true;
        }
    }
}