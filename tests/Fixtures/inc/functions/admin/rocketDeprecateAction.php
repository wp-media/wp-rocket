<?php
return [
    'rocketSaasCompleteJobStatus' => [
        'config' => [
            'old_hook' => 'rocket_rucss_pending_jobs_cron_rows_count',
            'new_hook' => 'rocket_saas_pending_jobs_cron_rows_count',
            'args' => [ 1 * 60 ],
            'version' => '3.16',
        ],
        'expected' => 1,
    ],
];