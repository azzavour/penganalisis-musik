<?php

namespace App\Imports;

use App\Models\MusicTrack;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class MusicTrackImport implements ToModel, WithHeadingRow, WithUpserts, WithProgressBar, WithChunkReading
{
    use Importable;

    /**
     * Metode ini memetakan setiap baris dari CSV ke model MusicTrack.
     * WithHeadingRow memungkinkan kita memanggil kolom berdasarkan namanya, misal $row['artistName'].
     *
     * @param array $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Pastikan kolom esensial tidak kosong untuk menghindari error
        if (empty($row['trackid']) || empty($row['artistid'])) {
            return null;
        }

        return new MusicTrack([
            'trackId'         => $row['trackid'], // Nama kolom di CSV adalah 'trackid' (lowercase)
            'trackName'       => $row['trackname'],
            'artistId'        => $row['artistid'],
            'artistName'      => $row['artistname'],
            'collectionName'  => $row['collectionname'],
            'primaryGenreName'=> $row['primarygenrename'],
            'releaseDate'     => Carbon::parse($row['releasedate']),
            'trackPrice'      => !empty($row['trackprice']) ? $row['trackprice'] : null,
            'collectionPrice' => !empty($row['collectionprice']) ? $row['collectionprice'] : null,
            'country'         => $row['country'],
            'currency'        => $row['currency'],
        ]);
    }

    /**
     * Metode ini digunakan oleh WithUpserts untuk mencari data yang sudah ada.
     * Jika trackId sudah ada di database, data akan di-update. Jika tidak, data baru akan dibuat.
     * Ini mencegah duplikasi data jika Anda menjalankan impor lebih dari sekali.
     *
     * @return string|array
     */
    public function uniqueBy()
    {
        return 'trackId';
    }

    /**
     * Menentukan ukuran chunk untuk dibaca dari file CSV.
     * Ini sangat penting untuk file besar agar tidak kehabisan memori.
     *
     * @return int
     */
    public function chunkSize(): int
    {
        return 500; // Proses 500 baris dalam satu waktu
    }
}