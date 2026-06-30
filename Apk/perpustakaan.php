<?php

define("FILE_DB", "database.json");

// ==========================================
// UTILS: ANSI COLOR CODES (Konstanta Warna)
// ==========================================
define("C_RESET", "\033[0m");
define("C_BOLD", "\033[1m");
define("C_RED", "\033[31m");
define("C_GREEN", "\033[32m");
define("C_YELLOW", "\033[33m");
define("C_BLUE", "\033[34m");
define("C_CYAN", "\033[36m");
define("C_WHITE", "\033[37m");

// ==========================================
// LOAD & SAVE JSON TO ARRAY
// ==========================================
function loadDatabase()
{
    if (!file_exists(FILE_DB)) {
        $init_kosong = ["buku" => [], "peminjam" => [], "peminjaman" => []];// Inisialisasi database kosong jika file tidak ada
        file_put_contents(FILE_DB, json_encode($init_kosong, JSON_PRETTY_PRINT));
        return $init_kosong;
    }

    $json_txt = file_get_contents(FILE_DB);
    $data = json_decode($json_txt, true); 

    if ($data === null || !isset($data['peminjaman']) || !isset($data['buku']) || !isset($data['peminjam'])) { // Validasi: Jika file JSON rusak atau tidak sesuai format, buat database kosong baru
        $init_kosong = ["buku" => [], "peminjam" => [], "peminjaman" => []];
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
    echo "\n" . C_CYAN . str_repeat("-", 172) . C_RESET . "\n";
    echo C_YELLOW . "Tekan [ENTER] untuk kembali ke menu..." . C_RESET;
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
    echo "\n" . C_BOLD . C_CYAN . "--- UTILS: TAMBAH BUKU BARU ---" . C_RESET . "\n";
    $isbn = trim(readline("Masukkan Nomor ISBN: ")); //readline digunakan untuk membaca inputan dari user, trim digunakan untuk menghapus spasi kosong di awal dan akhir inputan

    if (!preg_match('/^[0-9-]+$/', $isbn)) {
        echo C_RED . "❌ Gagal: Nomor ISBN tidak boleh mengandung huruf!" . C_RESET . "\n";
        jedaMenu();
        return; 
    }

    if (isset($db['buku'][$isbn])) {
        echo C_RED . "❌ Gagal: Buku dengan ISBN tersebut sudah terdaftar!" . C_RESET . "\n";
        jedaMenu();
        return;
    }

    $judul = readline("Masukkan Judul Buku: ");
    $db['buku'][$isbn] = ["judul" => $judul, "stok" => 1];//associative array untuk menyimpan data buku baru dengan ISBN sebagai key dan judul serta stok sebagai value

    saveDatabase($db);
    echo C_GREEN . "✅ Sukses: Buku '$judul' berhasil didaftarkan." . C_RESET . "\n";
    jedaMenu();
}

// ==========================================
// FITUR 2: REGISTRASI ANGGOTA BARU
// ==========================================
function tambahPeminjam()
{
    global $db;
    echo "\n" . C_BOLD . C_CYAN . "--- UTILS: REGISTRASI PEMINJAM ---" . C_RESET . "\n";
    $ktp = trim(readline("Masukkan Nomor KTP: "));

    if (isset($db['peminjam'][$ktp])) {
        echo C_RED . "❌ Gagal: Nomor KTP sudah terdaftar!" . C_RESET . "\n";
        jedaMenu();
        return;
    }

    $nama = readline("Masukkan Nama Lengkap: ");
    $email = trim(readline("Masukkan Email: "));

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo C_RED . "❌ Gagal: Format penulisan email salah!" . C_RESET . "\n";
        jedaMenu();
        return;
    }

    foreach ($db['peminjam'] as $p) {
        if ($p['email'] === $email) { // Cek apakah email sudah digunakan oleh anggota lain
            echo C_RED . "❌ Gagal: Email sudah digunakan oleh orang lain!" . C_RESET . "\n";
            jedaMenu();
            return;
        }
    }

    $db['peminjam'][$ktp] = ["nama" => $nama, "email" => $email];

    saveDatabase($db);
    echo C_GREEN . "✅ Sukses: Anggota baru '$nama' berhasil didaftarkan." . C_RESET . "\n";
    jedaMenu();
}

