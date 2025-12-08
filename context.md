# Laravel 11 Escrow Marketplace Project - AI Context Documentation

## Project Overview
This is a Laravel 11-based escrow marketplace platform with multi-cryptocurrency support (BTC/XMR), escrow functionality, dispute resolution system, support ticketing, and community forums.

## Technology Stack
- **Framework**: Laravel 11
- **Frontend**: Blade Templates + Tailwind CSS (no JavaScript, no icons/SVGs)
- **Database**: MySQL/MariaDB
- **Authentication**: Laravel's built-in authentication
- **File Storage**: Laravel Storage
- **Styling**: Pure Tailwind CSS utility classes

## Critical Laravel 11 Changes
- **Middleware Registration**: Constructor-based middleware (`$this->middleware()`) is REMOVED. All middleware must be registered at the route level in route files.
- **Route Files**: web.php, admin.php, staff.php, moderator.php
- **No Route Model Binding in Constructors**: Use route-level binding only

## Color Theme
- **Primary**: Amber/Yellow tones (amber-50, amber-100, amber-700, yellow-700)
- **Success**: Green
- **Danger**: Red
- **Info**: Blue
- **Warning**: Orange
- **Admin Interface**: Yellow/Amber theme
- **Moderator Interface**: Amber theme
- **Neutral**: Gray scale

## Project Structure

### Core Models and Relationships

#### User Model (`App\Models\User`)
**Key Fields**:
- id, uuid, username_pub, email, password
- two_factor_enabled, pin, recovery_passphrases
- is_admin, is_vendor, is_banned, ban_reason
- balance_btc, balance_xmr
- last_login_at, last_ip

**Relationships**:
- `hasMany` listings (as vendor)
- `hasManyThrough` venderOrders (orders through listings)
- `hasMany` orders (as buyer)
- `hasMany` initiatedDisputes (disputes created)
- `hasMany` disputesAgainst (disputes against them)
- `hasMany` assignedDisputes (as admin)
- `hasMany` forumPosts
- `hasMany` forumComments
- `hasMany` supportTickets
- `hasMany` assignedSupportTickets (as staff)
- `belongsToMany` roles

**Key Methods**:
- `fundWallets()` - creates initial wallet balances
- `hasRole($role)` - check user role
- `allDisputes()` - all disputes user is involved in

#### Order Model (`App\Models\Order`)
**Key Fields**:
- id, uuid, user_id, listing_id
- quantity, usd_price, btc_price, xmr_price
- status (pending, completed, cancelled, disputed)
- payment_method, transaction_id
- shipping_address (encrypted)

**Relationships**:
- `belongsTo` user (buyer)
- `belongsTo` listing
- `hasMany` messages (UserMessage)
- `hasOne` dispute

**Key Methods**:
- `hasActiveDispute()` - check if order has open dispute
- `canCreateDispute()` - validate dispute creation eligibility
- `shouldHoldEscrow()` - determine if escrow funds should be held

#### Listing Model (`App\Models\Listing`)
**Key Fields**:
- id, uuid, user_id, product_id
- title, description, price_usd
- quantity, origin_country_id, destination_country_id
- payment_method (direct, escrow)
- is_featured, status (active, inactive, deleted)

**Relationships**:
- `belongsTo` user (vendor)
- `belongsTo` product
- `belongsTo` originCountry
- `belongsTo` destinationCountry
- `hasMany` media (ListingMedia)
- `hasMany` orders
- `hasManyThrough` disputes (through orders)

#### Dispute Model (`App\Models\Dispute`)
**Key Fields**:
- id, uuid, order_id
- initiated_by, disputed_against, assigned_admin_id
- type, subject, description, status
- priority (low, medium, high, urgent)
- disputed_amount, refund_amount
- buyer_evidence, vendor_response
- resolution, admin_notes

**Statuses**:
- open, under_review, waiting_buyer, waiting_vendor
- escalated, resolved, closed

**Relationships**:
- `belongsTo` order
- `belongsTo` initiatedBy (User)
- `belongsTo` disputedAgainst (User)
- `belongsTo` assignedAdmin (User)
- `hasMany` messages (DisputeMessage)
- `hasMany` evidence (DisputeEvidence)

**Key Methods**:
- `isOpen()` - check if dispute is open
- `canUserParticipate($user)` - validate user participation
- `getOtherParty($user)` - get the other party in dispute

