# Support Ticket Escalation & Assignment Workflow

## Overview
Support tickets follow a clear escalation path from moderators to admins, ensuring efficient issue resolution.

## Workflow Stages

### 1. Ticket Creation (User)
**Route:** `POST /support` → `SupportTicketController@store`

**Process:**
- User submits support ticket with category, priority, description
- Rate limit: 3 tickets per hour per user
- Ticket auto-assigned to available moderator via `assignToAvailableStaff()`
- Assignment logic:
  - Filters for users with 'moderator' role
  - Calculates workload (open/pending/in_progress/on_hold tickets)
  - Assigns to moderator with lowest workload
  - Max workload: 15 tickets per moderator (configurable in `config/tickets.php`)
  - Returns `null` if all moderators at capacity
- System message created: "Ticket created and assigned to [moderator]"

**File:** [app/Http/Controllers/SupportTicketController.php](app/Http/Controllers/SupportTicketController.php) lines 122, 496-539

---

### 2. Moderator Assignment Actions
**Route Prefix:** `/moderator/tickets` (middleware: `['auth', 'moderator']`)

**Available Actions:**

#### Assign to Me
- **Route:** `POST /moderator/tickets/{ticket}/assign-me`
- **Method:** `ModeratorTicketController@assignMe`
- **Use Case:** Unassigned ticket, moderator claims it
- **UI:** Button visible when `!$supportTicket->assignedTo`

#### Unassign
- **Route:** `POST /moderator/tickets/{ticket}/unassign`
- **Method:** `ModeratorTicketController@unassign`
- **Use Case:** Moderator releases ticket back to queue
- **UI:** Button visible when `$supportTicket->assignedTo->id === auth()->id()`

#### Reassign to Another Moderator
- **Route:** `POST /moderator/tickets/{ticket}/reassign-staff`
- **Method:** `ModeratorTicketController@reassignStaff`
- **Validation:** `staff_id` must have 'moderator' role
- **Use Case:** Transfer to more specialized moderator

**Files:** 
- [routes/moderator.php](routes/moderator.php) lines 31-51
- [resources/views/moderator/tickets/show.blade.php](resources/views/moderator/tickets/show.blade.php) lines 21-32

---

### 3. Escalation to Admin
**Route:** `POST /moderator/tickets/{ticket}/escalate`
**Method:** `ModeratorTicketController@escalate`

**Requirements:**
- Ticket must be assigned to the current moderator
- Ticket status cannot already be 'escalated'

**Process:**
```php
1. Validate escalation_reason (required, max 500 chars)
2. Validate escalate_to (optional admin user ID)
3. Update ticket:
   - priority = 'urgent'
   - status = 'escalated'
   - metadata = {
       'escalated_by': moderator_id,
       'escalated_at': timestamp,
       'escalation_reason': reason
     }
4. If escalate_to specified:
   - Verify user has 'admin' role
   - Reassign ticket via $ticket->assignTo($admin)
5. Create escalation message (is_internal = true)
6. Log to AuditLog: 'ticket_escalated'
7. Redirect with success message
```

**UI Component:** `<details>` dropdown in page actions
- Textarea for escalation reason (required)
- Select dropdown to choose specific admin (optional)
- Auto-assign if no admin selected

**Files:**
- [app/Http/Controllers/Staff/ModeratorTicketController.php](app/Http/Controllers/Staff/ModeratorTicketController.php) lines 267-320
- [resources/views/moderator/tickets/show.blade.php](resources/views/moderator/tickets/show.blade.php) lines 37-71

---

### 4. Admin Management
**Route Prefix:** `/admin/support` (middleware: `['auth', 'admin']`)

**Available Actions:**

#### View Escalated Tickets
- **Route:** `GET /admin/support?status=escalated`
- **Method:** `AdminSupportTicketController@index`
- **Filters:** status, priority, category, assigned_to, date range
- **Stats:** total, open, unassigned, overdue, resolved today, avg response time

#### Reassign to Admin or Moderator
- **Route:** `POST /admin/support/{ticket}/reassign-staff`
- **Method:** `AdminSupportTicketController@reassignStaff`
- **Validation:** `staff_id` (nullable|exists:users,id)
- **Role Check:** User must have 'admin' OR 'moderator' role
- **Actions:**
  - If `staff_id` is null: unassign ticket
  - If `staff_id` provided: reassign to that admin/moderator
- **Audit:** Creates internal message with reassignment details

#### Direct Assignment
- **Route:** `POST /admin/support/{ticket}/assign`
- **Method:** `AdminSupportTicketController@assign`
- **Validation:** `assigned_to` (required|exists:users,id)
- **Role Check:** User must have 'admin' OR 'moderator' role

#### Resolve Ticket
- **Route:** `POST /admin/support/{ticket}/resolve`
- **Method:** `AdminSupportTicketController@resolve`
- **Updates:** status = 'resolved', resolved_at = now()