// ==========================================
// FITUR 3: INPUT PEMINJAMAN BUKU
// ==========================================
function prosesPeminjaman()
{
    global $db;
    echo "\n" . C_BOLD . C_CYAN . "--- FORM PEMINJAMAN BUKU ---" . C_RESET . "\n";
    $ktp = trim(readline("Masukkan Nomor KTP Peminjam: "));

    if (!isset($db['peminjam'][$ktp])) {//isset digunakan untuk memeriksa apakah data peminjam dengan KTP tersebut ada di database atau tidak
        echo C_RED . "❌ Gagal: Data Anggota tidak ditemukan!" . C_RESET . "\n";
        jedaMenu();
        return;
    }

    foreach ($db['peminjaman'] as $pinjam) {
        if ($pinjam['ktp'] === $ktp && $pinjam['status_kembali'] === "Dipinjam") {
            echo C_RED . "❌ Gagal: Peminjam masih membawa buku '" . $pinjam['judul_buku'] . "'!" . C_RESET . "\n";
            jedaMenu();
            return;
        }
    }

    $isbn = trim(readline("Masukkan Nomor ISBN Buku: "));

    if (!isset($db['buku'][$isbn])) {
        echo C_RED . "❌ Gagal: Buku tidak ditemukan!" . C_RESET . "\n";
        jedaMenu();
        return;
    }

    if ($db['buku'][$isbn]['stok'] <= 0) {
        echo C_RED . "❌ Gagal: Buku '" . $db['buku'][$isbn]['judul'] . "' sedang dipinjam orang lain!" . C_RESET . "\n";
        jedaMenu();
        return;
    }

    $tgl_pinjam = date('Y-m-d'); 
    echo C_GREEN . "📅 Tanggal Pinjam otomatis set hari ini: $tgl_pinjam" . C_RESET . "\n";

    $durasi = (int) readline("Mau pinjam berapa hari? (Maksimal 30 hari): "); 
    if ($durasi < 1 || $durasi > 30) {
        echo C_RED . "❌ Gagal: Durasi peminjaman minimal 1 hari dan maksimal 30 hari!" . C_RESET . "\n";
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
        "nama_peminjam" => $db['peminjam'][$ktp]['nama'],
        "isbn" => $isbn,
        "judul_buku" => $db['buku'][$isbn]['judul'],
        "tgl_pinjam" => $tgl_pinjam,
        "tgl_jatuh_tempo" => $tgl_jatuh_tempo,
        "status_kembali" => "Dipinjam"
    ];

    saveDatabase($db);
    echo C_GREEN . "✅ Sukses: Buku '" . $db['buku'][$isbn]['judul'] . "' berhasil dipinjam." . C_RESET . "\n";
    echo "📌 Batas Pengembalian: " . C_YELLOW . $tgl_jatuh_tempo . C_RESET . " ($durasi Hari).\n";
    echo "🆔 Kode Pinjam       : " . C_BOLD . C_CYAN . $id_pinjam . C_RESET . "\n";
    jedaMenu();
}

