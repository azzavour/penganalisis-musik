<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MusicTrack;
use App\Imports\AppleMusicImport;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Exception;

class MusicTrackController extends Controller
{
    /**
     * Mengambil data musik dengan pagination, search, dan sorting
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MusicTrack::query();

            // Handle search filters
            $filters = $request->get('filters', []);
            if (!empty($filters)) {
                foreach ($filters as $column => $value) {
                    if (!empty($value) && in_array($column, [
                        'trackName', 'artistName', 'primaryGenreName', 
                        'country', 'releaseDate', 'collectionName'
                    ])) {
                        $query->where($column, 'ILIKE', "%{$value}%");
                    }
                }
            }

            // Handle sorting
            $sortBy = $request->get('sort_by', 'id');
            $sortDirection = $request->get('sort_direction', 'asc');
            
            $allowedSortFields = [
                'id', 'trackName', 'artistName', 'primaryGenreName', 
                'releaseDate', 'trackPrice', 'country'
            ];
            
            if (in_array($sortBy, $allowedSortFields)) {
                $query->orderBy($sortBy, $sortDirection);
            }

            // Handle pagination
            $perPage = $request->get('per_page', 10);
            $perPage = min($perPage, 100); // Limit max items per page
            
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

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload dan import data dari file Excel
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:10240', // Max 10MB
            ]);

            $file = $request->file('file');
            
            // Store file temporarily
            $path = $file->store('temp');
            $fullPath = storage_path('app/' . $path);

            // Import data
            $import = new AppleMusicImport;
            Excel::import($import, $fullPath);

            // Delete temporary file
            unlink($fullPath);

            return response()->json([
                'success' => true,
                'message' => 'Data uploaded successfully!'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Preview data dari file Excel sebelum diupload
     */
    public function preview(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls|max:10240',
            ]);

            $file = $request->file('file');
            
            // Store file temporarily
            $path = $file->store('temp');
            $fullPath = storage_path('app/' . $path);

            // Read Excel file untuk preview
            $data = Excel::toArray(new AppleMusicImport, $fullPath);
            
            // Get first sheet data (limit to first 50 rows for preview)
            $previewData = array_slice($data[0], 0, 50);

            // Delete temporary file
            unlink($fullPath);

            return response()->json([
                'success' => true,
                'preview_data' => $previewData,
                'total_rows' => count($data[0])
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Preview failed: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * Get statistics data
     */
    public function stats(): JsonResponse
    {
        try {
            $totalTracks = MusicTrack::count();
            $totalArtists = MusicTrack::distinct('artistName')->count();
            $totalGenres = MusicTrack::distinct('primaryGenreName')->count();
            
            $topGenres = MusicTrack::select('primaryGenreName')
                ->selectRaw('COUNT(*) as count')
                ->groupBy('primaryGenreName')
                ->orderByDesc('count')
                ->limit(5)
                ->get();

            return response()->json([
                'success' => true,
                'stats' => [
                    'total_tracks' => $totalTracks,
                    'total_artists' => $totalArtists,
                    'total_genres' => $totalGenres,
                    'top_genres' => $topGenres
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching stats: ' . $e->getMessage()
            ], 500);
        }
    }
} 