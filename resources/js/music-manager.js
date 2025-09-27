import Alpine from 'alpinejs';
import axios from 'axios';
import * as XLSX from 'xlsx';

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
        uploadStatus: '',
        uploading: false,
        currentFile: null,
        
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

                const response = await axios.get('/music-manager/data', { params });
                
                if (response.data.success) {
                    this.musicData = response.data.data;
                    if (response.data.meta) {
                        this.currentPage = response.data.meta.current_page;
                        this.totalPages = response.data.meta.last_page;
                        this.totalRecords = response.data.meta.total;
                    }
                } else {
                    this.showNotification('Failed to fetch data', 'error');
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
            
            // Adjust start if we're near the end
            if (end - start < maxVisible - 1) {
                start = Math.max(1, end - maxVisible + 1);
            }
            
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        },

        // File upload handling
        async handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            if (!file.name.endsWith('.xlsx') && !file.name.endsWith('.xls')) {
                this.showNotification('Please select a valid Excel file (.xlsx or .xls)', 'error');
                return;
            }

            this.currentFile = file;
            this.loading = true;
            this.uploadStatus = 'Reading file...';

            try {
                // Read file using XLSX library for client-side preview
                const data = await this.readExcelFile(file);
                this.previewData = data.slice(0, 50); // Limit preview
                this.showUploadModal = true;
            } catch (error) {
                console.error('Error reading file:', error);
                this.showNotification('Error reading Excel file', 'error');
            } finally {
                this.loading = false;
                this.uploadStatus = '';
                // Reset input
                event.target.value = '';
            }
        },

        // Read Excel file using XLSX library
        readExcelFile(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    try {
                        const data = new Uint8Array(e.target.result);
                        const workbook = XLSX.read(data, { type: 'array' });
                        const sheetName = workbook.SheetNames[0];
                        const worksheet = workbook.Sheets[sheetName];
                        const jsonData = XLSX.utils.sheet_to_json(worksheet);
                        resolve(jsonData);
                    } catch (error) {
                        reject(error);
                    }
                };
                reader.onerror = () => reject(new Error('Failed to read file'));
                reader.readAsArrayBuffer(file);
            });
        },

        // Confirm and upload data
        async confirmUpload() {
            if (!this.currentFile) return;

            this.uploading = true;
            this.uploadStatus = 'Uploading data to database...';

            try {
                const formData = new FormData();
                formData.append('file', this.currentFile);

                const response = await axios.post('/music-manager/upload', formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });

                if (response.data.success) {
                    this.showNotification('Data uploaded successfully!', 'success');
                    this.closeModal();
                    await this.fetchData(); // Refresh data
                } else {
                    this.showNotification(response.data.message || 'Upload failed', 'error');
                }
            } catch (error) {
                console.error('Upload error:', error);
                const message = error.response?.data?.message || 'Error uploading data';
                this.showNotification(message, 'error');
            } finally {
                this.uploading = false;
                this.uploadStatus = '';
            }
        },

        // Close upload modal
        closeModal() {
            this.showUploadModal = false;
            this.previewData = [];
            this.currentFile = null;
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