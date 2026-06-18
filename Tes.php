<?php

$daftar_bbm = [
    1 => ["nama" => "Pertalite",       "harga" => 10000],
    2 => ["nama" => "Pertamax",        "harga" => 16250], 
    3 => ["nama" => "Pertamax Turbo",  "harga" => 20750], 
    4 => ["nama" => "Pertamina Dex",   "harga" => 24800]  
]; 

echo "=========================================\n";
echo "         APLIKASI KASIR PERTAMINA        \n";
echo "=========================================\n";
echo "[No]  Jenis BBM             Harga/Liter  \n";
echo "-----------------------------------------\n";

foreach ($daftar_bbm as $nomor => $bbm) {
    $nama_rapi  = str_pad($bbm['nama'], 22, " ");
    $harga_rapi = str_pad(number_format($bbm['harga'], 0, ',', '.'), 11, " ", STR_PAD_LEFT);
    echo "[$nomor]  " . $nama_rapi . "Rp " . $harga_rapi . "\n";
}
echo "=========================================\n";

echo "\n";

// Minta uang terlebih dahulu
echo "Masukkan Uang Tunai (Rp): ";
$uang = intval(trim(fgets(STDIN)));

// Pilih jenis BBM
echo "Pilihan BBM (1-4): ";
$pilihan = intval(trim(fgets(STDIN)));

if (!isset($daftar_bbm[$pilihan])) {
    echo "\n❌ MAAF, NOMOR BBM TIDAK TERSEDIA!\n";
    exit;
}

// Minta nominal pembelian (Rp) dan pastikan tidak melebihi uang
while (true) {
    echo "Mau beli berapa (Rp)? ";
    $nominal = intval(trim(fgets(STDIN)));

    if ($nominal <= 0) {
        echo "Masukkan nominal yang valid.\n";
        continue;
    }

    if ($nominal > $uang) {
        echo "\n❌ UANG TIDAK CUKUP. Saldo Anda: Rp " . number_format($uang, 0, ',', '.') . "\n";
        echo "Silakan masukkan nominal yang lebih kecil.\n";
        continue;
    }

    $liter_didapat = round($nominal / $daftar_bbm[$pilihan]['harga'], 4);
    $uang_terpakai = $nominal;
    break;
}

echo "\n=========================================\n";
echo "             STRUK NOTA BBM              \n";
echo "=========================================\n";
echo "BBM Pilihan    : " . $daftar_bbm[$pilihan]['nama'] . "\n";
echo "Harga / Liter  : Rp " . number_format($daftar_bbm[$pilihan]['harga'], 0, ',', '.') . "\n";
echo "-----------------------------------------\n";
echo "Uang Bayar     : Rp " . number_format($uang_terpakai, 0, ',', '.') . "\n";
echo "Bensin Didapat : " . $liter_didapat . " Liter\n"; 
echo "=========================================\n";

$kembalian = $uang - $uang_terpakai;
echo "Uang Diterima  : Rp " . number_format($uang, 0, ',', '.') . "\n";
echo "Kembalian       : Rp " . number_format($kembalian, 0, ',', '.') . "\n";
?>