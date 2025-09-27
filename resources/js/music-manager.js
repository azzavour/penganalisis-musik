import Alpine from 'alpinejs';
import axios from 'axios';

// Setup axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

// Alpine.js Music Manager Component
window.musicManager = function(initialStats = {}) {
    return {
        // Data properties
        musicData: [],
        loading: false,
        currentPage: 1,
        totalPages: 1,
        totalRecords: 0,
        perPage: 10,
        
        // Filter and sort
        filters: {
            trackName: '',
            artistName: '',
            primaryGenreName: '',
            country: ''
        },
        sortConfig: {
            key: 'id',
            direction: 'asc'
        },
        
        // Upload modal
        showUploadModal: false,
        previewData: [],
        previewHeaders: [], // Tambahkan ini untuk menyimpan header
        uploadStatus: '',
        uploading: false,
        fileToUpload: null, // Ganti nama dari currentFile
        
        // Stats and notifications
        stats: initialStats,
        notification: {
            show: false,
            message: '',
            type: 'success'
        },

        // Initialize component
        async init() {
            await this.fetchData();
        },

        // Fetch music data from API
        async fetchData() {
            this.loading = true;
            try {
                const params = {
                    page: this.currentPage,
                    per_page: this.perPage,
                    sort_by: this.sortConfig.key,
                    sort_direction: this.sortConfig.direction,
                    ...this.filters
                };

                // Clean empty filters
                Object.keys(params).forEach(key => {
                    if (params[key] === '' || params[key] === null || params[key] === undefined) {
                        delete params[key];
                    }
                });
                
                // Pastikan URL-nya benar
                const response = await axios.get('/api/music-tracks', { params });
                
                this.musicData = response.data.data;
                if (response.data.meta) {
                    this.currentPage = response.data.meta.current_page;
                    this.totalPages = response.data.meta.last_page;
                    this.totalRecords = response.data.meta.total;
                }

            } catch (error) {
                console.error('Error fetching data:', error);
                this.showNotification('Error fetching data', 'error');
            } finally {
                this.loading = false;
            }
        },

        // Apply filters with debounce
        async applyFilters() {
            this.currentPage = 1;
            await this.fetchData();
        },

        // Handle sorting
        async handleSort(key) {
            if (this.sortConfig.key === key) {
                this.sortConfig.direction = this.sortConfig.direction === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortConfig.key = key;
                this.sortConfig.direction = 'asc';
            }
            this.currentPage = 1;
            await this.fetchData();
        },

        // File upload handling (HANYA SATU FUNGSI INI)
        handleFileUpload(event) {
            this.fileToUpload = event.target.files[0];
            if (!this.fileToUpload) return;

            const formData = new FormData();
            formData.append('file', this.fileToUpload);

            // Kirim file ke backend untuk pratinjau
            axios.post('/api/music-tracks/preview', formData)
                .then(response => {
                    this.previewData = response.data.preview_data;
                    if (this.previewData.length > 0) {
                        this.previewHeaders = Object.keys(this.previewData[0]);
                    }
                    this.showUploadModal = true;
                })
                .catch(error => {
                    alert('Gagal memuat pratinjau: ' + (error.response?.data?.message || error.message));
                });
        },

        // Confirm and upload data
        async confirmUpload() {
            if (!this.fileToUpload) return;

            this.uploading = true;
            this.uploadStatus = 'Uploading data...';

            try {
                const formData = new FormData();
                formData.append('file', this.fileToUpload);

                const response = await axios.post('/api/music-tracks/upload', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' },
                });
                
                this.showNotification('Data uploaded successfully!', 'success');
                this.closeModal();
                await this.fetchData(); // Refresh data

            } catch (error) {
                console.error('Upload error:', error);
                const message = error.response?.data?.message || 'Error uploading data';
                this.showNotification(message, 'error');
            } finally {
                this.uploading = false;
                this.uploadStatus = '';
            }
        },
        
        // Pagination methods
        async previousPage() {
            if (this.currentPage > 1) {
                this.currentPage--;
                await this.fetchData();
            }
        },

        async nextPage() {
            if (this.currentPage < this.totalPages) {
                this.currentPage++;
                await this.fetchData();
            }
        },

        async goToPage(page) {
            if (page >= 1 && page <= this.totalPages && page !== this.currentPage) {
                this.currentPage = page;
                await this.fetchData();
            }
        },

        getVisiblePages() {
            const pages = [];
            const maxVisible = 5;
            let start = Math.max(1, this.currentPage - Math.floor(maxVisible / 2));
            let end = Math.min(this.totalPages, start + maxVisible - 1);
            
            if (end - start < maxVisible - 1) {
                start = Math.max(1, end - maxVisible + 1);
            }
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        },

        // Close upload modal
        closeModal() {
            this.showUploadModal = false;
            this.previewData = [];
            this.fileToUpload = null;
            this.uploadStatus = '';
            this.uploading = false;
        },

        // Show notification
        showNotification(message, type = 'success') {
            this.notification = {
                show: true,
                message,
                type
            };

            setTimeout(() => {
                this.notification.show = false;
            }, 3000);
        },

        // Utility methods
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            return new Date(dateString).toLocaleDateString();
        },

        formatPrice(price) {
            if (price == null || price === '') return 'N/A';
            return '$' + parseFloat(price).toFixed(2);
        }
    };
};

// Initialize Alpine.js
Alpine.start();