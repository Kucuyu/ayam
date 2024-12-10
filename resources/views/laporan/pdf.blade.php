<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
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
    <h3>Laporan Penjualan</h3>
    <p>Periode: {{ $awal }} s/d {{ $akhir }}</p>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Total Item</th>
                <th>Total Harga</th>
                <th>Status Member</th>
                <th>Poin Member</th>
                <th>Kasir</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $row)
            <tr>
                <td>{{ $row['DT_RowIndex'] }}</td>
                <td>{{ $row['tanggal'] }}</td>
                <td>{{ $row['nama'] }}</td>
                <td>{{ $row['total_item'] }}</td>
                <td>{{ $row['total_harga'] }}</td>
                <td>{{ $row['status_member'] }}</td>
                <td>{{ $row['poin_member'] }}</td>
                <td>{{ $row['kasir'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>