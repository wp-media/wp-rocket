<?php
/**
 * Global score widget "Add Page" button template.
 */

defined( 'ABSPATH' ) || exit;

$rocket_insights_add_button_args = [
	'label'      => $data['pages_num'] ? __( 'Add Pages', 'rocket' ) : __( 'Add Homepage', 'rocket' ),
	'parameters' => [
		'type' => 'all',
	],
	'url'        => '#rocket_insights',
	'attributes' => [
		'class'       => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-plus wpr-button--no-min-width wpr-ri-add-url-button wpr-ri-global-score-add-url-button',
		'data-source' => 'dashboard',
	],
];

// Add tooltip if reach max URL and disable btn.
if ( $data['reach_max_url'] ) {
	$rocket_insights_add_button_args['url']                    = '';
	$rocket_insights_add_button_args['attributes']['class']   .= ' wpr-btn-with-tool-tip disabled';
	$rocket_insights_add_button_args['attributes']['disabled'] = 'disabled';
	$rocket_insights_add_button_args['tooltip']                = esc_html__( 'You have reached your maximum page limit', 'rocket' );
}

$this->render_action_button(
	'link',
	$data['pages_num'] ? '' : 'rocket_rocket_insights_add_homepage',
	$rocket_insights_add_button_args
);
