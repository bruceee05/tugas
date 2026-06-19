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

// ===================================================================
// PROSES INPUT NAMA CUSTOMER (ANTI-ANGKA & ANTI-KOSONG)
// ===================================================================
while (true) {
    echo "Masukkan Nama Customer: ";
    // PENJELASAN fgets() & STDIN: fgets() itu fungsinya buat ngebuka gerbang input keyboard di terminal. 
    // STDIN (Standard Input) adalah tujuannya, yaitu ngebaca apa pun yang lo ketik di keyboard saat program jalan.
    // PENJELASAN trim(): Pas lo beres ngetik nama terus pencet "Enter", komputer bakal ngebaca tombol Enter itu sebagai karakter gaib (\n).
    // Fungsi trim() ini gunanya buat ngebabat habis spasi liar di ujung teks dan ngebuang "Enter gaib" itu biar teksnya bersih murni.
    $input_nama = trim(fgets(STDIN));

    // PENJELASAN str_replace(): Menghapus spasi sementara agar ctype_alpha tidak menganggap spasi sebagai karakter haram. 
    // Isiannya: str_replace('yang dicari', 'diganti apa', 'di variabel mana'). Di sini spasi diganti string kosong ('').
    $cek_nama = str_replace(' ', '', $input_nama);

    // PENJELASAN Operator === (Identik/Sama Persis): Berbeda dengan == biasa, === ini ngeceknya ketat banget. 
    // Dia memastikan nilainya sama DAN tipe datanya juga harus sama (misal string sama-sama string).
    // Di sini $input_nama === '' artinya ngecek apakah kasir langsung asal pencet Enter (isinya string kosong murni).
    // PENJELASAN ctype_alpha() & tanda !: ctype_alpha buat mastiin teks murni huruf A-Z / a-z. 
    // Tanda ! artinya "TIDAK". Jadi dibaca: "Jika input nama kosong ATAU cek_nama BUKAN huruf murni", maka error.
    if ($input_nama === '' || !ctype_alpha($cek_nama)) {
        echo "❌ ERROR: Nama customer harus berupa huruf saja (Tanpa angka / simbol)!\n\n";
        continue; 
    }

    $nama_customer = $input_nama;
    break; 
}

$keranjang = [];

