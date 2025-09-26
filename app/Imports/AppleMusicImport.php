<?php

namespace App\Imports;

use App\Models\MusicTrack;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithUpserts;

// Perhatikan: Tidak ada 'Importable' di sini untuk menghindari error
class AppleMusicImport implements ToModel, WithHeadingRow, WithProgressBar,WithUpserts
{
    use Importable;
    /**
     * Memetakan setiap baris dari CSV ke model.
     */
    public function model(array $row)
    {
        if (empty($row['trackid']) || empty($row['artistid'])) {
            return null;
        }

        return new MusicTrack([
            'trackId'         => $row['trackid'],
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
     * Mencegah duplikasi data berdasarkan 'trackId'.
     */
    public function uniqueBy()
    {
        return 'trackId';
    }

    /**
     * Memproses file dalam kelompok 500 baris untuk efisiensi memori.
     */
    public function chunkSize(): int
    {
        return 500;
    }
}