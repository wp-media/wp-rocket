<?php
return [
    'rocketSaasPendingJobsCronInterval' => [
        'config' => [
            'old_hook' => 'rocket_rucss_pending_jobs_cron_interval',
            'new_hook' => 'rocket_saas_pending_jobs_cron_interval',
            'args' => [ 1 * 60 ],
            'version' => '3.16',
        ],
        'expected' => 120,
    ],
    'rocketSaasPendingJobsCronRowsCount' => [
        'config' => [
            'old_hook' => 'rocket_rucss_pending_jobs_cron_rows_count',
            'new_hook' => 'rocket_saas_pending_jobs_cron_rows_count',
            'args' => [ 100 ],
            'version' => '3.16',
        ],
        'expected' => 200,
    ],
];