#### Close Ticket
- **Route:** `POST /admin/support/{ticket}/close`
- **Method:** `AdminSupportTicketController@close`
- **Updates:** status = 'closed', closed_at = now()

**Files:**
- [routes/admin.php](routes/admin.php) lines 72-89
- [app/Http/Controllers/Admin/AdminSupportTicketController.php](app/Http/Controllers/Admin/AdminSupportTicketController.php)

---

## Role Verification

### Moderator Role Check
```php
// In middleware (routes/moderator.php)
Route::middleware(['auth', 'moderator'])->prefix('moderator')->name('moderator.')->group(...)

// In controllers
if (!auth()->user()->hasRole('moderator')) {
    abort(403);
}
```

### Admin Role Check
```php
// In middleware (routes/admin.php)
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(...)

// In controllers (AdminSupportTicketController)
if (!$user->hasAnyRole(['admin', 'moderator'])) {
    return redirect()->back()->with('error', 'Selected user is not a staff member.');
}
```

**Important:** The 'support' role does NOT exist. Only 'admin' and 'moderator' roles handle support tickets.

---

## Database Schema

### support_tickets Table
```sql
- id (primary key)
- user_id (foreign key → users.id) -- Ticket creator
- assigned_to (foreign key → users.id) -- Current assignee (moderator or admin)
- ticket_number (unique, e.g., "TICK-12345")
- subject (string, max 255)
- description (text)
- category (enum: account, listing, order, dispute, technical, payment, other)
- priority (enum: low, medium, high, urgent)
- status (enum: open, in_progress, pending_user, on_hold, escalated, resolved, closed)
- metadata (jsonb) -- Stores escalation_reason, escalated_by, escalated_at
- first_response_at (timestamp)
- resolved_at (timestamp)
- closed_at (timestamp)
- last_activity_at (timestamp)
- created_at, updated_at
```

### Key Relationships
```php
// SupportTicket model
public function user() // belongsTo User (ticket creator)
public function assignedTo() // belongsTo User (staff assigned)
public function messages() // hasMany SupportTicketMessage
public function attachments() // hasMany SupportTicketAttachment
```

---

## Status Flow

```
open 
  ↓
in_progress (moderator working on it)
  ↓
[OPTION 1: Moderator Resolution]
  → resolved
  → closed

[OPTION 2: Escalation to Admin]
  → escalated (priority set to 'urgent')
  → reassigned to admin
  → admin resolves
  → resolved
  → closed

[OPTION 3: Need User Response]
  → pending_user
  → user responds
  → back to in_progress
```

---

## Configuration

### Ticket Limits
**File:** [config/tickets.php](config/tickets.php)

```php
'max_staff_workload' => 15, // Max tickets per moderator
'rate_limit' => 3, // Max tickets per hour per user
'rate_limit_window' => 60, // Minutes
```

### Ticket Categories
```php
'categories' => [
    'account' => 'Account Issues',
    'listing' => 'Listing Problems',
    'order' => 'Order Issues',
    'dispute' => 'Dispute Support',
    'technical' => 'Technical Problems',
    'payment' => 'Payment Issues',
    'other' => 'Other',
]
```

---

## Summary of Recent Fixes

### Issue
`AdminSupportTicketController` was checking for 'support' role which doesn't exist in the application.

### Solution
Updated all instances in `AdminSupportTicketController` to check for `['admin', 'moderator']` instead of `['admin', 'support']`:

1. **Line 87** - `index()` method: staff members filter query
2. **Line 198** - `show()` method: staff members for assignment dropdown
3. **Line 217** - `assign()` method: role validation
4. **Line 262** - `reassignStaff()` method: role validation
5. **Line 633** - `autoAssign()` method: available staff query

**Changed from:**
```php
$q->whereIn('name', ['admin', 'support'])
if (!$user->hasAnyRole(['admin', 'support']))
```

**Changed to:**
```php
$q->whereIn('name', ['admin', 'moderator'])
if (!$user->hasAnyRole(['admin', 'moderator']))
```

---

## Testing Checklist

- [ ] User creates ticket → auto-assigns to moderator
- [ ] Moderator views assigned tickets
- [ ] Moderator escalates ticket with reason
- [ ] Escalation sets status='escalated', priority='urgent'
- [ ] Escalation assigns to specific admin (if selected)
- [ ] Admin views escalated tickets
- [ ] Admin reassigns ticket to another admin
- [ ] Admin reassigns ticket back to moderator
- [ ] Admin resolves escalated ticket
- [ ] Audit log captures all actions
- [ ] Internal messages show escalation history

---

## Related Documentation

- [BLADE_CLEANUP_PLAN.md](BLADE_CLEANUP_PLAN.md) - UI/UX guidelines
- [context.md](context.md) - Full model relationships and database schema
- [.github/copilot-instructions.md](.github/copilot-instructions.md) - Controller patterns and role checking
