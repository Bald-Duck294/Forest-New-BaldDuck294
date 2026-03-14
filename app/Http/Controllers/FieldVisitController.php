<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\FieldVisit;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Validator;
use PDF;
use Excel;
use App\Exports\FieldVisitsExport;

class FieldVisitController extends Controller
{
    /**
     * Display a listing of the visits with filters.
     */
    public function index(Request $request)
    {
        $baseUrl = "https://fms.pugarch.in/public/storage/";
        $query = FieldVisit::with('user')->orderBy('created_at', 'desc');

        // ===============================
        // 🔹 Apply Filters
        // ===============================
        if ($request->filled('visitor_name')) {
            $query->where('visitor_name', 'like', '%' . $request->visitor_name . '%');
        }

        // if ($request->filled('asset_name')) {
        //     $query->whereHas('asset', function ($q) use ($request) {
        //         $q->where('name', 'like', '%' . $request->asset_name . '%');
        //     });
        // }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('visit_date', '<=', $request->date_to);
        }

        $visits = $query->paginate(10);

        // ===============================
        // 🔹 Export to Excel
        // ===============================
        if ($request->has('export') && $request->export === 'excel') {
            return Excel::download(new VisitsExport($query->get(), $request->all()), 'visits.xlsx');
        }

        // 🔹 Export to PDF
        if ($request->has('export') && $request->export === 'pdf') {
            $data = [
                'visits'  => $query->get(),
                'filters' => $request->all(),
                'company' => config('app.name'),
            ];
            $pdf = PDF::loadView('field_visits.export-pdf', $data);
            return $pdf->download('visits.pdf');
        }

        // $visits->media->each(function ($media) use ($baseUrl) {
        //     $media->url = $baseUrl . ltrim($media->path, '/');
        // });
        // dd($visits[0]);
        // ===============================
        // 🔹 Default: Show list view
        // ===============================
        return view('field_visits.index', compact('visits'));
    }

    public function create()
    {
        return view('field_visits.create');
    }

    public function store(Request $request)
    {
        $user = session('user');
        $rules = [
            'from' => 'required|string|max:191',
            'to' => 'required|string|max:191',
            'purpose' => 'required|string|max:191',
            'remark' => 'nullable|string',
            'photos_files.*' => 'nullable|image|max:5120',
        ];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $photos = [];
        if ($request->hasFile('photos_files')) {
            foreach ($request->file('photos_files') as $file) {
                if (!$file->isValid()) continue;
                $filename = Str::random(20) . '.' . $file->getClientOriginalExtension();
                $path = "field_visits/{$user->company_id}/" . $filename;
                Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));
                $photos[] = asset('storage/' . $path);
            }
        }

        // Base64 photos
        if ($request->filled('photos') && is_array($request->photos)) {
            foreach ($request->photos as $b64) {
                if (!preg_match('/^data:image\/(\w+);base64,/', $b64, $type)) continue;
                $data = base64_decode(substr($b64, strpos($b64, ',') + 1));
                $filename = Str::random(20) . '.' . $type[1];
                $path = "field_visits/{$user->company_id}/" . $filename;
                Storage::disk('public')->put($path, $data);
                $photos[] = asset('storage/' . $path);
            }
        }

        $location = null;
        if ($request->filled('lat') && $request->filled('lng')) {
            $location = ['lat' => (float)$request->lat, 'lng' => (float)$request->lng];
        }

        $visit = FieldVisit::create([
            'company_id' => $user->company_id,
            'user_id' => $user->id,
            'from' => $request->from,
            'to' => $request->to,
            'purpose' => $request->purpose,
            'remark' => $request->remark,
            'photos' => $photos,
            'location' => $location,
        ]);

        return redirect()->route('field_visits.show', $visit->id)->with('success', 'Visit saved');
    }

    public function show($id)
    {
        $baseUrl = "https://fms.pugarch.in/public/storage/";
        $visit = FieldVisit::where('id', $id)->with('user', 'media')->first();
        $visit->media->each(function ($media) use ($baseUrl) {
            $media->url = $baseUrl . ltrim($media->path, '/');
        });
        return view('field_visits.show', compact('visit'));
    }

    public function destroy($id)
    {
        $visit = FieldVisit::findOrFail($id);
        $visit->delete();
        return redirect()->route('field_visits.index')->with('success', 'Visit deleted');
    }

    // Export Excel
    public function exportExcel(Request $request)
    {
        $visits = $this->filter($request);
        $companyName = session('company')->name ?? 'Company';
        return Excel::download(new FieldVisitsExport($visits, $companyName), 'visits.xlsx');
    }

    // Export PDF
    public function exportPdf(Request $request)
    {
        $visits = $this->filter($request);
        $company = session('company');
        $pdf = PDF::loadView('field_visits.export-pdf', compact('visits', 'company'))
            ->setPaper('a4', 'landscape');
        return $pdf->download('visits.pdf');
    }

    private function filter($request)
    {
        $user = session('user');
        $query = FieldVisit::where('company_id', $user->company_id);
        if ($request->filled('purpose')) $query->where('purpose', 'like', "%{$request->purpose}%");
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }
        return $query->orderBy('id', 'desc')->get();
    }
}
