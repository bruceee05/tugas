<?php
// ===================================================================
// KONSTANTA & SETTING AWAL SPIDOMETER
// ===================================================================
$bensin_awal        = 4.0; 
$total_bar          = 6;

$file_spidometer    = "jarak_total.txt";
$file_bensin        = "bensin_total.txt";

// 1. Load Data Odometer (Jarak Total)
$jarak_tempuh_total = file_exists($file_spidometer) ? floatval(file_get_contents($file_spidometer)) : 0.0;

// 2. Load Data Sisa Bensin
$bensin_saat_ini    = file_exists($file_bensin) ? floatval(file_get_contents($file_bensin)) : $bensin_awal;

// Bersihkan layar sekali saja di awal sebelum simulasi dimulai agar terminal rapi
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { popen('cls', 'w'); } else { system('clear'); }

// ===================================================================
// FUNGSI-FUNGSI LOGIKA UTAMA
// ===================================================================

/**
 * FUNGSI FORMAT JARAK PINTAR:
 * Jika < 1000m             -> Tampil Meter saja (Contoh: 800 M)
 * Jika >= 1000m dan bulat  -> Tampil KM saja    (Contoh: 1 KM)
 * Jika >= 1000m ada sisa   -> Tampil KM dan M   (Contoh: 1 KM 200 M)
 */
function formatJarakKeKm($jarak_meter) {
    if ($jarak_meter >= 1000) {
        $km = floor($jarak_meter / 1000);
        $m  = round($jarak_meter % 1000);
        
        // JIKA BULAT (Sisa meternya 0), langsung tampilkan KM saja
        if ($m == 0) {
            return number_format($km, 0, ',', '.') . " KM";
        }
        
        // Jika ada sisa meter baru tampilkan KM dan M
        return number_format($km, 0, ',', '.') . " KM " . number_format($m, 0, ',', '.') . " M";
    } else {
        // Kalau kurang dari 1 KM, langsung tampilin meternya aja murni
        return number_format(round($jarak_meter), 0, ',', '.') . " M";
    }
}

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
 * 3. Fungsi Mendapatkan Jumlah Bar Indikator BBM
 */
function getJumlahBar($sisa_bensin, $bensin_awal, $total_bar) {
    if ($sisa_bensin <= 0) return 0;
    return (int) floor(($sisa_bensin / $bensin_awal) * $total_bar);
}

/**
 * 4. Fungsi Konversi km/jam ke meter per detik (mps)
 */
function dapatkanKecepatan($input_user_kmjam) {
    return $input_user_kmjam / 3.6; 
}

