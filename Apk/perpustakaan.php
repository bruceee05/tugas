<?php

define("FILE_DB", "database.json");

// ==========================================
// LOAD & SAVE JSON TO ARRAY
// ==========================================
function loadDatabase()
{
    if (!file_exists(FILE_DB)) {
        $init_kosong = ["buku" => [], "anggota" => [], "peminjaman" => []];// Inisialisasi database kosong jika file tidak ada
        file_put_contents(FILE_DB, json_encode($init_kosong, JSON_PRETTY_PRINT));
        return $init_kosong;
    }

    $json_txt = file_get_contents(FILE_DB);
    $data = json_decode($json_txt, true); 

    if ($data === null || !isset($data['peminjaman']) || !isset($data['buku']) || !isset($data['anggota'])) { // Validasi: Jika file JSON rusak atau tidak sesuai format, buat database kosong baru
        $init_kosong = ["buku" => [], "anggota" => [], "peminjaman" => []];
        file_put_contents(FILE_DB, json_encode($init_kosong, JSON_PRETTY_PRINT));// Simpan database kosong ke file JSON
        return $init_kosong;
    }

    return $data;
}

function saveDatabase($db)
{
    file_put_contents(FILE_DB, json_encode($db, JSON_PRETTY_PRINT));// Simpan array ke file JSON dengan format yang rapi
}

function jedaMenu()
{
    echo "\n" . str_repeat("-", 172) . "\n";
    echo "Tekan [ENTER] untuk kembali ke menu...";
    system('pause > nul');// Menunggu inputan ENTER dari user
}

// -----------------------------------------------------------------
// LOAD DATABASE DITARUH DI SINI (SEBELUM FUNGSI UTAMA DIGUNAKAN)
// -----------------------------------------------------------------
$db = loadDatabase();

// ==========================================
// FITUR 1: REGISTRASI BUKU BARU
// ==========================================
function tambahBuku()
{
    global $db; 
    echo "\n--- UTILS: TAMBAH BUKU BARU ---\n";
    $isbn = trim(readline("Masukkan Nomor ISBN: ")); //readline digunakan untuk membaca inputan dari user, trim digunakan untuk menghapus spasi kosong di awal dan akhir inputan

    if (!preg_match('/^[0-9-]+$/', $isbn)) {
        echo "❌ Gagal: Nomor ISBN tidak boleh mengandung huruf!\n";
        jedaMenu();
        return; 
    }

    if (isset($db['buku'][$isbn])) {
        echo "❌ Gagal: Buku dengan ISBN tersebut sudah terdaftar!\n";//isset digunakan untuk memeriksa apakah data buku dengan ISBN tersebut sudah ada di database atau belum
        jedaMenu();
        return;
    }

    $judul = readline("Masukkan Judul Buku: ");
    $db['buku'][$isbn] = ["judul" => $judul, "stok" => 1];//associative array untuk menyimpan data buku baru dengan ISBN sebagai key dan judul serta stok sebagai value

    saveDatabase($db);
    echo "✅ Sukses: Buku '$judul' berhasil didaftarkan.\n";
    jedaMenu();
}

