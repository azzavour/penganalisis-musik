<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MusicTrack;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;

class MusicManagerController extends Controller
{
    /**
     * Tampilkan halaman utama music manager
     */
    public function index()
    {
        $stats = [
            'total_tracks' => MusicTrack::count(),
            'total_artists' => MusicTrack::distinct('artistName')->count(),
            'total_genres' => MusicTrack::distinct('primaryGenreName')->count(),
        ];

        return view('music-manager.index', compact('stats'));
    }

    /**
     * API untuk mendapatkan data music dengan pagination dan filter
     */
    public function getData(Request $request): JsonResponse
    {
        $query = MusicTrack::query();

        // Apply filters
        if ($request->filled('trackName')) {
            $query->where('trackName', 'ILIKE', '%' . $request->trackName . '%');
        }
        if ($request->filled('artistName')) {
            $query->where('artistName', 'ILIKE', '%' . $request->artistName . '%');
        }
        if ($request->filled('primaryGenreName')) {
            $query->where('primaryGenreName', 'ILIKE', '%' . $request->primaryGenreName . '%');
        }
        if ($request->filled('country')) {
            $query->where('country', 'ILIKE', '%' . $request->country . '%');
        }

        // Apply sorting
        $sortBy = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_direction', 'asc');
        
        $allowedSortFields = ['id', 'trackName', 'artistName', 'primaryGenreName', 'releaseDate', 'trackPrice', 'country'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Pagination
        $perPage = min($request->get('per_page', 10), 50);
        $data = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $data->items(),
            'meta' => [
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
            ]
        ]);
    }

    /**
     * Preview data dari Excel file
     */
    public function previewUpload(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240'
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('temp');
            $fullPath = storage_path('app/' . $path);

            // Read Excel file
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
            $spreadsheet = $reader->load($fullPath);
            $worksheet = $spreadsheet->getActiveSheet();
            $data = $worksheet->toArray();

            // Get header row and data rows (limit to 50 for preview)
            $headers = array_shift($data);
            $previewData = array_slice($data, 0, 50);
            
            // Convert to associative array
            $processedData = [];
            foreach ($previewData as $row) {
                $processedData[] = array_combine($headers, $row);
            }

            // Clean up
            unlink($fullPath);

            return response()->json([
                'success' => true,
                'preview_data' => $processedData,
                'total_rows' => count($data) + 1, // +1 for header
                'headers' => $headers
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error reading file: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Upload dan save data ke database
     */
    public function uploadData(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240'
        ]);

        try {
            $file = $request->file('file');
            $path = $file->store('temp');
            $fullPath = storage_path('app/' . $path);

            // Use existing import class
            $import = new \App\Imports\AppleMusicImport;
            \Maatwebsite\Excel\Facades\Excel::import($import, $fullPath);

            // Clean up
            unlink($fullPath);

            return response()->json([
                'success' => true,
                'message' => 'Data uploaded successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 400);
        }
    }
}