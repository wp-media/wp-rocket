<?php

/**
 * Class ActionScheduler_AdminHelp
 * @codeCoverageIgnore
 */
class ActionScheduler_AdminHelp {
	const SCREEN_ID = 'tools_page_action-scheduler';

	public function add_help_tabs() {
		$screen = get_current_screen();

		if ( ! $screen || self::SCREEN_ID != $screen->id ) {
			return;
		}

		$screen->add_help_tab(
			array(
				'id'      => 'action_scheduler_about',
				'title'   => __( 'About', 'action-scheduler' ),
				'content' =>
					'<h2>' . __( 'About Action Scheduler', 'action-scheduler' ) . '</h2>' .
					'<p>' .
						__( 'Action Scheduler is a scalable, traceable job queue for background processing large sets of actions. Action Scheduler works by triggering an action hook to run at some time in the future. Scheduled actions can also be scheduled to run on a recurring schedule.', 'action-scheduler' ) .
					'</p>',
			)
		);

		$screen->add_help_tab(
			array(
				'id'      => 'action_scheduler_columns',
				'title'   => __( 'Columns', 'action-scheduler' ),
				'content' =>
					'<h2>' . __( 'Scheduled Action Columns', 'action-scheduler' ) . '</h2>' .
					'<ul>' .
					sprintf( '<li><strong>%1$s</strong>: %2$s</li>', __( 'Hook', 'action-scheduler' ), __( 'Name of the action hook that will be triggered.', 'action-scheduler' ) ) . 
					sprintf( '<li><strong>%1$s</strong>: %2$s</li>', __( 'Status', 'action-scheduler' ), __( 'Action statuses are Pending, Complete, Canceled, Failed', 'action-scheduler' ) ) . 
					sprintf( '<li><strong>%1$s</strong>: %2$s</li>', __( 'Arguments', 'action-scheduler' ), __( 'Optional data array passed to the action hook.', 'action-scheduler' ) ) . 
					sprintf( '<li><strong>%1$s</strong>: %2$s</li>', __( 'Group', 'action-scheduler' ), __( 'Optional action group.', 'action-scheduler' ) ) . 
					sprintf( '<li><strong>%1$s</strong>: %2$s</li>', __( 'Recurrence', 'action-scheduler' ), __( 'The action\'s schedule frequency.', 'action-scheduler' ) ) . 
					sprintf( '<li><strong>%1$s</strong>: %2$s</li>', __( 'Scheduled', 'action-scheduler' ), __( 'The date/time the action is/was scheduled to run.', 'action-scheduler' ) ) . 
					sprintf( '<li><strong>%1$s</strong>: %2$s</li>', __( 'Log', 'action-scheduler' ), __( 'Activity log for the action.', 'action-scheduler' ) ) . 
					'</ul>',
			)
		);
	}
}