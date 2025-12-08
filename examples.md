# Laravel 11 Marketplace - Code Examples & Patterns

## Controller Examples

### Basic CRUD Controller Pattern

```php
<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;

class ResourceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $resources = Resource::with(['user', 'relatedModel'])
            ->latest()
            ->paginate(20);
        
        return view('resources.index', compact('resources'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Pass any needed data for form dropdowns
        return view('resources.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'field' => 'required|string|max:255',
            'number_field' => 'required|numeric|min:0',
        ]);

        $resource = Resource::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return redirect()
            ->route('resources.show', $resource)
            ->with('success', 'Resource created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Resource $resource)
    {
        // Load additional relationships if needed
        $resource->load(['user', 'comments.user']);
        
        return view('resources.show', compact('resource'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Resource $resource)
    {
        // Authorization check
        if ($resource->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        return view('resources.edit', compact('resource'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Resource $resource)
    {
        // Authorization check
        if ($resource->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'field' => 'required|string|max:255',
        ]);

        $resource->update($validated);

        return redirect()
            ->route('resources.show', $resource)
            ->with('success', 'Resource updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Resource $resource)
    {
        // Authorization check
        if ($resource->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }

        $resource->delete();

        return redirect()
            ->route('resources.index')
            ->with('success', 'Resource deleted successfully.');
    }
}
```

### Admin Controller Pattern

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use Illuminate\Http\Request;

class AdminResourceController extends Controller
{
    /**
     * Display admin resource listing with filters
     */
    public function index(Request $request)
    {
        $query = Resource::with(['user']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('title', 'LIKE', "%{$search}%")
                ->orWhere('description', 'LIKE', "%{$search}%");
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Date range filter
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->get('from_date'));
        }
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->get('to_date'));
        }

        $resources = $query->latest()->paginate(20);

        // Get counts for filter buttons
        $statusCounts = [
            'all' => Resource::count(),
            'active' => Resource::where('status', 'active')->count(),
            'inactive' => Resource::where('status', 'inactive')->count(),
        ];

        return view('admin.resources.index', compact('resources', 'statusCounts'));
    }

    /**
     * Admin action example
     */
    public function toggleStatus(Resource $resource)
    {
        $newStatus = $resource->status === 'active' ? 'inactive' : 'active';
        $resource->update(['status' => $newStatus]);

        return redirect()
            ->back()
            ->with('success', "Resource {$newStatus}.");
    }
}
```

## Model Pattern Examples

### Model with Relationships

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ExampleModel extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'priority',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    /**
     * Boot method for auto-generating UUID
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    /**
     * Relationships
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Accessor example
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Business logic method
     */
    public function canBeEditedBy(User $user): bool
    {
        return $this->user_id === $user->id || $user->hasRole('admin');
    }
}
```

## Blade View Pattern Examples

### Index View (List)

```blade
@extends('layouts.app')

@section('page-title', 'Resources')

@section('breadcrumbs')
    <span class="text-gray-600">Resources</span>
@endsection

@section('page-heading')
    Resources
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Filter Bar --}}
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <form method="GET" action="{{ route('resources.index') }}" class="flex flex-wrap gap-4">
                <input type="text" 
                       name="search" 
                       placeholder="Search resources..." 
                       value="{{ request('search') }}"
                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                
                <select name="status" 
                        class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500">
                    <option value="">All Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" 
                        class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700">
                    Filter
                </button>
                
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('resources.index') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Clear
                    </a>
                @endif
            </form>
        </div>

        {{-- Status Tabs --}}
        <div class="flex space-x-2 border-b border-gray-200">
            <a href="{{ route('resources.index') }}" 
               class="px-4 py-2 -mb-px {{ !request('status') ? 'border-b-2 border-amber-600 text-amber-600' : 'text-gray-600 hover:text-gray-900' }}">
                All ({{ $statusCounts['all'] }})
            </a>
            <a href="{{ route('resources.index', ['status' => 'active']) }}" 
               class="px-4 py-2 -mb-px {{ request('status') === 'active' ? 'border-b-2 border-amber-600 text-amber-600' : 'text-gray-600 hover:text-gray-900' }}">
                Active ({{ $statusCounts['active'] }})
            </a>
            <a href="{{ route('resources.index', ['status' => 'inactive']) }}" 
               class="px-4 py-2 -mb-px {{ request('status') === 'inactive' ? 'border-b-2 border-amber-600 text-amber-600' : 'text-gray-600 hover:text-gray-900' }}">
                Inactive ({{ $statusCounts['inactive'] }})
            </a>
        </div>

        {{-- Resource List --}}
        <div class="space-y-4">
            @forelse($resources as $resource)
                <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                <a href="{{ route('resources.show', $resource) }}" 
                                   class="hover:text-amber-600">
                                    {{ $resource->title }}
                                </a>
                            </h3>
                            
                            <p class="text-gray-600 mb-3">{{ Str::limit($resource->description, 150) }}</p>
                            
                            <div class="flex items-center space-x-4 text-sm text-gray-500">
                                <span>By {{ $resource->user->username_pub }}</span>
                                <span>{{ $resource->created_at->diffForHumans() }}</span>
                                
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    {{ $resource->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ ucfirst($resource->status) }}
                                </span>
                            </div>
                        </div>
                        
                        <a href="{{ route('resources.show', $resource) }}" 
                           class="ml-4 text-amber-600 hover:text-amber-700 font-medium">
                            View
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-white border border-gray-200 rounded-lg p-8 text-center">
                    <p class="text-gray-500">No resources found.</p>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($resources->hasPages())
            <div class="flex justify-center">
                {{ $resources->links() }}
            </div>
        @endif
    </div>
@endsection
```