#### SupportTicket Model (`App\Models\SupportTicket`)
**Key Fields**:
- id, uuid, ticket_number, user_id, assigned_to
- category, subject, description
- status (open, in_progress, waiting_user, resolved, closed)
- priority (low, medium, high, urgent)
- first_response_at, resolved_at, closed_at

**Relationships**:
- `belongsTo` user
- `belongsTo` assignedTo (User as staff)
- `hasMany` messages (SupportTicketMessage)
- `hasMany` attachments (SupportTicketAttachment)

**Key Methods**:
- `generateTicketNumber()` - auto-generate ticket numbers

#### ForumPost Model (`App\Models\ForumPost`)
**Key Fields**:
- id, user_id, title, body
- status, views_count, last_activity_at
- is_pinned, is_locked

**Relationships**:
- `belongsTo` user
- `hasMany` comments (ForumComment)
- `morphMany` reports (ForumReport)

#### ForumComment Model (`App\Models\ForumComment`)
**Key Fields**:
- id, user_id, forum_post_id, parent_id, body

**Relationships**:
- `belongsTo` user
- `belongsTo` post (ForumPost)
- `belongsTo` parent (self-referential)
- `hasMany` replies (self-referential)
- `morphMany` reports (ForumReport)

### Controllers Architecture

#### Authentication Controllers
**AuthController** (`App\Http\Controllers\AuthController`)
- `showLoginForm()`, `login()`, `logout()`
- `showRegisterForm()`, `register()`

#### User-Facing Controllers
**HomeController** - Dashboard and listings index
**ListingController** - Product listing management
**OrderController** - Order creation and management
**DisputeController** - Dispute creation and resolution
**MessageController** - User-to-user messaging
**WalletController** - Wallet and transaction management
**ProfileController** - User profile and security settings
**SupportTicketController** - User support ticket management
**VendorController** - Vendor registration
**ForumPostController** - Forum post CRUD
**ForumCommentController** - Forum comment management
**ForumReportController** - Content reporting

#### Admin Controllers (`App\Http\Controllers\Admin`)
**AdminController** - Dashboard and reports
**AdminUsersController** - User management (ban, edit, wallet adjustments)
**AdminOrdersController** - Order oversight and intervention
**AdminListingsController** - Listing moderation (feature, disable)
**AdminDisputeController** - Dispute resolution and assignment
**AdminSupportTicketController** - Ticket assignment and management

#### Staff Controllers (`App\Http\Controllers\Staff`)
**StaffSupportTicketController** - Support ticket handling
- Staff members handle support tickets only

#### Moderator Controllers (`App\Http\Controllers\Staff`)
**ModeratorController** - Dashboard
**ModeratorUserController** - User oversight
**ModeratorContentController** - Content moderation (hide/show/delete)
**ForumModerationController** - Forum reports and bans
**ModeratorAuditController** - Audit log viewing

### Route Structure

#### Public Routes (web.php)
```php
// Auth routes (guest only)
/auth/login [GET, POST]
/auth/register [GET, POST]

// Authenticated routes
/logout [GET]
/profile [GET, PUT]
/profile/complete [GET]
/profile/password [GET, PUT]
/profile/pin [GET, PUT]
/profile/passphrases [GET, PUT]
/profile/{username} [GET] - public profile

// Listings
/listings [GET] - browse
/listings/create [GET, POST]
/listings/{listing} [GET] - view

// Orders
/orders [GET] - user's orders
/orders/{order} [GET] - view order
/listings/{listing}/create [POST] - create order
/listings/{listing}/orders [POST] - store order

// Disputes
/disputes [GET] - user's disputes
/disputes/create/{order} [GET, POST]
/disputes/{dispute} [GET]
/disputes/{dispute}/messages [POST]
/disputes/{dispute}/evidence [POST]
/disputes/{dispute}/evidence/{evidence}/download [GET]

// Support
/support [GET] - user's tickets
/support/create [GET, POST]
/support/{ticket} [GET]
/support/{ticket}/message [POST]
/support/{ticket}/attachment [POST]
/support/{ticket}/close [POST]

// Forum
/forum [GET] - post index
/forum/create [GET]
/forum/posts [POST]
/forum/posts/{post} [GET, PUT, DELETE]
/forum/posts/{post}/comments [POST]
/forum/comments/{comment}/reply [POST]
/forum/posts/{post}/report [POST]
/forum/comments/{comment}/report [POST]

// Messages
/messages [GET]
/messages/{thread} [GET, POST]

// Wallet
/wallet [GET]

// Vendor
/vendor/convert [GET, POST]
```

