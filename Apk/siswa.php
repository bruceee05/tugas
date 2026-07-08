<?php

function tekanEnterUntukLanjut() {
    echo "\nTekan Enter untuk kembali ke menu...";
    trim(fgets(STDIN));
}

function validasiNoSiswa($no_siswa, $mode = "baru") {
    if ($no_siswa === "") {
        return ["valid" => false, "pesan" => "Nomor siswa tidak boleh kosong."];
    }

    if (!preg_match('/^[0-9]+$/', $no_siswa)) {
        return ["valid" => false, "pesan" => "Nomor siswa hanya boleh angka."];
    }

    $ada_di_file = false;
    if (file_exists("data_siswa.csv")) {
        $file_baca = fopen("data_siswa.csv", "r");
        while (($line = fgets($file_baca)) !== FALSE) {
            $data = explode(";", trim($line));
            if (isset($data[0]) && $data[0] == $no_siswa) {
                $ada_di_file = true;
                break;
            }
        }
        fclose($file_baca);
    }

    if ($mode === "baru") {
        if ($ada_di_file) {
            return ["valid" => false, "pesan" => "Nomor siswa sudah terdaftar, silakan pakai nomor lain."];
        }
        return ["valid" => true, "pesan" => ""];
    }

    if ($mode === "ada") {
        if (!$ada_di_file) {
            return ["valid" => false, "pesan" => "Nomor siswa belum terdaftar, input data siswa dulu."];
        }
        return ["valid" => true, "pesan" => ""];
    }

    return ["valid" => false, "pesan" => "Mode validasi tidak valid."];
}

function validasiNamaSiswa($nama_siswa) {
    if ($nama_siswa === "") {
        return ["valid" => false, "pesan" => "Nama siswa tidak boleh kosong."];
    }

    if (!preg_match('/^[A-Za-z ]+$/', $nama_siswa)) {
        return ["valid" => false, "pesan" => "Nama siswa hanya boleh huruf dan spasi."];
    }

    return ["valid" => true, "pesan" => ""];
}

function validasiNilai($nilai, $label) {
    if ($nilai === "" || !is_numeric($nilai)) {
        return ["valid" => false, "pesan" => "$label harus berupa angka."];
    }

    $nilai_angka = (float)$nilai;
    if ($nilai_angka < 0 || $nilai_angka > 100) {
        return ["valid" => false, "pesan" => "$label tidak boleh kurang dari 0 atau lebih dari 100."];
    }

    return ["valid" => true, "nilai" => $nilai_angka, "pesan" => ""];
}

// Fungsi pembantu khusus membaca baris data siswa mentah (abaikan header/jarak/analisa bawah)
function bacaDataNilaiMentah() {
    if (!file_exists("data_nilai.csv")) return [];
    $mentah = [];
    $file = fopen("data_nilai.csv", "r");
    $header = fgets($file); // Lewati header utama
    
    while (($line = fgets($file)) !== FALSE) {
        $line = trim($line);
        if ($line === "") continue;
        $data = explode(";", $line);
        
        // Cek kalau baris ini adalah bagian dari tabel analisa bawah, hentikan pembacaan data siswa
        if (isset($data[0]) && ($data[0] === 'tabel analisa' || $data[0] === 'Mapel' || $data[0] === 'MTK' || $data[0] === 'IPA' || $data[0] === 'IPS' || $data[0] === '')) {
            continue;
        }
        
        if (count($data) >= 4 && is_numeric($data[0])) {
            $mentah[] = $data;
        }
    }
    fclose($file);
    return $mentah;
}

