<?php
// ===================================================================
// KONSTANTA & SETTING AWAL SPIDOMETER (DI LUAR LOOP UTAMA)
// ===================================================================
$bensin_awal        = 4.0;          // Batas maksimal kapasitas bensin awal (4 Liter)
$bensin_saat_ini    = $bensin_awal; // Indikator bensin real-time yang akan berkurang seiring jalan
$total_bar          = 6;            // Jumlah tingkatan bar bensin Yamaha Lexi (Ada 6 bar di foto)

// --- FITUR SIMPAN DATA (MEMORI SPIDOMETER) ---
$file_spidometer = "jarak_total.txt";

// file_exists: Memastikan file penyimpanan odometer sudah terbuat atau belum.
// file_get_contents: Mengambil data string angka jarak dari dalam file txt.
// floatval: Mengubah teks tersebut jadi angka pecahan desimal agar bisa dipakai berhitung.
// Jika file belum ada, otomatis odometer diset dari nol (0.0).
$jarak_tempuh_total = file_exists($file_spidometer) ? floatval(file_get_contents($file_spidometer)) : 0.0;

// ===================================================================
// DAFTAR FUNGSI-FUNGSI LOGIKA UTAMA SISTEM
// ===================================================================

/**
 * 1. Fungsi Hitung Jarak Tempuh
 * Menambahkan odometer total berdasarkan kecepatan meter per detik (mps) yang didapat.
 */
function hitungJarakTempuh($jarak_sekarang, $kecepatan_mps) {
    return $jarak_sekarang + $kecepatan_mps;
}

/**
 * 2. Fungsi Hitung Sisa Bensin
 * Mengurangi isi bensin secara real-time (Konfigurasi: 1 meter jalan memakan 0.001 liter bensin).
 * Jika hasil pengurangan minus, otomatis dikunci di angka 0 agar tidak error.
 */
function hitungSisaBensin($bensin_sekarang, $kecepatan_mps) {
    $bensin_terpakai = $kecepatan_mps / 1000; 
    $sisa = $bensin_sekarang - $bensin_terpakai;
    
    return ($sisa < 0) ? 0 : $sisa;
}

/**
 * 3. Fungsi Mendapatkan Jumlah Bar
 * Menghitung berapa jumlah baris BBM yang harus menyala berdasarkan rasio sisa bensin saat ini.
 * Menggunakan floor() agar pembulatan dipaksa ke bawah supaya penurunan bar bensin presisi.
 */
function getJumlahBar($sisa_bensin, $bensin_awal, $total_bar) {
    if ($sisa_bensin <= 0) return 0;
    return (int) floor(($sisa_bensin / $bensin_awal) * $total_bar);
}

/**
 * 4. Fungsi Simulasi Kecepatan
 * Mengacak kecepatan motor secara real-time antara rentang 40 s/d 60 km/jam.
 * Menggunakan Pass-by-Reference (tanda &) agar nilai variabel asli di luar fungsi bisa langsung berubah.
 */
function dapatkanKecepatan(&$kmjam, &$mps) {
    $kmjam = rand(40, 60); // Menghasilkan angka acak km/jam
    $mps   = $kmjam / 3.6; // Rumus konversi dari satuan km/jam ke satuan meter per detik (mps)
}


