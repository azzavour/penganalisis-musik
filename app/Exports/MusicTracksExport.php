<?php

namespace App\Exports;

use App\Models\MusicTrack;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class MusicTracksExport implements FromQuery, WithHeadings, ShouldAutoSize
{
    protected $selectedIds;

    public function __construct(array $selectedIds = null)
    {
        $this->selectedIds = $selectedIds;
    }

    /**
    * @return \Illuminate\Database\Query\Builder
    */
    public function query()
    {
        // Jika ada ID yang dipilih, hanya ekspor data tersebut
        if ($this->selectedIds) {
            return MusicTrack::query()->whereIn('id', $this->selectedIds);
        }

        // Jika tidak ada, ekspor semua data
        return MusicTrack::query();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        // Definisikan header kolom untuk file Excel
        return [
            'ID',
            'Track ID',
            'Track Name',
            'Artist ID',
            'Artist Name',
            'Album Name',
            'Genre',
            'Release Date',
            'Track Price',
            'Collection Price',
            'Country',
            'Currency',
        ];
    }
}