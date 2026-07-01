<?php

// Ini nama file tempat kita nyimpen data buku, anggota, dan pinjaman secara permanen
$file_db = "database.json";

// =========================================================================
// 1. FUNGSI UTAS (DATABASE UTILITY)
// =========================================================================

// Fungsi untuk membaca data dari file JSON (Alur: Ambil & Bongkar)
function loadDatabase($fileName) {
    // Kalo file database.json belum kebuat, kita bikin struktur dasarnya dulu
    if (!file_exists($fileName)) {
        // Berbentuk Associative Array kosong: laci buku, laci anggota, laci peminjaman
        return ['buku' => [], 'anggota' => [], 'peminjaman' => []];
    }
    // Kalo filenya ada, kita ambil isinya dalam bentuk teks string
    $isi_file = file_get_contents($fileName);
    
    // KUNCI: json_decode merubah teks JSON jadi Associative Array PHP karena dikasih 'true'
    return json_decode($isi_file, true); 
}

// Fungsi untuk menyimpan data ke file JSON (Alur: Bungkus & Simpan)
function saveDatabase($db) {
    global $file_db;
    // KUNCI: json_encode merubah kembali Associative Array PHP menjadi teks JSON yang rapi
    $json_data = json_encode($db, JSON_PRETTY_PRINT);
    // Tulis teks tersebut ke dalam file fisik database.json
    file_put_contents($file_db, $json_data);
}

// Load database di awal banget pas aplikasi pertama kali dinyalain
$db = loadDatabase($file_db);


// =========================================================================
// 2. PROSES UTAMA (MENU APLIKASI UTAMA)
// =========================================================================

