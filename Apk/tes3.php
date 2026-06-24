<?php
// ===================================================================
// KONSTANTA & SETTING AWAL SPIDOMETER
// ===================================================================
$bensin_awal        = 4.0; 
$total_bar          = 6;

$file_spidometer    = "jarak_total.txt";
$file_bensin        = "bensin_total.txt";

// floatval(): Mengubah teks dari file menjadi angka desimal agar bisa dihitung matematika
$jarak_tempuh_total = file_exists($file_spidometer) ? floatval(file_get_contents($file_spidometer)) : 0.0;
$bensin_saat_ini    = file_exists($file_bensin) ? floatval(file_get_contents($file_bensin)) : $bensin_awal;

// TERNARY OPERATOR & SYSTEM COMMAND: 
// PHP_OS untuk ngecek OS. Jika Windows pakai command 'cls', selain itu pakai 'clear' untuk rapihin terminal
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { popen('cls', 'w'); } else { system('clear'); }

// ===================================================================
// FUNGSI-FUNGSI LOGIKA UTAMA
// ===================================================================

/**
 * FUNGSI FORMAT JARAK PINTAR:
 * Mengubah input meter murni menjadi format KM dan M secara dinamis.
 */
function formatJarakKeKm($jarak_meter) {
    if ($jarak_meter >= 1000) {
        // floor(): Membulatkan angka ke bawah murni 
        $km = floor($jarak_meter / 1000);
        
        // MODULO (%): Mencari sisa bagi. Misal 2500 % 1000 = sisa 500. 
        // round(): Membulatkan ke angka terdekat supaya sisa meternya gak desimal pecah
        $m  = round($jarak_meter % 1000);
        
        // LOGIKA BARU: Jika sisa bagi ($m) bernilai 0 (artinya pas bulat kelipatan 1000)
        if ($m == 0) {
            // number_format(): Mengubah angka jadi format ribuan Indonesia (pake titik, misal 1.000)
            return number_format($km, 0, ',', '.') . " KM";
        }
        
        // Jika ada sisa meter baru digabung KM dan M
        return number_format($km, 0, ',', '.') . " KM " . number_format($m, 0, ',', '.') . " M";
    } else {
        // Kalau jarak di bawah 1000 meter, langsung cetak meternya saja
        return number_format(round($jarak_meter), 0, ',', '.') . " M";
    }
}

function hitungJarakTempuh($jarak_sekarang, $kecepatan_mps) {
    return $jarak_sekarang + $kecepatan_mps;
}

function hitungSisaBensin($bensin_sekarang, $kecepatan_mps) {
    // Rumus konsumsi bensin sesi CLI ini: kecepatan per detik dibagi 1000
    $bensin_terpakai = $kecepatan_mps / 1000;
    $sisa = $bensin_sekarang - $bensin_terpakai;
    return ($sisa < 0) ? 0 : $sisa;
}

function getJumlahBar($sisa_bensin, $bensin_awal, $total_bar) {
    if ($sisa_bensin <= 0) return 0;
    // (int): Type Casting, memaksa hasil pecahan pembagian menjadi angka bulat murni untuk jumlah bar bensin
    return (int) floor(($sisa_bensin / $bensin_awal) * $total_bar);
}

function dapatkanKecepatan($input_user_kmjam) {
    // Rumus Fisika: Konversi dari km/jam ke meter/detik (harus dibagi 3.6)
    return $input_user_kmjam / 3.6; 
}

