<?php
/**
 * Sidebar template.
 *
 * @since 3.0
 */

defined( 'ABSPATH' ) || exit;
/**
 * Fires at the top of the sidebar.
 *
 * Used by Recommendations widget to display personalized recommendations.
 *
 * @since 3.21
 */
do_action( 'rocket_sidebar' );
$this->render_part( 'documentation' );