// ==========================================
// FITUR 4: INPUT PENGEMBALIAN BUKU
// ==========================================
function prosesPengembalian()
{
    global $db;
    echo "\n" . C_BOLD . C_CYAN . "--- FORM PENGEMBALIAN BUKU ---" . C_RESET . "\n";
    $id_pinjam = trim(readline("Masukkan Kode Peminjaman (PJMxxx): "));

    if (!isset($db['peminjaman'][$id_pinjam]) || $db['peminjaman'][$id_pinjam]['status_kembali'] !== "Dipinjam") {
        echo C_RED . "❌ Gagal: Data peminjaman aktif tidak ditemukan!" . C_RESET . "\n";
        jedaMenu();
        return;
    }

    $data_pinjam = &$db['peminjaman'][$id_pinjam];

    echo C_WHITE . "Format Tanggal: YYYY-MM-DD (Contoh: 2026-07-05)" . C_RESET . "\n";
    $tgl_kembali = trim(readline("Masukkan Tanggal Pengembalian: "));

    if (empty($tgl_kembali) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $tgl_kembali, $matches)) { // Validasi format tanggal menggunakan regex
        echo C_RED . "❌ Gagal: Format penulisan tanggal salah! Gunakan format YYYY-MM-DD." . C_RESET . "\n";//regex digunakan untuk memeriksa apakah inputan tanggal sesuai dengan format yang diharapkan  
        jedaMenu();
        return;
    }

    $tahun = (int) $matches[1];
    $bulan = (int) $matches[2];
    $hari = (int) $matches[3];

    if (!checkdate($bulan, $hari, $tahun)) {
        echo C_RED . "❌ Gagal: Tanggal $tgl_kembali tidak valid di kalender asli!" . C_RESET . "\n";
        jedaMenu();
        return;
    }

    // ... kode yang atas tetep sama ...

    $jt = new DateTime($data_pinjam['tgl_jatuh_tempo']);
    $tk = new DateTime($tgl_kembali);
    $tp = new DateTime($data_pinjam['tgl_pinjam']); // <--- Tambahin objek tanggal pinjam

    // --- NAH, TAMBAHIN VALIDASI INI ---
    if ($tk < $tp) {
        echo C_RED . "❌ Gagal: Tanggal kembali gak masuk akal, masa sebelum tanggal pinjam!" . C_RESET . "\n";
        jedaMenu();
        return;
    }
    // ----------------------------------

    if ($tk <= $jt) {
        $status = "Tepat Waktu";
        $color_status = C_GREEN;
    } else {
        $selisih = $jt->diff($tk)->days;
        $status = "Terlambat ($selisih Hari)";
        $color_status = C_RED;
    }

    // ... kode ke bawahnya tetep sama ...

    $db['buku'][$data_pinjam['isbn']]['stok']++;  //++ untuk menambah stok buku karena sudah dikembalikan

    $data_pinjam['status_kembali'] = $status;
    $data_pinjam['tgl_kembali'] = $tgl_kembali;

    saveDatabase($db);
    echo C_GREEN . "✅ Sukses: Buku '" . $data_pinjam['judul_buku'] . "' telah diterima perpustakaan." . C_RESET . "\n";
    echo "📅 Tanggal Kembali: " . C_BOLD . $tgl_kembali . "\n";
    echo "📌 Status         : " . $color_status . C_BOLD . $status . C_RESET . "\n";
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

    echo "\n" . C_CYAN . str_repeat("=", 78) . "     " . str_repeat("=", 89) . C_RESET . "\n";
    echo C_CYAN . "| " . C_BOLD . C_WHITE . sprintf("%-74s", "DAFTAR BUKU PERPUSTAKAAN") . C_CYAN . " |     | " . C_BOLD . C_WHITE . sprintf("%-85s", "RIWAYAT PEMINJAMAN") . C_CYAN . " |\n" . C_RESET;
    echo C_CYAN . str_repeat("=", 78) . "     " . str_repeat("=", 89) . C_RESET . "\n";

    printf(
        C_CYAN . "| " . C_BOLD . C_WHITE . "%-13s" . C_CYAN . " | " . C_BOLD . C_WHITE . "%-25s" . C_CYAN . " | " . C_BOLD . C_WHITE . "%-4s" . C_CYAN . " | " . C_BOLD . C_WHITE . "%-22s" . C_CYAN . " |     | " . C_BOLD . C_WHITE . "%-8s" . C_CYAN . " | " . C_BOLD . C_WHITE . "%-14s" . C_CYAN . " | " . C_BOLD . C_WHITE . "%-20s" . C_CYAN . " | " . C_BOLD . C_WHITE . "%-12s" . C_CYAN . " | " . C_BOLD . C_WHITE . "%-18s" . C_CYAN . " |\n" . C_RESET,
        "ISBN Buku", "Judul Buku", "Stok", "Ketersediaan", "Kode PJM", "Nama Anggota", "Judul Buku", "Jatuh Tempo", "Status Kembali"
    );
    echo C_CYAN . str_repeat("-", 78) . "     " . str_repeat("-", 89) . C_RESET . "\n";

    for ($i = 0; $i < $max_rows; $i++) {

        if (isset($baris_buku[$i])) {
            if (is_string($baris_buku[$i])) {
                echo C_CYAN . $baris_buku[$i] . C_RESET;
            } else {
                $b = $baris_buku[$i];
                $color_stok = ($b['stok'] > 0) ? C_GREEN : C_RED;
                $color_status = ($b['stok'] > 0) ? C_GREEN : C_RED;

                printf(
                    C_CYAN . "| " . C_RESET . "%-13s" . C_CYAN . " | " . C_RESET . "%-25s" . C_CYAN . " | " . $color_stok . "%-4d" . C_CYAN . " | " . $color_status . "%-22s" . C_CYAN . " |" . C_RESET,
                    $b['isbn'], $b['judul'], $b['stok'], $b['status']
                );
            }
        } else {
            printf(C_CYAN . "| %-13s | %-25s | %-4s | %-22s |" . C_RESET, "", "", "", "");
        }

        echo "     ";

        if (isset($baris_pjm[$i])) {
            if (is_string($baris_pjm[$i])) {
                echo C_CYAN . $baris_pjm[$i] . C_RESET;
            } else {
                $p = $baris_pjm[$i];
                if ($p['status'] === "-") {
                    $color_pjm = C_WHITE;
                } elseif (strpos($p['status'], 'Terlambat') !== false) {
                    $color_pjm = C_RED;
                } else {
                    $color_pjm = C_GREEN;
                }

                printf(
                    C_CYAN . "| " . C_RESET . "%-8s" . C_CYAN . " | " . C_RESET . "%-14s" . C_CYAN . " | " . C_RESET . "%-20s" . C_CYAN . " | " . C_RESET . "%-12s" . C_CYAN . " | " . $color_pjm . "%-18s" . C_CYAN . " |" . C_RESET,
                    $p['id'], $p['nama'], $p['judul'], $p['jt'], $p['status']
                );
            }
        } else {
            printf(C_CYAN . "| %-8s | %-14s | %-20s | %-12s | %-18s |" . C_RESET, "", "", "", "", ""); 
        }

        echo "\n";
    }

    echo C_CYAN . str_repeat("=", 78) . "     " . str_repeat("=", 89) . C_RESET . "\n";
    jedaMenu();
}