// Fungsi utama untuk menjalankan aplikasi terminal
function jalankanAplikasi() {
    while (true) {
        echo "\n=== MENU APLIKASI RAPORT ===\n";
        echo "1. Input Data Siswa\n";
        echo "2. Input Nilai\n";
        echo "3. Cetak Nilai Raport\n";
        echo "4. Analisa Tabel (UPDATE CSV & TERMINAL)\n";
        echo "5. Keluar\n";
        echo "Pilih menu (1-5): ";
        
        $pilihan = trim(fgets(STDIN));

        if ($pilihan == '1') {
            inputDataSiswa();
        } elseif ($pilihan == '2') {
            inputNilaiSiswa();
        } elseif ($pilihan == '3') {
            cetakRaportSiswa();
        } elseif ($pilihan == '4') {
            analisaTabelMapel();
        } elseif ($pilihan == '5') {
            echo "Terima kasih! Aplikasi keluar.\n";
            break;
        } else {
            echo "Pilihan tidak valid, coba lagi.\n";
        }

        if ($pilihan != '5') {
            tekanEnterUntukLanjut();
        }
    }
}

// MENU 1: Input Data Siswa
function inputDataSiswa() {
    echo "\n--- INPUT DATA SISWA ---\n";

    while (true) {
        echo "Masukkan No Siswa: ";
        $no_siswa = trim(fgets(STDIN));
        $hasil_no = validasiNoSiswa($no_siswa, "baru");
        if ($hasil_no["valid"]) {
            break;
        }
        echo "❌ " . $hasil_no["pesan"] . "\n";
    }

    while (true) {
        echo "Masukkan Nama Siswa: ";
        $nama_siswa = trim(fgets(STDIN));
        $hasil_nama = validasiNamaSiswa($nama_siswa);
        if ($hasil_nama["valid"]) {
            break;
        }
        echo "❌ " . $hasil_nama["pesan"] . "\n";
    }

    $buat_header = !file_exists("data_siswa.csv");
    $file = fopen("data_siswa.csv", "a");
    if ($buat_header) {
        fwrite($file, "NO;NAMA\n");
    }
    
    fwrite($file, $no_siswa . ";" . $nama_siswa . "\n");
    fclose($file);
    echo "✅ Data siswa berhasil disimpan!\n";
}

// MENU 2: Input Nilai Mentah
function inputNilaiSiswa() {
    echo "\n--- INPUT NILAI SISWA ---\n";

    while (true) {
        echo "Masukkan No Siswa: ";
        $no_siswa = trim(fgets(STDIN));
        $hasil_no = validasiNoSiswa($no_siswa, "ada");
        if ($hasil_no["valid"]) {
            break;
        }
        echo "❌ " . $hasil_no["pesan"] . "\n";
    }

    // Ambil data nilai mentah yang sudah ada sebelumnya
    $data_lama = bacaDataNilaiMentah();
    
    // Cek apakah nomor siswa ini sudah pernah diberi nilai
    foreach ($data_lama as $d) {
        if ($d[0] == $no_siswa) {
            echo "❌ Gagal: Nilai untuk No Siswa [$no_siswa] sudah diinput sebelumnya!\n";
            return;
        }
    }

    while (true) {
        echo "Masukkan Nilai MTK: ";
        $mtk_input = trim(fgets(STDIN));
        $hasil_mtk = validasiNilai($mtk_input, "Nilai MTK");
        if ($hasil_mtk["valid"]) {
            $mtk = $hasil_mtk["nilai"];
            break;
        }
        echo "❌ " . $hasil_mtk["pesan"] . "\n";
    }

    while (true) {
        echo "Masukkan Nilai IPA: ";
        $ipa_input = trim(fgets(STDIN));
        $hasil_ipa = validasiNilai($ipa_input, "Nilai IPA");
        if ($hasil_ipa["valid"]) {
            $ipa = $hasil_ipa["nilai"];
            break;
        }
        echo "❌ " . $hasil_ipa["pesan"] . "\n";
    }

    while (true) {
        echo "Masukkan Nilai IPS: ";
        $ips_input = trim(fgets(STDIN));
        $hasil_ips = validasiNilai($ips_input, "Nilai IPS");
        if ($hasil_ips["valid"]) {
            $ips = $hasil_ips["nilai"];
            break;
        }
        echo "❌ " . $hasil_ips["pesan"] . "\n";
    }

    // Masukkan ke array data lama untuk ditulis ulang rapi
    $data_lama[] = [$no_siswa, $mtk, $ipa, $ips];

    // Tulis ulang struktur atas tabel siswa mentah secara aman
    $file = fopen("data_nilai.csv", "w");
    fwrite($file, "No;MTK;IPA;IPS\n"); 
    foreach ($data_lama as $d) {
        fwrite($file, $d[0] . ";" . $d[1] . ";" . $d[2] . ";" . $d[3] . "\n");
    }
    fclose($file);

    echo "✅ Data nilai berhasil disimpan ke data_nilai.csv!\n";
    echo "⚠️ Catatan: Silakan jalankan Menu 4 untuk menyinkronkan kalkulasi baris dan tabel analisa bawah.\n";
}