#### Admin Routes (admin.php)
```php
// Middleware: ['auth', 'admin']
// Prefix: /admin

/ - dashboard
/users - user management
/users/{user}/ban, /users/{user}/unban
/users/{user}/adjust-balance
/orders - order management
/orders/{order}/complete, /orders/{order}/cancel
/listings - listing oversight
/listings/{listing}/feature, /listings/{listing}/disable
/disputes - dispute management
/disputes/{dispute}/assign
/disputes/{dispute}/resolve
/disputes/{dispute}/add-admin-message
/support - support ticket management
/support/{ticket}/assign
/support/{ticket}/update-status
/reports - analytics and reports
/settings - system settings
```

#### Moderator Routes (moderator.php)
```php
// Middleware: ['auth', 'moderator']
// Prefix: /moderator

/ - dashboard
/users - user listing
/content - flagged content
/content/{type}/{id}/hide
/content/{type}/{id}/show
/content/{type}/{id} [DELETE]
/audit - audit logs
/forum/moderate/reports - review reports
/forum/moderate/reports/{report}/review [POST]
/forum/moderate/users/{user}/ban [POST]
/forum/moderate/users/{user}/unban [POST]
```

#### Staff Routes (staff.php)
```php
// Middleware: ['auth', 'role:support']
// Prefix: /staff

/support - ticket queue
/support/{ticket} - view ticket
/support/{ticket}/assign-me [POST]
/support/{ticket}/add-message [POST]
/support/{ticket}/update-status [POST]
```

### Database Schema

#### Core Tables
- **users**: User accounts and authentication
- **roles**: Role definitions (admin, moderator, support, vendor, user)
- **role_user**: Pivot table for user roles
- **permissions**: Permission definitions
- **permission_role**: Pivot table for role permissions

#### Marketplace Tables
- **listings**: Product listings
- **listing_media**: Images for listings
- **orders**: Purchase orders
- **products**: Product catalog
- **product_categories**: Product categorization
- **reviews**: Listing reviews
- **sales**: Sale records
- **favourites**: User favorites

#### Financial Tables
- **wallets**: User cryptocurrency wallets
- **wallet_transactions**: Transaction history

#### Communication Tables
- **user_messages**: Direct user messages
- **message_threads**: Message thread tracking
- **disputes**: Order dispute records
- **dispute_messages**: Dispute conversation
- **dispute_evidence**: Uploaded dispute evidence
- **support_tickets**: Support ticket records
- **support_ticket_messages**: Ticket messages
- **support_ticket_attachments**: Ticket file attachments

#### Forum Tables
- **forum_posts**: Forum post content
- **forum_comments**: Comments and replies
- **forum_reports**: Content reports

#### System Tables
- **audit_logs**: Moderation action logs
- **countries**: Country reference data
- **cache**, **cache_locks**: Laravel caching
- **jobs**, **job_batches**, **failed_jobs**: Queue system
- **sessions**: User sessions

### View Structure

#### Layouts
- `layouts/app.blade.php` - Main user layout
- `layouts/admin.blade.php` - Admin panel layout (yellow/amber theme)
- `layouts/moderator.blade.php` - Moderator panel layout
- `layouts/auth.blade.php` - Authentication pages layout
- `layouts/errors.blade.php` - Error page layout

#### View Directories
- `auth/` - Login and registration
- `profile/` - User profile management
- `listings/` - Product listings
- `orders/` - Order views
- `disputes/` - Dispute management
- `support/` - Support tickets
- `messages/` - User messaging
- `wallets/` - Wallet management
- `forum/` - Forum posts and comments
- `admin/` - Admin panel views
- `moderator/` - Moderator panel views
- `staff/` - Staff panel views
- `components/` - Reusable Blade components
- `errors/` - Error pages (401, 403, 404, 419, 429, 500, 503)

### Blade Templating Rules

