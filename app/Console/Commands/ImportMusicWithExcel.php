<?php

namespace App\Console\Commands;

use App\Imports\MusicTrackImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportMusicWithExcel extends Command
{
    /**
     * Nama dan signature dari perintah konsol.
     */
    protected $signature = 'app:import-excel {path}';

    /**
     * Deskripsi dari perintah konsol.
     */
    protected $description = 'Impor data musik dari file CSV menggunakan Laravel Excel';

    /**
     * Menjalankan perintah konsol.
     */
    public function handle()
    {
        $path = $this->argument('path');

        if (!file_exists($path)) {
            $this->error("File tidak ditemukan di lokasi: {$path}");
            return Command::FAILURE;
        }

        $this->info("Memulai impor data dari {$path} menggunakan Laravel Excel...");
        
        try {
            // Memanggil kelas import.
            // WithProgressBar akan secara otomatis menampilkan progress bar di konsol.
            Excel::import(new MusicTrackImport, $path);
            
            $this->info("\nImpor data berhasil diselesaikan.");

        } catch (Throwable $e) {
            $this->error("\nTerjadi kesalahan saat impor: " . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}