### Show View (Detail)

```blade
@extends('layouts.app')

@section('page-title', $resource->title)

@section('breadcrumbs')
    <a href="{{ route('resources.index') }}" class="text-amber-700 hover:text-amber-600">
        Resources
    </a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-600">{{ Str::limit($resource->title, 30) }}</span>
@endsection

@section('page-heading')
    {{ $resource->title }}
@endsection

@section('content')
    <div class="space-y-6">
        {{-- Main Content Card --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            {{-- Header --}}
            <div class="flex items-start justify-between mb-6 pb-6 border-b border-gray-200">
                <div class="flex-1">
                    <div class="flex items-center space-x-3 mb-2">
                        <h1 class="text-2xl font-bold text-gray-900">{{ $resource->title }}</h1>
                        
                        <span class="px-3 py-1 rounded text-sm font-medium
                            {{ $resource->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                            {{ ucfirst($resource->status) }}
                        </span>
                    </div>
                    
                    <div class="flex items-center space-x-4 text-sm text-gray-500">
                        <span>By {{ $resource->user->username_pub }}</span>
                        <span>{{ $resource->created_at->format('M d, Y') }}</span>
                    </div>
                </div>

                @if(auth()->check() && $resource->canBeEditedBy(auth()->user()))
                    <div class="flex space-x-2">
                        <a href="{{ route('resources.edit', $resource) }}" 
                           class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                            Edit
                        </a>
                        
                        <form method="POST" action="{{ route('resources.destroy', $resource) }}" 
                              onsubmit="return confirm('Are you sure you want to delete this resource?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">
                                Delete
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            {{-- Description --}}
            <div class="prose max-w-none">
                <p class="text-gray-700 whitespace-pre-wrap">{{ $resource->description }}</p>
            </div>
        </div>

        {{-- Additional Sections (Comments, etc.) --}}
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Comments</h2>
            
            {{-- Comment form or list here --}}
        </div>
    </div>
@endsection
```

### Form View (Create/Edit)

```blade
@extends('layouts.app')

@section('page-title', isset($resource) ? 'Edit Resource' : 'Create Resource')

@section('breadcrumbs')
    <a href="{{ route('resources.index') }}" class="text-amber-700 hover:text-amber-600">
        Resources
    </a>
    <span class="text-gray-300">/</span>
    <span class="text-gray-600">{{ isset($resource) ? 'Edit' : 'Create' }}</span>
@endsection

@section('page-heading')
    {{ isset($resource) ? 'Edit Resource' : 'Create New Resource' }}
@endsection

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <form method="POST" action="{{ isset($resource) ? route('resources.update', $resource) : route('resources.store') }}">
                @csrf
                @if(isset($resource))
                    @method('PUT')
                @endif

                {{-- Title Field --}}
                <div class="mb-6">
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Title <span class="text-red-600">*</span>
                    </label>
                    <input type="text" 
                           id="title"
                           name="title" 
                           value="{{ old('title', $resource->title ?? '') }}"
                           required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500
                                  @error('title') border-red-500 @enderror">
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Description Field --}}
                <div class="mb-6">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Description <span class="text-red-600">*</span>
                    </label>
                    <textarea id="description"
                              name="description" 
                              rows="6"
                              required
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500
                                     @error('description') border-red-500 @enderror">{{ old('description', $resource->description ?? '') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status Select --}}
                <div class="mb-6">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status
                    </label>
                    <select id="status" 
                            name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500
                                   @error('status') border-red-500 @enderror">
                        <option value="active" {{ old('status', $resource->status ?? 'active') === 'active' ? 'selected' : '' }}>
                            Active
                        </option>
                        <option value="inactive" {{ old('status', $resource->status ?? '') === 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Priority Radio Buttons --}}
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Priority
                    </label>
                    <div class="space-y-2">
                        @foreach(['low', 'medium', 'high'] as $priority)
                            <label class="flex items-center">
                                <input type="radio" 
                                       name="priority" 
                                       value="{{ $priority }}"
                                       {{ old('priority', $resource->priority ?? 'medium') === $priority ? 'checked' : '' }}
                                       class="text-amber-600 focus:ring-amber-500">
                                <span class="ml-2 text-gray-700">{{ ucfirst($priority) }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('priority')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Checkbox Example --}}
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" 
                               name="is_featured" 
                               value="1"
                               {{ old('is_featured', $resource->is_featured ?? false) ? 'checked' : '' }}
                               class="rounded text-amber-600 focus:ring-amber-500">
                        <span class="ml-2 text-gray-700">Feature this resource</span>
                    </label>
                </div>

                {{-- Form Actions --}}
                <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('resources.index') }}" 
                       class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300">
                        Cancel
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-amber-600 text-white rounded hover:bg-amber-700">
                        {{ isset($resource) ? 'Update' : 'Create' }} Resource
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
```

