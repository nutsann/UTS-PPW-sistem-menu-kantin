
<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

// READ: Menampilkan data menu, pencarian, dan filter kategori
Route::get('/menu', function (Request $request) {
    try {
        $pdo = DB::connection()->getPdo();

        $keyword = trim(strip_tags($request->query('q', '')));
        $kategori = $request->query('kategori_menu_id');

        $sql = "SELECT m.*, k.nama_kategori
                FROM menu m
                JOIN kategori_menu k
                ON m.kategori_menu_id = k.id
                WHERE 1=1";

        $params = [];

        if ($keyword !== '') {
            $sql .= " AND (
                m.kode_menu LIKE ?
                OR m.nama_menu LIKE ?
                OR m.barcode LIKE ?
            )";

            $cari = "%{$keyword}%";
            $params[] = $cari;
            $params[] = $cari;
            $params[] = $cari;
        }

        if ($kategori !== null && $kategori !== '') {
            $sql .= " AND m.kategori_menu_id = ?";
            $params[] = $kategori;
        }

        $sql .= " ORDER BY m.id DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $stmtKategori = $pdo->prepare(
            "SELECT * FROM kategori_menu ORDER BY nama_kategori"
        );
        $stmtKategori->execute();
        $kategoris = $stmtKategori->fetchAll(PDO::FETCH_ASSOC);

        return view('menu', compact(
            'menus',
            'kategoris',
            'keyword',
            'kategori'
        ));

    } catch (\PDOException $e) {
        report($e);

        return response(
            'Data menu tidak dapat ditampilkan. Silakan coba kembali.',
            503
        );
    }
})->name('menu.index');


// CREATE: Menambahkan menu baru
Route::post('/menu', function (Request $request) {
    $input = $request->all();

    $input['kode_menu'] = trim(strip_tags($input['kode_menu'] ?? ''));
    $input['nama_menu'] = trim(strip_tags($input['nama_menu'] ?? ''));
    $input['barcode'] = trim(strip_tags($input['barcode'] ?? ''));

    $validator = Validator::make($input, [
        'kode_menu' => 'required|alpha_num|size:10|unique:menu,kode_menu',
        'nama_menu' => 'required|string|min:3|max:100',
        'barcode' => 'required|string|min:5|max:30|unique:menu,barcode',
        'harga' => 'required|integer|min:1000|max:100000',
        'kategori_menu_id' => 'required|integer|exists:kategori_menu,id',
    ], [
        'kode_menu.required' => 'Kode menu wajib diisi.',
        'kode_menu.alpha_num' => 'Kode menu hanya boleh berisi huruf dan angka.',
        'kode_menu.size' => 'Kode menu harus tepat 10 karakter.',
        'kode_menu.unique' => 'Kode menu sudah digunakan.',
        'nama_menu.required' => 'Nama menu wajib diisi.',
        'nama_menu.min' => 'Nama menu minimal 3 karakter.',
        'barcode.unique' => 'Barcode sudah digunakan.',
        'harga.min' => 'Harga minimal Rp1.000.',
        'harga.max' => 'Harga maksimal Rp100.000.',
        'kategori_menu_id.exists' => 'Kategori yang dipilih tidak tersedia.',
    ]);

    if ($validator->fails()) {
        return redirect('/menu')
            ->withErrors($validator)
            ->withInput();
    }

    $data = $validator->validated();

    try {
        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare(
            "INSERT INTO menu
            (kode_menu, nama_menu, barcode, harga, kategori_menu_id, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())"
        );

        $stmt->execute([
            $data['kode_menu'],
            $data['nama_menu'],
            $data['barcode'],
            $data['harga'],
            $data['kategori_menu_id'],
        ]);

        return redirect('/menu')
            ->with('berhasil', 'Menu berhasil ditambahkan.');

    } catch (\PDOException $e) {
        report($e);

        if ($e->getCode() === '23000') {
            return redirect('/menu')
                ->withInput()
                ->with('gagal', 'Kode menu atau barcode sudah digunakan.');
        }

        return redirect('/menu')
            ->withInput()
            ->with('gagal', 'Menu gagal ditambahkan. Silakan coba kembali.');
    }
})->name('menu.store');


