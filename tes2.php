<?php

$daftar_menu = [
    1 => ["nama" => "Nasi Goreng", "harga" => 15000],
    2 => ["nama" => "Nasi Uduk",    "harga" => 12000],
    3 => ["nama" => "Ayam Bakar",   "harga" => 18000]
]; 

echo "=========================================\n";
echo "              DAFTAR MENU                \n";
echo "=========================================\n";
echo "[No]  Nama Menu             Harga        \n";
echo "-----------------------------------------\n";

foreach ($daftar_menu as $nomor => $menu) {
    $nama_rapi  = str_pad($menu['nama'], 22, " ");
    $harga_rapi = str_pad(number_format($menu['harga'], 0, ',', '.'), 11, " ", STR_PAD_LEFT);
    echo "[$nomor]  " . $nama_rapi . "Rp " . $harga_rapi . "\n";
}
echo "=========================================\n";

echo "\n";
echo "Masukkan Nama Customer: ";
$nama_customer = trim(fgets(STDIN));

$keranjang = [];

while (true) {
    echo "\n-----------------------------------------\n";
    echo "Pilih Nomor Menu (1-3): ";
    $pilihan = intval(trim(fgets(STDIN)));

    if (!isset($daftar_menu[$pilihan])) {
        echo "❌ MAAF, NOMOR MENU TIDAK ADA! Silakan pilih angka 1 sampai 3.\n";
        continue;
    }

    echo "Jumlah Beli: ";
    $jumlah = intval(trim(fgets(STDIN)));

    $keranjang[] = [
        'nama'     => $daftar_menu[$pilihan]['nama'],
        'harga'    => $daftar_menu[$pilihan]['harga'],
        'qty'      => $jumlah,
        'subtotal' => $daftar_menu[$pilihan]['harga'] * $jumlah
    ];

    echo "Mau tambah menu lain? (y/n): ";
    $tanya = strtolower(trim(fgets(STDIN)));

    if ($tanya !== 'y') {
        break;
    }
}

echo "\n=========================================\n";
echo "         ISI KERANJANG BELANJA           \n";
echo "=========================================\n";
echo "Nama Customer: " . $nama_customer . "\n";
echo "-----------------------------------------\n";

$grand_total = 0;

foreach ($keranjang as $item) {
    echo "- " . str_pad($item['nama'], 15, " ") . " (" . $item['qty'] . "x) = Rp " . number_format($item['subtotal'], 0, ',', '.') . "\n";
    
    $grand_total += $item['subtotal'];
}

echo "-----------------------------------------\n";
echo "TOTAL YANG HARUS DIBAYAR: Rp " . number_format($grand_total, 0, ',', '.') . "\n";
echo "=========================================\n";
?>