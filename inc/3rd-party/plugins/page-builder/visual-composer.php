<?php

defined( 'ABSPATH' ) || exit;

if ( defined( 'WPB_VC_VERSION' ) && class_exists( 'Vc_Manager' ) ) :
	/**
	 * Disable nonce checking for Visual Composer grid
	 */
	add_filter( 'vc_grid_get_grid_data_access', '__return_true' );
endif;