// while(true) artinya aplikasi bakal muter terus-terusan nampilin menu, gak langsung mati
while (true) {
    // str_repeat fungsinya buat bikin garis pembatas biar tampilan console rapi
    echo "\n" . str_repeat("=", 40) . "\n";
    echo "   PERPUSTAKAAN \n";
    echo str_repeat("=", 40) . "\n";
    echo "1. Tambah Buku Baru\n";
    echo "2. Daftar Anggota Baru\n";
    echo "3. Transaksi Peminjaman\n";
    echo "4. Pengembalian Buku\n";
    echo "5. Laporan Tabel Berdampingan\n";
    echo "6. Keluar Aplikasi\n";
    echo str_repeat("-", 40) . "\n";
    
    // readline buat nangkep ketikan pilihan menu dari user
    $pilihan = readline("Pilih Menu (1-6): ");
    echo "\n";

    switch ($pilihan) {
        case 1:
            echo "--- MENU 1: TAMBAH BUKU BARU---\n";
            
            // Perulangan untuk validasi ISBN, gak bakal berhenti sebelum inputannya bener
            while (true) {//readline() digunakan untuk membaca input dari pengguna di terminal, mirip seperti fgets(STDIN) tapi lebih ringkas dan nyaman.
                $isbn = readline("Masukkan ISBN (Contoh: 978-602): ");//
                // REGEX: ^[0-9-]+$ artinya inputan WAJIB cuma angka dan strip (-), gak boleh huruf
                if (preg_match('/^[0-9-]+$/', $isbn)) {
                    break; // Kalo bener, keluar dari loop validasi ISBN
                }
                echo "❌ Salah! ISBN cuma boleh diisi angka dan tanda strip (-) doang.\n";
            }

            $judul = readline("Masukkan Judul Buku: ");
            $stok  = (int)readline("Masukkan Jumlah Stok: "); // (int) buat maksain inputan jadi angka bulat

            // MERAKIT data buku baru ke dalam Associative Array (pake kata/Key huruf)
            $buku_baru = [
                'isbn'   => $isbn,
                'judul'  => $judul,
                'stok'   => $stok,
                'status' => 'Tersedia' // Otomatis diset Tersedia di awal
            ];

            // MENYIMPAN: [] kosong artinya memasukkan data buku baru ke urutan paling bawah
            $db['buku'][] = $buku_baru;
            saveDatabase($db); // Panggil fungsi buat nulis ke file JSON
            echo "✅ Buku berhasil ditambahkan!\n";
            break;

        case 2:
            echo "--- MENU 2: DAFTAR ANGGOTA BARU ---\n";
            $nama = readline("Masukkan Nama Anggota: ");

            // MEMBUAT ID OTOMATIS: count() ngitung total anggota sekarang, ditambah 1
            // str_pad() bertugas nambal angka 0 di sebelah kiri biar formatnya 3 digit (001, 002, dst)
            $id_anggota = "AGT" . str_pad(count($db['anggota']) + 1, 3, "0", STR_PAD_LEFT);

            // MERAKIT data anggota baru ke dalam Associative Array
            $anggota_baru = [
                'id'   => $id_anggota,
                'nama' => $nama
            ];

            $db['anggota'][] = $anggota_baru;
            saveDatabase($db);
            echo "✅ Anggota terdaftar! ID Anda: $id_anggota\n";
            break;

        case 3:
            echo "--- MENU 3: TRANSAKSI PEMINJAMAN ---\n";
            $id_member = readline("Masukkan ID Anggota Peminjam: ");
            $isbn_buku = readline("Masukkan ISBN Buku yang Dipinjam: ");
            
            // Generate Kode PJM otomatis mirip kayak ID Anggota (PJM001, PJM002, dst)
            $kode_pjm = "PJM" . str_pad(count($db['peminjaman']) + 1, 3, "0", STR_PAD_LEFT);

            // MERAKIT data transaksi peminjaman baru
            $peminjaman_baru = [
                'kode_pjm'       => $kode_pjm,
                'nama_anggota'   => $id_member,
                'isbn'           => $isbn_buku,
                'jatuh_tempo'    => date('Y-m-d', strtotime('+7 days')), // Otomatis ngitung 7 hari kedepan
                'status_kembali' => 'Dipinjam' // Status awal saat meminjam
            ];

            $db['peminjaman'][] = $peminjaman_baru;
            saveDatabase($db);
            echo "✅ Transaksi Berhasil! Kode PJM Anda: $kode_pjm\n";
            break;

        case 4:
            echo "--- MENU 4: PENGEMBALIAN BUKU ---\n";
            $cari_pjm = readline("Masukkan Kode PJM yang mau dikembalikan: ");
            
            $ketemu = false;
            // foreach buat nyari data di dalam laci peminjaman satu per satu
            foreach ($db['peminjaman'] as $key => $val) {
                if ($val['kode_pjm'] === $cari_pjm) {
                    
                    // AMPERSAND (&) KUNCI: Mengikat data variabel ke database asli biar langsung ke-update
                    $data_pinjam = &$db['peminjaman'][$key];
                    
                    $opsi_kembali = readline("Apakah telat mengembalikan? (y/n): ");
                    // Ternary Operator (Shortcut If-Else): Jika diketik 'y' maka 'Terlambat', jika tidak 'Tepat Waktu'
                    $data_pinjam['status_kembali'] = (strtolower($opsi_kembali) == 'y') ? 'Terlambat' : 'Tepat Waktu';
                    
                    $ketemu = true;
                    break; // Kalo udah ketemu, hentikan pencarian loop
                }
            }

            if ($ketemu) {
                saveDatabase($db); // Database induk otomatis ikut berubah karena ampersand (&) tadi
                echo "✅ Status pengembalian berhasil di-update!\n";
            } else {
                echo "❌ Kode PJM tidak ditemukan!\n";
            }
            break;

        case 5:
            echo "--- MENU 5: LAPORAN TABEL BERDAMPINGAN ---\n";
            
            // HEADER TABEL: printf merakit format kolom sekaligus langsung mencetak ke layar
            // Karakter '|' sengaja diketik manual untuk dijadikan dinding pembatas antar kolom tabel
            echo str_repeat("-", 158) . "\n";
            printf("| %-13s | %-25s | %-4s | %-12s |     | %-8s | %-14s | %-13s | %-12s | %-14s |\n",
                "ISBN Buku", "Judul Buku", "Stok", "Status", "Kode PJM", "ID Anggota", "ISBN Buku", "Jatuh Tempo", "Status PJM");
            echo str_repeat("-", 158) . "\n";

            // LOGIKA MAX: Mencari tahu mana data yang paling banyak antara Buku vs Peminjaman
            // Angka terbesar itu yang dijadikan patokan jumlah baris ke bawah ($max_rows)
            $max_rows = max(count($db['buku']), count($db['peminjaman']));

            if ($max_rows == 0) {
                // SPRINTF: Merakit teks peringatan dulu, disimpan ke variabel, baru bisa dicetak bawah
                $baris_kosong = sprintf("| %-61s |     | %-72s |", "Belum ada data buku.", "Belum ada data peminjaman.");
                echo $baris_kosong . "\n";
            } else {
                // Perulangan 'for' bakal berjalan otomatis sebanyak angka dari hasil max() tadi
                for ($i = 0; $i < $max_rows; $i++) {
                    
                    // TERNARY & ISSET: Mengatasi tabel pincang. 
                    // Cek data buku indeks ke-$i ada gak? Kalo ada ambil isi lacinya, kalo abis diisi kosong ""
                    $b_isbn  = isset($db['buku'][$i]) ? $db['buku'][$i]['isbn'] : "";
                    $b_judul = isset($db['buku'][$i]) ? $db['buku'][$i]['judul'] : "";
                    $b_stok  = isset($db['buku'][$i]) ? $db['buku'][$i]['stok'] : "";
                    $b_stat  = isset($db['buku'][$i]) ? $db['buku'][$i]['status'] : "";

                    // Sisi Kanan: Berlaku hal yang sama untuk data peminjaman
                    $p_kode  = isset($db['peminjaman'][$i]) ? $db['peminjaman'][$i]['kode_pjm'] : "";
                    $p_nama  = isset($db['peminjaman'][$i]) ? $db['peminjaman'][$i]['nama_anggota'] : "";
                    $p_isbn  = isset($db['peminjaman'][$i]) ? $db['peminjaman'][$i]['isbn'] : "";
                    $p_tempo = isset($db['peminjaman'][$i]) ? $db['peminjaman'][$i]['jatuh_tempo'] : "";
                    $p_stat  = isset($db['peminjaman'][$i]) ? $db['peminjaman'][$i]['status_kembali'] : "";

                    // Cetak baris data berdampingan secara rapi, \n bertindak sebagai pengetuk [ENTER]
                    printf("| %-13s | %-25s | %-4s | %-12s |     | %-8s | %-14s | %-13s | %-12s | %-14s |\n",
                        $b_isbn, $b_judul, $b_stok, $b_stat, $p_kode, $p_nama, $p_isbn, $p_tempo, $p_stat);
                }
            }
            echo str_repeat("-", 158) . "\n";
            break;

        case 6:
            echo "Keluar dari sistem. Terima kasih!\n";
            exit; // Mematikan perulangan while(true) dan menghentikan total program

        default:
            echo "❌ Pilihan menu tidak valid!\n";
    }
}