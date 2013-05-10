<?php

/**
 * TO DO - Description
 *
 * since 1.0
 *
 * @access public
 * @return void
 */

add_action( 'admin_footer', 'warning_using_permalinks' );
function warning_using_permalinks() {

	if( $GLOBALS['wp_rewrite']->using_permalinks() )
		return false;

 	?>

	<div class="error">
	<p><?php _e('A custom permalink structure is required for <strong>WP Rocket</strong> to work correctly. Please go to the Permalinks Options Page to configure your permalinks.', WP_ROCKET_TEXTDOMAIN ); ?></p>
	</div>

	<?php
}