// ===================================================================
// PROSES BELANJA (PERULANGAN UTAMA)
// ===================================================================
while (true) {
    
    // Loop kecil 1: Mengunci validasi pemilihan nomor menu
    while (true) {
        echo "\nPilih Nomor Menu (1-3): ";
        $input_pilihan = trim(fgets(STDIN));

        // PENJELASAN isset(): Mengecek apakah nomor laci array di $daftar_menu itu beneran ada atau gak isinya. 
        // Jadi kalau kasir iseng ketik angka 5, program gak akan crash karena !isset bakal mendeteksi kalau laci nomor 5 itu kosong.
        if ($input_pilihan === '' || !ctype_digit($input_pilihan) || !isset($daftar_menu[intval($input_pilihan)])) {
            echo "❌ MAAF, NOMOR MENU TIDAK ADA! Silakan pilih angka 1 sampai 3.\n";
            continue; 
        }

        // PENJELASAN intval(): Mengubah teks inputan keyboard ("string") menjadi angka murni ("integer") agar bisa dibaca oleh sistem laci array PHP.
        $pilihan = intval($input_pilihan);
        break; 
    }

    // Loop kecil 2: Mengunci validasi jumlah beli (anti-huruf, anti-nol/minus)
    while (true) {
        echo "Jumlah Beli (pcs/bungkus): ";
        $input_jumlah = trim(fgets(STDIN));

        if ($input_jumlah === '' || !ctype_digit($input_jumlah) || intval($input_jumlah) <= 0) {
            echo "❌ Input jumlah tidak valid. Masukkan angka bulat positif saja (Min. 1).\n\n";
            continue; 
        }

        $jumlah = intval($input_jumlah);
        break; 
    }

    // PENJELASAN array [] (Push Data): Tanda [] kosong setelah variabel artinya menumpuk data baru ke baris paling bawah laci $keranjang. 
    // Di dalamnya kita bikin stiker label ('nama', 'harga', dll) biar datanya terkelompok rapi dan tidak saling menimpa.
    $keranjang[] = [
        'nama'     => $daftar_menu[$pilihan]['nama'],
        'harga'    => $daftar_menu[$pilihan]['harga'],
        'qty'      => $jumlah,
        'subtotal' => $daftar_menu[$pilihan]['harga'] * $jumlah 
    ];

    // 🎯 KUNCI MATI PERTANYAAN LO DI SINI (LOOP KECIL 3)
    while (true) {
        echo "Mau tambah menu lain? (y/n): ";
        // PENJELASAN strtolower(): Mengubah otomatis huruf kapital (Y besar) jadi huruf kecil (y kecil). 
        $tanya = strtolower(trim(fgets(STDIN)));

        // PENJELASAN in_array(): Fungsi untuk mengecek apakah sebuah teks ada di dalam daftar list array.
        // Di sini kita bikin list array kata suci bbm murni yaitu ['y', 'n']. 
        // Tanda ! artinya "TIDAK ADA". Jadi dibaca: "Jika inputan kasir BUKAN huruf y dan BUKAN huruf n..." maka ditolak mentah-mentah!
        if (!in_array($tanya, ['y', 'n'])) {
            echo "❌ INPUT SALAH! Cuma boleh ketik huruf 'y' (untuk tambah) atau 'n' (untuk selesai).\n\n";
            continue; // Memaksa kasir mengulang ketik jawaban (y/n) yang bener
        }

        break; // Jawaban bener (kalau gak 'y' pasti 'n'), keluar dari loop kecil pertanyaan
    }

    // Setelah lolos filter di atas, baru kita cek: Kalau kasir ngetik 'n', saatnya beneran break/berhenti belanja
    if ($tanya === 'n') {
        break; // Keluar dari loop belanja utama, lanjut hitung struk pembayran
    }

    // Menampilkan kembali papan menu agar kasir tidak usah scroll layar ke atas
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
// HITUNG TOTAL BELANJAAN (AKUMULASI)
// ===================================================================
$grand_total = 0;
// PENJELASAN foreach & kata kunci 'as': Membongkar isi laci array $keranjang yang banyak, 
// diambil satu per satu dari baris teratas, lalu baris tersebut dinamai alias sebagai variabel tunggal $item.
foreach ($keranjang as $item) {
    // PENJELASAN Operator += (Penjumlahan Estafet): Daripada nulis $grand_total = $grand_total + $item['subtotal'], 
    // disingkat pakai += biar keren khas anak RPL. Isinya bakal terus ditambahin numpuk ke nilai $grand_total sebelumnya.
    $grand_total += $item['subtotal']; 
}

echo "\n-----------------------------------------\n";
echo "TOTAL YANG HARUS DIBAYAR: Rp " . number_format($grand_total, 0, ',', '.') . "\n";
echo "-----------------------------------------\n";

// ===================================================================
// PROSES INPUT UANG BAYAR & VALIDASI KECUKUPAN
// ===================================================================
while (true) {
    echo "Masukkan Uang Bayar (Rp): ";
    $input_bayar = trim(fgets(STDIN));

    // Filter: Anti-kosong, anti-huruf, dan anti-minus
    if ($input_bayar === '' || !ctype_digit($input_bayar) || intval($input_bayar) <= 0) {
        echo "❌ Input salah! Mohon masukkan angka bulat saja (Tanpa huruf / simbol).\n\n";
        continue; 
    }

    $uang_bayar = intval($input_bayar);

    // Cek apakah uangnya kurang dari total belanja
    if ($uang_bayar < $grand_total) {
        echo "❌ MAAF, UANG ANDA KURANG! Total tagihan adalah Rp " . number_format($grand_total, 0, ',', '.') . "\n";
        echo "Silakan masukkan uang bayar yang cukup.\n\n";
        continue; // Mengulang input uang, program tidak mati bawaan (Sistem POS)
    }

    break; 
}

// Menghitung sisa uang kembalian pembeli
$kembalian = $uang_bayar - $grand_total;

// ===================================================================
// OUTPUT STRUK NOTA FINAL RESTORAN
// ===================================================================
echo "\n=========================================\n";
echo "          ISI KERANJANG BELANJA          \n";
echo "=========================================\n";
// PENJELASAN ucwords(): Mengubah huruf pertama pada tiap kata menjadi huruf KAPITAL secara otomatis agar tampilan nama di struk rapi.
echo "Nama Customer: " . ucwords($nama_customer) . "\n";
echo "-----------------------------------------\n";

foreach ($keranjang as $item) {
    echo "- " . str_pad($item['nama'], 15, " ") . " (" . $item['qty'] . "x) = Rp " . number_format($item['subtotal'], 0, ',', '.') . "\n";
}

echo "-----------------------------------------\n";
echo "Total Belanja  : Rp " . number_format($grand_total, 0, ',', '.') . "\n"; 
echo "Uang Bayar     : Rp " . number_format($uang_bayar, 0, ',', '.') .  "\n";  
echo "Kembalian      : Rp " . number_format($kembalian, 0, ',', '.') . "\n";   
echo "=========================================\n";
?>