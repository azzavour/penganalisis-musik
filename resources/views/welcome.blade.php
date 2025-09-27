<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Penganalisis Musik</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="musicManager()"
         x-init="fetchData()">

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Data Musik</h1>
            <div>
                <input type="file" @change="handleFileUpload" accept=".xlsx" class="hidden" x-ref="fileInput">
                <button @click="$refs.fileInput.click()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg shadow-md transition duration-300 ease-in-out">
                    Tambah Data
                </button>
            </div>
        </div>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <template x-for="column in columns" :key="column.key">
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center justify-between">
                                        <span x-text="column.label"></span>
                                        <div class="flex flex-col">
                                           <svg @click="sortBy(column.key, 'asc')" class="w-3 h-3 cursor-pointer" fill="currentColor" viewBox="0 0 20 20"><path d="M10 3l-5 5h10l-5-5z"/></svg>
                                           <svg @click="sortBy(column.key, 'desc')" class="w-3 h-3 cursor-pointer" fill="currentColor" viewBox="0 0 20 20"><path d="M10 17l5-5H5l5 5z"/></svg>
                                        </div>
                                    </div>
                                    <input type="text" x-model="search[column.key]" @input.debounce.500ms="searchData" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm text-sm" :placeholder="'Cari ' + column.label + '...'">
                                </th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" x-show="!loading">
                        <template x-for="track in tracks" :key="track.id">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="track.trackName"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="track.artistName"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="track.primaryGenreName"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="track.releaseDate"></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="track.trackPrice"></td>
                            </tr>
                        </template>
                         <tr x-show="tracks.length === 0">
                            <td :colspan="columns.length" class="text-center py-4">Tidak ada data ditemukan.</td>
                        </tr>
                    </tbody>
                </table>
                <div x-show="loading" class="text-center p-4">Memuat data...</div>
            </div>

            <div class="px-6 py-4" x-show="!loading && pagination.last_page > 1">
                 <nav class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan <span x-text="pagination.from"></span> sampai <span x-text="pagination.to"></span> dari <span x-text="pagination.total"></span> hasil
                    </div>
                    <div>
                        <button @click="changePage(pagination.current_page - 1)" :disabled="pagination.current_page === 1" class="px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50">
                            Sebelumnya
                        </button>
                        <button @click="changePage(pagination.current_page + 1)" :disabled="pagination.current_page === pagination.last_page" class="ml-2 px-3 py-1 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50">
                            Berikutnya
                        </button>
                    </div>
                </nav>
            </div>
        </div>

        <div x-show="showModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full" x-cloak>
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 shadow-lg rounded-md bg-white">
                <div class="mt-3 text-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900">Pratinjau Data Excel</h3>
                    <div class="mt-2 px-7 py-3">
                        <div class="overflow-auto max-h-96">
                             <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50 sticky top-0">
                                    <tr>
                                        <template x-for="header in previewHeaders">
                                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase" x-text="header"></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(row, index) in previewData" :key="index">
                                        <tr>
                                            <template x-for="header in previewHeaders">
                                                 <td class="px-4 py-2 whitespace-nowrap text-sm" x-text="row[header]"></td>
                                            </template>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="items-center px-4 py-3">
                        <button @click="uploadFile" class="px-4 py-2 bg-green-500 text-white text-base font-medium rounded-md w-full shadow-sm hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-300" x-text="uploading ? 'Mengunggah...' : 'Simpan ke Database'" :disabled="uploading"></button>
                        <button @click="showModal = false" class="mt-3 px-4 py-2 bg-gray-200 text-gray-800 text-base font-medium rounded-md w-full shadow-sm hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function musicManager() {
            return {
                tracks: [],
                loading: false,
                pagination: {},
                search: {
                    trackName: '',
                    artistName: '',
                    primaryGenreName: '',
                },
                sort: {
                    by: 'id',
                    direction: 'asc'
                },
                columns: [
                    { key: 'trackName', label: 'Nama Lagu' },
                    { key: 'artistName', label: 'Artis' },
                    { key: 'primaryGenreName', label: 'Genre' },
                    { key: 'releaseDate', label: 'Tanggal Rilis' },
                    { key: 'trackPrice', label: 'Harga' },
                ],
                showModal: false,
                previewData: [],
                previewHeaders: [],
                fileToUpload: null,
                uploading: false,

                fetchData(page = 1) {
                    this.loading = true;
                    const params = new URLSearchParams({
                        page,
                        filters: JSON.stringify(this.search),
                        sort_by: this.sort.by,
                        sort_direction: this.sort.direction,
                        per_page: 10
                    });

                    axios.get(`/api/music-tracks?${params.toString()}`)
                        .then(response => {
                            this.tracks = response.data.data;
                            this.pagination = response.data.meta;
                        })
                        .catch(error => console.error(error))
                        .finally(() => this.loading = false);
                },

                searchData() {
                    this.fetchData(1);
                },

                sortBy(column, direction) {
                    this.sort.by = column;
                    this.sort.direction = direction;
                    this.fetchData(1);
                },

                changePage(page) {
                    if (page > 0 && page <= this.pagination.last_page) {
                        this.fetchData(page);
                    }
                },

                handleFileUpload(event) {
                    this.fileToUpload = event.target.files[0];
                    if (!this.fileToUpload) return;

                    const formData = new FormData();
                    formData.append('file', this.fileToUpload);

                    axios.post('/api/music-tracks/preview', formData)
                        .then(response => {
                             this.previewData = response.data.preview_data;
                             if(this.previewData.length > 0){
                               this.previewHeaders = Object.keys(this.previewData[0]);
                             }
                             this.showModal = true;
                        })
                        .catch(error => {
                             alert('Gagal memuat pratinjau: ' + error.response.data.message);
                        });
                },

                uploadFile() {
                    this.uploading = true;
                    const formData = new FormData();
                    formData.append('file', this.fileToUpload);

                     axios.post('/api/music-tracks/upload', formData)
                        .then(response => {
                            alert('Data berhasil diunggah!');
                            this.showModal = false;
                            this.fetchData(1); // Refresh data
                        })
                        .catch(error => {
                            alert('Gagal mengunggah file: ' + error.response.data.message);
                        })
                        .finally(() => {
                            this.uploading = false;
                            this.$refs.fileInput.value = '';
                        });
                }
            }
        }
    </script>
</body>
</html>