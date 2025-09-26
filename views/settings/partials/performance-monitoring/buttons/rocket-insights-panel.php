<?php
/**
 * Rocket Insight Tab "Add Page" button template.
 */

defined( 'ABSPATH' ) || exit;

$rocket_pma_add_page_button_args = [
	'label'      => __( 'Add page +', 'rocket' ),
	'attributes' => [
		'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-pma-add-url-button',
		'id'    => 'add_page_speed_radar',
	],
];
if ( $data['reach_max_url'] ) {
	$rocket_pma_add_page_button_args['attributes']['class']   .= ' wpr-btn-with-tool-tip disabled';
	$rocket_pma_add_page_button_args['attributes']['disabled'] = 'disabled';
	$rocket_pma_add_page_button_args['url']                    = '#';
	$rocket_pma_add_page_button_args['tooltip']                = esc_html__( 'You have reached your maximum page limit', 'rocket' );
}
$this->render_action_button(
	'button',
	'add_page_speed_radar',
	$rocket_pma_add_page_button_args
);
