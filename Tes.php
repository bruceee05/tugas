<?php

// ===================================================================
// DATA DAFTAR PILIHAN BBM PERTAMINA (ARRAY MULTIDIMENSI)
// ===================================================================
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

// Mengulang (looping) untuk merapikan tampilan daftar BBM ke layar terminal
foreach ($daftar_bbm as $nomor => $bbm) {
    // str_pad digunakan untuk memberi spasi otomatis agar teks rata kiri/kanan
    $nama_rapi  = str_pad($bbm['nama'], 22, " ");
    
    // COMMENT: memformat angka Rupiah menjadi bulat tanpa koma desimal (, 0)
    // dan otomatis memasang tanda titik setiap kelipatan 3 angka dari belakang (ribuan).
    $harga_rapi = str_pad(number_format($bbm['harga'], 0, ',', '.'), 11, " ", STR_PAD_LEFT);
    echo "[$nomor]  " . $nama_rapi . "Rp " . $harga_rapi . "\n";
}
echo "=========================================\n";

echo "\n";

// ===================================================================
// PROSES 1: PILIHAN JENIS BBM (ALUR POS)
// ===================================================================
while (true) {
    echo "Pilihan BBM (1-4): ";
    $input_pilihan = trim(fgets(STDIN));

    // Validasi: Cek apakah inputan kosong, bukan angka, atau nomornya gak ada di daftar bbm
    if ($input_pilihan === '' || !ctype_digit($input_pilihan) || !isset($daftar_bbm[intval($input_pilihan)])) {
        echo "\n❌ MAAF, NOMOR BBM TIDAK TERSEDIA! Silakan pilih angka 1 sampai 4.\n";
        continue; // Mengulang loop pertanyaan pilihan BBM jika salah input
    }

    $pilihan = intval($input_pilihan); // Mengubah input teks menjadi angka murni
    break; // Lolos validasi, keluar dari loop BBM
}

// ===================================================================
// PROSES 2: TENTUKAN NOMINAL BELI (ALUR POS)
// ===================================================================
$harga_per_liter = $daftar_bbm[$pilihan]['harga'];

while (true) {
    echo "Mau beli berapa (Rp)? ";
    $input_nominal = trim(fgets(STDIN));

    // Validasi dasar: Harus angka bulat positif dan gak boleh langsung di-enter kosong
    if ($input_nominal === '' || !ctype_digit($input_nominal) || intval($input_nominal) <= 0) {
        echo "❌ Masukkan nominal yang valid.\n";
        continue; // Mengulang pertanyaan jika input salah
    }

    $nominal = intval($input_nominal);
    
    // Rumus Matematika: Nominal rupiah dibagi harga bbm untuk mencari takaran liter
    $liter_didapat = $nominal / $harga_per_liter;
    $uang_terpakai = $nominal; // Nominal ini yang disimpan sebagai total tagihan belanja bbm
    break; // Keluar loop nominal
}

// Menampilkan total belanjaan yang harus dibayar sebelum kasir memasukkan uang tunai
echo "\n-----------------------------------------\n";
echo "TOTAL YANG HARUS DIBAYAR: Rp " . number_format($uang_terpakai, 0, ',', '.') . "\n";
echo "-----------------------------------------\n";

// ===================================================================
// PROSES 3: INPUT UANG BAYAR & VALIDASI KECUKUPAN (ALUR POS)
// ===================================================================
while (true) {
    echo "Masukkan Uang Tunai / Bayar (Rp): ";
    $input_uang = trim(fgets(STDIN));

    // Filter anti-karakter huruf dan enter kosong
    if ($input_uang === '' || !ctype_digit($input_uang) || intval($input_uang) <= 0) {
        echo "❌ Input tidak valid. Masukkan angka bulat positif saja.\n\n";
        continue;
    }

    $uang = intval($input_uang);

    // LOGIKA PENGECEKAN UANG KURANG
    // Jika uang yang dibayarkan customer LEBIH KECIL ('<') dari total tagihan bbm...
    if ($uang < $uang_terpakai) {
        echo "\n❌ MAAF, UANG ANDA KURANG! Total tagihan adalah Rp " . number_format($uang_terpakai, 0, ',', '.') . "\n";
        echo "Silakan masukkan nominal uang bayar yang cukup.\n\n";
        continue; // Menggunakan 'continue' agar program balik nanya ulang input uangnya tanpa mati
    }

    break; // Uang sudah pas atau lebih, keluar dari loop pembayaran
}

// Rumus mencari uang kembalian akhir
$kembalian = $uang - $uang_terpakai;

// ===================================================================
// OUTPUT: CETAK STRUK NOTA BBM FINAL
// ===================================================================
echo "\n=========================================\n";
echo "             STRUK NOTA BBM              \n";
echo "=========================================\n";
echo "BBM Pilihan    : " . $daftar_bbm[$pilihan]['nama'] . "\n";

// Menggunakan ', 0' karena nominal harga rupiah berupa angka bulat tanpa desimal
echo "Harga / Liter  : Rp " . number_format($harga_per_liter, 0, ',', '.') . "\n";
echo "-----------------------------------------\n";
echo "Uang Bayar     : Rp " . number_format($uang_terpakai, 0, ',', '.') . "\n";

// 🎯 COMMENT: PENGGANTI TRIK '+ 0' YANG ERROR KEMARIN
// floatval() bertugas otomatis membuang angka 0 yang gak berguna di belakang koma (misal 2.000 jadi 2 murni).
// str_replace() dipakai untuk mengubah titik desimal bawaan komputer menjadi koma standar Indonesia (misal 1.5 jadi 1,5).
$liter_bersih = str_replace('.', ',', floatval($liter_didapat));
echo "Bensin Didapat : " . $liter_bersih . " Liter\n"; 

echo "=========================================\n";

// Menggunakan ', 0' agar tampilan uang diterima dan kembalian tercetak rapi format Rupiah
echo "Uang Diterima  : Rp " . number_format($uang, 0, ',', '.') . "\n";
echo "Kembalian      : Rp " . number_format($kembalian, 0, ',', '.') . "\n";
echo "=========================================\n";
?>