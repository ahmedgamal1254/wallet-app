<script type="text/javascript">
        var gk_isXlsx = false;
        var gk_xlsxFileLookup = {};
        var gk_fileData = {};
        function filledCell(cell) {
          return cell !== '' && cell != null;
        }
        function loadFileData(filename) {
        if (gk_isXlsx && gk_xlsxFileLookup[filename]) {
            try {
                var workbook = XLSX.read(gk_fileData[filename], { type: 'base64' });
                var firstSheetName = workbook.SheetNames[0];
                var worksheet = workbook.Sheets[firstSheetName];

                // Convert sheet to JSON to filter blank rows
                var jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1, blankrows: false, defval: '' });
                // Filter out blank rows (rows where all cells are empty, null, or undefined)
                var filteredData = jsonData.filter(row => row.some(filledCell));

                // Heuristic to find the header row by ignoring rows with fewer filled cells than the next row
                var headerRowIndex = filteredData.findIndex((row, index) =>
                  row.filter(filledCell).length >= filteredData[index + 1]?.filter(filledCell).length
                );
                // Fallback
                if (headerRowIndex === -1 || headerRowIndex > 25) {
                  headerRowIndex = 0;
                }

                // Convert filtered JSON back to CSV
                var csv = XLSX.utils.aoa_to_sheet(filteredData.slice(headerRowIndex)); // Create a new sheet from filtered array of arrays
                csv = XLSX.utils.sheet_to_csv(csv, { header: 1 });
                return csv;
            } catch (e) {
                console.error(e);
                return "";
            }
        }
        return gk_fileData[filename] || "";
        }
        </script><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Admin Panel') }} - @yield('title')</title>
    <!-- Font Awesome CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-gray-800 text-white hidden md:block">
            <div class="p-4">
                <h1 class="text-2xl font-bold">{{ config('app.name', 'Admin Panel') }}</h1>
            </div>
            <nav class="mt-4">
                <ul>
                    <li>
                        <a href="{{ route('admin.dashboard') }}" class="block py-2 px-4 hover:bg-gray-700 {{ Route::is('admin.dashboard') ? 'bg-gray-700' : '' }}">
                            Dashboard
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.referral_codes.index') }}" class="block py-2 px-4 hover:bg-gray-700 {{ Route::is('admin.referral_codes.*') ? 'bg-gray-700' : '' }}">
                            Referral Codes
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.topups.index') }}" class="block py-2 px-4 hover:bg-gray-700 {{ Route::is('admin.topups.*') ? 'bg-gray-700' : '' }}">Top-up Requests</a></li>
                    <li>
                        <a href="{{ route('admin.withdrawals.index') }}"
                        class="block py-2 px-4 hover:bg-gray-700 {{ Route::is('admin.withdrawals.*') ? 'bg-gray-700' : '' }}">
                        Withdrawal Requests
                        </a>
                    </li>
                    <li class="mt-4">
                        <a href="{{ route('admin.logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                            class="ml-4 py-2 px-4 rounded text-white bg-red-600 hover:bg-red-700 transition">
                            <i class="fa-solid fa-right-from-bracket mr-2"></i>
                            Logout
                        </a>
                    </li>
                    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </ul>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <header class="bg-white shadow p-4 flex justify-between items-center px-8">
                <h2 class="text-xl font-semibold">@yield('title')</h2>

                <div class="flex items-center space-x-6">
                    {{-- User --}}
                    <div class="flex items-center space-x-2">
                        <img src="{{ asset('images/avater.png') }}" class="w-10 h-10 object-cover rounded-full" alt="Avatar">
                        <span class="font-medium text-gray-700">{{ auth('admin')->user()->name }}</span>
                    </div>

                    {{-- Notifications --}}
                    <div class="relative">
                        <button id="notification-btn" class="relative focus:outline-none">
                            <img src="{{ asset('images/bell.png') }}" alt="Notifications" class="w-10 h-10 object-cover">
                            @if(auth('admin')->user()->unreadNotifications->count() > 0)
                                <span id="notification-count"
                                    class="absolute -top-2 -right-2 bg-red-600 text-white text-xs rounded-full px-1.5 py-0.5">
                                    {{ auth('admin')->user()->unreadNotifications->count() }}
                                </span>
                            @endif
                        </button>

                        {{-- Dropdown --}}
                        <div id="notification-dropdown"
                            class="hidden absolute right-0 mt-2 w-72 bg-white shadow-lg rounded-lg z-50">
                            <div class="p-3 border-b font-semibold">Notifications</div>
                            <ul id="notifications-list" class="max-h-60 overflow-y-auto">
                                @foreach(auth('admin')->user()->notifications as $notification)
                                    <li id="notification-{{ $notification->id }}"
                                        class="px-4 py-2 flex justify-between items-center
                                            {{ $notification->read_at ? 'text-gray-500' : 'text-black' }}">
                                        <span>{{ $notification->data['message'] ?? 'No message' }}</span>

                                        @if(!$notification->read_at)
                                            <button class="text-blue-600 text-sm hover:underline mark-read"
                                                    data-id="{{ $notification->id }}">
                                                Mark as Read
                                            </button>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </header>


            <!-- Mobile Menu -->
            <div x-data="{ open: false }" x-show="open" class="md:hidden bg-gray-800 text-white p-4">
                <nav>
                    <ul>
                        <li><a href="{{ route('admin.dashboard') }}" class="block py-2 px-4 hover:bg-gray-700">Dashboard</a></li>
                        <li><a href="{{ route('admin.topups.index') }}" class="block py-2 px-4 hover:bg-gray-700">Top-up Requests</a></li>
                        <li><a href="{{ route('admin.withdrawals.index') }}" class="block py-2 px-4 hover:bg-gray-700">Withdrawal Requests</a></li>
                        <li><a href="{{ route('admin.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-mobile').submit();" class="block py-2 px-4 hover:bg-gray-700">Logout</a></li>
                        <form id="logout-form-mobile" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </ul>
                </nav>
            </div>

            <!-- Content -->
            <main class="flex-1 p-6">
                @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                @if ($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Toggle Dropdown
            const btn = document.getElementById("notification-btn");
            const dropdown = document.getElementById("notification-dropdown");

            btn.addEventListener("click", () => {
                dropdown.classList.toggle("hidden");
            });

            // AJAX Mark as Read
            document.querySelectorAll(".mark-read").forEach(button => {
                button.addEventListener("click", async (e) => {
                    e.preventDefault();
                    const id = button.getAttribute("data-id");

                    const response = await fetch(`/admin/notifications/${id}/read`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": "{{ csrf_token() }}",
                            "Accept": "application/json"
                        }
                    });

                    const result = await response.json();

                    if (result.success) {
                        const li = document.getElementById(`notification-${id}`);
                        li.classList.remove("text-black");
                        li.classList.add("text-gray-500");
                        button.remove();

                        // نقص العدّاد
                        const countEl = document.getElementById("notification-count");
                        if (countEl) {
                            let count = parseInt(countEl.innerText);
                            count = count - 1;
                            if (count > 0) {
                                countEl.innerText = count;
                            } else {
                                countEl.remove();
                            }
                        }
                    }
                });
            });
        });
    </script>

</body>
</html>
