<?php

return [
    'pagination' => [
        'posts_per_page' => 15,
        'comments_per_page' => 20,
        'reports_per_page' => 20,
    ],

    'limits' => [
        'max_post_length' => 10000,
        'max_comment_length' => 5000,
        'max_report_reason_length' => 1000,
        'max_nested_comment_depth' => 3,
        'max_reports_per_day' => 5,
    ],

    'auto_moderation' => [
        'new_user_threshold_months' => 2,
        'suspicious_reports_threshold' => 10,
        'enable_auto_ban' => false, // Set to true to enable automatic banning
    ],
];
