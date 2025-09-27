<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MusicTrack;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class MusicManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    // Ambil data statistik
    $stats = [
        'total_tracks' => MusicTrack::count(),
        'total_artists' => MusicTrack::distinct('artistName')->count(),
        'total_genres' => MusicTrack::distinct('primaryGenreName')->count(),
    ];

    // Ambil daftar genre unik untuk dropdown
    $genres = MusicTrack::distinct()->orderBy('primaryGenreName')->pluck('primaryGenreName');

    $query = MusicTrack::query();

    // LOGIKA PENCARIAN YANG DIPERBAIKI
    if ($request->has('search')) {
        $searchTerms = $request->input('search');

        foreach ($searchTerms as $key => $value) {
            if (!empty($value)) {
                switch ($key) {
                    case 'primaryGenreName':
                        $query->where('primaryGenreName', $value);
                        break;
                    case 'releaseDate':
                        $query->whereDate('releaseDate', $value);
                        break;
                    case 'price_min':
                        $query->where('trackPrice', '>=', $value);
                        break;
                    case 'price_max':
                        $query->where('trackPrice', '<=', $value);
                        break;
                    // FIX: Tambahkan case ini untuk menangani pencarian harga secara spesifik
                    case 'trackPrice':
                        $query->where('trackPrice', $value);
                        break;
                    default:
                        // Pencarian teks yang tidak case-sensitive untuk kolom lainnya
                        $query->whereRaw('LOWER("'.$key.'") LIKE ?', ['%'.strtolower($value).'%']);
                        break;
                }
            }
        }
    }

    // Handle pengurutan
    if ($request->has('sort_by')) {
        $direction = $request->input('sort_direction', 'asc');
        $query->orderBy($request->input('sort_by'), $direction);
    } else {
        $query->latest('id');
    }

    // Paginasi
    $musicData = $query->paginate(10)->withQueryString();

    // Kirim SEMUA data yang dibutuhkan ke view
    return view('music-manager.index', compact('stats', 'musicData', 'genres'));
}
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {    
        return view('music-manager.create');
    }

      public function export(Request $request)
    {
        // Ambil ID yang dipilih dari request, jika ada
        $selectedIds = $request->input('selected_ids');

        // Tentukan nama file
        $fileName = 'music-tracks-' . now()->format('Y-m-d') . '.xlsx';

        // Panggil class export dengan ID yang dipilih (atau null jika tidak ada)
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\MusicTracksExport($selectedIds), $fileName);
    }
}