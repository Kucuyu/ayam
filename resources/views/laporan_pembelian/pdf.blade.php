<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <h3>Laporan Pembelian</h3>
    <p>Periode: {{ $awal }} s/d {{ $akhir }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Karyawan</th>
                <th>Total Item</th>
                <th>Total Harga</th>
                <th>Total Pembelian</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
            <tr>
                <td>{{ $row['DT_RowIndex'] }}</td>
                <td>{{ $row['tanggal'] }}</td>
                <td>{{ $row['karyawan'] }}</td>
                <td>{{ $row['total_item'] }}</td>
                <td>{{ $row['total_harga'] }}</td>
                <td>{{ $row['pembelian'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
