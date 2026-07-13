<?php

function tekanEnterUntukLanjut() {
    echo "\nTekan Enter untuk kembali ke menu...";
    trim(fgets(STDIN));
}

function validasiNoSiswa($no_siswa, $mode = "baru") {
    $no_siswa = trim($no_siswa);
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
            if (isset($data[0]) && trim($data[0]) == $no_siswa) {
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
    $nama_siswa = trim($nama_siswa);
    if ($nama_siswa === "") {
        return ["valid" => false, "pesan" => "Nama siswa tidak boleh kosong."];
    }

    if (!preg_match('/^[A-Za-z ]+$/', $nama_siswa)) {
        return ["valid" => false, "pesan" => "Nama siswa hanya boleh huruf dan spasi."];
    }

    return ["valid" => true, "pesan" => ""];
}

function validasiNilai($nilai, $label) {
    $nilai = trim($nilai);
    if ($nilai === "" || !is_numeric($nilai)) {
        return ["valid" => false, "pesan" => "$label harus berupa angka."];
    }

    $nilai_angka = (float)$nilai;
    if ($nilai_angka < 0 || $nilai_angka > 100) {
        return ["valid" => false, "pesan" => "$label tidak boleh kurang dari 0 atau lebih dari 100."];
    }

    return ["valid" => true, "nilai" => $nilai_angka, "pesan" => ""];
}

// Fungsi pembantu membaca database mentah nilai
function bacaDataNilaiMentah() {
    if (!file_exists("data_nilai.csv")) return [];
    $mentah = [];
    $file = fopen("data_nilai.csv", "r");

    while (($line = fgets($file)) !== FALSE) {
        $line = trim($line);
        
        if ($line === "" || $line === ";" || strpos($line, "No;") !== false || strpos($line, "tabel analisa") !== false || strpos($line, "Mapel;") !== false) {
            continue;
        }

        $data = explode(";", $line);
        $no_siswa = isset($data[0]) ? trim($data[0]) : '';

        if (preg_match('/^[0-9]+$/', $no_siswa)) {
            $base_index = count($data) >= 5 ? 2 : 1;
            if (count($data) > $base_index + 2) {
                $mtk = str_replace(',', '.', trim($data[$base_index]));
                $ipa = str_replace(',', '.', trim($data[$base_index + 1]));
                $ips = str_replace(',', '.', trim($data[$base_index + 2]));

                $mentah[] = [
                    $no_siswa,
                    (float)$mtk,
                    (float)$ipa,
                    (float)$ips
                ];
            }
        }
    }
    fclose($file);
    return $mentah;
}

function jalankanAplikasi() {
    while (true) {
        echo "\n=== MENU APLIKASI RAPORT ===\n";
        echo "1. Input Data Siswa\n";
        echo "2. Input/Update Nilai\n";
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
    
    fwrite($file, trim($no_siswa) . ";" . trim($nama_siswa) . "\n");
    fclose($file);
    echo "✅ Data siswa berhasil disimpan!\n";
}

// MENU 2: Input/Update Nilai Mentah (URUTANNYA SEKARANG DIPERMANENKAN DI CSV)
function inputNilaiSiswa() {
    echo "\n--- INPUT / UPDATE NILAI SISWA ---\n";

    while (true) {
        echo "Masukkan No Siswa: ";
        $no_siswa = trim(fgets(STDIN));
        $hasil_no = validasiNoSiswa($no_siswa, "ada");
        if ($hasil_no["valid"]) {
            break;
        }
        echo "❌ " . $hasil_no["pesan"] . "\n";
    }

    $data_lama = bacaDataNilaiMentah();
    $index_ketemu = -1;
    
    foreach ($data_lama as $index => $d) {
        if ($d[0] == $no_siswa) {
            $index_ketemu = $index;
            break;
        }
    }

    if ($index_ketemu != -1) {
        echo "⚠️ Data nilai No Siswa [$no_siswa] sudah ada.\n";
        echo "Apakah Anda ingin memperbarui/mengubah nilainya? (y/n): ";
        $konfirmasi = strtolower(trim(fgets(STDIN)));
        if ($konfirmasi !== 'y') {
            echo "❌ Dibatalkan. Nilai lama tetap disimpan.\n";
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

    if ($index_ketemu != -1) {
        $data_lama[$index_ketemu] = [$no_siswa, $mtk, $ipa, $ips];
        $pesan_sukses = "✅ Data nilai berhasil DIPERBARUI ke data_nilai.csv!";
    } else {
        $data_lama[] = [$no_siswa, $mtk, $ipa, $ips];
        $pesan_sukses = "✅ Data nilai berhasil disimpan ke data_nilai.csv!";
    }

    // 🔥 KUNCI UTAMA: Urutkan dulu isi array $data_lama sebelum ditulis ulang ke CSV!
    usort($data_lama, function($a, $b) {
        return (int)$a[0] <=> (int)$b[0];
    });

    $nama_siswa_map = [];
    if (file_exists("data_siswa.csv")) {
        $file_siswa = fopen("data_siswa.csv", "r");
        fgets($file_siswa); 
        while (($line = fgets($file_siswa)) !== FALSE) {
            $data = explode(";", trim($line));
            if (count($data) >= 2) {
                $nama_siswa_map[trim($data[0])] = trim($data[1]);
            }
        }
        fclose($file_siswa);
    }

    $file = fopen("data_nilai.csv", "w");
    fwrite($file, "No;Nama;MTK;IPA;IPS;Nilai Terbesar;Nilai Terkecil;Nilai Rata-Rata;Jumlah Nilai\n"); 
    
    foreach ($data_lama as $d) {
        $sNo = trim($d[0]);
        $sNama = isset($nama_siswa_map[$sNo]) ? $nama_siswa_map[$sNo] : "Siswa " . $sNo;
        $fix_mtk = (float)$d[1];
        $fix_ipa = (float)$d[2];
        $fix_ips = (float)$d[3];
        
        fwrite($file, "$sNo;$sNama;$fix_mtk;$fix_ipa;$fix_ips;;;;\n");
    }
    fclose($file);

    echo $pesan_sukses . "\n";
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
            if (isset($data[0]) && trim($data[0]) == $cari_no) {
                $nama_siswa = trim($data[1]);
                break;
            }
        }
        fclose($file_siswa);
    }

    $daftar_nilai = bacaDataNilaiMentah();
    foreach ($daftar_nilai as $d) {
        if ($d[0] == $cari_no) {
            $mtk = (float)$d[1];
            $ipa = (float)$d[2];
            $ips = (float)$d[3];
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
    $rata_rata  = number_format($jumlah / count($nilai_array), 2, ',', '');

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

// MENU 4: Analisa Tabel (DIJAMIN TETAP TERURUT DAN FORMAT KELUARAN AMAN)
function analisaTabelMapel() {
    $daftar_nilai = bacaDataNilaiMentah();

    if (empty($daftar_nilai)) {
        echo "\n❌ Belum ada data nilai untuk dianalisa di data_nilai.csv.\n";
        return;
    }

    // Urutkan array berdasarkan nomor siswa terkecil ke terbesar
    usort($daftar_nilai, function($a, $b) {
        return (int)$a[0] <=> (int)$b[0];
    });

    $nama_siswa_map = [];
    if (file_exists("data_siswa.csv")) {
        $file_siswa = fopen("data_siswa.csv", "r");
        fgets($file_siswa); 
        while (($line = fgets($file_siswa)) !== FALSE) {
            $data = explode(";", trim($line));
            if (count($data) >= 2) {
                $nama_siswa_map[trim($data[0])] = trim($data[1]);
            }
        }
        fclose($file_siswa);
    }

    $file = fopen("data_nilai.csv", "w");
    fwrite($file, "No;Nama;MTK;IPA;IPS;Nilai Terbesar;Nilai Terkecil;Nilai Rata-Rata;Jumlah Nilai\n");

    $all_mtk = []; $all_ipa = []; $all_ips = [];

    foreach ($daftar_nilai as $d) {
        $sNo = $d[0];
        $sNama = isset($nama_siswa_map[$sNo]) ? $nama_siswa_map[$sNo] : "Siswa " . $sNo;
        $vMtk = (float)$d[1]; $vIpa = (float)$d[2]; $vIps = (float)$d[3];

        $all_mtk[] = $vMtk; $all_ipa[] = $vIpa; $all_ips[] = $vIps;

        $sMax = max($vMtk, $vIpa, $vIps);
        $sMin = min($vMtk, $vIpa, $vIps);
        $sTotal = $vMtk + $vIpa + $vIps;
        $sRata = number_format($sTotal / 3, 2, ',', '');

        fwrite($file, "$sNo;$sNama;$vMtk;$vIpa;$vIps;$sMax;$sMin;$sRata;$sTotal\n");
    }

    fwrite($file, str_repeat("\n", 12));

    $hitung_stats = function($mapel_array) {
        $jml = array_sum($mapel_array);
        return [
            'max' => max($mapel_array),
            'min' => min($mapel_array),
            'avg' => number_format($jml / count($mapel_array), 2, ',', ''),
            'sum' => number_format($jml, 2, ',', '')
        ];
    };

    $stats_mtk = $hitung_stats($all_mtk);
    $stats_ipa = $hitung_stats($all_ipa);
    $stats_ips = $hitung_stats($all_ips);

    fwrite($file, ";tabel analisa;;;;\n");
    fwrite($file, ";Mapel;Nilai Terbesar;Nilai Terkecil;Nilai Rata-Rata;Jumlah Nilai\n");
    fwrite($file, ";MTK;{$stats_mtk['max']};{$stats_mtk['min']};{$stats_mtk['avg']};{$stats_mtk['sum']}\n");
    fwrite($file, ";IPA;{$stats_ipa['max']};{$stats_ipa['min']};{$stats_ipa['avg']};{$stats_ipa['sum']}\n");
    fwrite($file, ";IPS;{$stats_ips['max']};{$stats_ips['min']};{$stats_ips['avg']};{$stats_ips['sum']}\n");

    fclose($file);
    echo "\n📊 [SISTEM] Sukses! File data_nilai.csv berhasil diperbarui.\n";

    echo "\n=========================================================================\n";
    echo "                        TABEL ANALISA MATA PELAJARAN                     \n";
    echo "=========================================================================\n";
    echo sprintf("| %-7s | %-14s | %-14s | %-15s | %-12s |\n", "MAPEL", "Nilai Terbesar", "Nilai Terkecil", "Nilai Rata-Rata", "Jumlah Nilai");
    echo "-------------------------------------------------------------------------\n";
    echo sprintf("| %-7s | %-14d | %-14d | %-15s | %-12s |\n", "MTK", $stats_mtk['max'], $stats_mtk['min'], $stats_mtk['avg'], $stats_mtk['sum']);
    echo "-------------------------------------------------------------------------\n";
    echo sprintf("| %-7s | %-14d | %-14d | %-15s | %-12s |\n", "IPA", $stats_ipa['max'], $stats_ipa['min'], $stats_ipa['avg'], $stats_ipa['sum']);
    echo "-------------------------------------------------------------------------\n";
    echo sprintf("| %-7s | %-14d | %-14d | %-15s | %-12s |\n", "IPS", $stats_ips['max'], $stats_ips['min'], $stats_ips['avg'], $stats_ips['sum']);
    echo "=========================================================================\n";
}

jalankanAplikasi();