// MENU 3: Cetak Nilai Raport Per Siswa
function cetakRaportSiswa() {
    echo "\n--- CETAK NILAI RAPORT ---\n";
    echo "Masukkan No Siswa yang dicari: ";
    $cari_no = trim(fgets(STDIN));

    $mtk = 0; $ipa = 0; $ips = 0;
    $nama_siswa = "Siswa Tanpa Nama";
    $nilai_ditemukan = false;

    if (file_exists("data_siswa.csv")) {
        $file_siswa = fopen("data_siswa.csv", "r");
        while (($line = fgets($file_siswa)) !== FALSE) {
            $data = explode(";", trim($line));
            if (isset($data[0]) && $data[0] == $cari_no) {
                $nama_siswa = $data[1];
                break;
            }
        }
        fclose($file_siswa);
    }

    $daftar_nilai = bacaDataNilaiMentah();
    foreach ($daftar_nilai as $d) {
        if ($d[0] == $cari_no) {
            $mtk = (int)$d[1];
            $ipa = (int)$d[2];
            $ips = (int)$d[3];
            $nilai_ditemukan = true;
            break;
        }
    }

    if (!$nilai_ditemukan) {
        echo "❌ Maaf, No Siswa [$cari_no] nilainya belum diinput di Menu 2.\n";
        return;
    }

    $nilai_array = [$mtk, $ipa, $ips];
    $terbesar   = max($nilai_array);
    $terkecil   = min($nilai_array);
    $jumlah     = array_sum($nilai_array);
    $rata_rata  = number_format($jumlah / count($nilai_array), 2);

    echo "\n=========================================\n";
    echo "RAPORT HASIL BELAJAR SISWA\n";
    echo "=========================================\n";
    echo "No Siswa   : $cari_no\n";
    echo "Nama       : $nama_siswa\n";
    echo "-----------------------------------------\n";
    echo "Nilai MTK  : $mtk\n";
    echo "Nilai IPA  : $ipa\n";
    echo "Nilai IPS  : $ips\n";
    echo "-----------------------------------------\n";
    echo "Hasil Perhitungan:\n";
    echo "- Nilai Terbesar  : $terbesar\n";
    echo "- Nilai Terkecil  : $terkecil\n";
    echo "- Nilai Rata-Rata : $rata_rata\n";
    echo "- Jumlah Nilai    : $jumlah\n";
    echo "=========================================\n";
}

