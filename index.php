<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? 'user') !== 'admin') {
    if (isset($_SESSION['user_id'])) {
        header('Location: user_dashboard.php');
    } else {
        header('Location: login.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - WA Scheduler</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

<nav class="bg-blue-600 p-4 mt-0 w-full z-10 top-0 shadow">
    <div class="container mx-auto flex flex-wrap items-center justify-between">
        <div class="flex items-center text-white font-extrabold text-xl">
            <i class="fab fa-whatsapp mr-2"></i> WA Scheduler Web
        </div>
        <div class="flex items-center">
            <span class="text-white mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (Admin)</span>
            <a href="docs.php" target="_blank" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm mr-2"><i class="fas fa-book"></i> API Docs</a>
            <a href="users.php" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm mr-2"><i class="fas fa-users"></i> Manage Users</a>
            <button onclick="openSettingsModal()" class="bg-gray-700 hover:bg-gray-900 text-white font-bold py-2 px-4 rounded text-sm mr-2"><i class="fas fa-cog"></i> Settings</button>
            <a href="logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mx-auto mt-8 px-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Message Schedules</h2>
        <div>
            <button onclick="deleteSelected()" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded mr-2 hidden" id="bulkDeleteBtn">
                <i class="fas fa-trash mr-1"></i> Delete Selected
            </button>
            <button onclick="openModal()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-plus mr-1"></i> Add Schedule
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded shadow p-4 border-l-4 border-blue-500">
            <h3 class="text-gray-500 text-sm">Total Schedules</h3>
            <p class="text-2xl font-bold" id="stat-total">0</p>
        </div>
        <div class="bg-white rounded shadow p-4 border-l-4 border-yellow-500">
            <h3 class="text-gray-500 text-sm">Pending</h3>
            <p class="text-2xl font-bold" id="stat-pending">0</p>
        </div>
        <div class="bg-white rounded shadow p-4 border-l-4 border-green-500">
            <h3 class="text-gray-500 text-sm">Completed</h3>
            <p class="text-2xl font-bold" id="stat-completed">0</p>
        </div>
        <div class="bg-white rounded shadow p-4 border-l-4 border-red-500">
            <h3 class="text-gray-500 text-sm">Failed</h3>
            <p class="text-2xl font-bold" id="stat-failed">0</p>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-4">
        <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-700">Show</label>
            <select id="itemsPerPageSelect" onchange="changeItemsPerPage(this.value)" class="border rounded px-2 py-1 focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white">
                <option value="10">10</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="all">All</option>
            </select>
            <label class="text-sm font-medium text-gray-700">entries</label>
        </div>
        <div class="flex items-center gap-4 w-full md:w-auto">
            <select id="senderFilter" onchange="changeSenderFilter(this.value)" class="border rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white" title="Filter by Sender">
                <option value="ALL">All Senders</option>
            </select>
            <input type="date" id="dateFilter" onchange="changeDateFilter(this.value)" class="border rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white" title="Filter by Date">
            <select id="loopFilter" onchange="changeLoopFilter(this.value)" class="border rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white" title="Filter by Loop">
                <option value="ALL">All Loops</option>
                <option value="NONE">No Loop</option>
                <option value="daily">Daily</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
            </select>
            <select id="statusFilter" onchange="changeFilter(this.value)" class="border rounded px-3 py-2 focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white">
                <option value="ALL">All Status</option>
                <option value="PENDING">PENDING</option>
                <option value="PROCESSING">PROCESSING</option>
                <option value="COMPLETED">COMPLETED</option>
                <option value="FAILED">FAILED</option>
            </select>
            <div class="relative w-full md:w-64">
                <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="searchInput" onkeyup="handleSearch(this.value)" class="bg-white border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full pl-10 p-2" placeholder="Search...">
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-10">
                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll()">
                    </th>
                    <th onclick="sortBy('id')" class="cursor-pointer px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hover:bg-gray-200">ID <i class="fas fa-sort ml-1 opacity-50"></i></th>
                    <th onclick="sortBy('sender')" class="cursor-pointer px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hover:bg-gray-200">Sender <i class="fas fa-sort ml-1 opacity-50"></i></th>
                    <th onclick="sortBy('phone_number')" class="cursor-pointer px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hover:bg-gray-200">Phone <i class="fas fa-sort ml-1 opacity-50"></i></th>
                    <th onclick="sortBy('message')" class="cursor-pointer px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hover:bg-gray-200">Message <i class="fas fa-sort ml-1 opacity-50"></i></th>
                    <th onclick="sortBy('scheduled_time')" class="cursor-pointer px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hover:bg-gray-200">Scheduled Time <i class="fas fa-sort ml-1 opacity-50"></i></th>
                    <th onclick="sortBy('status')" class="cursor-pointer px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider hover:bg-gray-200">Status <i class="fas fa-sort ml-1 opacity-50"></i></th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="schedules-tbody">
                <tr><td colspan="8" class="px-5 py-5 text-center text-gray-500">Loading...</td></tr>
            </tbody>
        </table>
        
        <!-- Pagination controls -->
        <div class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
            <span class="text-xs xs:text-sm text-gray-900" id="pagination-info">
                Showing 0 to 0 of 0 Entries
            </span>
            <div class="inline-flex mt-2 xs:mt-0" id="pagination-controls">
                <!-- Buttons will be injected here -->
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="scheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4" id="modal-title">Add Schedule</h3>
            <form id="scheduleForm" onsubmit="saveSchedule(event)">
                <input type="hidden" id="scheduleId" value="">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone Number (with Country Code)</label>
                    <input type="text" id="phoneNumber" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="+628123456789" required>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                    <textarea id="messageContent" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" rows="3" required></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Scheduled Time</label>
                    <input type="datetime-local" id="scheduledTime" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" required>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ulangi Pengiriman? (Looping)</label>
                    <select id="loopInterval" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="">Tidak Ada (Sekali Saja)</option>
                        <option value="daily">Setiap Hari (Daily)</option>
                        <option value="weekly">Setiap Minggu (Weekly)</option>
                        <option value="monthly">Setiap Bulan (Monthly)</option>
                    </select>
                </div>

                <div class="mb-4 hidden" id="statusDiv">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select id="scheduleStatus" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500">
                        <option value="PENDING">PENDING</option>
                        <option value="PROCESSING">PROCESSING</option>
                        <option value="COMPLETED">COMPLETED</option>
                        <option value="FAILED">FAILED</option>
                    </select>
                </div>
                
                <div class="items-center px-4 py-3 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button type="button" onclick="closeModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- FCM Settings Modal -->
<div id="settingsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">FCM Configuration</h3>
            <form id="settingsForm" onsubmit="saveSettings(event)">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Firebase Server Key</label>
                    <textarea id="fcmServerKey" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" rows="3" placeholder="AAAA..." required></textarea>
                </div>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Android Device Token</label>
                    <textarea id="fcmDeviceToken" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" rows="3" placeholder="c8xK..." required></textarea>
                </div>
                
                <div class="items-center px-4 py-3 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Save
                    </button>
                    <button type="button" onclick="closeSettingsModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let schedulesData = [];
    let filteredData = [];
    let currentPage = 1;
    let itemsPerPage = 10;
    let currentSort = { column: 'id', order: 'desc' };
    let currentSearch = '';
    let currentFilter = 'ALL';
    let currentDateFilter = '';
    let currentSenderFilter = 'ALL';
    let currentLoopFilter = 'ALL';

    // Format date string to local input format
    function formatForInput(dateString) {
        if (!dateString) return '';
        const d = new Date(dateString);
        d.setMinutes(d.getMinutes() - d.getTimezoneOffset());
        return d.toISOString().slice(0, 16);
    }

    // Format status with color badge
    function getStatusBadge(status) {
        if (status === 'PENDING') return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">PENDING</span>';
        if (status === 'PROCESSING') return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">PROCESSING</span>';
        if (status === 'COMPLETED') return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">COMPLETED</span>';
        if (status === 'FAILED') return '<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">FAILED</span>';
        return status;
    }

    // Load data from API
    async function loadSchedules() {
        try {
            const res = await fetch('api/schedules.php');
            const data = await res.json();
            
            if (data.status === 'success') {
                schedulesData = data.data;
                
                // Populate Sender dropdown
                const senders = [...new Set(schedulesData.map(item => item.sender ? item.sender : 'Unknown'))];
                const senderFilterDropdown = document.getElementById('senderFilter');
                const existingValue = currentSenderFilter;
                
                senderFilterDropdown.innerHTML = '<option value="ALL">All Senders</option>';
                senders.forEach(sender => {
                    const option = document.createElement('option');
                    option.value = sender;
                    option.text = sender;
                    senderFilterDropdown.appendChild(option);
                });
                
                if (senders.includes(existingValue)) {
                    senderFilterDropdown.value = existingValue;
                } else {
                    currentSenderFilter = 'ALL';
                }

                applyFiltersAndSort();
                updateStats();
            } else {
                alert('Failed to load data: ' + data.message);
            }
        } catch (error) {
            console.error(error);
            alert('Error loading data');
        }
    }

    function updateStats() {
        document.getElementById('stat-total').innerText = schedulesData.length;
        document.getElementById('stat-pending').innerText = schedulesData.filter(s => s.status === 'PENDING').length;
        document.getElementById('stat-completed').innerText = schedulesData.filter(s => s.status === 'COMPLETED').length;
        document.getElementById('stat-failed').innerText = schedulesData.filter(s => s.status === 'FAILED').length;
    }

    function handleSearch(val) {
        currentSearch = val.toLowerCase();
        currentPage = 1;
        applyFiltersAndSort();
    }

    function changeFilter(val) {
        currentFilter = val;
        currentPage = 1;
        applyFiltersAndSort();
    }

    function changeDateFilter(val) {
        currentDateFilter = val;
        currentPage = 1;
        applyFiltersAndSort();
    }

    function changeSenderFilter(val) {
        currentSenderFilter = val;
        currentPage = 1;
        applyFiltersAndSort();
    }

    function changeLoopFilter(val) {
        currentLoopFilter = val;
        currentPage = 1;
        applyFiltersAndSort();
    }

    function changeItemsPerPage(val) {
        if (val === 'all') {
            itemsPerPage = 999999;
        } else {
            itemsPerPage = parseInt(val, 10);
        }
        currentPage = 1;
        applyFiltersAndSort();
    }

    function sortBy(column) {
        if (currentSort.column === column) {
            currentSort.order = currentSort.order === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.column = column;
            currentSort.order = 'asc';
        }
        applyFiltersAndSort();
    }

    function applyFiltersAndSort() {
        let result = [...schedulesData];
        
        // Filter Sender
        if (currentSenderFilter !== 'ALL') {
            result = result.filter(item => {
                const sName = item.sender ? item.sender : 'Unknown';
                return sName === currentSenderFilter;
            });
        }

        // Filter Loop
        if (currentLoopFilter !== 'ALL') {
            if (currentLoopFilter === 'NONE') {
                result = result.filter(item => !item.is_loop || item.is_loop == 0 || !item.loop_interval);
            } else {
                result = result.filter(item => item.is_loop == 1 && item.loop_interval === currentLoopFilter);
            }
        }

        // Filter Date
        if (currentDateFilter) {
            result = result.filter(item => {
                if (!item.scheduled_time) return false;
                return item.scheduled_time.startsWith(currentDateFilter);
            });
        }
        
        // Filter Status
        if (currentFilter !== 'ALL') {
            result = result.filter(item => item.status === currentFilter);
        }
        
        // Search
        if (currentSearch) {
            result = result.filter(item => {
                return (item.id && item.id.toString().includes(currentSearch)) ||
                       (item.phone_number && item.phone_number.includes(currentSearch)) ||
                       (item.message && item.message.toLowerCase().includes(currentSearch)) ||
                       (item.sender && item.sender.toLowerCase().includes(currentSearch));
            });
        }
        
        // Sort
        result.sort((a, b) => {
            let valA = a[currentSort.column] || '';
            let valB = b[currentSort.column] || '';
            
            if (currentSort.column === 'id') {
                valA = parseInt(valA, 10);
                valB = parseInt(valB, 10);
            } else {
                valA = valA.toString().toLowerCase();
                valB = valB.toString().toLowerCase();
            }
            
            if (valA < valB) return currentSort.order === 'asc' ? -1 : 1;
            if (valA > valB) return currentSort.order === 'asc' ? 1 : -1;
            return 0;
        });
        
        filteredData = result;
        renderTable();
    }

    function renderTable() {
        const tbody = document.getElementById('schedules-tbody');
        tbody.innerHTML = '';
        
        if (filteredData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="px-5 py-5 text-center text-gray-500">No schedules found</td></tr>';
            updatePaginationInfo(0, 0, 0);
            renderPaginationControls(0);
            return;
        }
        
        const totalItems = filteredData.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);
        
        if (currentPage > totalPages) currentPage = totalPages;
        if (currentPage < 1) currentPage = 1;
        
        const startIdx = (currentPage - 1) * itemsPerPage;
        const endIdx = Math.min(startIdx + itemsPerPage, totalItems);
        
        const paginatedData = filteredData.slice(startIdx, endIdx);
        
        paginatedData.forEach(schedule => {
            const senderName = schedule.sender ? schedule.sender : 'Unknown';
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    <input type="checkbox" class="rowCheckbox" value="${schedule.id}" onclick="updateBulkDeleteBtn()">
                </td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><p class="text-gray-900 whitespace-no-wrap">${schedule.id}</p></td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><p class="text-gray-900 whitespace-no-wrap font-bold">${senderName}</p></td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><p class="text-gray-900 whitespace-no-wrap">${schedule.phone_number}</p></td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><p class="text-gray-900 whitespace-no-wrap max-w-xs truncate">${schedule.message}</p></td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><p class="text-gray-900 whitespace-no-wrap">${schedule.scheduled_time} ${schedule.is_loop == 1 ? '<span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded bg-purple-100 text-purple-800" title="Looping: '+schedule.loop_interval+'"><i class="fas fa-redo text-[10px] mr-1 mt-[2px]"></i> '+schedule.loop_interval+'</span>' : ''}</p></td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">${getStatusBadge(schedule.status)}</td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                    <button onclick="editSchedule(${schedule.id})" class="text-blue-500 hover:text-blue-800 mr-3"><i class="fas fa-edit"></i></button>
                    <button onclick="deleteSchedule(${schedule.id})" class="text-red-500 hover:text-red-800"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        updatePaginationInfo(startIdx + 1, endIdx, totalItems);
        renderPaginationControls(totalPages);
        
        // Reset select all checkbox state
        const selectAllCb = document.getElementById('selectAll');
        if (selectAllCb) selectAllCb.checked = false;
        updateBulkDeleteBtn();
    }

    function updatePaginationInfo(start, end, total) {
        const info = document.getElementById('pagination-info');
        if (info) {
            info.innerText = `Showing ${start} to ${end} of ${total} Entries`;
        }
    }

    function renderPaginationControls(totalPages) {
        const controls = document.getElementById('pagination-controls');
        if (!controls) return;
        
        if (totalPages <= 1) {
            controls.innerHTML = '';
            return;
        }

        let html = '';
        
        // Prev button
        const prevDisabled = currentPage === 1;
        html += `<button onclick="changePage(${currentPage - 1})" class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded-l ${prevDisabled ? 'opacity-50 cursor-not-allowed' : ''}" ${prevDisabled ? 'disabled' : ''}>Prev</button>`;
        
        // Page numbers (simple version, showing all pages if not too many)
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, currentPage + 2);
        
        if (startPage > 1) {
            html += `<button onclick="changePage(1)" class="text-sm bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border-t border-b border-l border-gray-200">1</button>`;
            if (startPage > 2) html += `<span class="text-sm bg-white text-gray-800 font-semibold py-2 px-4 border-t border-b border-l border-gray-200">...</span>`;
        }

        for (let i = startPage; i <= endPage; i++) {
            if (i === currentPage) {
                html += `<button class="text-sm bg-blue-500 text-white font-semibold py-2 px-4 border border-blue-500">${i}</button>`;
            } else {
                html += `<button onclick="changePage(${i})" class="text-sm bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border-t border-b border-l border-gray-200">${i}</button>`;
            }
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) html += `<span class="text-sm bg-white text-gray-800 font-semibold py-2 px-4 border-t border-b border-l border-gray-200">...</span>`;
            html += `<button onclick="changePage(${totalPages})" class="text-sm bg-white hover:bg-gray-100 text-gray-800 font-semibold py-2 px-4 border-t border-b border-l border-gray-200">${totalPages}</button>`;
        }
        
        // Next button
        const nextDisabled = currentPage === totalPages;
        html += `<button onclick="changePage(${currentPage + 1})" class="text-sm bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 border border-gray-200 rounded-r ${nextDisabled ? 'opacity-50 cursor-not-allowed' : ''}" ${nextDisabled ? 'disabled' : ''}>Next</button>`;
        
        controls.innerHTML = html;
    }

    function changePage(page) {
        currentPage = page;
        renderTable();
    }

    function toggleSelectAll() {
        const selectAllCb = document.getElementById('selectAll');
        const checkboxes = document.querySelectorAll('.rowCheckbox');
        checkboxes.forEach(cb => cb.checked = selectAllCb.checked);
        updateBulkDeleteBtn();
    }

    function updateBulkDeleteBtn() {
        const checkedCount = document.querySelectorAll('.rowCheckbox:checked').length;
        const btn = document.getElementById('bulkDeleteBtn');
        if (checkedCount > 0) {
            btn.classList.remove('hidden');
        } else {
            btn.classList.add('hidden');
        }
        
        // Update select all checkbox state
        const selectAllCb = document.getElementById('selectAll');
        const totalCheckboxes = document.querySelectorAll('.rowCheckbox').length;
        if (selectAllCb && totalCheckboxes > 0) {
            selectAllCb.checked = (checkedCount === totalCheckboxes);
        }
    }

    async function deleteSelected() {
        const checkboxes = document.querySelectorAll('.rowCheckbox:checked');
        const ids = Array.from(checkboxes).map(cb => cb.value);
        
        if (ids.length === 0) return;
        
        if (!confirm(`Are you sure you want to delete ${ids.length} schedules?`)) return;
        
        const btn = document.getElementById('bulkDeleteBtn');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Deleting...';
        btn.disabled = true;
        
        try {
            await Promise.all(ids.map(id => fetch('api/schedules.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            })));
            loadSchedules();
        } catch (err) {
            console.error(err);
            alert('Failed to delete some schedules');
        } finally {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    }

    // Modal handling
    function openModal() {
        document.getElementById('scheduleModal').classList.remove('hidden');
        document.getElementById('modal-title').innerText = 'Add Schedule';
        document.getElementById('scheduleId').value = '';
        document.getElementById('phoneNumber').value = '';
        document.getElementById('messageContent').value = '';
        document.getElementById('scheduledTime').value = '';
        document.getElementById('loopInterval').value = '';
        document.getElementById('statusDiv').classList.add('hidden');
    }

    function closeModal() {
        document.getElementById('scheduleModal').classList.add('hidden');
    }

    function editSchedule(id) {
        const schedule = schedulesData.find(s => s.id == id);
        if (!schedule) return;
        
        document.getElementById('scheduleModal').classList.remove('hidden');
        document.getElementById('modal-title').innerText = 'Edit Schedule';
        document.getElementById('scheduleId').value = schedule.id;
        document.getElementById('phoneNumber').value = schedule.phone_number;
        document.getElementById('messageContent').value = schedule.message;
        document.getElementById('scheduledTime').value = formatForInput(schedule.scheduled_time);
        document.getElementById('loopInterval').value = schedule.is_loop == 1 ? schedule.loop_interval : '';
        
        document.getElementById('statusDiv').classList.remove('hidden');
        document.getElementById('scheduleStatus').value = schedule.status;
    }

    // API actions
    async function saveSchedule(e) {
        e.preventDefault();
        
        const id = document.getElementById('scheduleId').value;
        const loopVal = document.getElementById('loopInterval').value;
        const payload = {
            phone_number: document.getElementById('phoneNumber').value,
            message: document.getElementById('messageContent').value,
            scheduled_time: document.getElementById('scheduledTime').value,
            is_loop: loopVal ? 1 : 0,
            loop_interval: loopVal
        };

        let method = 'POST';
        if (id) {
            method = 'PUT';
            payload.id = id;
            payload.status = document.getElementById('scheduleStatus').value;
        }

        try {
            const res = await fetch('api/schedules.php', {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                closeModal();
                loadSchedules();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (err) {
            console.error(err);
            alert('Request failed');
        }
    }

    async function deleteSchedule(id) {
        if (!confirm('Are you sure you want to delete this schedule?')) return;
        
        try {
            const res = await fetch('api/schedules.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                loadSchedules();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (err) {
            console.error(err);
            alert('Request failed');
        }
    }

    // Initialize
    loadSchedules();

    // Settings handling
    function openSettingsModal() {
        document.getElementById('settingsModal').classList.remove('hidden');
        loadSettings();
    }

    function closeSettingsModal() {
        document.getElementById('settingsModal').classList.add('hidden');
    }

    async function loadSettings() {
        try {
            const res = await fetch('api/fcm_settings.php');
            const data = await res.json();
            if (data.status === 'success') {
                document.getElementById('fcmServerKey').value = data.data.server_key;
                document.getElementById('fcmDeviceToken').value = data.data.device_token;
            }
        } catch (e) { console.error(e); }
    }

    async function saveSettings(e) {
        e.preventDefault();
        const payload = {
            server_key: document.getElementById('fcmServerKey').value,
            device_token: document.getElementById('fcmDeviceToken').value
        };
        try {
            const res = await fetch('api/fcm_settings.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if (data.status === 'success') {
                alert('Settings saved successfully!');
                closeSettingsModal();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (e) {
            console.error(e);
            alert('Request failed');
        }
    }
</script>

</body>
</html>