// ==========================================
// FITUR 2: REGISTRASI ANGGOTA BARU
// ==========================================
function tambahAnggota()
{
    global $db;
    echo "\n--- UTILS: REGISTRASI ANGGOTA ---\n";//readline digunakan untuk membaca inputan dari user
    $ktp = trim(readline("Masukkan Nomor KTP: "));//trim digunakan untuk menghapus spasi kosong di awal dan akhir inputan.

    if (isset($db['anggota'][$ktp])) {
        echo "❌ Gagal: Nomor KTP sudah terdaftar!\n";
        jedaMenu();
        return;
    }

    $nama = trim(readline("Masukkan Nama Lengkap: "));

    // Validasi nama agar tidak bisa diinput menggunakan angka atau simbol aneh
    if (!preg_match('/^[a-zA-Z\s]+$/', $nama)) {
        echo "❌ Gagal: Nama lengkap hanya boleh berisi huruf dan spasi!\n";
        jedaMenu();
        return;
    }

    $email = trim(readline("Masukkan Email: "));

    // Validasi email agar .cok langsung gagal, hanya menerima ekstensi domain standar (.com, .id, dll)
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/\.([a-z]{2,4}|co\.id)$/i', $email) || preg_match('/\.cok$/i', $email)) {
        echo "❌ Gagal: Format penulisan email salah atau domain tidak diizinkan!\n";
        jedaMenu();
        return;
    }

    foreach ($db['anggota'] as $p) {
        if ($p['email'] === $email) { // Cek apakah email sudah digunakan oleh anggota lain
            echo "❌ Gagal: Email sudah digunakan oleh orang lain!\n";
            jedaMenu();
            return;
        }
    }

    $db['anggota'][$ktp] = ["nama" => $nama, "email" => $email];

    saveDatabase($db);
    echo "✅ Sukses: Anggota baru '$nama' berhasil didaftarkan.\n";
    jedaMenu();
}

// ==========================================
// FITUR 3: INPUT PEMINJAMAN BUKU
// ==========================================
function prosesPeminjaman()
{
    global $db;
    echo "\n--- FORM PEMINJAMAN BUKU ---\n";
    $ktp = trim(readline("Masukkan Nomor KTP Anggota: "));

    if (!isset($db['anggota'][$ktp])) {//isset digunakan untuk memeriksa apakah data peminjam dengan KTP tersebut ada di database atau tidak
        echo "❌ Gagal: Data Anggota tidak ditemukan!\n";
        jedaMenu();
        return;
    }

    foreach ($db['peminjaman'] as $pinjam) {
        if ($pinjam['ktp'] === $ktp && $pinjam['status_kembali'] === "Dipinjam") {
            echo "❌ Gagal: Anggota masih membawa buku '" . $pinjam['judul_buku'] . "'!\n";
            jedaMenu();
            return;
        }
    }

    $isbn = trim(readline("Masukkan Nomor ISBN Buku: "));

    if (!isset($db['buku'][$isbn])) {
        echo "❌ Gagal: Buku tidak ditemukan!\n";
        jedaMenu();
        return;
    }

    if ($db['buku'][$isbn]['stok'] <= 0) {
        echo "❌ Gagal: Buku '" . $db['buku'][$isbn]['judul'] . "' sedang dipinjam orang lain!\n";
        jedaMenu();
        return;
    }

    $tgl_pinjam = date('Y-m-d'); 
    echo "📅 Tanggal Pinjam otomatis set hari ini: $tgl_pinjam\n";

    $durasi = (int) readline("Mau pinjam berapa hari? (Maksimal 30 hari): "); 
    if ($durasi < 1 || $durasi > 30) {
        echo "❌ Gagal: Durasi peminjaman minimal 1 hari dan maksimal 30 hari!\n";
        jedaMenu();
        return;
    }

    $date = new DateTime($tgl_pinjam); 
    $date->modify("+$durasi days"); 
    $tgl_jatuh_tempo = $date->format('Y-m-d'); // Format tanggal jatuh tempo sesuai format YYYY-MM-DD

    $db['buku'][$isbn]['stok']--; 

    $id_pinjam = "PJM" . str_pad(count($db['peminjaman']) + 1, 3, "0", STR_PAD_LEFT);

    $db['peminjaman'][$id_pinjam] = [
        "ktp" => $ktp,
        "nama_peminjam" => $db['anggota'][$ktp]['nama'],
        "isbn" => $isbn,
        "judul_buku" => $db['buku'][$isbn]['judul'],
        "tgl_pinjam" => $tgl_pinjam,
        "tgl_jatuh_tempo" => $tgl_jatuh_tempo,
        "status_kembali" => "Dipinjam"
    ];

    saveDatabase($db);
    echo "✅ Sukses: Buku '" . $db['buku'][$isbn]['judul'] . "' berhasil dipinjam.\n";
    echo "📌 Batas Pengembalian: " . $tgl_jatuh_tempo . " ($durasi Hari).\n";
    echo "🆔 Kode Pinjam       : " . $id_pinjam . "\n";
    jedaMenu();
}

