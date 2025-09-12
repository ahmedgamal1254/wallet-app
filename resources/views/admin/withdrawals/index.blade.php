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

@section('title', 'Withdrawal Requests')

@section('content')
<div class="bg-white p-6 rounded-lg shadow">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold mb-4">Withdrawal Requests</h3>
        <div>
            <a href="{{ route('admin.withdrawals.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">Create Withdrawal Request</a>
        </div>
    </div>

    <table class="w-full text-left border border-gray-200 rounded-lg overflow-hidden shadow-sm">
        <thead class="bg-gray-100 text-gray-700">
            <tr>
                <th class="py-3 px-4">Requester</th>
                <th class="py-3 px-4">Amount</th>
                <th class="py-3 px-4">Status</th>
                <th class="py-3 px-4">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse ($withdrawals as $withdrawal)
                <tr class="hover:bg-gray-50">
                    <!-- Requester -->
                    <td class="py-3 px-4 flex items-center space-x-2">
                        <i class="fa-solid fa-user text-gray-500"></i>
                        <span>{{ $withdrawal->requester->name ?? 'N/A' }}</span>
                    </td>

                    <!-- Amount -->
                    <td class="py-3 px-4 text-green-600 font-semibold">
                        {{ number_format($withdrawal->amount, 2) }} EGP
                    </td>

                    <!-- Status -->
                    <td class="py-3 px-4">
                        {!! status_badge($withdrawal->status) !!}
                    </td>

                    <!-- Actions -->
                    <td class="py-3 px-4">
                        <a href="{{ route('admin.withdrawals.show', $withdrawal) }}"
                        class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                            View
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="py-6 text-center text-gray-500">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        No withdrawal requests found.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="mt-4">
        {{ $withdrawals->links() }}
    </div>
</div>
@endsection
