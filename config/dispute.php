<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Dispute Management Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the dispute management system including moderator
    | workload limits, auto-assignment rules, and escalation settings.
    |
    */

    // Maximum number of active disputes a moderator can have
    'max_moderator_workload' => env('DISPUTE_MAX_MODERATOR_WORKLOAD', 10),

    // Auto-assignment settings
    'auto_assignment' => [
        'enabled' => env('DISPUTE_AUTO_ASSIGNMENT_ENABLED', true),
        'max_age_hours' => env('DISPUTE_AUTO_ASSIGNMENT_MAX_AGE', 24), // Only auto-assign disputes newer than this
        'batch_size' => env('DISPUTE_AUTO_ASSIGNMENT_BATCH_SIZE', 10), // Max disputes to assign at once
    ],

    // Default information request deadline (in days)
    'default_info_request_deadline_days' => env('DISPUTE_DEFAULT_INFO_DEADLINE', 3),

    // Escalation settings
    'escalation' => [
        'auto_escalate_after_days' => env('DISPUTE_AUTO_ESCALATE_DAYS', 7), // Auto-escalate if no progress
        'priority_escalation_hours' => [
            'urgent' => 6,
            'high' => 24,
            'medium' => 72,
            'low' => 168, // 1 week
        ],
    ],

    // Status definitions
    'statuses' => [
        'open' => 'Open - Awaiting assignment',
        'under_review' => 'Under Review - Assigned to moderator',
        'waiting_vendor' => 'Waiting for Vendor Response',
        'waiting_buyer' => 'Waiting for Buyer Response',
        'waiting_both' => 'Waiting for Both Parties',
        'escalated' => 'Escalated to Admin',
        'resolved' => 'Resolved',
        'closed' => 'Closed',
    ],

    // Priority levels
    'priorities' => [
        'low' => 'Low Priority',
        'medium' => 'Medium Priority',
        'high' => 'High Priority',
        'urgent' => 'Urgent',
    ],

    // Message types for dispute communication
    'message_types' => [
        'user_message' => 'User Message',
        'moderator_note' => 'Moderator Note',
        'system_message' => 'System Message',
        'assignment_update' => 'Assignment Update',
        'info_request' => 'Information Request',
        'escalation' => 'Escalation Notice',
        'status_update' => 'Status Update',
    ],
];
