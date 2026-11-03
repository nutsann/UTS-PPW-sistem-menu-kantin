
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Menu Kantin</title>
</head>
<body>

    <h1>Sistem Menu Kantin</h1>

    <h2>Tambah Menu</h2>

    @if (session('berhasil'))
        <p>{{ session('berhasil') }}</p>
    @endif

    @if (session('gagal'))
        <p>{{ session('gagal') }}</p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <form action="{{ route('menu.store') }}" method="POST">
        @csrf

        <p>
            <label>Kode Menu:</label><br>
            <input type="text" name="kode_menu"
                value="{{ old('kode_menu') }}">
        </p>

        <p>
            <label>Nama Menu:</label><br>
            <input type="text" name="nama_menu"
                value="{{ old('nama_menu') }}">
        </p>

        <p>
            <label>Barcode:</label><br>
            <input type="text" name="barcode"
                value="{{ old('barcode') }}">
        </p>

        <p>
            <label>Harga:</label><br>
            <input type="number" name="harga"
                value="{{ old('harga') }}">
        </p>

        <p>
            <label>Kategori:</label><br>
            <select name="kategori_menu_id">
                <option value="">-- Pilih Kategori --</option>

                @foreach ($kategoris as $kategoriItem)
                    <option value="{{ $kategoriItem['id'] }}"
                        {{ old('kategori_menu_id') == $kategoriItem['id'] ? 'selected' : '' }}>
                        {{ $kategoriItem['nama_kategori'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Simpan Menu</button>
    </form>

    <hr>

    <h2>Daftar Menu Kantin</h2>

    <form action="{{ route('menu.index') }}" method="GET">
        <p>
            <label>Cari Menu:</label><br>
            <input type="text" name="q"
                placeholder="Kode, nama, atau barcode"
                value="{{ $keyword }}">
            <button type="submit">Cari</button>
        </p>

        <p>
            <label>Filter Kategori:</label><br>
            <select name="kategori_menu_id">
                <option value="">Semua Kategori</option>

                @foreach ($kategoris as $kategoriItem)
                    <option value="{{ $kategoriItem['id'] }}"
                        {{ $kategori == $kategoriItem['id'] ? 'selected' : '' }}>
                        {{ $kategoriItem['nama_kategori'] }}
                    </option>
                @endforeach
            </select>

            <button type="submit">Filter</button>
            <a href="{{ route('menu.index') }}">Reset</a>
        </p>
    </form>

    <table border="1" cellpadding="8">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode Menu</th>
                <th>Nama Menu</th>
                <th>Barcode</th>
                <th>Harga</th>
                <th>Status Harga</th>
                <th>Kategori</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($menus as $index => $menu)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $menu['kode_menu'] }}</td>
                    <td>{{ $menu['nama_menu'] }}</td>
                    <td>{{ $menu['barcode'] }}</td>
                    <td>Rp{{ number_format($menu['harga'], 0, ',', '.') }}</td>
                    <td>
                        @if (statusHarga($menu['harga']) == 'Premium')
                            Premium
                        @else
                            Reguler
                        @endif
                    </td>
                    <td>{{ $menu['nama_kategori'] }}</td>
                    <td>
                        <a href="{{ route('menu.edit', $menu['id']) }}">
                            Edit
                        </a>

                        <form action="{{ route('menu.destroy', $menu['id']) }}"
                            method="POST">
                            @csrf
                            @method('DELETE')

                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Data menu belum tersedia.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
