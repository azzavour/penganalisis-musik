<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MusicTrack;
use Illuminate\Http\Request;

class MusicManagerController extends Controller
{
    /**
     * Tampilkan halaman utama music manager dengan data.
     */
    /**
     * Tampilkan halaman utama music manager dengan data.
     */
    public function index(Request $request)
{
    // Ambil data statistik
    $stats = [
        'total_tracks' => MusicTrack::count(),
        'total_artists' => MusicTrack::distinct('artistName')->count(),
        'total_genres' => MusicTrack::distinct('primaryGenreName')->count(),
    ];

    // Initialize the query builder here
    $query = MusicTrack::query();

    // LOGIKA BARU UNTUK SEARCH PER KOLOM
    if ($request->has('search')) {
        $searchTerms = $request->input('search');
        foreach ($searchTerms as $key => $value) {
            // Hanya proses jika ada input di search bar
            if (!empty($value)) {
                $query->where($key, 'like', '%' . $value . '%');
            }
        }
    }

    // Handle pengurutan
    if ($request->has('sort_by')) {
        $direction = $request->input('sort_direction', 'asc');
        $query->orderBy($request->input('sort_by'), $direction);
    } else {
        // Urutkan berdasarkan data terbaru jika tidak ada sorting lain
        $query->latest('id');
    }

    // Ambil data untuk tabel dengan paginasi
    $musicData = $query->paginate(10)->withQueryString();

    // Kirim data ke view
    return view('music-manager.index', compact('stats', 'musicData'));
}
        public function create()
    {   
        return view('music-manager.create');
    }
}