// ===================================================================
// LOOP UTAMA (SISTEM BERKENDARA BERULANG TANPA RESET)
// ===================================================================
while (true) {

    // VALIDASI GERBANG AWAL
    if ($bensin_saat_ini <= 0) {
        echo "\n❌ TIDAK BISA JALAN! Bensin motor habis total.\n";
        while (true) {
            echo "⛽ Mau isi bensin dulu? (y/n): ";
            $isi_gerbang = strtolower(trim(fgets(STDIN)));
            if ($isi_gerbang === 'y') {
                $bensin_saat_ini = $bensin_awal;
                file_put_contents($file_bensin, $bensin_saat_ini);
                echo "✅ Tangki diisi penuh kembali (4.0L)! Silakan masukkan input perjalanan.\n";
                break;
            } elseif ($isi_gerbang === 'n') {
                echo "❌ Perjalanan dibatalkan karena tidak ada bensin. Program keluar.\n";
                exit;
            }
            echo "❌ INPUT SALAH! Ketik 'y' atau 'n'.\n";
        }
    }

    // ===============================================================
    // INPUT & VALIDASI KETAT
    // ===============================================================
    while (true) {
        echo "\n";
        echo "Speedometer Saat Ini        : " . formatJarakKeKm($jarak_tempuh_total) . "\n";
        echo "Bensin Saat Ini             : " . number_format($bensin_saat_ini, 2, ',', '.') . " Liter\n";
        echo "\n";

        echo "Masukkan Kecepatan Motor (km/jam)        : ";
        $input_kecepatan = trim(fgets(STDIN));

        echo "Masukkan Waktu Perjalanan (Menit)        : ";
        $input_waktu_menit = trim(fgets(STDIN));

        if ($input_kecepatan === '' || $input_waktu_menit === '') {
            echo "❌ ERROR: Semua data tidak boleh kosong!\n";
            continue;
        }

        if (!ctype_digit($input_kecepatan) || !ctype_digit($input_waktu_menit)) {
            echo "❌ ERROR: Semua input hanya boleh angka bulat positif!\n";
            continue;
        }

        if (intval($input_kecepatan) <= 0 || intval($input_waktu_menit) <= 0) {
            echo "❌ ERROR: Semua nilai harus lebih besar dari 0!\n";
            continue;
        }

        $kecepatan_user       = intval($input_kecepatan);
        $waktu_menit          = intval($input_waktu_menit);
        $sisa_waktu_detik     = $waktu_menit * 60;
        $kecepatan_mps_hitung = dapatkanKecepatan($kecepatan_user);

        $jarak_target_user = round($kecepatan_mps_hitung * $sisa_waktu_detik);

        break; 
    }

    echo "\n=========================================\n";
    echo "          MOTOR KEMBALI MELAJU...         \n";
    echo "=========================================\n";
    echo "Kecepatan Motor          : " . $kecepatan_user . " km/jam\n";
    echo "Target Jarak             : " . formatJarakKeKm($jarak_target_user) . "\n";
    echo "Durasi                   : " . $waktu_menit . " Menit (" . $sisa_waktu_detik . " Detik)\n";
    echo "=========================================\n";
    sleep(2); 
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { popen('cls', 'w'); } else { system('clear'); }

    $jarak_tertempuh_sesi_ini = 0;
    $is_mogok = false; 

    // ================================================================
    // SIMULASI LIVE REAL-TIME
    // ================================================================
    while ($sisa_waktu_detik >= 0) {

        $sudah_sampai = false;
        if ($jarak_tertempuh_sesi_ini >= $jarak_target_user) {
            $jarak_tertempuh_sesi_ini = $jarak_target_user;
            $sudah_sampai             = true; 
        } else {
            $jarak_tempuh_total       = hitungJarakTempuh($jarak_tempuh_total, $kecepatan_mps_hitung);
            $jarak_tertempuh_sesi_ini = hitungJarakTempuh($jarak_tertempuh_sesi_ini, $kecepatan_mps_hitung);
        }

        if (!$sudah_sampai) {
            $bensin_saat_ini = hitungSisaBensin($bensin_saat_ini, $kecepatan_mps_hitung);
            if ($bensin_saat_ini <= 0) {
                $bensin_saat_ini = 0;
            }
        }

        if ($jarak_tertempuh_sesi_ini >= $jarak_target_user) {
            $jarak_tertempuh_sesi_ini = $jarak_target_user;
            $jarak_tempuh_total       = round($jarak_tempuh_total); 
            $sudah_sampai             = true; 
        }

        $bar_aktif      = getJumlahBar($bensin_saat_ini, $bensin_awal, $total_bar);
        $tampilan_menit = floor($sisa_waktu_detik / 60);
        $tampilan_detik = $sisa_waktu_detik % 60; 
        $waktu_format   = sprintf("%02d:%02d", $tampilan_menit, $tampilan_detik);

        echo "\e[H";

        // VISUALISASI PANEL DISPLAY YAMAHA LEXI CUSTOM
        echo "========================================================\n";
        echo "                   PANEL SPEEDOMETER                    \n";
        echo "========================================================\n";
        echo "  [F] " . ($bar_aktif >= 6 ? "◢◤" : "  ") . "      |\n";
        echo "      " . ($bar_aktif >= 5 ? "◢◤" : "  ") . "      |    Sisa Waktu         : " . $waktu_format . " Mnt\n";
        echo "      " . ($bar_aktif >= 4 ? "◢◤" : "  ") . "      |    Jarak Saat Ini     : " . formatJarakKeKm($jarak_tertempuh_sesi_ini) . "\n";
        echo "      " . ($bar_aktif >= 3 ? "◢◤" : "  ") . "      |    jarak Tujuan       : " . formatJarakKeKm($jarak_target_user) . "\n";
        echo "      " . ($bar_aktif >= 2 ? "◢◤" : "  ") . "      |    Kecepatan          : " . ($sudah_sampai ? 0 : $kecepatan_user) . " km/h\n";
        echo "  [E] " . ($bar_aktif >= 1 ? "◢◤" : "  ") . "      |\n";
        echo "--------------------------------------------------------\n";
        echo " Total Jarak : " . formatJarakKeKm($jarak_tempuh_total) . "  |  Bensin: " . number_format($bensin_saat_ini, 2, ',', '.') . " L (" . $bar_aktif . "/6)\n";
        echo "========================================================\n";

        if ($sudah_sampai) {
            sleep(1); 
            break; 
        }

        if ($sisa_waktu_detik == 0) {
            sleep(1);
            break;
        }

        $sisa_waktu_detik--;

        // LOGIKA JIKA BENSIN HABIS
        if ($bensin_saat_ini <= 0) {
            echo "\n❌ MOGOK! Bensin lu habis di tengah jalan.\n";
            $is_mogok = true;
            sleep(2);
            break; 
        }

        sleep(1); 
    }

    // Simpan data
    file_put_contents($file_spidometer, $jarak_tempuh_total);
    file_put_contents($file_bensin, $bensin_saat_ini);

    // KONDISI SETELAH MOGOK
    if ($is_mogok) {
        while (true) {
            echo "\n⛽ Motor lu berhenti karena bensin habis. Mau isi bensin sekarang? (y/n): ";
            $pilihan_isi = strtolower(trim(fgets(STDIN)));
            if ($pilihan_isi === 'y') {
                $bensin_saat_ini = $bensin_awal;
                file_put_contents($file_bensin, $bensin_saat_ini);
                echo "✅ Bensin berhasil diisi penuh kembali (4.0L)!\n";
                break;
            } elseif ($pilihan_isi === 'n') {
                echo "❌ Bensin tidak diisi. Kondisi tangki motor tetap kosong.\n";
                break;
            }
            echo "❌ INPUT SALAH! Ketik 'y' atau 'n'.\n";
        }
    } else {
        if ($sudah_sampai) {
            echo "\n🏁 Sampai! Target waktu perjalanan selesai.\n";
        } else {
            echo "\n Ga nyampe Waktu habis.\n";
        }
    }

    // Dialog Interaktif Lanjut Sesi
    while (true) {
        echo "\nLanjut ga? (y/n): ";
        $tanya = strtolower(trim(fgets(STDIN)));
        if ($tanya !== 'y' && $tanya !== 'n') {
            echo "❌ INPUT SALAH! Ketik 'y' atau 'n'.\n";
            continue;
        }
        break;
    }

    if ($tanya === 'n') {
        echo "\nPerjalanan selesai!\n";
        break;
    }
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { popen('cls', 'w'); } else { system('clear'); }
}

// ===================================================================
// REKAPAN AKHIR (OUTPUT NOTA AKUMULATIF GLOBAL)
// ===================================================================
echo "\n========================================================\n";
echo "                       SPEEDOMETER                      \n";
echo "========================================================\n";
echo "Total Jarak Speedometer : " . formatJarakKeKm($jarak_tempuh_total) . "\n";
echo "Sisa Bensin Akhir       : " . number_format($bensin_saat_ini, 2, ',', '.') . " Liter\n";
echo "========================================================\n";
?>