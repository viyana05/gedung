<?php
// Mengambil nilai 'indexarray' dari URL
$id = $_GET['indexarray'] ?? 0;
$id = is_numeric($id) && isset($pesan[$id]) ? $id : 0;

// Daftar gedung beserta harga sewanya per hari
require 'datadummy.php';

// Menentukan gedung yang dipilih berdasarkan input form atau default dari daftar berdasarkan indexarray
$pilih_gedung = $_POST['ruangan'] ?? $pesan[$id][0];

// Mendapatkan harga gedung yang dipilih
$pilih_harga = array_column($pesan, 1, 0)[$pilih_gedung] ?? 0; //mencari yg kita pilih

// Mengecek apakah opsi catering dipilih oleh pengguna
$catering = isset($_POST['catering']);

// Mengambil durasi sewa
$durasi = $_POST['durasi'] ?? '';

// Inisialisasi total pembayaran
$total_bayar = 0;
$errors = []; // menyimpan semrntara

// Validasi Form menggunakan numeric, jika tidak maka akan tampil error 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!is_numeric($durasi) || $durasi <= 0) {
        $errors[] = "Durasi harus berupa angka lebih dari 0";
    }

    // menggnakan numeric, jika tidak maka akan tampil error
    if (!isset($_POST['identitas']) || !is_numeric($_POST['identitas']) || strlen($_POST['identitas']) !== 16) {
        $errors[] = "Nomor Identitas harus berupa 16 digit angka.";
    }

    if (empty($errors)) {
        $total_harga_gedung = $pilih_harga * $durasi;
        $diskon = ($durasi >= 3) ? 0.1 * $total_harga_gedung : 0;
        $biaya_catering = $catering ? 1200000 * $durasi : 0;
        $total_bayar = $total_harga_gedung - $diskon + $biaya_catering;
    }


    // Jika tombol "Pesan" ditekan, tampilkan alert dan redirect
    if (isset($_POST['simpan'])) { // Mengecek apakah tombol "Simpan" telah ditekan
        $nama = $_POST['nama']; // Mengambil input nama dari form
        $identitas = $_POST['identitas']; // Mengambil input nomor identitas dari form
        $gender = $_POST['gender']; // Mengambil input jenis kelamin dari form
        $ruangan = $_POST['ruangan']; // Mengambil input jenis gedung yang dipilih dari form
        $check = $catering ? 'Ya' : 'Tidak'; // Mengecek apakah pengguna memilih opsi catering 

        // Membuat array asosiatif untuk menyimpan detail pesanan
        $pesanan = [
            "Nama" => $nama,
            "Nomor Identitas" => $identitas,
            "Jenis Kelamin" => $gender,
            "Jenis gedung" => $ruangan,
            "Catering" => $check,
            "Durasi" => $durasi,
            "Diskon" => $diskon,
            "Total Bayar" => number_format($total_bayar, 0, ',', '.') // Format angka untuk tampilan lebih rapi
        ];

        // Membuat string detail pesanan untuk ditampilkan dalam alert
        $detail_pesanan = "Pesanan Berhasil!\n\n";
        foreach ($pesanan as $key => $value) { // Looping untuk menyusun detail pesanan
            $detail_pesanan .= "$key: $value\n"; // Menambahkan setiap item pesanan ke dalam string
        }

        // Menampilkan alert dengan detail pesanan dan mengarahkan kembali ke halaman utama
        echo "<script>
                alert(`$detail_pesanan`);
                window.location.href = 'index.php';
            </script>";
        exit(); // Menghentikan eksekusi kode setelah redirect
    }
}
?>



<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Form Pemesanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="card">
            <div class="card-header bg-dark text-white text-center">
                <h5>Form Pemesanan</h5>
            </div>
            <div class="card-body">
                <!-- Menampilkan error jika ada -->
                <?php if ($errors) { ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach ($errors as $error) { ?>
                                <li><?= $error ?></li>
                            <?php } ?>
                        </ul>
                    </div>
                <?php } ?>

                <!-- Form Pemesanan -->
                <form method="POST">
                    <!-- Input Nama Pemesan -->
                    <input type="text" class="form-control mb-3" name="nama" placeholder="Nama Pemesan" value="<?= $_POST['nama'] ?? '' ?>" required>

                    <!-- Input Jenis Kelamin -->
                    <div class="mb-3">
                        <label class="form-label">Jenis Kelamin</label><br>
                        <input class="form-check-input" type="radio" name="gender" value="Laki-laki" <?= ($_POST['gender'] ?? '') === 'Laki-laki' ? 'checked' : '' ?>> Laki-laki
                        <input class="form-check-input ms-3" type="radio" name="gender" value="Perempuan" <?= ($_POST['gender'] ?? '') === 'Perempuan' ? 'checked' : '' ?>> Perempuan
                    </div>

                    <!-- Input Nomor Identitas -->
                    <input type="text" class="form-control mb-3" name="identitas" placeholder="Nomor Identitas (16 digit)" value="<?= $_POST['identitas'] ?? '' ?>" required>

                    <!-- Dropdown Pilihan Gedung -->
                    <select class="form-select mb-3" name="ruangan" onchange="this.form.submit()"> <!-- jika dropdown dipilih maka akan tesubmit otomatis -->
                        <?php foreach ($pesan as $indexarray => $nilai) { ?>
                            <option value="<?= $nilai[0] ?>" <?= ($nilai[0] === $pilih_gedung) ? 'selected' : '' ?>>
                                <?= $nilai[0] ?>
                            </option>
                        <?php } ?>
                    </select>

                    <!-- Input Harga Gedung (Readonly) -->
                    <input type="text" class="form-control mb-3" name="harga" value="<?= number_format($pilih_harga, 0, ',', '.') ?>" readonly>

                    <!-- Input Tanggal Sewa -->
                    <input type="date" class="form-control mb-3" name="tanggal" value="<?= $_POST['tanggal'] ?? '' ?>" required>

                    <!-- Input Durasi Sewa -->
                    <input type="number" class="form-control mb-3" name="durasi" placeholder="Durasi Sewa (hari)" value="<?= $durasi ?>" required>

                    <!-- Checkbox untuk memilih catering -->
                    <div class="mb-3">
                        <input class="form-check-input" type="checkbox" name="catering" <?= $catering ? 'checked' : '' ?>> Termasuk catering (Rp 1.200.000)
                    </div>

                    <!-- Menampilkan Total Bayar -->
                    <input type="text" class="form-control mb-3" id="total" value="<?= $total_bayar ? number_format($total_bayar, 0, ',', '.') : '' ?>" placeholder="Total Bayar" readonly>

                    <!-- Tombol untuk menghitung total -->
                    <button type="submit" class="btn btn-primary">Hitung Total</button>

                    <!-- Tombol Simpan (akan mengarahkan ke hasil.php) -->
                    <button type="submit" name="simpan" class="btn btn-pink">Simpan</button>

                    <!-- Tombol Reset -->
                    <button type="reset" class="btn btn-danger">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>