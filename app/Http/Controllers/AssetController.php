<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Asset;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Validator;
use Excel;
use PDF;
use App\Exports\AssetsExport;

class AssetController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth'); // ensure users are logged in
    // }

    // List with filters & pagination
    public function index(Request $request)
    {
        $user = session('user');
        $companyId = $user->company_id ?? null;

        $query = Asset::query();
        if ($companyId) $query->where('company_id', $companyId);

        // Filters
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('year')) {
            $query->where('year', $request->year);
        } else {
            if ($request->filled('year_from')) $query->where('year', '>=', $request->year_from);
            if ($request->filled('year_to')) $query->where('year', '<=', $request->year_to);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } else {
            if ($request->filled('date_from')) $query->where('created_at', '>=', $request->date_from . ' 00:00:00');
            if ($request->filled('date_to')) $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $assets = $query->orderBy('id', 'desc')->paginate(12);

        return view('assets.index', compact('assets'));
    }

    // Show create form
    public function create()
    {
        return view('assets.create');
    }

    // Store asset (multipart form or base64 photos)
    public function store(Request $request)
    {
        $user = session('user');

        $rules = [
            'name' => 'required|string|max:191',
            'category' => 'nullable|string|max:191',
            'condition' => 'nullable|string|max:100',
            'year' => 'nullable|integer',
            'description' => 'nullable|string',
            'photos_files.*' => 'nullable|image|max:5120' // each file max 5MB
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $photos = [];
        $thumbnail = null;
        $companyId = $user->company_id ?? '0';

        // 1) Handle uploaded files (photos_files[])
        if ($request->hasFile('photos_files')) {
            foreach ($request->file('photos_files') as $file) {
                if (!$file->isValid()) continue;
                $ext = $file->getClientOriginalExtension();
                $filename = Str::random(20) . '.' . $ext;
                $path = "asset_photos/{$companyId}/" . $filename;
                Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
                $url = asset('storage/' . $path);
                $photos[] = $url;
                if (!$thumbnail) $thumbnail = $url;
            }
        }

        // 2) Handle base64 photos array (photos[] as base64 strings)
        if ($request->filled('photos') && is_array($request->photos)) {
            foreach ($request->photos as $b64) {
                if (!preg_match('/^data:image\/(\w+);base64,/', $b64, $type)) continue;
                $b64data = substr($b64, strpos($b64, ',') + 1);
                $ext = strtolower($type[1]);
                $b64data = str_replace(' ', '+', $b64data);
                $data = base64_decode($b64data);
                if ($data === false) continue;
                $filename = Str::random(20) . '.' . $ext;
                $path = "asset_photos/{$companyId}/" . $filename;
                Storage::disk('public')->put($path, $data);
                $url = asset('storage/' . $path);
                $photos[] = $url;
                if (!$thumbnail) $thumbnail = $url;
            }
        }

        // 3) Create location JSON if lat/lng provided
        $location = null;
        if ($request->filled('lat') && $request->filled('lng')) {
            $location = ['lat' => (float)$request->lat, 'lng' => (float)$request->lng];
        } elseif ($request->filled('location')) {
            // accept JSON string/object
            $loc = $request->input('location');
            if (is_string($loc)) {
                $decoded = json_decode($loc, true);
                if (json_last_error() === JSON_ERROR_NONE) $location = $decoded;
            } elseif (is_array($loc)) {
                $location = $loc;
            }
        }

        $asset = Asset::create([
            'company_id' => $companyId,
            'name' => $request->name,
            'category' => $request->category,
            'condition' => $request->condition ?? 'Good',
            'year' => $request->year,
            'description' => $request->description,
            'photo' => $thumbnail,
            'photos' => $photos ?: null,
            'location' => $location
        ]);

        return redirect()->route('assets.show', $asset->id)->with('success', 'Asset created');
    }

    // Show detail page
    public function show($id)
    {
        $asset = Asset::findOrFail($id);
        return view('assets.show', compact('asset'));
    }

    // Show edit form
    public function edit($id)
    {
        $asset = Asset::findOrFail($id);
        return view('assets.edit', compact('asset'));
    }

    // Update (append new photos, keep existing, delete optional)
    public function update(Request $request, $id)
    {
        $asset = Asset::findOrFail($id);
        $user = session('user');
        $companyId = $user->company_id ?? '0';

        $rules = [
            'name' => 'required|string|max:191',
            'category' => 'nullable|string|max:191',
            'condition' => 'nullable|string|max:100',
            'year' => 'nullable|integer',
            'description' => 'nullable|string',
            'photos_files.*' => 'nullable|image|max:5120',
            'delete_photos' => 'nullable|array'
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Remove selected existing photos (if any)
        $existing = $asset->photos ?: [];
        $toDelete = $request->input('delete_photos', []);
        if (!empty($toDelete)) {
            foreach ($toDelete as $url) {
                // remove from array
                $existing = array_values(array_filter($existing, function ($u) use ($url) {
                    return $u !== $url;
                }));
                // Also attempt to delete the underlying file if it's in storage/app/public
                $prefix = asset('storage') . '/';
                if (strpos($url, $prefix) === 0) {
                    $relative = substr($url, strlen($prefix));
                    if (Storage::disk('public')->exists($relative)) {
                        Storage::disk('public')->delete($relative);
                    }
                }
            }
        }

        // Append new files
        if ($request->hasFile('photos_files')) {
            foreach ($request->file('photos_files') as $file) {
                if (!$file->isValid()) continue;
                $ext = $file->getClientOriginalExtension();
                $filename = Str::random(20) . '.' . $ext;
                $path = "asset_photos/{$companyId}/" . $filename;
                Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
                $url = asset('storage/' . $path);
                $existing[] = $url;
            }
        }

        // Append new base64 photos
        if ($request->filled('photos') && is_array($request->photos)) {
            foreach ($request->photos as $b64) {
                if (!preg_match('/^data:image\/(\w+);base64,/', $b64, $type)) continue;
                $b64data = substr($b64, strpos($b64, ',') + 1);
                $ext = strtolower($type[1]);
                $b64data = str_replace(' ', '+', $b64data);
                $data = base64_decode($b64data);
                if ($data === false) continue;
                $filename = Str::random(20) . '.' . $ext;
                $path = "asset_photos/{$companyId}/" . $filename;
                Storage::disk('public')->put($path, $data);
                $url = asset('storage/' . $path);
                $existing[] = $url;
            }
        }

        // Update thumbnail: either keep current or set to first of existing
        $thumbnail = $asset->photo;
        if (!empty($existing)) $thumbnail = $existing[0];

        // Update location if provided
        $location = $asset->location;
        if ($request->filled('lat') && $request->filled('lng')) {
            $location = ['lat' => (float)$request->lat, 'lng' => (float)$request->lng];
        } elseif ($request->filled('location')) {
            $loc = $request->input('location');
            if (is_string($loc)) {
                $decoded = json_decode($loc, true);
                if (json_last_error() === JSON_ERROR_NONE) $location = $decoded;
            } elseif (is_array($loc)) {
                $location = $loc;
            }
        }

        $asset->update([
            'name' => $request->name,
            'category' => $request->category,
            'condition' => $request->condition,
            'year' => $request->year,
            'description' => $request->description,
            'photos' => !empty($existing) ? array_values($existing) : null,
            'photo' => $thumbnail,
            'location' => $location
        ]);

        return redirect()->route('assets.show', $asset->id)->with('success', 'Asset updated');
    }

    // Delete asset & photos
    public function destroy($id)
    {
        $asset = Asset::findOrFail($id);
        // delete photos from disk if exist
        $prefix = asset('storage') . '/';
        foreach ($asset->photos ?? [] as $url) {
            if (strpos($url, $prefix) === 0) {
                $relative = substr($url, strlen($prefix));
                if (Storage::disk('public')->exists($relative)) {
                    Storage::disk('public')->delete($relative);
                }
            }
        }
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'Asset deleted');
    }

    public function exportExcel(Request $request)
    {
        $assets = $this->filterAssets($request);
        $company = session('company');
        $companyName = $company->name ?? 'Company';

        // Collect filter info nicely
        $filters = [
            'Category' => $request->category ?: 'All',
            'Year' => ($request->year_from && $request->year_to) ? "{$request->year_from} - {$request->year_to}" : 'All',
            'Date' => ($request->date_from && $request->date_to) ? "{$request->date_from} - {$request->date_to}" : 'All',
            'Search' => $request->search ?: 'All',
        ];

        return \Excel::download(new \App\Exports\AssetsExport($assets, $filters, $companyName), 'assets.xlsx');
    }


    public function exportPdf(Request $request)
    {
        $assets = $this->filterAssets($request);
        $company = session('company');
        $companyName = $company->name ?? 'Company';

        $filters = [
            'Category' => $request->category ?: 'All',
            'Year' => ($request->year_from && $request->year_to) ? "{$request->year_from} - {$request->year_to}" : 'All',
            'Date' => ($request->date_from && $request->date_to) ? "{$request->date_from} - {$request->date_to}" : 'All',
            'Search' => $request->search ?: 'All',
        ];

        $pdf = \PDF::loadView('assets.export-pdf', compact('assets', 'filters', 'companyName'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('assets.pdf');
    }


    /**
     * 🔹 Shared filter logic (reused for list & export)
     */
    private function filterAssets(Request $request)
    {
        $user = session('user');
        $query = Asset::query()->where('company_id', $user->company_id);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('year_from') && $request->filled('year_to')) {
            $query->whereBetween('year', [$request->year_from, $request->year_to]);
        }
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('category', 'like', "%{$request->search}%");
            });
        }

        return $query->orderBy('id', 'desc')->get();
    }
}
