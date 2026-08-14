<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Users - WA Scheduler</title>
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
            <a href="index.php" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm mr-2"><i class="fas fa-home"></i> Dashboard</a>
            <span class="text-white mr-4">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <a href="logout.php" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">Logout</a>
        </div>
    </div>
</nav>

<div class="container mx-auto mt-8 px-4">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">API Users & Keys</h2>
        <button onclick="openUserModal()" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
            <i class="fas fa-user-plus mr-1"></i> Add User
        </button>
    </div>

    <!-- Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">ID</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Username</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">API Key</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Created At</th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody id="users-tbody">
                <tr><td colspan="5" class="px-5 py-5 text-center text-gray-500">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div id="userModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Add New API User</h3>
            <form id="userForm" onsubmit="saveUser(event)">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Username (Teman Anda)</label>
                    <input type="text" id="usernameInput" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-1 focus:ring-blue-500" placeholder="Contoh: Budi" required>
                    <p class="text-xs text-gray-500 mt-1">API Key akan di-generate secara otomatis.</p>
                </div>
                
                <div class="items-center px-4 py-3 sm:flex sm:flex-row-reverse">
                    <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm">
                        Create
                    </button>
                    <button type="button" onclick="closeUserModal()" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let usersData = [];

    async function loadUsers() {
        try {
            const res = await fetch('api/users_api.php');
            const data = await res.json();
            
            if (data.status === 'success') {
                usersData = data.data;
                renderTable();
            } else {
                alert('Failed to load users: ' + data.message);
            }
        } catch (error) {
            console.error(error);
            alert('Error loading data');
        }
    }

    function renderTable() {
        const tbody = document.getElementById('users-tbody');
        tbody.innerHTML = '';
        
        if (usersData.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="px-5 py-5 text-center text-gray-500">No users found</td></tr>';
            return;
        }
        
        usersData.forEach(user => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><p class="text-gray-900 whitespace-no-wrap">${user.id}</p></td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><p class="text-gray-900 font-bold whitespace-no-wrap">${user.username}</p></td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm">
                    <div class="flex items-center">
                        <code class="bg-gray-100 px-2 py-1 rounded text-red-600 mr-2">${user.api_key}</code>
                        <button onclick="copyToClipboard('${user.api_key}')" class="text-gray-500 hover:text-gray-700" title="Copy"><i class="fas fa-copy"></i></button>
                    </div>
                </td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm"><p class="text-gray-900 whitespace-no-wrap">${user.created_at}</p></td>
                <td class="px-5 py-5 border-b border-gray-200 bg-white text-sm text-center">
                    <button onclick="deleteUser(${user.id})" class="text-red-500 hover:text-red-800" title="Delete User"><i class="fas fa-trash"></i></button>
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert("API Key disalin ke clipboard!");
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    }

    function openUserModal() {
        document.getElementById('userModal').classList.remove('hidden');
        document.getElementById('usernameInput').value = '';
    }

    function closeUserModal() {
        document.getElementById('userModal').classList.add('hidden');
    }

    async function saveUser(e) {
        e.preventDefault();
        
        const payload = {
            username: document.getElementById('usernameInput').value
        };

        try {
            const res = await fetch('api/users_api.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                closeUserModal();
                loadUsers();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (err) {
            console.error(err);
            alert('Request failed');
        }
    }

    async function deleteUser(id) {
        if (!confirm('Apakah Anda yakin ingin menghapus user ini? Mereka tidak akan bisa lagi menggunakan API.')) return;
        
        try {
            const res = await fetch('api/users_api.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: id })
            });
            const data = await res.json();
            
            if (data.status === 'success') {
                loadUsers();
            } else {
                alert('Error: ' + data.message);
            }
        } catch (err) {
            console.error(err);
            alert('Request failed');
        }
    }

    // Initialize
    loadUsers();
</script>

</body>
</html>
