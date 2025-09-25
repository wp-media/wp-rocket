<?php
/**
 * Global score widget "Add Page" button template.
 */

defined( 'ABSPATH' ) || exit;

$rocket_pma_add_button_args = [
	'label'      => $data['pages_num'] ? __( 'Add Pages', 'rocket' ) : __( 'Add Homepage', 'rocket' ),
	'parameters' => [
		'type' => 'all',
	],
	'url'        => '#rocket_insights',
	'attributes' => [
		'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-plus wpr-button--no-min-width wpr-pma-add-url-button wpr-pma-global-score-add-url-button',
	],
];

// Add tooltip if reach max URL and disable btn.
if ( $data['reach_max_url'] ) {
	$rocket_pma_add_button_args['url']                    = '';
	$rocket_pma_add_button_args['attributes']['class']   .= ' wpr-btn-with-tool-tip disabled';
	$rocket_pma_add_button_args['attributes']['disabled'] = 'disabled';
	$rocket_pma_add_button_args['tooltip']                = esc_html__( 'You have reached your maximum page limit', 'rocket' );
}

$this->render_action_button(
	'link',
	$data['pages_num'] ? '' : 'rocket_pm_add_homepage',
	$rocket_pma_add_button_args
);
