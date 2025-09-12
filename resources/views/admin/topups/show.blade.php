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

@section('title', 'Top-up Request Details')

@section('content')
<div class="bg-white p-6 rounded-lg shadow">
    <h3 class="text-lg font-semibold mb-4">Top-up Request Details</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <p><strong>User:</strong> {{ $topup->user->name ?? 'N/A' }}</p>
            <p><strong>Amount:</strong> {{ $topup->amount }}</p>
            <p><strong>Status:</strong> {!! status_badge($topup->status) !!}</p>
            <p><strong>Processed By:</strong> {{ $topup->processedBy->name ?? 'N/A' }}</p>
            <p><strong>Created At:</strong> {{ $topup->created_at->format('Y-m-d H:i:s') }}</p>
        </div>
    </div>

    @if ($topup->status === 'pending')
        <div class="mt-6 flex space-x-4">
            <form action="{{ route('admin.topups.approve', $topup) }}" method="POST">
                @csrf
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Approve</button>
            </form>
            <form action="{{ route('admin.topups.reject', $topup) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="reason" class="block text-sm font-medium text-gray-700">Reason for Rejection</label>
                    <textarea name="reason" id="reason" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" required></textarea>
                </div>
                <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700">Reject</button>
            </form>
        </div>
    @endif
</div>
@endsection
