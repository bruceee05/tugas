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

// Input uang tunai, validasi hanya angka
while (true) {
    echo "Masukkan Uang Tunai (Rp): ";
    $input_uang = trim(fgets(STDIN));

    if ($input_uang === '' || !ctype_digit($input_uang) || intval($input_uang) <= 0) {
        echo "Input tidak valid. Masukkan angka bulat positif saja.\n";
        continue;
    }

    $uang = intval($input_uang);
    break;
}

// Pilih jenis BBM dengan validasi input
while (true) {
    echo "Pilihan BBM (1-4): ";
    $input_pilihan = trim(fgets(STDIN));

    if ($input_pilihan === '' || !ctype_digit($input_pilihan) || !isset($daftar_bbm[intval($input_pilihan)])) {
        echo "\n❌ MAAF, NOMOR BBM TIDAK TERSEDIA! Silakan pilih angka 1 sampai 4.\n";
        continue;
    }

    $pilihan = intval($input_pilihan);
    break;
}

// Minta nominal pembelian (Rp) dan pastikan tidak melebihi uang
while (true) {
    echo "Mau beli berapa (Rp)? ";
    $input_nominal = trim(fgets(STDIN));

    if ($input_nominal === '' || !ctype_digit($input_nominal) || intval($input_nominal) <= 0) {
        echo "Masukkan nominal yang valid.\n";
        continue;
    }

    $nominal = intval($input_nominal);
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