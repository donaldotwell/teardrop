<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportTicketAttachment extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['data_uri'];

    public function supportTicket()
    {
        return $this->belongsTo(SupportTicket::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get data URI for displaying the image
     */
    public function getDataUriAttribute(): string
    {
        return "data:{$this->type};base64,{$this->content}";
    }

    /**
     * Check if attachment is an image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->type, 'image/');
    }
}