// ==========================================
// FITUR 4: INPUT PENGEMBALIAN BUKU
// ==========================================
function prosesPengembalian()
{
    global $db;
    echo "\n--- FORM PENGEMBALIAN BUKU ---\n";
    $id_pinjam = trim(readline("Masukkan Kode Peminjaman (PJMxxx): "));

    if (!isset($db['peminjaman'][$id_pinjam]) || $db['peminjaman'][$id_pinjam]['status_kembali'] !== "Dipinjam") {
        echo "❌ Gagal: Data peminjaman aktif tidak ditemukan!\n";
        jedaMenu();
        return;
    }

    $data_pinjam = &$db['peminjaman'][$id_pinjam];

    echo "Format Tanggal: YYYY-MM-DD (Contoh: 2026-07-05)\n";
    $tgl_kembali = trim(readline("Masukkan Tanggal Pengembalian: "));

    if (empty($tgl_kembali) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tgl_kembali, $matches)) { // Validasi format tanggal menggunakan regex
        echo "❌ Gagal: Format penulisan tanggal salah! Gunakan format YYYY-MM-DD.\n";//regex digunakan untuk memeriksa apakah inputan tanggal sesuai dengan format yang diharapkan  
        jedaMenu();
        return;
    }

    $tahun = (int) $matches[1];
    $bulan = (int) $matches[2];
    $hari = (int) $matches[3];

    if (!checkdate($bulan, $hari, $tahun)) {
        echo "❌ Gagal: Tanggal $tgl_kembali tidak valid di kalender asli!\n";
        jedaMenu();
        return;
    }

    $jt = new DateTime($data_pinjam['tgl_jatuh_tempo']);
    $tk = new DateTime($tgl_kembali);
    $tp = new DateTime($data_pinjam['tgl_pinjam']); 

    if ($tk < $tp) {
        echo "❌ Gagal: Tanggal kembali gak masuk akal, masa sebelum tanggal pinjam!\n";
        jedaMenu();
        return;
    }

    if ($tk <= $jt) {
        $status = "Tepat Waktu";
    } else {
        $selisih = $jt->diff($tk)->days;
        $status = "Terlambat ($selisih Hari)";
    }

    $db['buku'][$data_pinjam['isbn']]['stok']++;  //++ untuk menambah stok buku karena sudah dikembalikan

    $data_pinjam['status_kembali'] = $status;
    $data_pinjam['tgl_kembali'] = $tgl_kembali;

    saveDatabase($db);
    echo "✅ Sukses: Buku '" . $data_pinjam['judul_buku'] . "' telah diterima perpustakaan.\n";
    echo "📅 Tanggal Kembali: " . $tgl_kembali . "\n";
    echo "📌 Status         : " . $status . "\n";
    jedaMenu();
}

