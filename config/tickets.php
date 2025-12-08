<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Ticket Management Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the ticket management system including moderator
    | workload limits, auto-assignment rules, and escalation settings.
    |
    */

    // Maximum number of active tickets a moderator can have
    'max_moderator_workload' => env('TICKET_MAX_MODERATOR_WORKLOAD', 15),

    // Auto-assignment settings
    'auto_assignment' => [
        'enabled' => env('TICKET_AUTO_ASSIGNMENT_ENABLED', true),
        'max_age_hours' => env('TICKET_AUTO_ASSIGNMENT_MAX_AGE', 48), // Only auto-assign tickets newer than this
        'batch_size' => env('TICKET_AUTO_ASSIGNMENT_BATCH_SIZE', 20), // Max tickets to assign at once
        'categories' => ['account_issues', 'user_reports', 'content_moderation', 'dispute_appeals'],
    ],

    // Moderator-specific ticket categories
    'moderator_categories' => [
        'account_issues' => 'Account Issues',
        'user_reports' => 'User Reports',
        'content_moderation' => 'Content Moderation',
        'dispute_appeals' => 'Dispute Appeals',
    ],

    // All ticket categories
    'categories' => [
        'account_issues' => 'Account Issues',
        'user_reports' => 'User Reports',
        'content_moderation' => 'Content Moderation',
        'dispute_appeals' => 'Dispute Appeals',
        'technical_support' => 'Technical Support',
        'billing' => 'Billing & Payments',
        'feature_request' => 'Feature Request',
        'bug_report' => 'Bug Report',
        'other' => 'Other',
    ],

    // Ticket statuses
    'statuses' => [
        'open' => 'Open',
        'in_progress' => 'In Progress',
        'pending_user' => 'Pending User Response',
        'on_hold' => 'On Hold',
        'escalated' => 'Escalated',
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

    // SLA targets (in hours)
    'sla_targets' => [
        'first_response' => [
            'urgent' => 1,   // 1 hour
            'high' => 4,     // 4 hours
            'medium' => 12,  // 12 hours
            'low' => 24,     // 24 hours
        ],
        'resolution' => [
            'urgent' => 8,   // 8 hours
            'high' => 24,    // 1 day
            'medium' => 72,  // 3 days
            'low' => 168,    // 1 week
        ],
    ],

    // Escalation rules
    'escalation' => [
        'auto_escalate_overdue_hours' => env('TICKET_AUTO_ESCALATE_HOURS', 48),
        'escalation_reasons' => [
            'complex_issue' => 'Complex issue requiring admin expertise',
            'policy_violation' => 'Potential policy violation',
            'security_concern' => 'Security-related concern',
            'legal_matter' => 'Legal or compliance matter',
            'high_value_user' => 'High-value user requiring special attention',
            'technical_limitation' => 'Technical limitation requiring developer input',
            'other' => 'Other (please specify)',
        ],
    ],

    // Follow-up settings
    'follow_up' => [
        'default_days' => 3,
        'categories_requiring_follow_up' => [
            'account_issues',
            'dispute_appeals',
        ],
    ],

    // Message types for ticket communication
    'message_types' => [
        'user_message' => 'User Message',
        'staff_message' => 'Staff Response',
        'system_message' => 'System Message',
        'assignment_update' => 'Assignment Update',
        'status_update' => 'Status Update',
        'escalation' => 'Escalation Notice',
        'follow_up' => 'Follow-up Message',
        'internal_note' => 'Internal Note',
    ],

    // Notification settings
    'notifications' => [
        'new_ticket_threshold_minutes' => 30, // Notify about new tickets after 30 minutes
        'overdue_ticket_reminder_hours' => 24, // Remind about overdue tickets every 24 hours
        'escalation_notification' => true,
    ],
];