// MENU 4: Analisa Tabel (Satu File Gabung Nilai, Kasih 12 Jarak Baris Kosong + Print Terminal)
function analisaTabelMapel() {
    $daftar_nilai = bacaDataNilaiMentah();

    if (empty($daftar_nilai)) {
        echo "\n❌ Belum ada data nilai untuk dianalisa di data_nilai.csv.\n";
        return;
    }

    // Ambil Nama Siswa mapping dari data_siswa.csv
    $nama_siswa_map = [];
    if (file_exists("data_siswa.csv")) {
        $file_siswa = fopen("data_siswa.csv", "r");
        fgets($file_siswa); // Skip header siswa
        while (($line = fgets($file_siswa)) !== FALSE) {
            $data = explode(";", trim($line));
            if (count($data) >= 2) {
                $nama_siswa_map[$data[0]] = $data[1];
            }
        }
        fclose($file_siswa);
    }

    // Tulis ulang file data_nilai.csv dari atas (Tabel Siswa Lengkap)
    $file = fopen("data_nilai.csv", "w");
    
    // Header Atas
    fwrite($file, "No;Nama;MTK;IPA;IPS;Nilai Terbesar;Nilai Terkecil;Nilai Rata-Rata;Jumlah Nilai\n");

    $all_mtk = []; $all_ipa = []; $all_ips = [];

    foreach ($daftar_nilai as $d) {
        $sNo = $d[0];
        $sNama = isset($nama_siswa_map[$sNo]) ? $nama_siswa_map[$sNo] : "Siswa " . $sNo;
        $vMtk = (int)$d[1]; $vIpa = (int)$d[2]; $vIps = (int)$d[3];

        $all_mtk[] = $vMtk; $all_ipa[] = $vIpa; $all_ips[] = $vIps;

        $sMax = max($vMtk, $vIpa, $vIps);
        $sMin = min($vMtk, $vIpa, $vIps);
        $sTotal = $vMtk + $vIpa + $vIps;
        $sRata = number_format($sTotal / 3, 2, ',', '');

        // Tulis baris siswa lengkap kalkulasi ke samping
        fwrite($file, "$sNo;$sNama;$vMtk;$vIpa;$vIps;$sMax;$sMin;$sRata;$sTotal\n");
    }

    // --- KASIH JARAK JAUH KE BAWAH DI EXCEL (12 Baris Kosong Jeda) ---
    fwrite($file, str_repeat("\n", 12));

    // Hitung Analisa Tiap Mapel (Vertikal)
    $hitung_stats = function($mapel_array) {
        $jml = array_sum($mapel_array);
        return [
            'max' => max($mapel_array),
            'min' => min($mapel_array),
            'avg' => number_format($jml / count($mapel_array), 2, ',', ''),
            'sum' => $jml
        ];
    };

    $stats_mtk = $hitung_stats($all_mtk);
    $stats_ipa = $hitung_stats($all_ipa);
    $stats_ips = $hitung_stats($all_ips);

    // Tulis Tabel Analisa Kotak Bawah di dalam file CSV yang sama
    fwrite($file, ";tabel analisa\n");
    fwrite($file, ";Nilai Terbesar;Nilai Terkecil;Nilai Rata-Rata;Jumlah Nilai\n");
    fwrite($file, "MTK;{$stats_mtk['max']};{$stats_mtk['min']};{$stats_mtk['avg']};{$stats_mtk['sum']}\n");
    fwrite($file, "IPA;{$stats_ipa['max']};{$stats_ipa['min']};{$stats_ipa['avg']};{$stats_ipa['sum']}\n");
    fwrite($file, "IPS;{$stats_ips['max']};{$stats_ips['min']};{$stats_ips['avg']};{$stats_ips['sum']}\n");

    fclose($file);
    echo "\n📊 [SISTEM] Sukses! File data_nilai.csv berhasil diperbarui dengan jarak renggang.\n";

    // MUNCULKAN JUGA TABEL ANALISANYA DI TERMINAL BIAR KELIHATAN
    echo "\n=========================================================================\n";
    echo "                        TABEL ANALISA MATA PELAJARAN                     \n";
    echo "=========================================================================\n";
    echo sprintf("| %-7s | %-14s | %-14s | %-15s | %-12s |\n", "MAPEL", "Nilai Terbesar", "Nilai Terkecil", "Nilai Rata-Rata", "Jumlah Nilai");
    echo "-------------------------------------------------------------------------\n";
    echo sprintf("| %-7s | %-14d | %-14d | %-15s | %-12d |\n", "MTK", $stats_mtk['max'], $stats_mtk['min'], $stats_mtk['avg'], $stats_mtk['sum']);
    echo "-------------------------------------------------------------------------\n";
    echo sprintf("| %-7s | %-14d | %-14d | %-15s | %-12d |\n", "IPA", $stats_ipa['max'], $stats_ipa['min'], $stats_ipa['avg'], $stats_ipa['sum']);
    echo "-------------------------------------------------------------------------\n";
    echo sprintf("| %-7s | %-14d | %-14d | %-15s | %-12d |\n", "IPS", $stats_ips['max'], $stats_ips['min'], $stats_ips['avg'], $stats_ips['sum']);
    echo "=========================================================================\n";
}

// Jalankan aplikasinya
jalankanAplikasi();