// ==========================================
// INTERFACE MENU CONSOLE MAIN LOOP (PALING BAWAH)
// ==========================================
while (true) {
    echo "\n" . C_CYAN . "======================================\n";
    echo "       " . C_BOLD . C_WHITE . "PANEL ADMIN PERPUSTAKAAN" . C_CYAN . "       \n";
    echo "======================================\n" . C_RESET;
    echo C_GREEN . "1." . C_RESET . " Registrasi Buku Baru\n";
    echo C_GREEN . "2." . C_RESET . " Registrasi Anggota Baru\n";
    echo C_GREEN . "3." . C_RESET . " Input Peminjaman Buku\n";
    echo C_GREEN . "4." . C_RESET . " Input Pengembalian Buku\n";
    echo C_GREEN . "5." . C_RESET . " Lihat Daftar & Riwayat Peminjaman\n";
    echo C_RED . "6." . C_RESET . " Keluar Sistem\n";
    echo C_CYAN . "--------------------------------------" . C_RESET . "\n";
    $pilihan = readline(C_BOLD . "Pilih Menu (1-6): " . C_RESET);

    switch ($pilihan) {
        case "1":
            tambahBuku();
            break;
        case "2":
            tambahPeminjam();
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
            echo C_BOLD . C_YELLOW . "Keluar dari sistem perpustakaan. Sampai jumpa!" . C_RESET . "\n";
            exit;
        default:
            echo C_RED . "❌ Pilihan menu keliru!" . C_RESET . "\n";
            jedaMenu();
    }
}