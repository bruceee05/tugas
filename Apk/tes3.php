<?php
// ===================================================================
// KONSTANTA & SETTING AWAL SPIDOMETER
// ===================================================================
$bensin_awal        = 4.0;
$bensin_saat_ini    = $bensin_awal;
$total_bar          = 6;

$file_spidometer    = "jarak_total.txt";

// file_exists: Memastikan file penyimpanan odometer sudah terbuat atau belum.
// file_get_contents: Mengambil data string angka jarak dari dalam file txt.
// floatval: Mengubah teks tersebut jadi angka pecahan desimal agar bisa dipakai berhitung.
// Jika file belum ada, otomatis odometer diset dari nol (0.0).
$jarak_tempuh_total = file_exists($file_spidometer) ? floatval(file_get_contents($file_spidometer)) : 0.0;

// ===================================================================
// FUNGSI-FUNGSI
// ===================================================================

/**
 * 1. Fungsi Hitung Jarak Tempuh
 */
function hitungJarakTempuh($jarak_sekarang, $kecepatan_mps) {
    return $jarak_sekarang + $kecepatan_mps;
}

/**
 * 2. Fungsi Hitung Sisa Bensin
 */
function hitungSisaBensin($bensin_sekarang, $kecepatan_mps) {
    $bensin_terpakai = $kecepatan_mps / 1000;
    $sisa = $bensin_sekarang - $bensin_terpakai;
    return ($sisa < 0) ? 0 : $sisa;
}

/**
 * 3. Fungsi Mendapatkan Jumlah Bar
 */
function getJumlahBar($sisa_bensin, $bensin_awal, $total_bar) {
    if ($sisa_bensin <= 0) return 0;
    return (int) floor(($sisa_bensin / $bensin_awal) * $total_bar);
}

/**
 * 4. Fungsi Mendapatkan Kecepatan User (Pass-by-Reference)
 */
function dapatkanKecepatan(&$kmjam, &$mps, $input_user_kmjam) {
    $kmjam = $input_user_kmjam;
    $mps   = $kmjam / 3.6;
}