// ===================================================================
// LOOP UTAMA (SISTEM BERKENDARA BERULANG TANPA RESET)
// ===================================================================
while (true) {

    if ($bensin_saat_ini <= 0) {
        echo "\n❌ TIDAK BISA JALAN! Bensin motor habis total.\n";
        while (true) {
            // STDIN & fgets(): Membuka gerbang terminal input user secara manual di PHP CLI
            // trim(): Menghapus spasi atau enter tak sengaja di ujung inputan user
            echo "⛽ Mau isi bensin dulu? (y/n): ";
            $isi_gerbang = strtolower(trim(fgets(STDIN)));
            if ($isi_gerbang === 'y') {
                $bensin_saat_ini = $bensin_awal;
                file_put_contents($file_bensin, $bensin_saat_ini);
                echo "✅ Tangki diisi penuh kembali (4.0L)! Silakan masukkan input perjalanan.\n";
                break;
            } elseif ($isi_gerbang === 'n') {
                echo "❌ Perjalanan dibatalkan karena tidak ada bensin. Program keluar.\n";
                exit; // Menutup paksa seluruh jalannya program PHP
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

        // Validasi 1: Memastikan string tidak kosong murni (ditandai petik kosong)
        if ($input_kecepatan === '' || $input_waktu_menit === '') {
            echo "❌ ERROR: Semua data tidak boleh kosong!\n";
            continue; // Keluar dari iterasi sekarang dan mengulang loop input dari atas lagi
        }

        // Validasi 2: ctype_digit() berfungsi memastikan isi string murni hanya karakter angka 0-9 (anti minus/desimal)
        if (!ctype_digit($input_kecepatan) || !ctype_digit($input_waktu_menit)) {
            echo "❌ ERROR: Semua input hanya boleh angka bulat positif!\n";
            continue;
        }

        // Validasi 3: intval() mengubah tipe data teks inputan CLI tadi menjadi tipe data Integer resmi
        if (intval($input_kecepatan) <= 0 || intval($input_waktu_menit) <= 0) {
            echo "❌ ERROR: Semua nilai harus lebih besar dari 0!\n";
            continue;
        }

        $kecepatan_user       = intval($input_kecepatan);
        $waktu_menit          = intval($input_waktu_menit);
        $sisa_waktu_detik     = $waktu_menit * 60; // Konversi durasi menit ke hitungan detik untuk loop real-time
        $kecepatan_mps_hitung = dapatkanKecepatan($kecepatan_user);

        // Rumus matematika mencari target jarak dalam meter (Kecepatan * Waktu)
        $jarak_target_user = round($kecepatan_mps_hitung * $sisa_waktu_detik);

        break; // Keluar dari loop input validasi karena data dinyatakan lolos/aman
    }

    echo "\n=========================================\n";
    echo "          MOTOR KEMBALI MELAJU...         \n";
    echo "=========================================\n";
    echo "Kecepatan Motor          : " . $kecepatan_user . " km/jam\n";
    echo "Target Jarak             : " . formatJarakKeKm($jarak_target_user) . "\n";
    echo "Durasi                   : " . $waktu_menit . " Menit (" . $sisa_waktu_detik . " Detik)\n";
    echo "=========================================\n";
    sleep(2); // Menahan tampilan layar terminal selama 2 detik sebelum masuk ke simulasi live
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { popen('cls', 'w'); } else { system('clear'); }

    $jarak_tertempuh_sesi_ini = 0;
    $is_mogok = false; 

    // ================================================================
    // SIMULASI LIVE REAL-TIME (Tiap detik)
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
            $jarak_tempuh_total       = round($jarak_tempuh_total); //round(): Membulatkan ke angka terdekat supaya total jarak speedometer tidak pecah desimal
            $sudah_sampai             = true; 
        }

        $bar_aktif      = getJumlahBar($bensin_saat_ini, $bensin_awal, $total_bar);
        $tampilan_menit = floor($sisa_waktu_detik / 60);
        $tampilan_detik = $sisa_waktu_detik % 60; 
        
        // sprintf(): Mengatur format teks string. "%02d" memaksa angka satuan memiliki angka nol di depannya (misal 5 detik ditulis 05)
        $waktu_format   = sprintf("%02d:%02d", $tampilan_menit, $tampilan_detik);

        // ANSI ESCAPE CODE (\e[H): Mengembalikan kursor terminal ke pojok kiri atas (baris 1, kolom 1).
        // Efeknya layar akan menimpa teks lama secara real-time tanpa berkedip (anti-flicker), beda dari system('clear') yang bikin kedip.
        echo "\e[H";

        // VISUALISASI PANEL DISPLAY CUSTOM
        echo "========================================================\n";
        echo "                   PANEL SPEEDOMETER                    \n";
        echo "========================================================\n";
        // Operator Ternary (? :) digunakan di sini untuk menentukan apakah bar kotak bensin "◢◤" dicetak atau dibiarkan spasi kosong "  "
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
            break; // Memecah/keluar dari loop simulasi waktu karena motor sudah sampai tujuan
        }

        if ($sisa_waktu_detik == 0) {
            sleep(1);
            break;
        }

        $sisa_waktu_detik--; // -- Mengurangi sisa waktu detik agar loop simulasi berjalan mundur dari target waktu

        if ($bensin_saat_ini <= 0) {
            echo "\n❌ MOGOK! Bensin lu habis di tengah jalan.\n";
            $is_mogok = true;
            sleep(2);
            break; 
        }

        sleep(1); // sleep(1) menahan jalannya program selama pas 1 detik agar simulasi berjalan real-time per detik
    }

    // file_put_contents(): Menyimpan/menulis data terbaru langsung ke file TXT (Overwriting secara otomatis)
    file_put_contents($file_spidometer, $jarak_tempuh_total);
    file_put_contents($file_bensin, $bensin_saat_ini);

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