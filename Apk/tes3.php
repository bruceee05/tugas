<?php

// --- 1. MEMBUAT MEMORI ODOMETER ---
$file_odometer = "jarak_total.txt";

// file_exists: Fungsi bawaan PHP untuk mengecek apakah sebuah file sudah ada atau belum.
// file_get_contents: Mengambil/membaca seluruh isi teks yang ada di dalam file.
// floatval: Mengubah teks yang dibaca tadi menjadi angka desimal (float) agar bisa dijumlahkan.
// Operator Ternary (? :) adalah singkatan dari If-Else. Jika file ada (?), ambil nilainya. Jika tidak ada (:), set jadi 0.
$jarak_tersimpan = file_exists($file_odometer) ? floatval(file_get_contents($file_odometer)) : 0;

// --- 2. SETTINGAN AWAL MOTOR ---
$bensin_awal = 4;              // Stok bensin awal di tangki (4 Liter)
$bensin_saat_ini = $bensin_awal;   // Set bensin sekarang dalam kondisi penuh (4 Liter)
$jarak_tempuh_sesi_ini = 0;     // Jarak perjalanan baru dimulai dari angka 0

echo "=== MENGHIDUPKAN MESIN ===\n";

// --- 3. MENERIMA INPUT DARI USER ---
echo "Masukkan target jarak (meter): ";
// trim: Menghapus spasi kosong atau karakter enter yang tidak sengaja ikut ketgetik di awal/akhir input.
// fgets(STDIN): Perintah khusus PHP CLI (terminal) untuk menunggu dan mengambil apa pun yang diketik user sampai menekan Enter.
// intval: Memaksa/mengubah data input menjadi format angka bulat (integer) demi keamanan perhitungan.
$target_jarak = intval(trim(fgets(STDIN))); 

echo "Masukkan kecepatan (km/jam): ";
$kecepatan = intval(trim(fgets(STDIN)));    

// --- 4. FUNGSI UNTUK MENGGAMBAR BAR BENSIN ---
function hitung_bar($bensin, $total) {
    // ceil (Ceiling): Pembulatan angka desimal ke ATAS ke angka bulat terdekat (contoh: 4.1 jadi 5).
    // Ini jarang muncul, gunanya di sini agar lampu indikator kotak terakhir gak cepet mati selama bensin belum benar-benar 0.
    $jumlah_bar = ceil(($bensin / $total) * 6); // Konversi sisa bensin ke skala 6 baris kotak
    // ceil digunakan untuk memastikan bahwa jika ada sisa bensin, setidaknya satu bar akan tetap menyala, sehingga indikator tidak langsung mati saat bensin masih tersisa sedikit.
    // max: Fungsi matematika untuk mengambil angka tertinggi. 
    // Di sini dipake mengunci angka minimal di 0, supaya kalau bensinnya minus, tampilan bar tidak ikutan error/minus.
    $jumlah_bar = max(0, $jumlah_bar); 
    
    // str_repeat: Fungsi unik untuk menduplikat/mengulang teks otomatis sebanyak angka yang ditentukan.
    // Tanda titik (.) di PHP adalah Operator Konkatenasi yang gunanya untuk menyambung dua teks menjadi satu.
    // return: Perintah wajib di fungsi untuk melempar/mengembalikan hasil rakitan teks bar ini keluar agar bisa dicetak.
    return str_repeat("█", $jumlah_bar) . str_repeat("-", 6 - $jumlah_bar);
}

// --- 5. RUMUS DASAR KECEPATAN ---
$meter_per_detik = ($kecepatan * 1000) / 3600; // Mengubah satuan km/jam menjadi meter per detik (m/s)

// --- 6. PROSES SIMULASI PERJALANAN (REAL-TIME) ---
// Perulangan while berjalan terus selama jarak belum sampai target DAN bensin masih di atas 0
while ($jarak_tempuh_sesi_ini < $target_jarak && $bensin_saat_ini > 0) {
    $bensin_terpakai = $meter_per_detik / 1000; // Rumus kaku: Setiap menempuh 1 meter membutuhkan 0.001 liter bensin
    $bensin_saat_ini -= $bensin_terpakai;       // Mengurangi sisa bensin di tangki
    $jarak_tempuh_sesi_ini += $meter_per_detik; // Menambah jarak yang sudah dilewati motor

    // \033[H\033[J: Ini adalah "ANSI Escape Code" (perintah sakti terminal). 
    // \033[H memindahkan kursor ketikan balik ke pojok kiri atas, \033[J menghapus seluruh teks lama di layar.
    // Efeknya layar langsung bersih dalam sekejap, membuat angka baru tercetak di posisi yang sama (efek real-time).
    echo "\033[H\033[J"; 
    
    // Tampilkan Dashboard ke Layar Terminal
    echo "=== SEDANG BERJALAN ===\n";
    // number_format: Fungsi untuk merapikan angka. Angka 2 berarti menampilkan maksimal 2 angka di belakang koma (desimal).
    echo "Sisa Bensin    : " . number_format($bensin_saat_ini, 2) . " L\n"; 
    echo "Indikator Bar  : [" . hitung_bar($bensin_saat_ini, $bensin_awal) . "]\n"; // Memanggil fungsi gambar bar di atas
    // Angka 0 di number_format artinya angka dibulatkan tanpa koma biar tampilan jaraknya bersih.
    echo "Jarak Sesi Ini : " . number_format($jarak_tempuh_sesi_ini, 0) . " m\n"; 
    echo "=======================\n";
    
    // usleep (Microsecond Sleep): Menghentikan sementara jalannya program (rem komputer).
    // Angka 500000 microsecond = 0.5 detik. Tanpa ini, komputer bakal ngitung super cepat (0.0001 detik langsung kelar),
    // sehingga mata manusia gak bakal sempat melihat pergerakan angka real-time nya.
    usleep(500000); 
}

// --- 7. NOTIFIKASI HASIL AKHIR ---
echo "\033[H\033[J"; // Bersihkan layar dashboard putaran terakhir untuk diganti teks ringkasan
$total_semua = $jarak_tersimpan + $jarak_tempuh_sesi_ini; // Jumlahkan odometer lama dengan jarak di sesi ini

// file_put_contents: Kebalikan dari file_get_contents. Fungsi ini langsung menulis dan menyimpan data angka terbaru ke file txt.
file_put_contents($file_odometer, $total_semua); 

echo "=============================\n";
echo "    RINGKASAN PERJALANAN     \n";
echo "=============================\n";
if ($bensin_saat_ini <= 0) {
    echo "Status        : BENSIN HABIS!\n";
} else {
    echo "Status        : SAMPAI TUJUAN\n";
}
echo "Jarak Sesi Ini: " . number_format($jarak_tempuh_sesi_ini, 0) . " meter\n";
echo "Total Odometer: " . number_format($total_semua, 0) . " meter\n";
echo "Sisa Bensin   : " . number_format(max(0, $bensin_saat_ini), 2) . " Liter\n";
echo "=============================\n";
?>