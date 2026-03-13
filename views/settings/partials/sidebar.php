<?php
/**
 * Sidebar template.
 *
 * @since 3.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fires before any sidebar content is rendered.
 *
 * This hook is used to display widgets like the Global Score widget
 * on all WP Rocket admin pages.
 *
 * @since 3.17
 */
do_action( 'rocket_before_sidebar_content' );

/**
 * Fires after sidebar content before quick actions (deprecated).
 *
 * @deprecated 3.17 Use 'rocket_before_sidebar_content' instead.
 */
do_action( 'rocket_after_sidebar_content' );

/**
 * Fires at the top of the sidebar.
 *
 * Used by Recommendations widget to display personalized recommendations.
 *
 * @since 3.21
 */
do_action( 'rocket_sidebar' );

$this->render_part( 'documentation' );
