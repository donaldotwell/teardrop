<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardBase;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CardController extends Controller
{
    public function index(Request $request): View
    {
        $vendor = $request->user();
        $bases  = CardBase::where('vendor_id', $vendor->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('vendor.cards.index', compact('bases'));
    }

    public function create(): View
    {
        return view('vendor.cards.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'         => 'required|string|max:120',
            'price_usd'    => 'required|numeric|min:0.01|max:9999',
            'discount_pct' => 'nullable|numeric|min:0|max:99',
            'file'         => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $vendor = $request->user();
        $file   = $request->file('file');

        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->withErrors(['file' => 'Could not read uploaded file.']);
        }

        $base = DB::transaction(function () use ($vendor, $request) {
            return CardBase::create([
                'vendor_id'       => $vendor->id,
                'name'            => trim($request->input('name')),
                'price_usd'       => $request->input('price_usd'),
                'discount_pct'    => $request->input('discount_pct', 0),
                'record_count'    => 0,
                'available_count' => 0,
                'sold_count'      => 0,
                'is_active'       => true,
            ]);
        });

        [$inserted, $skipped] = $this->parseAndInsert($handle, $base, $base->price_usd);
        fclose($handle);

        if ($inserted === 0) {
            $base->delete();
            return back()->withErrors(['file' => "No valid records found. {$skipped} row(s) skipped (missing card_number, exp_month, exp_year, cvv, or cardholder_name)."]);
        }

        $base->update([
            'record_count'    => $inserted,
            'available_count' => $inserted,
        ]);

        Log::info('Cards base uploaded', [
            'vendor_id' => $vendor->id,
            'base_id'   => $base->id,
            'inserted'  => $inserted,
            'skipped'   => $skipped,
        ]);

        return redirect()
            ->route('vendor.cards.show', $base)
            ->with('success', "Base created: {$inserted} card(s) imported, {$skipped} skipped.");
    }

    public function show(Request $request, CardBase $base): View
    {
        $this->authorizeBase($base, $request->user());

        $records = $base->records()
            ->orderBy('status')
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('vendor.cards.show', compact('base', 'records'));
    }

    public function update(Request $request, CardBase $base): RedirectResponse
    {
        $this->authorizeBase($base, $request->user());

        $validated = $request->validate([
            'name'            => 'required|string|max:120',
            'price_usd'       => 'required|numeric|min:0.01|max:9999',
            'discount_pct'    => 'nullable|numeric|min:0|max:99',
            'update_existing' => 'nullable|in:1',
        ]);

        $base->update([
            'name'         => $validated['name'],
            'price_usd'    => $validated['price_usd'],
            'discount_pct' => $validated['discount_pct'] ?? 0,
        ]);

        if ($request->boolean('update_existing')) {
            Card::where('base_id', $base->id)
                ->where('status', 'available')
                ->update(['price_usd' => $validated['price_usd']]);
        }

        $msg = 'Base updated.';
        if ($request->boolean('update_existing')) $msg .= ' Existing unsold cards repriced.';
        if ((float) ($validated['discount_pct'] ?? 0) > 0) $msg .= ' Discount of ' . $validated['discount_pct'] . '% applied.';
        return back()->with('success', $msg);
    }

    public function upload(Request $request, CardBase $base): RedirectResponse
    {
        $this->authorizeBase($base, $request->user());

        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $handle = fopen($request->file('file')->getRealPath(), 'r');
        if (!$handle) {
            return back()->withErrors(['file' => 'Could not read uploaded file.']);
        }

        [$inserted, $skipped] = $this->parseAndInsert($handle, $base, $base->price_usd);
        fclose($handle);

        if ($inserted === 0) {
            return back()->withErrors(['file' => "No valid records found. {$skipped} row(s) skipped."]);
        }

        $base->increment('record_count',    $inserted);
        $base->increment('available_count', $inserted);

        Log::info('Cards base appended', [
            'vendor_id' => $base->vendor_id,
            'base_id'   => $base->id,
            'inserted'  => $inserted,
            'skipped'   => $skipped,
        ]);

        return back()->with('success', "{$inserted} card(s) added, {$skipped} skipped.");
    }

    public function toggle(Request $request, CardBase $base): RedirectResponse
    {
        $this->authorizeBase($base, $request->user());
        $base->update(['is_active' => !$base->is_active]);

        return back()->with('success', 'Base ' . ($base->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function destroy(Request $request, CardBase $base): RedirectResponse
    {
        $this->authorizeBase($base, $request->user());

        if ($base->sold_count > 0) {
            return back()->withErrors(['error' => 'Cannot delete a base that has sold records.']);
        }

        $base->delete();

        return redirect()->route('vendor.cards.index')
            ->with('success', 'Base deleted.');
    }

    /**
     * Parse a pipe-delimited card CSV (with or without header row) and bulk-insert.
     * Returns [inserted_count, skipped_count].
     *
     * Expected positional format (pipe-separated):
     *   card_number|exp_month|exp_year|cvv|cardholder_name|address|city|state|zip|country|email|phone
     */
    private function parseAndInsert($handle, CardBase $base, float $priceUsd): array
    {
        $records   = [];
        $skipped   = 0;
        $inserted  = 0;
        $batchSize = 500;
        $now       = now()->toDateTimeString();
        $firstRow  = true;

        // Named-column map — populated if header row detected
        $colIndex  = null;

        while (($row = fgetcsv($handle, 0, '|', '"', '\\')) !== false) {
            // Pad short rows
            while (count($row) < 12) {
                $row[] = '';
            }

            if ($firstRow) {
                $firstRow = false;
                // If first field looks like a header (not purely digits), treat as named header
                if (!ctype_digit(preg_replace('/\s+/', '', $row[0]))) {
                    $normalized = array_map(fn($h) => strtolower(trim($h)), $row);
                    $colIndex   = array_flip($normalized);
                    continue; // skip header row
                }
            }

            // Resolve field values — by name if header present, else by position
            $get = function (string $name, int $pos) use ($row, $colIndex): string {
                if ($colIndex !== null && isset($colIndex[$name])) {
                    return trim($row[$colIndex[$name]] ?? '');
                }
                return trim($row[$pos] ?? '');
            };

            $cardNumber     = $get('card_number',      0);
            $expMonth       = $get('exp_month',        1);
            $expYear        = $get('exp_year',         2);
            $cvv            = $get('cvv',              3);
            $cardholderName = $get('cardholder_name',  4);

            if ($cardNumber === '' || $expMonth === '' || $expYear === '' || $cvv === '' || $cardholderName === '') {
                $skipped++;
                continue;
            }

            $records[] = [
                'base_id'          => $base->id,
                'vendor_id'        => $base->vendor_id,
                'card_number'      => $cardNumber,
                'exp_month'        => $expMonth,
                'exp_year'         => $expYear,
                'cvv'              => $cvv,
                'cardholder_name'  => $cardholderName,
                'address'          => $get('address', 5) ?: null,
                'city'             => $get('city',    6) ?: null,
                'state'            => $get('state',   7) ?: null,
                'zip'              => $get('zip',     8) ?: null,
                'country'          => $get('country', 9) ?: null,
                'email'            => $get('email',  10) ?: null,
                'phone'            => $get('phone',  11) ?: null,
                'price_usd'        => $priceUsd,
                'status'           => 'available',
                'created_at'       => $now,
                'updated_at'       => $now,
            ];

            if (count($records) >= $batchSize) {
                Card::insert($records);
                $inserted += count($records);
                $records   = [];
            }
        }

        if (!empty($records)) {
            Card::insert($records);
            $inserted += count($records);
        }

        return [$inserted, $skipped];
    }

    private function authorizeBase(CardBase $base, $user): void
    {
        if ($base->vendor_id !== $user->id) {
            abort(403);
        }
    }
}
