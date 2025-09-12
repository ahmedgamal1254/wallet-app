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
        </script>@extends('admin.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <!-- Total Users -->
    <div class="bg-white p-4 rounded-lg shadow flex items-center">
        <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
            <i class="fa-solid fa-users text-2xl"></i>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-700">Total Users</h3>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</p>
        </div>
    </div>

    <!-- Total Admins -->
    <div class="bg-white p-4 rounded-lg shadow flex items-center">
        <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
            <i class="fa-solid fa-user-shield text-2xl"></i>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-700">Total Admins</h3>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['total_admins'] }}</p>
        </div>
    </div>

    <!-- Pending Withdrawals -->
    <div class="bg-white p-4 rounded-lg shadow flex items-center">
        <div class="p-3 rounded-full bg-red-100 text-red-600 mr-4">
            <i class="fa-solid fa-money-bill-transfer text-2xl"></i>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-700">Pending Withdrawals</h3>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_withdrawals'] }}</p>
        </div>
    </div>

    <!-- Pending Top-ups -->
    <div class="bg-white p-4 rounded-lg shadow flex items-center">
        <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
            <i class="fa-solid fa-wallet text-2xl"></i>
        </div>
        <div>
            <h3 class="text-lg font-semibold text-gray-700">Pending Top-ups</h3>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['pending_topups'] }}</p>
        </div>
    </div>
</div>


<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Withdrawals -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Recent Withdrawal Requests</h3>
        <table class="w-full text-left">
            <thead>
                <tr>
                    <th class="py-2">Requester</th>
                    <th class="py-2">Amount</th>
                    <th class="py-2">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentWithdrawals as $withdrawal)
                    <tr>
                        <td class="py-2">{{ $withdrawal->requester->name ?? 'N/A' }}</td>
                        <td class="py-2">{{ $withdrawal->amount }}</td>
                        <td class="py-2">{!! status_badge($withdrawal->status) !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Recent Top-ups -->
    <div class="bg-white p-6 rounded-lg shadow">
        <h3 class="text-lg font-semibold mb-4">Recent Top-up Requests</h3>
        <table class="w-full text-left">
            <thead>
                <tr>
                    <th class="py-2">User</th>
                    <th class="py-2">Amount</th>
                    <th class="py-2">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($recentTopups as $topup)
                    <tr>
                        <td class="py-2">{{ $topup->user->name ?? 'N/A' }}</td>
                        <td class="py-2">{{ $topup->amount }}</td>
                        <td class="py-2">{!! status_badge($topup->status) !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