// ===================================================================
// LOOP UTAMA
// ===================================================================
while (true) {

    if ($bensin_saat_ini <= 0) {
        echo "\n❌ TIDAK BISA JALAN! Bensin sudah habis total.\n";
        break;
    }

    // ===============================================================
    // INPUT & VALIDASI
    // ===============================================================
    while (true) {
        echo "\n";
        echo "Posisi Speedometer Saat Ini : " . number_format($jarak_tempuh_total, 0, ',', '.') . " Meter\n";
        echo "Sisa Bensin Saat Ini        : " . number_format($bensin_saat_ini, 2, ',', '.') . " Liter\n";
        echo "\n";

        echo "Masukkan Kecepatan Motor (km/jam)        : ";
        $input_kecepatan = trim(fgets(STDIN));

        echo "Masukkan Jarak Target Perjalanan (Meter) : ";
        $input_jarak_baru = trim(fgets(STDIN));

        echo "Masukkan Waktu Perjalanan (Menit)        : ";
        $input_waktu_menit = trim(fgets(STDIN));

        if ($input_kecepatan === '' || $input_jarak_baru === '' || $input_waktu_menit === '') {
            echo "❌ ERROR: Semua data tidak boleh kosong!\n";
            continue;
        }

        if (!ctype_digit($input_kecepatan) || !ctype_digit($input_jarak_baru) || !ctype_digit($input_waktu_menit)) {
            echo "❌ ERROR: Semua input hanya boleh angka bulat positif!\n";
            continue;
        }

        if (intval($input_kecepatan) <= 0 || intval($input_jarak_baru) <= 0 || intval($input_waktu_menit) <= 0) {
            echo "❌ ERROR: Semua nilai harus lebih besar dari 0!\n";
            continue;
        }

        $kecepatan_user    = intval($input_kecepatan);
        $jarak_target_user = intval($input_jarak_baru);
        $waktu_menit       = intval($input_waktu_menit);
        $sisa_waktu_detik  = $waktu_menit * 60;

        dapatkanKecepatan($kecepatan_kmjam_display, $kecepatan_mps_hitung, $kecepatan_user);

        break;
    }

    echo "\n=========================================\n";
    echo "          MOTOR KEMBALI MELAJU...         \n";
    echo "=========================================\n";
    echo "Kecepatan Motor          : " . $kecepatan_user . " km/jam\n";
    echo "Target Jarak             : " . number_format($jarak_target_user, 0, ',', '.') . " Meter\n";
    echo "Durasi Simulasi          : " . $waktu_menit . " Menit (" . $sisa_waktu_detik . " Detik)\n";
    echo "=========================================\n";
    sleep(2);

    // ================================================================
    // VARIABEL SESI
    // ================================================================
    $jarak_tertempuh_sesi_ini = 0;

    // ================================================================
    // SIMULASI LIVE REAL-TIME
    // ================================================================
    while ($sisa_waktu_detik > 0) {

        // 1. Gerakkan motor
        $jarak_tempuh_total       = hitungJarakTempuh($jarak_tempuh_total, $kecepatan_mps_hitung);
        $jarak_tertempuh_sesi_ini = hitungJarakTempuh($jarak_tertempuh_sesi_ini, $kecepatan_mps_hitung);

        // 2. Kurangi bensin
        $bensin_saat_ini = hitungSisaBensin($bensin_saat_ini, $kecepatan_mps_hitung);
        if ($bensin_saat_ini <= 0) {
            $bensin_saat_ini = 0;
        }

        // 3. Snap jarak kalau sudah melewati target (biar panel nampilin angka pas)
        $sudah_sampai = false;
        if ($jarak_tertempuh_sesi_ini >= $jarak_target_user) {
            $jarak_tertempuh_sesi_ini = $jarak_target_user;
            $jarak_tempuh_total       = round($jarak_tempuh_total);
            $sudah_sampai             = true;
        }

        // 4. Ambil bar & format waktu
        $bar_aktif      = getJumlahBar($bensin_saat_ini, $bensin_awal, $total_bar);
        $tampilan_menit = floor($sisa_waktu_detik / 60);
        $tampilan_detik = $sisa_waktu_detik % 60;
        $waktu_format   = sprintf("%02d:%02d", $tampilan_menit, $tampilan_detik);

        // 5. Refresh panel — tampil dulu dengan angka yang sudah benar
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { popen('cls', 'w'); } else { system('clear'); }

        echo "================================================\n";
        echo "               PANEL SPEEDOMETER                \n";
        echo "================================================\n";
        echo "  [F] " . ($bar_aktif >= 6 ? "◢◤" : "  ") . "      |\n";
        echo "      " . ($bar_aktif >= 5 ? "◢◤" : "  ") . "      |    Sisa Waktu : " . $waktu_format . " Mnt\n";
        echo "      " . ($bar_aktif >= 4 ? "◢◤" : "  ") . "      |    Jarak Sesi : " . number_format($jarak_tertempuh_sesi_ini, 0, ',', '.') . " M\n";
        echo "      " . ($bar_aktif >= 3 ? "◢◤" : "  ") . "      |    Target      : " . number_format($jarak_target_user, 0, ',', '.') . " M\n";
        echo "      " . ($bar_aktif >= 2 ? "◢◤" : "  ") . "      |    Kecepatan  : " . $kecepatan_user . " km/h\n";
        echo "  [E] " . ($bar_aktif >= 1 ? "◢◤" : "  ") . "      |\n";
        echo "------------------------------------------------\n";
        echo " Speedometer : " . number_format($jarak_tempuh_total, 0, ',', '.') . " M  |  Bensin: " . number_format($bensin_saat_ini, 2, ',', '.') . " L (" . $bar_aktif . "/6)\n";
        echo "================================================\n";

        // 6. Baru setelah panel tampil, cek apakah harus berhenti
        if ($sudah_sampai) {
            sleep(1); // Biar panel sempat keliatan sebentar sebelum lanjut
            break;
        }

        $sisa_waktu_detik--;

        // Cek mogok
        if ($bensin_saat_ini <= 0) {
            echo "\n❌ MOGOK! Bensin habis di jalan.\n";
            file_put_contents($file_spidometer, $jarak_tempuh_total);
            break 2;
        }

        sleep(1);
    }

    // ================================================================
    // SELESAI
    // ================================================================
    if ($sudah_sampai) {
        echo "\n🏁 Sampai! Jarak " . number_format($jarak_target_user, 0, ',', '.') . " M tercapai.\n";
    } else {
        echo "\n❌ Tidak tercapai! Waktu habis.\n";
    }

    file_put_contents($file_spidometer, $jarak_tempuh_total);

    while (true) {
        echo "\nMau lanjut berkendara lagi? (y/n): ";
        $tanya = strtolower(trim(fgets(STDIN)));
        if ($tanya !== 'y' && $tanya !== 'n') {
            echo "❌ INPUT SALAH! Ketik 'y' atau 'n'.\n";
            continue;
        }
        break;
    }

    if ($tanya === 'n') {
        echo "\nMotor diparkir. Perjalanan selesai!\n";
        break;
    }
}

// ===================================================================
// REKAPAN AKHIR
// ===================================================================
echo "\n=======================================\n";
echo "                SPEEDOMETER              \n";
echo "=========================================\n";
echo "Total Jarak Speedometer : " . number_format($jarak_tempuh_total, 0, ',', '.') . " Meter\n";
echo "Sisa Bensin Akhir       : " . number_format($bensin_saat_ini, 2, ',', '.') . " Liter\n";
echo "=========================================\n";
?>