// ===================================================================
// LOOP BESAR UTAMA: AGAR BISA INPUT JARAK BERULANG-ULANG TANPA RESET
// ===================================================================
while (true) {
    
    // Validasi awal sebelum jalan: Jika bensin kosong, gerbang input langsung dikunci
    if ($bensin_saat_ini <= 0) {
        echo "\n❌ TIDAK BISA JALAN! Bensin sudah habis total. Silakan isi bensin dulu.\n";
        break; 
    }

    // ===============================================================
    // VALIDASI INPUT KETAT: ANTI-HURUF, ANTI-MINUS, ANTI-SIMBOL
    // ===============================================================
    while (true) {
        echo "\nPosisi Speedometer Saat Ini: " . number_format($jarak_tempuh_total, 0, ',', '.') . " Meter\n";
        echo "Sisa Bensin Saat Ini    : " . number_format($bensin_saat_ini, 2, ',', '.') . " Liter\n";
        echo "Masukkan Jarak Perjalanan Baru (Meter): ";
        
        $input_jarak_baru = trim(fgets(STDIN));

        // Cek jika user langsung tekan enter tanpa isi angka
        if ($input_jarak_baru === '') {
            echo "❌ ERROR: Jarak tidak boleh kosong!\n";
            continue; 
        }

        // ctype_digit: Memastikan string murni berisi angka bulat positif (Hukumnya wajib)
        if (!ctype_digit($input_jarak_baru)) {
            echo "❌ ERROR: Hanya boleh angka bulat positif! (Tanpa huruf, spasi, simbol, atau minus '-')\n";
            continue; 
        }

        // Memastikan jarak yang diinput tidak boleh nol atau minus
        if (intval($input_jarak_baru) <= 0) {
            echo "❌ ERROR: Jarak harus lebih besar dari 0 meter!\n";
            continue; 
        }

        $jarak_baru = intval($input_jarak_baru);
        break; // Input lolos validasi, keluar dari loop input
    }

    // Menghitung target angka odometer akhir yang harus dicapai pada sesi perjalanan ini
    $target_finish = $jarak_tempuh_total + $jarak_baru;

    echo "\n=========================================\n";
    echo "       MOTOR KEMBALI MELAJU...           \n";
    echo "=========================================\n";
    echo "Target Speedometer Akhir: " . number_format($target_finish, 0, ',', '.') . " Meter\n";
    echo "=========================================\n";
    sleep(2); // Delay visual 2 detik sebelum masuk ke mode panel live digital

    // ===============================================================
    // SIMULASI PERJALANAN LIVE & REAL-TIME (MENGALIR TIAP 1 DETIK)
    // ===============================================================
    while (true) {
        $kecepatan_kmjam = 0; $kecepatan_mps = 0;
        dapatkanKecepatan($kecepatan_kmjam, $kecepatan_mps);

        // 1. Perbarui nilai odometer berdasarkan pergerakan jarak per detik ini
        $jarak_tempuh_total = hitungJarakTempuh($jarak_tempuh_total, $kecepatan_mps);

        // Kunci penahan jarak: Menghindari odometer kebablasan melewati target finish sesi ini
        if ($jarak_tempuh_total >= $target_finish) {
            $selisih_kelebihan = $jarak_tempuh_total - $target_finish;
            $jarak_tempuh_total = $target_finish; // Paksa odometer pas di angka target
            
            // Potong sisa kecepatan mps di detik terakhir agar hitungan bensin tidak kelebihan
            $kecepatan_mps = $kecepatan_mps - $selisih_kelebihan;
        }

        // 2. Potong kapasitas bensin secara murni dari pergerakan jarak detik ini
        $bensin_saat_ini = hitungSisaBensin($bensin_saat_ini, $kecepatan_mps);

        // Jika bensin menyentuh angka 0, paksa kecepatan mati total (mogok)
        if ($bensin_saat_ini <= 0) {
            $bensin_saat_ini = 0;
            $kecepatan_kmjam = 0;
        }

        // Ambil data jumlah bar bensin aktif untuk dilempar ke panel display Lexi
        $bar_aktif = getJumlahBar($bensin_saat_ini, $bensin_awal, $total_bar);

        // REFRESH PANEL INDIKATOR DI TERMINAL (CLS untuk Windows, Clear untuk Linux/Mac)
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { popen('cls', 'w'); } else { system('clear'); }

        // Hitung sisa jarak mundur (countdown) sesi perjalanan menuju finish
        $sisa_jarak_sesi = $target_finish - $jarak_tempuh_total;

        // ===============================================================
        // VISUALISASI LAYAR UTAMA SPEEDOMETER YAMAHA LEXI CUSTOM
        // ===============================================================
        echo "================================================\n";
        echo "               PANEL SPEEDOMETER                \n";
        echo "================================================\n";
        
        // Baris 6 (Paling Atas / Kondisi Full)
        echo "  [F] " . ($bar_aktif >= 6 ? "◢◤" : "  ") . "      | \n";
        // Baris 5
        echo "      " . ($bar_aktif >= 5 ? "◢◤" : "  ") . "      |    Sisa Jarak Sesi:\n";
        // Baris 4
        echo "      " . ($bar_aktif >= 4 ? "◢◤" : "  ") . "      |    " . number_format($sisa_jarak_sesi, 0, ',', '.') . " Meter\n";
        // Baris 3
        echo "      " . ($bar_aktif >= 3 ? "◢◤" : "  ") . "      | \n";
        // Baris 2
        echo "      " . ($bar_aktif >= 2 ? "◢◤" : "  ") . "      |    Kecepatan Real-Time:\n";
        // Baris 1 (Paling Bawah / Kondisi Empty)
        echo "  [E] " . ($bar_aktif >= 1 ? "◢◤" : "  ") . "      |    " . $kecepatan_kmjam . " km/h\n";
        
        echo "------------------------------------------------\n";
        echo " Speedometer Total : " . number_format($jarak_tempuh_total, 0, ',', '.') . " M  |  Bensin: " . number_format($bensin_saat_ini, 2, ',', '.') . " L (" . $bar_aktif . "/6)\n"; 
        echo "================================================\n";

        // KONDISI BREAK PERJALANAN 1: Motor mogok total karena bensin habis di jalan
        if ($bensin_saat_ini <= 0) {
            echo "\n❌ MOGOK! Bensin habis di jalan.\n";
            file_put_contents($file_spidometer, $jarak_tempuh_total); // Simpan odometer terakhir
            break 2; // Keluar dari loop simulasi dan loop besar sekaligus
        }

        // KONDISI BREAK PERJALANAN 2: Motor sukses mendarat di target tujuan sesi ini
        if ($jarak_tempuh_total >= $target_finish) {
            echo "\n🏁 SAMPAI! Motor sudah sampai di tujuan sesi ini.\n";
            file_put_contents($file_spidometer, $jarak_tempuh_total); // Simpan odometer sukses
            break; // Keluar dari loop simulasi live menuju pertanyaan interaktif
        }

        sleep(1); // Interval detak simulasi real-time per 1 detik
    }

    // ===============================================================
    // PERTANYAAN INTERAKTIF: MAU JALAN LAGI ATAU SELESAI?
    // ===============================================================
    while (true) {
        echo "\nMau lanjut berkendara lagi? (y/n): ";
        $tanya = strtolower(trim(fgets(STDIN)));
        
        if ($tanya !== 'y' && $tanya !== 'n') {
            echo "❌ INPUT SALAH! Ketik 'y' untuk lanjut atau 'n' untuk parkir.\n";
            continue; 
        }
        break; 
    }

    // Jika memilih 'n', motor resmi diparkir dan loop besar utama selesai
    if ($tanya === 'n') {
        echo "\nMotor diparkir. Perjalanan selesai!\n";
        break; 
    }
}

// ===================================================================
// OUTPUT DATA AKHIR (STRUK REKAPAN AKUMULATIF KESELURUHAN)
// ===================================================================
echo "\n=========================================\n";
echo "         REKAPAN AKHIR SPEEDOMETER          \n";
echo "=========================================\n";
echo "Total Jarak Speedometer : " . number_format($jarak_tempuh_total, 0, ',', '.') . " Meter\n";
echo "Sisa Bensin Akhir       : " . number_format($bensin_saat_ini, 2, ',', '.') . " Liter\n";
echo "=========================================\n";
?>