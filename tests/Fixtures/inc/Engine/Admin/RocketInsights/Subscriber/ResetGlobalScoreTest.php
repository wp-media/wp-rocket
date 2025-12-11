<?php

return [
	'testShouldResetGlobalScoreOnJobCompleted' => [
		'config' => [
			'hook_to_test' => 'rocket_rocket_insights_job_completed',
			'setup_data' => true,
		],
		'expected' => [
			'hook_registered' => true,
			'hook_name' => 'rocket_rocket_insights_job_completed',
			'method_callable' => true,
			'global_score_reset' => true,
		],
	],
	'testShouldResetGlobalScoreOnJobFailed' => [
		'config' => [
			'hook_to_test' => 'rocket_rocket_insights_job_failed',
			'setup_data' => true,
		],
		'expected' => [
			'hook_registered' => true,
			'hook_name' => 'rocket_rocket_insights_job_failed',
			'method_callable' => true,
			'global_score_reset' => true,
		],
	],
	'testShouldResetGlobalScoreOnJobAdded' => [
		'config' => [
			'hook_to_test' => 'rocket_rocket_insights_job_added',
			'setup_data' => true,
		],
		'expected' => [
			'hook_registered' => true,
			'hook_name' => 'rocket_rocket_insights_job_added',
			'method_callable' => true,
			'global_score_reset' => true,
		],
	],
	'testShouldResetGlobalScoreOnJobRetest' => [
		'config' => [
			'hook_to_test' => 'rocket_rocket_insights_job_retest',
			'setup_data' => true,
		],
		'expected' => [
			'hook_registered' => true,
			'hook_name' => 'rocket_rocket_insights_job_retest',
			'method_callable' => true,
			'global_score_reset' => true,
		],
	],
	'testShouldResetGlobalScoreOnJobDeleted' => [
		'config' => [
			'hook_to_test' => 'rocket_rocket_insights_job_deleted',
			'setup_data' => true,
		],
		'expected' => [
			'hook_registered' => true,
			'hook_name' => 'rocket_rocket_insights_job_deleted',
			'method_callable' => true,
			'global_score_reset' => true,
		],
	],
	'testShouldResetGlobalScoreDirectly' => [
		'config' => [
			'setup_data' => true,
			// Test without triggering specific hooks
		],
		'expected' => [
			'method_callable' => true,
			'global_score_reset' => true,
		],
	],
];
