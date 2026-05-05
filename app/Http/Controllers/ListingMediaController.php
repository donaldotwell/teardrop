<?php

namespace App\Http\Controllers;

use App\Models\ListingMedia;
use Illuminate\Http\Response;

class ListingMediaController extends Controller
{
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    public function show(ListingMedia $listingMedia): Response
    {
        abort_if(
            !$listingMedia->listing()->where('is_active', true)->whereNull('deleted_at')->exists(),
            404
        );

        $contentType = in_array($listingMedia->type, self::ALLOWED_TYPES, true)
            ? $listingMedia->type
            : 'image/jpeg';

        return response(base64_decode($listingMedia->content), 200, [
            'Content-Type'  => $contentType,
            'Cache-Control' => 'public, max-age=2592000, immutable',
        ]);
    }
}
