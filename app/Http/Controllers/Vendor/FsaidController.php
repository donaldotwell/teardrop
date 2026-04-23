<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\FsaidBase;
use App\Models\Fsaid;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FsaidController extends Controller
{
    public function index(Request $request): View
    {
        $bases = FsaidBase::where('vendor_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('vendor.fsaid.index', compact('bases'));
    }

    public function create(): View
    {
        return view('vendor.fsaid.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'      => 'required|string|max:120',
            'price_usd' => 'required|numeric|min:0.01|max:9999',
            'file'      => 'required|file|mimes:csv,txt|max:20480', // 20 MB
        ]);

        $vendor = $request->user();
        $handle = fopen($request->file('file')->getRealPath(), 'r');

        if (!$handle) {
            return back()->withErrors(['file' => 'Could not read uploaded file.']);
        }

        $rawHeader = fgetcsv($handle);
        if (!$rawHeader) {
            fclose($handle);
            return back()->withErrors(['file' => 'CSV file is empty or unreadable.']);
        }

        // Normalize header: lowercase, strip underscores/spaces for flexible matching
        $header    = array_map(fn($h) => strtolower(trim(str_replace([' ', '_'], '', $h))), $rawHeader);
        $rawHeader = array_map(fn($h) => strtolower(trim($h)), $rawHeader);

        // Map flexible CSV column names → DB column
        $map = [
            'firstname'         => 'first_name',
            'lastname'          => 'last_name',
            'email'             => 'email',
            'emailpass'         => 'email_pass',
            'fauname'           => 'fa_uname',
            'fapass'            => 'fa_pass',
            'backupcode'        => 'backup_code',
            'securityqa'        => 'security_qa',
            'state'             => 'state',
            'gender'            => 'gender',
            'zip'               => 'zip',
            'dob'               => 'dob',
            'address'           => 'address',
            'description'       => 'description',
            'ssn'               => 'ssn',
            'cs'                => 'cs',
            'city'              => 'city',
            'country'           => 'country',
            'enrollment'        => 'enrollment',
            'enrollmentdetails' => 'enrollment_details',
            'twofa'             => 'two_fa',
            'level'             => 'level',
            'programs'          => 'programs',
        ];

        // Build column-index lookup using normalized names
        $colIndex = [];
        foreach ($header as $i => $norm) {
            if (isset($map[$norm])) {
                $colIndex[$map[$norm]] = $i;
            }
        }
        // Also try raw header (already lowercased) for exact column names like 'first_name'
        foreach ($rawHeader as $i => $raw) {
            $rawNorm = str_replace('_', '', $raw);
            if (isset($map[$rawNorm]) && !isset($colIndex[$map[$rawNorm]])) {
                $colIndex[$map[$rawNorm]] = $i;
            }
        }

        // Must have at least first_name, last_name, email, email_pass
        foreach (['first_name', 'last_name', 'email', 'email_pass'] as $req) {
            if (!isset($colIndex[$req])) {
                fclose($handle);
                $csvName = array_search($req, $map) ?? $req;
                return back()->withErrors(['file' => "CSV is missing required column: {$csvName}"]);
            }
        }

        $base = DB::transaction(fn() => FsaidBase::create([
            'vendor_id'       => $vendor->id,
            'name'            => trim($request->input('name')),
            'price_usd'       => $request->input('price_usd'),
            'record_count'    => 0,
            'available_count' => 0,
            'sold_count'      => 0,
            'is_active'       => true,
        ]));

        $get = fn(array $row, string $col): ?string =>
            isset($colIndex[$col]) ? (trim($row[$colIndex[$col]] ?? '') ?: null) : null;

        $records   = [];
        $skipped   = 0;
        $inserted  = 0;
        $batchSize = 500;
        $now       = now()->toDateTimeString();

        while (($row = fgetcsv($handle)) !== false) {
            while (count($row) < count($header)) {
                $row[] = '';
            }

            $firstName = $get($row, 'first_name');
            $lastName  = $get($row, 'last_name');
            $email     = $get($row, 'email');
            $emailPass = $get($row, 'email_pass');

            if (!$firstName || !$lastName || !$email || !$emailPass) {
                $skipped++;
                continue;
            }

            $records[] = [
                'uuid'               => (string) \Illuminate\Support\Str::uuid(),
                'base_id'            => $base->id,
                'vendor_id'          => $vendor->id,
                'first_name'         => $firstName,
                'last_name'          => $lastName,
                'dob'                => $get($row, 'dob'),
                'ssn'                => $get($row, 'ssn'),
                'gender'             => $get($row, 'gender'),
                'address'            => $get($row, 'address'),
                'city'               => $get($row, 'city'),
                'state'              => $get($row, 'state'),
                'zip'                => $get($row, 'zip'),
                'country'            => $get($row, 'country'),
                'cs'                 => $get($row, 'cs'),
                'description'        => $get($row, 'description'),
                'email'              => $email,
                'email_pass'         => $emailPass,
                'fa_uname'           => $get($row, 'fa_uname'),
                'fa_pass'            => $get($row, 'fa_pass'),
                'backup_code'        => $get($row, 'backup_code'),
                'security_qa'        => $get($row, 'security_qa'),
                'two_fa'             => $get($row, 'two_fa'),
                'level'              => $get($row, 'level'),
                'programs'           => $get($row, 'programs'),
                'enrollment'         => $get($row, 'enrollment'),
                'enrollment_details' => $get($row, 'enrollment_details'),
                'status'             => 'available',
                'created_at'         => $now,
                'updated_at'         => $now,
            ];

            if (count($records) >= $batchSize) {
                Fsaid::insert($records);
                $inserted += count($records);
                $records   = [];
            }
        }

        fclose($handle);

        if (!empty($records)) {
            Fsaid::insert($records);
            $inserted += count($records);
        }

        if ($inserted === 0) {
            $base->delete();
            return back()->withErrors(['file' => "No valid records found. {$skipped} row(s) skipped (missing firstName, lastName, email, or emailPass)."]);
        }

        $base->update([
            'record_count'    => $inserted,
            'available_count' => $inserted,
        ]);

        Log::info('FSAID base uploaded', [
            'vendor_id' => $vendor->id,
            'base_id'   => $base->id,
            'inserted'  => $inserted,
            'skipped'   => $skipped,
        ]);

        return redirect()
            ->route('vendor.fsaid.show', $base)
            ->with('success', "Base created: {$inserted} record(s) imported, {$skipped} skipped.");
    }

    public function show(Request $request, FsaidBase $base): View
    {
        $this->authorize($base, $request->user());

        $query = Fsaid::where('base_id', $base->id);

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($sub) use ($q) {
                $sub->where('first_name', 'like', "%{$q}%")
                    ->orWhere('last_name',  'like', "%{$q}%")
                    ->orWhere('email',      'like', "%{$q}%")
                    ->orWhere('fa_uname',   'like', "%{$q}%");
            });
        }
        if ($request->filled('status') && in_array($request->input('status'), ['available', 'sold'])) {
            $query->where('status', $request->input('status'));
        }
        if ($request->filled('state')) {
            $query->where('state', $request->input('state'));
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->input('city') . '%');
        }
        if ($request->filled('level')) {
            $query->where('level', $request->input('level'));
        }
        if ($request->filled('enrollment')) {
            $query->where('enrollment', $request->input('enrollment'));
        }
        if ($request->filled('two_fa')) {
            if ($request->input('two_fa') === 'yes') {
                $query->whereNotNull('two_fa')->where('two_fa', '!=', '');
            } else {
                $query->where(fn($q) => $q->whereNull('two_fa')->orWhere('two_fa', ''));
            }
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }
        if ($request->filled('country')) {
            $query->where('country', 'like', '%' . $request->input('country') . '%');
        }

        $sort = $request->input('sort', 'default');
        match ($sort) {
            'name'    => $query->orderBy('first_name')->orderBy('last_name'),
            'email'   => $query->orderBy('email'),
            'newest'  => $query->orderByDesc('created_at'),
            'oldest'  => $query->orderBy('created_at'),
            default   => $query->orderBy('status')->orderByDesc('created_at'),
        };

        $records = $query->paginate(50)->withQueryString();

        $states  = Fsaid::where('base_id', $base->id)->whereNotNull('state')->where('state', '!=', '')->distinct()->orderBy('state')->pluck('state');
        $genders = Fsaid::where('base_id', $base->id)->whereNotNull('gender')->where('gender', '!=', '')->distinct()->orderBy('gender')->pluck('gender');

        return view('vendor.fsaid.show', compact('base', 'records', 'states', 'genders'));
    }

    public function toggle(Request $request, FsaidBase $base): RedirectResponse
    {
        $this->authorize($base, $request->user());
        $base->update(['is_active' => !$base->is_active]);

        return back()->with('success', 'Base ' . ($base->is_active ? 'activated' : 'deactivated') . '.');
    }

    public function destroy(Request $request, FsaidBase $base): RedirectResponse
    {
        $this->authorize($base, $request->user());

        if ($base->sold_count > 0) {
            return back()->withErrors(['error' => 'Cannot delete a base that has sold records.']);
        }

        $base->delete();

        return redirect()->route('vendor.fsaid.index')->with('success', 'Base deleted.');
    }

    private function authorize(FsaidBase $base, $user): void
    {
        if ($base->vendor_id !== $user->id) {
            abort(403);
        }
    }
}
