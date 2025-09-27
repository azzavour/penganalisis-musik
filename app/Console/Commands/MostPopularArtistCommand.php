<?php

namespace App\Console\Commands;

use App\Models\MusicTrack;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MostPopularArtistCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'music:most-popular-artist {--limit=10 : Jumlah artis terpopuler yang ingin ditampilkan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menampilkan artis paling populer berdasarkan jumlah lagu di database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $this->info("Menampilkan {$limit} artis terpopuler...");

        // Query untuk menghitung jumlah lagu per artis
        $popularArtists = MusicTrack::query()
            ->select('artistName', DB::raw('COUNT(*) as total_lagu'))
            ->groupBy('artistName')
            ->orderByDesc('total_lagu')
            ->limit($limit)
            ->get();

        if ($popularArtists->isEmpty()) {
            $this->warn('Tidak ada data artis untuk dianalisis.');
            return 0;
        }

        // Siapkan data untuk ditampilkan dalam tabel
        $tableData = [];
        foreach ($popularArtists as $index => $artist) {
            $tableData[] = [
                'Peringkat' => $index + 1,
                'Nama Artis' => $artist->artistName,
                'Jumlah Lagu' => $artist->total_lagu,
            ];
        }

        // Tampilkan hasil dalam bentuk tabel
        $headers = ['Peringkat', 'Nama Artis', 'Jumlah Lagu'];
        $this->table($headers, $tableData);

        return 0;
    }
}