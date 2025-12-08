<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisputeEvidence extends Model
{
    use HasFactory;

    /**
     * The attributes that are guarded.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'file_size' => 'integer',
        ];
    }

    /**
     * Get the dispute that owns this evidence.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function dispute(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Dispute::class);
    }

    /**
     * Get the user who uploaded this evidence.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function uploadedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the admin who verified this evidence.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function verifiedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Get the base64 data URI for direct rendering.
     *
     * @return string
     */
    public function getDataUriAttribute(): string
    {
        return "data:{$this->type};base64,{$this->content}";
    }

    /**
     * Get human-readable file size.
     *
     * @return string
     */
    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Check if the evidence file is an image.
     *
     * @return bool
     */
    public function isImage(): bool
    {
        return str_starts_with($this->type, 'image/');
    }

    /**
     * Check if the evidence file is a document.
     *
     * @return bool
     */
    public function isDocument(): bool
    {
        return in_array($this->type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain',
        ]);
    }

    /**
     * Verify this evidence.
     *
     * @param User $admin
     * @return void
     */
    public function verify(User $admin): void
    {
        $this->update([
            'is_verified' => true,
            'verified_at' => now(),
            'verified_by' => $admin->id,
        ]);

        // Add message to dispute about verification
        $this->dispute->messages()->create([
            'user_id' => $admin->id,
            'message' => "Evidence '{$this->file_name}' has been verified by admin.",
            'message_type' => 'system_message',
            'is_internal' => false,
        ]);
    }

    /**
     * Get the evidence type color for display.
     *
     * @return string
     */
    public function getTypeColor(): string
    {
        return match ($this->evidence_type) {
            'product_photo'     => 'blue',
            'packaging_photo'   => 'green',
            'shipping_label'    => 'purple',
            'receipt'           => 'yellow',
            'communication'     => 'indigo',
            'damage_photo'      => 'red',
            'tracking_info'     => 'cyan',
            'other_document'    => 'gray',
            default             => 'gray',
        };
    }

    /**
     * Scope for verified evidence only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope for unverified evidence only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Scope for evidence of a specific type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('evidence_type', $type);
    }
}
