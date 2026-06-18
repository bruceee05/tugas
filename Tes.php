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

echo "Pilihan BBM (1-4): ";
$pilihan = intval(trim(fgets(STDIN)));

if (!isset($daftar_bbm[$pilihan])) {
    echo "\n❌ MAAF, NOMOR BBM TIDAK TERSEDIA!\n";
    exit; 
}

echo "Masukkan Nominal Uang (Rp): ";
$uang = intval(trim(fgets(STDIN))); 

$liter_didapat = round($uang / $daftar_bbm[$pilihan]['harga'], 2);

echo "\n=========================================\n";
echo "             STRUK NOTA BBM              \n";
echo "=========================================\n";
echo "BBM Pilihan    : " . $daftar_bbm[$pilihan]['nama'] . "\n";
echo "Harga / Liter  : Rp " . number_format($daftar_bbm[$pilihan]['harga'], 0, ',', '.') . "\n";
echo "-----------------------------------------\n";
echo "Uang Bayar     : Rp " . number_format($uang, 0, ',', '.') . "\n";
echo "Bensin Didapat : " . $liter_didapat . " Liter\n"; 
echo "=========================================\n";
?>