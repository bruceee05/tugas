<?php
// ===================================================================
// DATA DAFTAR MENU RESTORAN
// ===================================================================
$daftar_menu = [
    1 => ["nama" => "Nasi Goreng", "harga" => 15000],
    2 => ["nama" => "Nasi Uduk",    "harga" => 12000],
    3 => ["nama" => "Ayam Bakar",   "harga" => 18000]
]; 

echo "\n=========================================\n";
echo "              DAFTAR MENU                \n";
echo "=========================================\n";
echo "[No]  Nama Menu             Harga        \n";
echo "-----------------------------------------\n";

foreach ($daftar_menu as $nomor => $menu) {
    // str_pad() biasa: Matok kolom nama 22 karakter, sisa ruang kosong ditambah spasi di KANAN (Rata Kiri)
    $nama_rapi  = str_pad($menu['nama'], 22, " ");
    
    // number_format & ',','.' buat maksa format Rupiah (ribuan pakai titik).
    // STR_PAD_LEFT (Konstanta huruf besar) buat matok kolom harga 11 karakter, spasi di KIRI (Rata Kanan)
    $harga_rapi = str_pad(number_format($menu['harga'], 0, ',', '.'), 11, " ", STR_PAD_LEFT);
    
    echo "[$nomor]  " . $nama_rapi . "Rp " . $harga_rapi . "\n";
}
echo "=========================================\n";

echo "Masukkan Nama Customer: ";
// fgets buat buka gerbang input keyboard, trim() buat ngebabat "enter gaib" (\n) pas kasir pencet enter
$nama_customer = trim(fgets(STDIN));

$keranjang = [];

// ===================================================================
// PROSES BELANJA (PERULANGAN UTAMA)
// ===================================================================
while (true) {
    // Loop menu utama + filter biar ga crash kalau kasir iseng enter kosong atau ngetik huruf
    while (true) {
        echo "\nPilih Nomor Menu (1-3): ";
        $input_pilihan = trim(fgets(STDIN));

        // === Cek ketat nilai & tipe data. !ctype_digit artinya BUKAN murni angka bulat. isset buat cek nomor di array
        if ($input_pilihan === '' || !ctype_digit($input_pilihan) || !isset($daftar_menu[intval($input_pilihan)])) {
            echo "❌ MAAF, NOMOR MENU TIDAK ADA! Silakan pilih angka 1 sampai 3.\n";
            continue; // Mengulang pertanyaan pilih menu saja
        }

        // intval mengubah teks inputan keyboard menjadi angka murni agar bisa dibaca laci array
        $pilihan = intval($input_pilihan);
        break; // Keluar dari loop filter menu, lanjut ke jumlah beli
    }

    echo "Jumlah Beli: ";
    // Nested Loop (Loop bersarang): Khusus mengunci dan mengisolasi error di jumlah beli aja
    while (true) {
        echo "Jumlah Beli (pcs/bungkus - masukkan angka): ";
        $input_jumlah = trim(fgets(STDIN));

        // Validasi ketat biar ga bisa diisi huruf, minus, atau enter kosong (=== dan !ctype_digit)
        if ($input_jumlah === '' || !ctype_digit($input_jumlah) || intval($input_jumlah) <= 0) {
            echo "Input jumlah tidak valid. Masukkan angka bulat positif saja.\n";
            continue; // Mengulang pertanyaan jumlah beli saja
        }

        $jumlah = intval($input_jumlah);
        break; // Matikan loop jumlah beli, lanjut masukkan ke keranjang
    }

    $keranjang[] = [
        'nama'     => $daftar_menu[$pilihan]['nama'],
        'harga'    => $daftar_menu[$pilihan]['harga'],
        'qty'      => $jumlah,
        'subtotal' => $daftar_menu[$pilihan]['harga'] * $jumlah
    ];

    echo "Mau tambah menu lain? (y/n): ";
    // strtolower buat otomatis ngubah huruf kapital (Y besar) jadi kecil (y kecil) biar ga eror
    $tanya = strtolower(trim(fgets(STDIN)));

    // Operator !== buat cek ketat nilai & tipe data. Kalau kasir ga ngetik 'y', stop belanja
    if ($tanya !== 'y') {
        break; // break cuma menghentikan perulangan belanja, kode cetak struk di bawah tetap jalan
    }

    echo "\n=========================================\n";
    echo "              DAFTAR MENU                \n";
    echo "=========================================\n";
    foreach ($daftar_menu as $nomor => $menu) {
        $nama_rapi  = str_pad($menu['nama'], 22, " ");
        $harga_rapi = str_pad(number_format($menu['harga'], 0, ',', '.'), 11, " ", STR_PAD_LEFT);
        echo "[$nomor]  " . $nama_rapi . "Rp " . $harga_rapi . "\n";
    }
    echo "=========================================\n";
}

// ===================================================================
// HITUNG TOTAL BELANJAAN
// ===================================================================
$grand_total = 0;
foreach ($keranjang as $item) {
    $grand_total += $item['subtotal']; // Estafet penjumlahan subtotal ke grand_total
}

echo "\n-----------------------------------------\n";
echo "TOTAL YANG HARUS DIBAYAR: Rp " . number_format($grand_total, 0, ',', '.') . "\n";
echo "Masukkan Uang Bayar (Rp): ";
$uang_bayar = intval(trim(fgets(STDIN)));

// Validasi pembayaran uang tunai
if ($uang_bayar < $grand_total) {
    echo "\n❌ MAAF, UANG ANDA KURANG! Transaksi dibatalkan.\n";
    exit; // exit langsung mematikan total seluruh program detik ini juga biar struk toko ga kecetak
}

// Rumus matematika pengurangan untuk mencari uang kembalian
$kembalian = $uang_bayar - $grand_total;

// ===================================================================
// OUTPUT STRUK NOTA FINAL
// ===================================================================
echo "\n=========================================\n"; // \n di depan berfungsi sebagai Enter otomatis
echo "         ISI KERANJANG BELANJA           \n";
echo "=========================================\n";
// ucwords() bikin huruf pertama tiap kata nama customer otomatis jadi kapital/rapi
echo "Nama Customer: " . ucwords($nama_customer) . "\n";
echo "-----------------------------------------\n";

foreach ($keranjang as $item) {
    echo "- " . str_pad($item['nama'], 15, " ") . " (" . $item['qty'] . "x) = Rp " . number_format($item['subtotal'], 0, ',', '.') . "\n";
}

echo "-----------------------------------------\n";
echo "Total Belanja  : Rp " . number_format($grand_total, 0, ',', '.') . "\n"; // Cetak total belanja rapi
echo "Uang Bayar     : Rp " . number_format($uang_bayar, 0, ',', '.') . "\n";  // Cetak uang yang diinput kasir
echo "Kembalian      : Rp " . number_format($kembalian, 0, ',', '.') . "\n";   // Cetak hasil pengurangan nominal kembalian
echo "=========================================\n";
?>