## Route Definition Examples

### web.php Pattern

```php
<?php

use App\Http\Controllers\ResourceController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Guest-only routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // Resource routes
    Route::resource('resources', ResourceController::class);
    
    // OR explicit routes:
    Route::prefix('resources')->name('resources.')->group(function () {
        Route::get('/', [ResourceController::class, 'index'])->name('index');
        Route::get('/create', [ResourceController::class, 'create'])->name('create');
        Route::post('/', [ResourceController::class, 'store'])->name('store');
        Route::get('/{resource}', [ResourceController::class, 'show'])->name('show');
        Route::get('/{resource}/edit', [ResourceController::class, 'edit'])->name('edit');
        Route::put('/{resource}', [ResourceController::class, 'update'])->name('update');
        Route::delete('/{resource}', [ResourceController::class, 'destroy'])->name('destroy');
    });
    
    // Custom actions
    Route::post('/resources/{resource}/favorite', [ResourceController::class, 'favorite'])
        ->name('resources.favorite');
});
```

### admin.php Pattern

```php
<?php

use App\Http\Controllers\Admin\AdminResourceController;
use Illuminate\Support\Facades\Route;

// All routes require auth and admin middleware
Route::middleware(['auth', 'admin'])->name('admin.')->group(function () {
    
    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Resource management
    Route::prefix('resources')->name('resources.')->group(function () {
        Route::get('/', [AdminResourceController::class, 'index'])->name('index');
        Route::get('/{resource}', [AdminResourceController::class, 'show'])->name('show');
        
        // Admin actions
        Route::post('/{resource}/approve', [AdminResourceController::class, 'approve'])
            ->name('approve');
        Route::post('/{resource}/reject', [AdminResourceController::class, 'reject'])
            ->name('reject');
        Route::post('/{resource}/toggle-status', [AdminResourceController::class, 'toggleStatus'])
            ->name('toggle-status');
    });
    
    // Bulk actions
    Route::post('/resources/bulk-action', [AdminResourceController::class, 'bulkAction'])
        ->name('resources.bulk-action');
});
```

## Database Migration Examples

### Creating a Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            
            $table->string('title');
            $table->text('description');
            
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['status', 'created_at']);
            $table->index('is_featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
```

### Creating a Pivot Table

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resource_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            
            $table->unique(['resource_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resource_tag');
    }
};
```

## Validation Patterns

### Form Request Example

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreResourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'status' => ['required', Rule::in(['active', 'inactive', 'pending'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'is_featured' => ['boolean'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['exists:tags,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Please provide a title for the resource.',
            'description.required' => 'Please provide a description.',
        ];
    }
}
```

## Common Blade Components

### Alert Component (resources/views/components/alert.blade.php)

```blade
@props(['type' => 'info'])

@php
$classes = match($type) {
    'success' => 'bg-green-50 border-green-200 text-green-800',
    'error' => 'bg-red-50 border-red-200 text-red-800',
    'warning' => 'bg-yellow-50 border-yellow-200 text-yellow-800',
    'info' => 'bg-blue-50 border-blue-200 text-blue-800',
    default => 'bg-gray-50 border-gray-200 text-gray-800',
};
@endphp

<div {{ $attributes->merge(['class' => "border rounded-lg p-4 {$classes}"]) }}>
    {{ $slot }}
</div>
```

Usage:
```blade
<x-alert type="success">
    Operation completed successfully!
</x-alert>
```

### Status Badge Component

```blade
@props(['status'])

@php
$classes = match($status) {
    'active', 'completed', 'approved' => 'bg-green-100 text-green-700',
    'pending', 'waiting' => 'bg-yellow-100 text-yellow-700',
    'inactive', 'cancelled', 'rejected' => 'bg-red-100 text-red-700',
    'processing', 'in_progress' => 'bg-blue-100 text-blue-700',
    default => 'bg-gray-100 text-gray-700',
};
@endphp

<span {{ $attributes->merge(['class' => "px-2 py-1 text-xs font-medium rounded {$classes}"]) }}>
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>
```

Usage:
```blade
<x-status-badge :status="$resource->status" />
```

This supplementary document provides ready-to-use code patterns that follow the project's conventions and Laravel 11 best practices.