#### Mandatory Guidelines
1. **No JavaScript** - Pure Blade and Tailwind only
2. **No Icons or SVGs** - Use text, Unicode symbols, or Tailwind styling
3. **Separate Files** - Each view should be its own file
4. **Color Theme Consistency** - Use amber/yellow for primary, maintain existing theme
5. **Tailwind Classes Only** - No custom CSS
6. **Responsive Design** - Mobile-first approach with Tailwind breakpoints

#### Common Patterns
```blade
{{-- Status Badges --}}
<span class="px-2 py-1 text-xs font-medium rounded
    {{ $status === 'active' ? 'bg-green-100 text-green-700' : '' }}
    {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
    {{ $status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}">
    {{ ucfirst($status) }}
</span>

{{-- Card Container --}}
<div class="bg-white border border-gray-200 rounded-lg p-6">
    <!-- Content -->
</div>

{{-- Buttons --}}
<button class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
    Action
</button>

{{-- Links --}}
<a href="{{ route('name') }}" class="text-amber-700 hover:text-amber-600">
    Link Text
</a>

{{-- Form Input --}}
<input type="text" 
       class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
       name="field">

{{-- Table --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Header
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">Data</td>
            </tr>
        </tbody>
    </table>
</div>
```

### Authentication & Authorization

#### Roles
- **Admin**: Full system access, dispute resolution, user management
- **Moderator**: Content moderation, forum management, audit logs
- **Support**: Support ticket handling
- **Vendor**: Can create listings
- **User**: Basic marketplace access

#### Middleware Usage (Laravel 11 Style)
```php
// In route files only, NOT in controllers
Route::middleware(['auth'])->group(function () {
    // Protected routes
});

Route::middleware(['auth', 'admin'])->group(function () {
    // Admin-only routes
});

Route::middleware(['auth', 'moderator'])->group(function () {
    // Moderator routes
});

Route::middleware(['auth', 'role:support'])->group(function () {
    // Support staff routes
});
```

### Business Logic Patterns

#### Order Flow
1. User browses listings
2. Creates order with quantity
3. Payment processed (BTC/XMR)
4. Order status: pending → completed/cancelled
5. If issues arise → dispute creation (only for completed orders)

#### Dispute Flow
1. Buyer creates dispute on completed order
2. Vendor notified, can respond
3. Status: open → under_review → waiting_buyer/waiting_vendor → escalated
4. Admin assigns, reviews evidence
5. Resolution applied (refund/partial/reject)
6. Status: resolved → closed