// EDIT: Menampilkan formulir edit
Route::get('/menu/{id}/edit', function ($id) {
    try {
        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare(
            "SELECT * FROM menu WHERE id = ?"
        );
        $stmt->execute([$id]);

        $menu = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$menu) {
            return redirect('/menu')
                ->with('gagal', 'Data menu tidak ditemukan.');
        }

        $stmtKategori = $pdo->prepare(
            "SELECT * FROM kategori_menu ORDER BY nama_kategori"
        );
        $stmtKategori->execute();

        $kategoris = $stmtKategori->fetchAll(PDO::FETCH_ASSOC);

        return view('edit-menu', compact('menu', 'kategoris'));

    } catch (\PDOException $e) {
        report($e);

        return redirect('/menu')
            ->with('gagal', 'Form edit tidak dapat ditampilkan.');
    }
})->whereNumber('id')->name('menu.edit');


// UPDATE: Menyimpan perubahan menu
Route::put('/menu/{id}', function (Request $request, $id) {
    $input = $request->all();

    $input['kode_menu'] = trim(strip_tags($input['kode_menu'] ?? ''));
    $input['nama_menu'] = trim(strip_tags($input['nama_menu'] ?? ''));
    $input['barcode'] = trim(strip_tags($input['barcode'] ?? ''));

    $validator = Validator::make($input, [
        'kode_menu' => [
            'required',
            'alpha_num',
            'size:10',
            Rule::unique('menu', 'kode_menu')->ignore($id),
        ],
        'nama_menu' => 'required|string|min:3|max:100',
        'barcode' => [
            'required',
            'string',
            'min:5',
            'max:30',
            Rule::unique('menu', 'barcode')->ignore($id),
        ],
        'harga' => 'required|integer|min:1000|max:100000',
        'kategori_menu_id' => 'required|integer|exists:kategori_menu,id',
    ]);

    if ($validator->fails()) {
        return redirect("/menu/{$id}/edit")
            ->withErrors($validator)
            ->withInput();
    }

    $data = $validator->validated();

    try {
        $pdo = DB::connection()->getPdo();

        $stmtCek = $pdo->prepare(
            "SELECT id FROM menu WHERE id = ?"
        );
        $stmtCek->execute([$id]);

        if (!$stmtCek->fetch()) {
            return redirect('/menu')
                ->with('gagal', 'Data menu tidak ditemukan.');
        }

        $stmt = $pdo->prepare(
            "UPDATE menu
             SET kode_menu = ?,
                 nama_menu = ?,
                 barcode = ?,
                 harga = ?,
                 kategori_menu_id = ?,
                 updated_at = NOW()
             WHERE id = ?"
        );

        $stmt->execute([
            $data['kode_menu'],
            $data['nama_menu'],
            $data['barcode'],
            $data['harga'],
            $data['kategori_menu_id'],
            $id,
        ]);

        return redirect('/menu')
            ->with('berhasil', 'Menu berhasil diperbarui.');

    } catch (\PDOException $e) {
        report($e);

        if ($e->getCode() === '23000') {
            return redirect("/menu/{$id}/edit")
                ->withInput()
                ->with('gagal', 'Kode menu atau barcode sudah digunakan.');
        }

        return redirect("/menu/{$id}/edit")
            ->withInput()
            ->with('gagal', 'Menu gagal diperbarui.');
    }
})->whereNumber('id')->name('menu.update');


// DELETE: Menghapus menu
Route::delete('/menu/{id}', function ($id) {
    try {
        $pdo = DB::connection()->getPdo();

        $stmt = $pdo->prepare(
            "DELETE FROM menu WHERE id = ?"
        );

        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            return redirect('/menu')
                ->with('berhasil', 'Menu berhasil dihapus.');
        }

        return redirect('/menu')
            ->with('gagal', 'Data menu tidak ditemukan.');

    } catch (\PDOException $e) {
        report($e);

        return redirect('/menu')
            ->with('gagal', 'Menu gagal dihapus.');
    }
})->whereNumber('id')->name('menu.destroy');
