<?php
session_start();
require_once 'api/config.php';

// Check authentication
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';

// Get award category from URL
$awardCategory = $_GET['category'] ?? null;

if (!$awardCategory) {
    header('Location: awards.php#award-list');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($awardCategory); ?> - Applicants | LILAC</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Material Symbols -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />

    <!-- Custom Styles -->
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-slide-in {
            animation: slideIn 0.3s ease-out forwards;
        }

        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }
    </style>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 sticky top-0 z-50">
        <div class="container mx-auto px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="awards.php#award-list" class="flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-primary transition-colors">
                        <span class="material-symbols-outlined">arrow_back</span>
                        <span>Back to Award List</span>
                    </a>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        <?php echo htmlspecialchars($_SESSION['username']); ?>
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container mx-auto px-6 py-8">

        <!-- Page Title -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2" id="page-title">
                Loading...
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                All applicants for this award category
            </p>
        </div>

        <!-- Statistics Cards -->
        <div id="stats-container" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 text-center animate-slide-in stagger-1 opacity-0">
                <div class="text-4xl font-bold text-blue-600 dark:text-blue-400 mb-2">
                    <span id="stat-total">0</span>
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Total Applicants</div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 text-center animate-slide-in stagger-2 opacity-0">
                <div class="text-4xl font-bold text-yellow-600 dark:text-yellow-400 mb-2">
                    <span id="stat-pending">0</span>
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Pending Review</div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 text-center animate-slide-in stagger-3 opacity-0">
                <div class="text-4xl font-bold text-green-600 dark:text-green-400 mb-2">
                    <span id="stat-recognized">0</span>
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Recognized</div>
            </div>

            <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-6 text-center animate-slide-in stagger-4 opacity-0">
                <div class="text-4xl font-bold text-purple-600 dark:text-purple-400 mb-2">
                    <span id="stat-processed">0</span>
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400">Processed</div>
            </div>
        </div>

        <!-- Filter and Search -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
                    <input type="text" id="search-input" placeholder="Search by username or email..."
                        class="w-full pl-10 pr-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                </div>

                <select id="status-filter" class="px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                    <option value="">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Recognized</option>
                    <option value="analyzed">Processed</option>
                </select>

                <select id="eligibility-filter" class="px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                    <option value="">All Eligibility</option>
                    <option value="eligible">✅ Eligible (≥90%)</option>
                    <option value="almost">🟡 Almost Eligible (70-89%)</option>
                    <option value="not-eligible">❌ Not Eligible (<70%)</option>
                </select>

                <select id="sort-select" class="px-4 py-2 bg-gray-50 dark:bg-gray-900 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-primary focus:border-primary outline-none transition-all">
                    <option value="similarity-desc">Similarity (High to Low)</option>
                    <option value="similarity-asc">Similarity (Low to High)</option>
                    <option value="date-desc">Date (Newest First)</option>
                    <option value="date-asc">Date (Oldest First)</option>
                </select>
            </div>
        </div>

        <!-- Applicants Grid -->
        <div id="applicants-container" class="grid grid-cols-1 gap-6">
            <!-- Loading indicator -->
            <div id="loading-indicator" class="col-span-full flex items-center justify-center py-12">
                <div class="flex items-center gap-3 text-gray-500 dark:text-gray-400">
                    <svg class="animate-spin h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span>Loading applicants...</span>
                </div>
            </div>
        </div>

    </main>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 right-6 px-6 py-3 rounded-lg shadow-lg transform translate-y-20 opacity-0 transition-all duration-300 z-50">
        <span id="toast-message"></span>
    </div>

    <script>
        const AUTH_TOKEN = '<?php echo $_SESSION['token'] ?? ''; ?>';
        const AWARD_CATEGORY = '<?php echo addslashes($awardCategory); ?>';
        const IS_ADMIN = <?php echo $isAdmin ? 'true' : 'false'; ?>;

        let allApplicants = [];
        let filteredApplicants = [];

        // Load applicants data
        async function loadApplicants() {
            try {
                const response = await fetch(`api/award-applicants.php?category=${encodeURIComponent(AWARD_CATEGORY)}`, {
                    headers: {
                        'Authorization': 'Bearer ' + AUTH_TOKEN
                    }
                });

                if (!response.ok) throw new Error('Failed to fetch applicants');

                const result = await response.json();
                if (!result.success) throw new Error(result.error || 'Failed to load applicants');

                allApplicants = result.applicants;
                filteredApplicants = [...allApplicants];

                // Update page title
                document.getElementById('page-title').textContent = AWARD_CATEGORY;

                // Update statistics
                updateStatistics(result.stats);

                // Render applicants
                renderApplicants();

            } catch (error) {
                console.error('Error loading applicants:', error);
                showToast('Failed to load applicants: ' + error.message, 'error');
            }
        }

        // Update statistics
        function updateStatistics(stats) {
            document.getElementById('stat-total').textContent = stats.total || 0;
            document.getElementById('stat-pending').textContent = stats.pending || 0;
            document.getElementById('stat-recognized').textContent = stats.recognized || 0;
            document.getElementById('stat-processed').textContent = stats.processed || 0;
        }

        // Render applicants
        function renderApplicants() {
            const container = document.getElementById('applicants-container');
            const loadingIndicator = document.getElementById('loading-indicator');

            if (loadingIndicator) {
                loadingIndicator.remove();
            }

            if (filteredApplicants.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <span class="material-symbols-outlined text-6xl text-gray-300 dark:text-gray-600 mb-4">inbox</span>
                        <p class="text-gray-500 dark:text-gray-400">No applicants found</p>
                    </div>
                `;
                return;
            }

            container.innerHTML = filteredApplicants.map((app, index) => {
                const similarity = Math.round((app.similarity_score || app.match_percentage / 100) * 100);

                const statusMap = {
                    'pending': { bg: 'bg-yellow-100 dark:bg-yellow-900/30', text: 'text-yellow-700 dark:text-yellow-400', border: 'border-yellow-200 dark:border-yellow-800', label: 'Pending' },
                    'approved': { bg: 'bg-green-100 dark:bg-green-900/30', text: 'text-green-700 dark:text-green-400', border: 'border-green-200 dark:border-green-800', label: 'Recognized' },
                    'analyzed': { bg: 'bg-purple-100 dark:bg-purple-900/30', text: 'text-purple-700 dark:text-purple-400', border: 'border-purple-200 dark:border-purple-800', label: 'Processed' },
                    'rejected': { bg: 'bg-red-100 dark:bg-red-900/30', text: 'text-red-700 dark:text-red-400', border: 'border-red-200 dark:border-red-800', label: 'Rejected' }
                };

                const currentStatus = (app.award_status || 'pending').toLowerCase();
                const statusColor = statusMap[currentStatus] || statusMap['pending'];

                const progressColorClass = similarity >= 90 ? 'bg-gradient-to-r from-green-400 to-green-600' :
                                          similarity >= 75 ? 'bg-gradient-to-r from-blue-400 to-blue-600' :
                                          similarity >= 60 ? 'bg-gradient-to-r from-yellow-400 to-yellow-600' :
                                          'bg-gradient-to-r from-red-400 to-red-600';

                const textColorClass = similarity >= 90 ? 'text-green-600 dark:text-green-400' :
                                      similarity >= 75 ? 'text-blue-600 dark:text-blue-400' :
                                      similarity >= 60 ? 'text-yellow-600 dark:text-yellow-400' :
                                      'text-red-600 dark:text-red-400';

                // Eligibility status based on similarity score
                const eligibilityStatus = similarity >= 90 ? {
                    label: '✅ Eligible',
                    bg: 'bg-green-100 dark:bg-green-900/30',
                    text: 'text-green-700 dark:text-green-400',
                    border: 'border-green-300 dark:border-green-700'
                } : similarity >= 70 ? {
                    label: '🟡 Almost Eligible',
                    bg: 'bg-yellow-100 dark:bg-yellow-900/30',
                    text: 'text-yellow-700 dark:text-yellow-400',
                    border: 'border-yellow-300 dark:border-yellow-700'
                } : {
                    label: '❌ Not Eligible',
                    bg: 'bg-red-100 dark:bg-red-900/30',
                    text: 'text-red-700 dark:text-red-400',
                    border: 'border-red-300 dark:border-red-700'
                };

                return `
                    <div class="bg-white dark:bg-gray-800 border ${statusColor.border} rounded-lg p-6 hover:shadow-lg transition-all animate-slide-in opacity-0" style="animation-delay: ${index * 0.05}s">
                        <!-- User Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="material-symbols-outlined text-3xl text-gray-600 dark:text-gray-400">account_circle</span>
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">${escapeHtml(app.username || 'Unknown User')}</h3>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">${escapeHtml(app.email || 'No email')}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-400 ml-12">
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">calendar_today</span>
                                        <span>Submitted: ${new Date(app.created_at).toLocaleDateString()}</span>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <span class="material-symbols-outlined text-sm">description</span>
                                        <span>${escapeHtml(app.submission_title || 'No title')}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col items-end gap-2">
                                <span class="px-4 py-2 ${statusColor.bg} ${statusColor.text} rounded-full text-sm font-medium">
                                    ${statusColor.label}
                                </span>
                                <span class="px-4 py-2 ${eligibilityStatus.bg} ${eligibilityStatus.text} border ${eligibilityStatus.border} rounded-full text-sm font-semibold">
                                    ${eligibilityStatus.label}
                                </span>
                            </div>
                        </div>

                        <!-- Similarity Score & Eligibility -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Similarity Score</span>
                                <span class="text-xl font-bold ${textColorClass}">${similarity}%</span>
                            </div>
                            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-4 overflow-hidden">
                                <div class="h-4 rounded-full transition-all duration-500 flex items-center justify-end pr-2 ${progressColorClass}"
                                     style="width: ${similarity}%">
                                    <span class="text-xs font-bold text-white">${similarity}%</span>
                                </div>
                            </div>
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                ${similarity >= 90 ? '90% and above = Eligible' : similarity >= 70 ? '70%–89% = Almost Eligible' : 'Below 70% = Not Eligible'}
                            </div>
                        </div>

                        <!-- Matched Criteria -->
                        ${app.matched_criteria && app.matched_criteria.length > 0 ? `
                            <div class="mb-4">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Matched Criteria</span>
                                    <span class="text-sm text-gray-500 dark:text-gray-400">${app.criteria_met || app.matched_criteria.length}/${app.criteria_total || app.matched_criteria.length + (app.unmatched_criteria?.length || 0)}</span>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    ${app.matched_criteria.map(c => `
                                        <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 rounded-lg text-xs font-medium">
                                            ✓ ${escapeHtml(c)}
                                        </span>
                                    `).join('')}
                                </div>
                            </div>
                        ` : ''}

                        <!-- Unmatched Criteria -->
                        ${app.unmatched_criteria && app.unmatched_criteria.length > 0 ? `
                            <div class="mb-4">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 block mb-2">Unmatched Criteria</span>
                                <div class="flex flex-wrap gap-2">
                                    ${app.unmatched_criteria.map(c => `
                                        <span class="px-3 py-1 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 rounded-lg text-xs font-medium">
                                            ✗ ${escapeHtml(c)}
                                        </span>
                                    `).join('')}
                                </div>
                            </div>
                        ` : ''}

                        <!-- Status Update Buttons (Admin Only) -->
                        ${IS_ADMIN ? `
                            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300 block mb-3">Update Status:</span>
                                <div class="flex gap-2">
                                    <button onclick="updateStatus('${app.award_id}', 'pending')"
                                        class="flex-1 px-4 py-2 ${currentStatus === 'pending' ? 'bg-yellow-600 text-white' : 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'} rounded-lg font-medium hover:opacity-80 transition-opacity">
                                        Pending
                                    </button>
                                    <button onclick="updateStatus('${app.award_id}', 'recognized')"
                                        class="flex-1 px-4 py-2 ${currentStatus === 'approved' ? 'bg-green-600 text-white' : 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'} rounded-lg font-medium hover:opacity-80 transition-opacity">
                                        Recognized
                                    </button>
                                    <button onclick="updateStatus('${app.award_id}', 'processed')"
                                        class="flex-1 px-4 py-2 ${currentStatus === 'analyzed' ? 'bg-purple-600 text-white' : 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400'} rounded-lg font-medium hover:opacity-80 transition-opacity">
                                        Processed
                                    </button>
                                </div>
                            </div>
                        ` : ''}
                    </div>
                `;
            }).join('');
        }

        // Update status
        async function updateStatus(awardId, newStatus) {
            try {
                const response = await fetch(`api/award-applicants.php?award_id=${awardId}`, {
                    method: 'POST',
                    headers: {
                        'Authorization': 'Bearer ' + AUTH_TOKEN,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ status: newStatus })
                });

                if (!response.ok) throw new Error('Failed to update status');

                const result = await response.json();
                if (!result.success) throw new Error(result.error || 'Failed to update');

                showToast(`Status updated to: ${newStatus}`, 'success');

                // Reload applicants
                await loadApplicants();

            } catch (error) {
                console.error('Error updating status:', error);
                showToast('Failed to update status: ' + error.message, 'error');
            }
        }

        // Filter and search
        function applyFilters() {
            const searchTerm = document.getElementById('search-input').value.toLowerCase();
            const statusFilter = document.getElementById('status-filter').value;
            const eligibilityFilter = document.getElementById('eligibility-filter').value;
            const sortBy = document.getElementById('sort-select').value;

            // Filter
            filteredApplicants = allApplicants.filter(app => {
                const matchesSearch = !searchTerm ||
                    (app.username && app.username.toLowerCase().includes(searchTerm)) ||
                    (app.email && app.email.toLowerCase().includes(searchTerm)) ||
                    (app.submission_title && app.submission_title.toLowerCase().includes(searchTerm));

                const matchesStatus = !statusFilter ||
                    (app.award_status && app.award_status.toLowerCase() === statusFilter);

                // Eligibility filter
                const similarity = (app.similarity_score || app.match_percentage / 100) * 100;
                let matchesEligibility = !eligibilityFilter;
                if (eligibilityFilter) {
                    if (eligibilityFilter === 'eligible') {
                        matchesEligibility = similarity >= 90;
                    } else if (eligibilityFilter === 'almost') {
                        matchesEligibility = similarity >= 70 && similarity < 90;
                    } else if (eligibilityFilter === 'not-eligible') {
                        matchesEligibility = similarity < 70;
                    }
                }

                return matchesSearch && matchesStatus && matchesEligibility;
            });

            // Sort
            filteredApplicants.sort((a, b) => {
                const simA = (a.similarity_score || a.match_percentage / 100) * 100;
                const simB = (b.similarity_score || b.match_percentage / 100) * 100;
                const dateA = new Date(a.created_at);
                const dateB = new Date(b.created_at);

                switch(sortBy) {
                    case 'similarity-desc': return simB - simA;
                    case 'similarity-asc': return simA - simB;
                    case 'date-desc': return dateB - dateA;
                    case 'date-asc': return dateA - dateB;
                    default: return simB - simA;
                }
            });

            renderApplicants();
        }

        // Show toast notification
        function showToast(message, type = 'info') {
            const toast = document.getElementById('toast');
            const toastMessage = document.getElementById('toast-message');

            const colors = {
                success: 'bg-green-600 text-white',
                error: 'bg-red-600 text-white',
                info: 'bg-blue-600 text-white'
            };

            toast.className = `fixed bottom-6 right-6 px-6 py-3 rounded-lg shadow-lg transition-all duration-300 z-50 ${colors[type]}`;
            toastMessage.textContent = message;

            setTimeout(() => {
                toast.style.transform = 'translateY(0)';
                toast.style.opacity = '1';
            }, 10);

            setTimeout(() => {
                toast.style.transform = 'translateY(20px)';
                toast.style.opacity = '0';
            }, 3000);
        }

        // Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        // Event listeners
        document.getElementById('search-input').addEventListener('input', applyFilters);
        document.getElementById('status-filter').addEventListener('change', applyFilters);
        document.getElementById('eligibility-filter').addEventListener('change', applyFilters);
        document.getElementById('sort-select').addEventListener('change', applyFilters);

        // Load data on page load
        loadApplicants();
    </script>
</body>
</html>
