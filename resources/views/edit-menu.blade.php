
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu Kantin</title>
</head>
<body>

    <h1>Edit Menu Kantin</h1>

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

    <form action="{{ route('menu.update', $menu['id']) }}" method="POST">
        @csrf
        @method('PUT')

        <p>
            <label>Kode Menu:</label><br>
            <input type="text" name="kode_menu"
                value="{{ old('kode_menu', $menu['kode_menu']) }}">
        </p>

        <p>
            <label>Nama Menu:</label><br>
            <input type="text" name="nama_menu"
                value="{{ old('nama_menu', $menu['nama_menu']) }}">
        </p>

        <p>
            <label>Barcode:</label><br>
            <input type="text" name="barcode"
                value="{{ old('barcode', $menu['barcode']) }}">
        </p>

        <p>
            <label>Harga:</label><br>
            <input type="number" name="harga"
                value="{{ old('harga', $menu['harga']) }}">
        </p>

        <p>
            <label>Kategori:</label><br>
            <select name="kategori_menu_id">
                @foreach ($kategoris as $kategoriItem)
                    <option value="{{ $kategoriItem['id'] }}"
                        {{ old('kategori_menu_id', $menu['kategori_menu_id']) == $kategoriItem['id'] ? 'selected' : '' }}>
                        {{ $kategoriItem['nama_kategori'] }}
                    </option>
                @endforeach
            </select>
        </p>

        <button type="submit">Simpan Perubahan</button>
    </form>

    <p>
        <a href="{{ route('menu.index') }}">Kembali ke Daftar Menu</a>
    </p>

</body>
</html>
