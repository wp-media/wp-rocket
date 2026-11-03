<?php
/**
 * Sidebar template of the inner pages.
 *
 * It only fires the sidebar hooks: the Rocket Insights score and Recommendations cards hook onto them.
 * The page prints the sidebar aside only when nothing is output here.
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
 * Fires after the sidebar content (deprecated).
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
