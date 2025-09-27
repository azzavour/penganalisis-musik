@extends('layouts.app')

@section('title', 'Music Manager')

@section('content')
<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Music Data Manager</h1>
            <div class="flex items-center space-x-4 text-sm text-gray-500">
                <span>{{ $stats['total_tracks'] }} tracks</span>
                <span>|</span>
                <span>{{ $stats['total_artists'] }} artists</span>
                <span>|</span>
                <span>{{ $stats['total_genres'] }} genres</span>
            </div>
        </div>

        <div class="mb-6">
             <button class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 cursor-pointer">
                Add New Data
            </button>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full data-table">
                    <thead>
                        <tr>
                            <th>Track Name</th>
                            <th>Artist Name</th>
                            <th>Genre</th>
                            <th>Release Date</th>
                            <th>Price</th>
                            <th>Country</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($musicData as $track)
                            <tr>
                                <td>{{ $track->trackName }}</td>
                                <td class="text-gray-500">{{ $track->artistName }}</td>
                                <td class="text-gray-500">{{ $track->primaryGenreName }}</td>
                                <td class="text-gray-500">{{ $track->releaseDate->format('d M Y') }}</td>
                                <td class="text-gray-500">${{ number_format($track->trackPrice, 2) }}</td>
                                <td class="text-gray-500">{{ $track->country }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-12 text-gray-500">
                                    No data found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-6">
            {{ $musicData->links() }}
        </div>
    </div>
</div>
@endsection