#### Support Ticket Flow
1. User creates ticket with category/subject
2. Auto-generates ticket number (ST-YYYYMMDD-####)
3. Staff assigns themselves or admin assigns
4. Status: open → in_progress → waiting_user → resolved → closed
5. Messages exchanged, attachments supported

#### Forum Moderation Flow
1. User posts content
2. Other users can report (spam, harassment, etc.)
3. Report appears in moderator queue
4. Moderator reviews: dismiss or take action
5. Actions: hide content, delete content, ban user
6. Audit log created for all actions

### Security Considerations

#### Encryption
- Shipping addresses stored encrypted
- Two-factor authentication support
- PIN codes for sensitive actions
- Recovery passphrases (hashed)

#### Access Control
- Role-based permissions
- Route-level middleware protection
- Model-level authorization checks
- Soft deletes for user data

#### Input Validation
- All forms use Laravel validation
- File upload restrictions on support/dispute evidence
- XSS protection via Blade escaping
- CSRF protection on all forms

### Common Helper Patterns

#### Route Names
```php
// User routes
route('home')
route('listings.show', $listing)
route('orders.show', $order)
route('disputes.show', $dispute)
route('support.show', $ticket)

// Admin routes
route('admin.dashboard')
route('admin.users.show', $user)
route('admin.disputes.show', $dispute)

// Moderator routes
route('moderator.dashboard')
route('moderator.content.index')
```

#### Model Route Binding
All models use UUID for routing:
```php
public function getRouteKeyName(): string
{
    return 'uuid';
}
```

#### Common Blade Includes
```blade
@extends('layouts.app')

@section('page-title', 'Page Title')

@section('breadcrumbs')
    <!-- Breadcrumb navigation -->
@endsection

@section('page-heading')
    Page Heading
@endsection

@section('content')
    <!-- Main content -->
@endsection
```

### Error Handling

#### Custom Error Pages
- 401 (Unauthorized)
- 403 (Forbidden)
- 404 (Not Found)
- 419 (CSRF Token Mismatch)
- 429 (Too Many Requests)
- 500 (Server Error)
- 503 (Service Unavailable)

All use `layouts/errors.blade.php` with amber/yellow theme.

### Development Guidelines

#### When Creating New Features
1. Define model relationships first
2. Create migration with proper foreign keys
3. Add model methods for business logic
4. Create controller with RESTful methods
5. Define routes (in appropriate file: web/admin/staff/moderator)
6. Apply middleware at route level (NOT in constructor)
7. Create Blade views following theme
8. Test with different user roles

#### Code Style
- Follow PSR-12 coding standards
- Use type hints in PHP 8.x
- Leverage Laravel 11 features
- Keep controllers thin, use service classes for complex logic
- Use form requests for validation
- Use resource controllers where appropriate

#### Naming Conventions
- Models: Singular PascalCase (User, Order, Dispute)
- Controllers: PascalCase with Controller suffix
- Routes: Lowercase with hyphens (kebab-case)
- Views: Lowercase with underscores (snake_case)
- Database tables: Plural snake_case (users, forum_posts)

### Testing Considerations

#### Test User Roles
- Regular user (username: user)
- Vendor (username: vendor)
- Support staff (username: support)
- Moderator (username: moderator)
- Admin (username: admin)

#### Test Scenarios
- Complete order flow
- Dispute creation and resolution
- Support ticket lifecycle
- Forum posting and moderation
- User banning and unbanning
- Wallet transactions
- File uploads (evidence, attachments)

### Performance Optimizations

#### Eager Loading
Always eager load relationships to avoid N+1:
```php
Dispute::with(['order.listing', 'initiatedBy', 'disputedAgainst'])
    ->get();
```

#### Pagination
Use Laravel pagination for all lists:
```php
$disputes = Dispute::latest()->paginate(20);
```

#### Caching
Cache configuration in cache tables.

### File Storage Structure

```
storage/app/
├── disputes/
│   └── evidence/
├── support/
│   └── attachments/
└── listings/
    └── media/
```

All file operations use Storage facade with proper access control.

## API Context for AI Models

When generating code for this project:

1. **Always use route-level middleware** - Never `$this->middleware()` in constructors
2. **Maintain color consistency** - Amber/yellow theme throughout
3. **No JavaScript** - Pure Blade templating only
4. **Separate files** - Never concatenate multiple Blade files
5. **Follow existing patterns** - Check similar controllers/views for consistency
6. **UUID routing** - All models use UUID, not ID
7. **Proper relationships** - Use existing Eloquent relationships
8. **Security first** - Validate input, check permissions, use Blade escaping
9. **Mobile responsive** - Use Tailwind breakpoints (sm:, md:, lg:, xl:)
10. **Accessibility** - Use semantic HTML and ARIA labels where needed

## Common AI Queries

### "Create a new view for X"
- Check layouts/ for appropriate parent layout
- Use existing component patterns from components/
- Follow color theme (amber/yellow primary)
- No icons, no JavaScript
- Responsive with Tailwind

### "Add a new route for Y"
- Determine appropriate route file (web/admin/staff/moderator)
- Apply middleware at route level
- Use resource routes where applicable
- Follow RESTful conventions

### "Create a controller method for Z"
- Check existing similar controllers
- Use dependency injection
- Return appropriate views or redirects
- Handle both success and error cases
- Add proper authorization checks

### "Modify the database schema"
- Create new migration file
- Follow naming conventions (YYYY_MM_DD_HHMMSS_description)
- Add proper foreign keys and indexes
- Update corresponding model relationships

## File Naming Reference

### Controllers
- User-facing: `XyzController.php` in `app/Http/Controllers/`
- Admin: `AdminXyzController.php` in `app/Http/Controllers/Admin/`
- Staff: `StaffXyzController.php` in `app/Http/Controllers/Staff/`
- Moderator: `ModeratorXyzController.php` in `app/Http/Controllers/Staff/`

### Views
- Pattern: `resource_action.blade.php`
- Examples: `listings_show.blade.php`, `disputes_create.blade.php`
- Admin: `admin_resource_action.blade.php`
- Moderator: `moderator_resource_action.blade.php`

### Models
- Singular PascalCase: `User.php`, `ForumPost.php`
- Location: `app/Models/`

This documentation provides complete context for AI models to understand and work with this Laravel 11 marketplace project effectively.