// ==========================================
// FITUR 5: LIHAT LAPORAN (TABEL BERSEBELAHAN)
// ==========================================
function tampilkanLaporan()
{
    global $db;

    $baris_buku = [];
    if (empty($db['buku'])) {
        $baris_buku[] = sprintf("| %-74s |", "Belum ada data buku. Silakan tambah buku di menu 1.");
    } else {
        foreach ($db['buku'] as $isbn => $b) {
            $status_buku = ($b['stok'] > 0) ? "Tersedia" : "Sedang Dipinjam";
            $baris_buku[] = ["isbn" => $isbn, "judul" => $b['judul'], "stok" => $b['stok'], "status" => $status_buku];//associative array
        }
    }

    $baris_pjm = [];
    if (empty($db['peminjaman'])) {
        $baris_pjm[] = sprintf("| %-85s |", "Belum ada riwayat aktivitas peminjaman buku.");
    } else {
        foreach ($db['peminjaman'] as $id => $t) {
            $status_pjm = $t['status_kembali'];
            if ($status_pjm === "Dipinjam") {
                $status_pjm = "-";
            }
            $baris_pjm[] = ["id" => $id, "nama" => $t['nama_peminjam'], "judul" => $t['judul_buku'], "jt" => $t['tgl_jatuh_tempo'], "status" => $status_pjm];//associative array 
        }
    }

    $max_rows = max(count($baris_buku), count($baris_pjm));

    echo "\n" . str_repeat("=", 78) . "     " . str_repeat("=", 89) . "\n";
    echo "| " . sprintf("%-74s", "DAFTAR BUKU PERPUSTAKAAN") . " |     | " . sprintf("%-85s", "RIWAYAT PEMINJAMAN") . " |\n";
    echo str_repeat("=", 78) . "     " . str_repeat("=", 89) . "\n";

    printf(
        "| %-13s | %-25s | %-4s | %-22s |     | %-8s | %-14s | %-20s | %-12s | %-18s |\n",
        "ISBN Buku", "Judul Buku", "Stok", "Ketersediaan", "Kode PJM", "Nama Anggota", "Judul Buku", "Jatuh Tempo", "Status Kembali"
    );
    echo str_repeat("-", 78) . "     " . str_repeat("-", 89) . "\n";

    for ($i = 0; $i < $max_rows; $i++) {

        if (isset($baris_buku[$i])) {
            if (is_string($baris_buku[$i])) {
                echo $baris_buku[$i];
            } else {
                $b = $baris_buku[$i];
                printf(
                    "| %-13s | %-25s | %-4d | %-22s |",
                    $b['isbn'], $b['judul'], $b['stok'], $b['status']
                );
            }
        } else {
            printf("| %-13s | %-25s | %-4s | %-22s |", "", "", "", "");
        }

        echo "     ";

        if (isset($baris_pjm[$i])) {
            if (is_string($baris_pjm[$i])) {
                echo $baris_pjm[$i];
            } else {
                $p = $baris_pjm[$i];
                printf(
                    "| %-8s | %-14s | %-20s | %-12s | %-18s |",
                    $p['id'], $p['nama'], $p['judul'], $p['jt'], $p['status'] //untuk menampilkan data peminjaman dengan warna sesuai status pengembalian
                );
            }
        } else {
            printf("| %-8s | %-14s | %-20s | %-12s | %-18s |", "", "", "", "", ""); 
        }

        echo "\n";
    }

    echo str_repeat("=", 78) . "     " . str_repeat("=", 89) . "\n";
    jedaMenu();
}

// ==========================================
// INTERFACE MENU CONSOLE MAIN LOOP (PALING BAWAH)
// ==========================================
while (true) {
    echo "\n======================================\n";
    echo "       PANEL ADMIN PERPUSTAKAAN       \n";
    echo "======================================\n";
    echo "1. Tambah Buku Baru\n";
    echo "2. Registrasi Anggota Baru\n";
    echo "3. Peminjaman Buku\n";
    echo "4. Pengembalian Buku\n";
    echo "5. Lihat Daftar & Riwayat Peminjaman\n";
    echo "6. Keluar\n";
    echo "--------------------------------------\n";
    $pilihan = readline("Pilih Menu (1-6): ");

    switch ($pilihan) {
        case "1":
            tambahBuku();
            break;
        case "2":
            tambahAnggota();
            break;
        case "3":
            prosesPeminjaman();
            break;
        case "4":
            prosesPengembalian();
            break;
        case "5":
            tampilkanLaporan();
            break;
        case "6":
            echo "Keluar dari sistem perpustakaan. Sampai jumpa!\n";
            exit;
        default:
            echo "❌ Pilihan menu keliru!\n";
            jedaMenu();
    }                                  
}