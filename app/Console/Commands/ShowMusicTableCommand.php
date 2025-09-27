<?php

namespace App\Console\Commands;

use App\Models\MusicTrack;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;

class ShowMusicTableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'music:show-all {--limit=15 : Batasi jumlah baris yang ditampilkan}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Menampilkan data musik dari database dalam bentuk tabel';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');

        $this->info("Mengambil {$limit} baris pertama dari data musik...");

        // Tentukan kolom mana saja yang ingin ditampilkan
        $kolomTampilan = [
            'id',
            'trackName',
            'artistName',
            'primaryGenreName',
            'trackPrice',
            'releaseDate',
        ];

        // Ambil data dari database, tapi hanya kolom yang kita butuhkan
        $tracks = MusicTrack::query()
            ->select($kolomTampilan) // Hanya pilih kolom yang ditentukan
            ->limit($limit)
            ->get();

        if ($tracks->isEmpty()) {
            $this->warn('Database kosong. Tidak ada data musik untuk ditampilkan.');
            return 0;
        }

        // Header tabel akan sesuai dengan kolom yang kita pilih
        $headers = $kolomTampilan;
        
        // Tampilkan data menggunakan komponen tabel dari Artisan
        $this->table($headers, $tracks->toArray());

        $total = MusicTrack::count();
        $this->info("Menampilkan {$tracks->count()} dari total {$total} data.");

        return 0;
    }
}