<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\FullzBase;
use App\Models\Fullz;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FullzController extends Controller
{
    /**
     * List all bases uploaded by this vendor.
     */
    public function index(Request $request): View
    {
        $vendor = $request->user();
        $bases  = FullzBase::where('vendor_id', $vendor->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('vendor.autoshop.index', compact('bases'));
    }

    /**
     * Show the upload form.
     */
    public function create(): View
    {
        return view('vendor.autoshop.create');
    }

    /**
     * Process the CSV upload, parse records, insert.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'      => 'required|string|max:120',
            'price_usd' => 'required|numeric|min:0.01|max:9999',
            'file'      => 'required|file|mimes:csv,txt|max:10240', // 10MB
        ]);

        $vendor = $request->user();
        $file   = $request->file('file');

        // Open file for streaming — no full-file memory load
        $handle = fopen($file->getRealPath(), 'r');
        if (!$handle) {
            return back()->withErrors(['file' => 'Could not read uploaded file.']);
        }

        // --- Read and normalize header row ---
        $rawHeader = fgetcsv($handle);
        if (!$rawHeader) {
            fclose($handle);
            return back()->withErrors(['file' => 'CSV file is empty or unreadable.']);
        }

        $header = array_map(fn($h) => strtolower(trim($h)), $rawHeader);

        $required = ['name', 'ssn', 'dob'];
        foreach ($required as $col) {
            if (!in_array($col, $header, true)) {
                fclose($handle);
                return back()->withErrors(['file' => "CSV is missing required column: {$col}"]);
            }
        }

        $colIndex = array_flip($header);

        // --- Parse rows ---
        $records  = [];
        $skipped  = 0;
        $inserted = 0;
        $batchSize = 500;

        // Create the base record first so we have a base_id
        $base = DB::transaction(function () use ($vendor, $request) {
            return FullzBase::create([
                'vendor_id'       => $vendor->id,
                'name'            => trim($request->input('name')),
                'price_usd'       => $request->input('price_usd'),
                'record_count'    => 0,
                'available_count' => 0,
                'sold_count'      => 0,
                'is_active'       => true,
            ]);
        });

        $now = now()->toDateTimeString();

        while (($row = fgetcsv($handle)) !== false) {
            // Pad short rows
            while (count($row) < count($header)) {
                $row[] = '';
            }

            $get = fn(string $col): string => isset($colIndex[$col])
                ? trim($row[$colIndex[$col]] ?? '')
                : '';

            $name = $get('name');
            $ssn  = $get('ssn');
            $dob  = $get('dob');

            // Skip rows missing the three required fields
            if ($name === '' || $ssn === '' || $dob === '') {
                $skipped++;
                continue;
            }

            $records[] = [
                'base_id'    => $base->id,
                'vendor_id'  => $vendor->id,
                'name'       => $name,
                'address'    => $get('address')  ?: null,
                'city'       => $get('city')     ?: null,
                'state'      => $get('state')    ?: null,
                'zip'        => $get('zip')      ?: null,
                'phone_no'   => $get('phone_no') ?: null,
                'gender'     => $get('gender')   ?: null,
                'ssn'        => $ssn,
                'dob'        => $dob,
                'status'     => 'available',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (count($records) >= $batchSize) {
                Fullz::insert($records);
                $inserted += count($records);
                $records = [];
            }
        }

        fclose($handle);

        if (!empty($records)) {
            Fullz::insert($records);
            $inserted += count($records);
        }

        if ($inserted === 0) {
            // Nothing usable — delete the empty base
            $base->delete();
            return back()->withErrors(['file' => "No valid records found. {$skipped} row(s) skipped (missing name, ssn, or dob)."]);
        }

        // Update base counts
        $base->update([
            'record_count'    => $inserted,
            'available_count' => $inserted,
        ]);

        Log::info("Autoshop base uploaded", [
            'vendor_id' => $vendor->id,
            'base_id'   => $base->id,
            'inserted'  => $inserted,
            'skipped'   => $skipped,
        ]);

        return redirect()
            ->route('vendor.autoshop.show', $base)
            ->with('success', "Base created: {$inserted} record(s) imported, {$skipped} skipped.");
    }

    /**
     * Show all records in a base (vendor's view — sees sold records too).
     */
    public function show(Request $request, FullzBase $base): View
    {
        $this->authorizeBase($base, $request->user());

        $records = $base->records()
            ->orderBy('status')          // available first
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('vendor.autoshop.show', compact('base', 'records'));
    }

    /**
     * Toggle a base active/inactive.
     */
    public function toggle(Request $request, FullzBase $base): RedirectResponse
    {
        $this->authorizeBase($base, $request->user());
        $base->update(['is_active' => !$base->is_active]);

        return back()->with('success', 'Base ' . ($base->is_active ? 'activated' : 'deactivated') . '.');
    }

    /**
     * Delete a base and all its records (only if no records have been sold).
     */
    public function destroy(Request $request, FullzBase $base): RedirectResponse
    {
        $this->authorizeBase($base, $request->user());

        if ($base->sold_count > 0) {
            return back()->withErrors(['error' => 'Cannot delete a base that has sold records.']);
        }

        $base->delete();

        return redirect()->route('vendor.autoshop.index')
            ->with('success', 'Base deleted.');
    }

    private function authorizeBase(FullzBase $base, $user): void
    {
        if ($base->vendor_id !== $user->id) {
            abort(403);
        }
    }
}
