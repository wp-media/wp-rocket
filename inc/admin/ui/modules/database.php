<?php 
defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );

add_settings_section( 'rocket_display_database_options', __( 'Database Optimization', 'rocket' ), '__return_false', 'rocket_database' );
add_settings_field(
	'rocket_optimize_posts',
	__( 'Posts cleanup:', 'rocket' ),
	'rocket_field',
	'rocket_database',
	'rocket_display_database_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __('Revisions', 'rocket' ),
			'label_for'    => 'database_revisions',
			'label_screen' => __( 'Cleanup revisions', 'rocket' ),
		),
		array(
    		'type'         => 'helper_help',
    		'name'         => 'revisions_desc',
    		'description'  => sprintf( _n( '%d revision in your database.', '%d revisions in your database.', rocket_count_cleanup_items( 'revisions' ), 'rocket' ), rocket_count_cleanup_items( 'revisions' ) )
		),
		array(
			'type'         => 'checkbox',
			'label'        => __('Auto Drafts', 'rocket' ),
			'label_for'    => 'database_auto_drafts',
			'label_screen' => __( 'Cleanup auto drafts', 'rocket' ),
		),
		array(
    		'type'         => 'helper_help',
    		'name'         => 'auto_drafts_desc',
    		'description'  => sprintf( _n( '%d draft in your database.', '%d drafts in your database.', rocket_count_cleanup_items( 'auto_drafts' ), 'rocket' ), rocket_count_cleanup_items( 'auto_drafts' ) )
		),
		array(
			'type'         => 'checkbox',
			'label'        => __('Trashed posts', 'rocket' ),
			'label_for'    => 'database_trashed_posts',
			'label_screen' => __( 'Cleanup trashed posts', 'rocket' ),
		),
		array(
    		'type'         => 'helper_help',
    		'name'         => 'trashed_posts_desc',
    		'description'  => sprintf( _n( '%d trashed post in your database.', '%d trashed posts in your database.', rocket_count_cleanup_items( 'trashed_posts' ), 'rocket' ), rocket_count_cleanup_items( 'trashed_posts' ) )
		),
	)
);

add_settings_field(
	'rocket_optimize_comments',
	__( 'Comments cleanup:', 'rocket' ),
	'rocket_field',
	'rocket_database',
	'rocket_display_database_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __('Unapproved comments', 'rocket' ),
			'label_for'    => 'database_unapproved_comments',
			'label_screen' => __( 'Cleanup unapproved comments', 'rocket' ),
		),
		array(
    		'type'         => 'helper_help',
    		'name'         => 'unapproved_comments_desc',
    		'description'  => sprintf( _n( '%d unapproved comment in your database.', '%d unapproved comments in your database.', rocket_count_cleanup_items( 'unapproved_comments' ), 'rocket' ), rocket_count_cleanup_items( 'unapproved_comments' ) )
		),
		array(
			'type'         => 'checkbox',
			'label'        => __('Spam comments', 'rocket' ),
			'label_for'    => 'database_spam_comments',
			'label_screen' => __( 'Cleanup spam comments', 'rocket' ),
		),
		array(
    		'type'         => 'helper_help',
    		'name'         => 'spam_comments_desc',
    		'description'  => sprintf( _n( '%d spam comment in your database.', '%d spam comments in your database.', rocket_count_cleanup_items( 'spam_comments' ), 'rocket' ), rocket_count_cleanup_items( 'spam_comments' ) )
		),
		array(
			'type'         => 'checkbox',
			'label'        => __('Trashed comments', 'rocket' ),
			'label_for'    => 'database_trashed_comments',
			'label_screen' => __( 'Cleanup trashed comments', 'rocket' ),
		),
		array(
    		'type'         => 'helper_help',
    		'name'         => 'trashed_comments_desc',
    		'description'  => sprintf( _n( '%d trashed comment in your database.', '%d trashed comments in your database.', rocket_count_cleanup_items( 'trashed_comments' ), 'rocket' ), rocket_count_cleanup_items( 'trashed_comments' ) )
		),
	)
);

add_settings_field(
	'rocket_optimize_transients',
	__( 'Transients cleanup:', 'rocket' ),
	'rocket_field',
	'rocket_database',
	'rocket_display_database_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __('Expired transients', 'rocket' ),
			'label_for'    => 'database_expired_transients',
			'label_screen' => __( 'Cleanup expired transients', 'rocket' ),
		),
		array(
    		'type'         => 'helper_help',
    		'name'         => 'expired_transients_desc',
    		'description'  => sprintf( _n( '%d expired transient in your database.', '%d expired transients in your database.', rocket_count_cleanup_items( 'expired_transients' ), 'rocket' ), rocket_count_cleanup_items( 'expired_transients' ) )
		),
		array(
			'type'         => 'checkbox',
			'label'        => __('All transients', 'rocket' ),
			'label_for'    => 'database_all_transients',
			'label_screen' => __( 'Cleanup all transients', 'rocket' ),
		),
		array(
    		'type'         => 'helper_help',
    		'name'         => 'all_transients_desc',
    		'description'  => sprintf( _n( '%d transient in your database.', '%d transients in your database.', rocket_count_cleanup_items( 'all_transients' ), 'rocket' ), rocket_count_cleanup_items( 'all_transients' ) )
		),
	)
);

add_settings_field(
	'rocket_optimize_database',
	__( 'Database cleanup:', 'rocket' ),
	'rocket_field',
	'rocket_database',
	'rocket_display_database_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __('Optimize tables', 'rocket' ),
			'label_for'    => 'database_optimize_tables',
			'label_screen' => __( 'Optimize database tables', 'rocket' ),
		),
		array(
    		'type'         => 'helper_help',
    		'name'         => 'optimize_tables_desc',
    		'description'  => sprintf( _n( '%d table to optimize in your database.', '%d tables to optimize in your database.', rocket_count_cleanup_items( 'optimize_tables' ), 'rocket' ), rocket_count_cleanup_items( 'optimize_tables' ) )
		),
	)
);

add_settings_field(
	'rocket_database_cron',
	__( 'Automatic cleanup:', 'rocket' ),
	'rocket_field',
	'rocket_database',
	'rocket_display_database_options',
	array(
		array(
			'type'         => 'checkbox',
			'label'        => __('Schedule automatic cleanup', 'rocket' ),
			'name'         => 'schedule_automatic_cleanup',
			'label_screen' => __( 'Schedule an automatic cleanup of the database', 'rocket' ),
		),
		array(
    		'parent'       => 'schedule_automatic_cleanup',
			'type'         => 'select',
			'label'        => __('Frequency', 'rocket' ),
			'name'         => 'automatic_cleanup_frequency',
			'label_screen' => __( 'Frequency for the automatic cleanup', 'rocket' ),
			'options'      => array(
    			'daily'    => __( 'Daily', 'rocket' ),
    			'weekly'   => __( 'Weekly', 'rocket' ),
    			'monthly'  => __( 'Monthly', 'rocket' )
			)
		),
	)
);

add_settings_field(
	'rocket_run_optimize',
	__( 'Run cleanup:', 'rocket' ),
	'rocket_field',
	'rocket_database',
	'rocket_display_database_options',
	array(
        array(
            'type' => 'submit_optimize',
        ),
		array(
    		'type'         => 'helper_description',
    		'name'         => 'submit_optimize_desc',
    		'description'  => __( 'If you made any change to the settings, use the save and optimize button', 'rocket' )
		),
	)
);