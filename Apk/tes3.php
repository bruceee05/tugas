<?php
// ===================================================================
// KONSTANTA & SETTING AWAL SPIDOMETER
// ===================================================================
$bensin_awal        = 4.0; 
$total_bar          = 6;

$file_spidometer    = "jarak_total.txt";
$file_bensin        = "bensin_total.txt";

// file_exists(): Fungsi bawaan PHP untuk ngecek apakah sebuah file beneran ada di folder atau gak (menghindari error saat dibaca).
// file_get_contents(): Berfungsi membaca/mengambil seluruh isi teks murni dari dalam file target (.txt).
// floatval(): Mengonversi (cast) tipe data teks string dari file menjadi angka desimal/pecahan agar bisa dihitung secara matematis.
$jarak_tempuh_total = file_exists($file_spidometer) ? floatval(file_get_contents($file_spidometer)) : 0.0;
$bensin_saat_ini    = file_exists($file_bensin) ? floatval(file_get_contents($file_bensin)) : $bensin_awal;

// PHP_OS: Konstanta global PHP untuk mendeteksi sistem operasi server/komputer yang sedang menjalankan script ini.
// substr(PHP_OS, 0, 3): Mengambil 3 karakter pertama nama OS (misal jika 'Windows' diambil 'WIN').
// strtoupper(): Memaksa string menjadi huruf kapital semua (biar 'win' atau 'Win' tetap kebaca 'WIN').
// popen('cls', 'w'): Command khusus Windows untuk membersihkan layar terminal tanpa memicu lag atau flicker.
// system('clear'): Command standar OS Linux/macOS (Terminal Bash) untuk membersihkan text di layar layar.
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { popen('cls', 'w'); } else { system('clear'); }

// ===================================================================
// FUNGSI-FUNGSI LOGIKA UTAMA
// ===================================================================

/**
 * FUNGSI FORMAT JALUR DINAMIS
 */
function formatJarakKeKm($jarak_meter) {
    if ($jarak_meter >= 1000) {
        // floor(): Fungsi matematika untuk membulatkan angka desimal ke bawah murni (Contoh: 2.7 KM dipaksa jadi 2 KM).
        $km = floor($jarak_meter / 1000);
        
        // OPERATOR MODULO (%): Mencari sisa hasil pembagian. (Contoh: 2500 dibagi 1000 dapet 2, sisanya 500 meter).
        // round(): Fungsi pembulatan matematika terdekat agar sisa meternya gak menghasilkan angka koma tak berujung.
        $m  = round($jarak_meter % 1000);
        
        // KONDISIONAL BULAT: Mengecek apakah sisa meternya pas 0 (artinya jarak kelipatan 1000 meter murni).
        if ($m == 0) {
            // number_format(): Mengubah format angka mentah jadi standar ribuan Indonesia dengan pemisah titik (Contoh: 10000 -> 10.000).
            return number_format($km, 0, ',', '.') . " KM";
        }
        
        return number_format($km, 0, ',', '.') . " KM " . number_format($m, 0, ',', '.') . " M";
    } else {
        return number_format(round($jarak_meter), 0, ',', '.') . " M";
    }
}

function hitungJarakTempuh($jarak_sekarang, $kecepatan_mps) {
    return $jarak_sekarang + $kecepatan_mps;
}

function hitungSisaBensin($bensin_sekarang, $kecepatan_mps) {
    $bensin_terpakai = $kecepatan_mps / 1000;
    $sisa = $bensin_sekarang - $bensin_terpakai;
    return ($sisa < 0) ? 0 : $sisa;
}

function getJumlahBar($sisa_bensin, $bensin_awal, $total_bar) {
    if ($sisa_bensin <= 0) return 0;
    // (int): Tipe Casting eksplisit, memaksa nilai hasil pembagian yang tadinya pecahan (float) agar berubah jadi integer bulat murni.
    return (int) floor(($sisa_bensin / $bensin_awal) * $total_bar);
}

function dapatkanKecepatan($input_user_kmjam) {
    // Rumus Fisika: Konversi dari Km/Jam ke Meter/Detik wajib dibagi dengan konstanta 3.6.
    return $input_user_kmjam / 3.6; 
}

// ===================================================================
// LOOP UTAMA (SISTEM BERKENDARA BERULANG TANPA RESET)
// ===================================================================
while (true) {

    if ($bensin_saat_ini <= 0) {
        echo "\n❌ TIDAK BISA JALAN! Bensin motor habis total.\n";
        while (true) {
            // STDIN: Standard Input, membuka jalur komunikasi console/terminal agar script PHP siap mendengarkan ketikan user.
            // fgets(): Fungsi untuk menangkap baris teks yang diketikkan user di terminal setelah mereka menekan tombol Enter.
            // strtolower(): Memaksa input huruf dari user menjadi huruf kecil (User ngetik 'Y' atau 'y' bakal dianggap sama).
            // trim(): Menghapus karakter tak terlihat seperti spasi gaib atau sisa 'newline/enter' (\n) di ujung teks input.
            echo "⛽ Mau isi bensin dulu? (y/n): ";
            $isi_gerbang = strtolower(trim(fgets(STDIN)));
            if ($isi_gerbang === 'y') {
                $bensin_saat_ini = $bensin_awal;
                // file_put_contents(): Menulis/menimpa data teks baru ke dalam file.txt secara otomatis tanpa perlu pakai fopen() & fwrite().
                file_put_contents($file_bensin, $bensin_saat_ini);
                echo "✅ Tangki diisi penuh kembali (4.0L)! Silakan masukkan input perjalanan.\n";
                break;
            } elseif ($isi_gerbang === 'n') {
                echo "❌ Perjalanan dibatalkan karena tidak ada bensin.\n";
                exit; // exit: Keyword fatal untuk menghentikan total seluruh jalannya thread program PHP saat itu juga.
            }
            echo "❌ INPUT SALAH! Ketik 'y' atau 'n'.\n";
        }
    }

    // ===============================================================
    // INPUT & VALIDASI KETAT
    // ===============================================================
    while (true) {
        echo "\n";
        echo "Speedometer Saat Ini        : " . formatJarakKeKm($jarak_tempuh_total) . "\n"; //formatJarakKeKm(): Fungsi custom untuk mengubah jarak meter menjadi format KM/Meter yang mudah dibaca.
        echo "Bensin Saat Ini             : " . number_format($bensin_saat_ini, 2, ',', '.') . " Liter\n";
        echo "\n";

        echo "Masukkan Kecepatan Motor (km/jam)        : ";
        $input_kecepatan = trim(fgets(STDIN));

        echo "Masukkan Waktu Perjalanan (Menit)        : ";
        $input_waktu_menit = trim(fgets(STDIN));

        // IDENTIK CHECK (=== ''): Validasi string kosong. Memastikan user tidak langsung mencet enter tanpa mengisi data angka.
        if ($input_kecepatan === '' || $input_waktu_menit === '') {
            echo "❌ ERROR: Semua data tidak boleh kosong!\n";
            continue; // continue: Melompati sisa kode di bawahnya dan langsung memaksa loop kembali mengulang dari baris atas.
        }

        // ctype_digit(): Fungsi check tipe data string bawaan C-Type. Memastikan isi teks string '100%' hanya karakter angka 0-9. 
        // Efeknya: Menolak keras input karakter minus (-), huruf (a-z), simbol, maupun tanda titik/koma desimal.
        if (!ctype_digit($input_kecepatan) || !ctype_digit($input_waktu_menit)) {
            echo "❌ ERROR: Semua input hanya boleh angka bulat positif!\n";
            continue;
        }

        // intval(): Mengubah (parsing) variabel berjenis teks string angka murni tadi menjadi tipe data Integer resmi di memory PHP.
        if (intval($input_kecepatan) <= 0 || intval($input_waktu_menit) <= 0) {
            echo "❌ ERROR: Semua nilai harus lebih besar dari 0!\n";
            continue;
        }

        $kecepatan_user       = intval($input_kecepatan);
        $waktu_menit          = intval($input_waktu_menit);
        $sisa_waktu_detik     = $waktu_menit * 60; 
        $kecepatan_mps_hitung = dapatkanKecepatan($kecepatan_user);

        $jarak_target_user = round($kecepatan_mps_hitung * $sisa_waktu_detik);

        break; // break: Menghancurkan status loop validasi karena data inputan user sudah sah dan aman diolah.
    }

    echo "\n=========================================\n";
    echo "          MOTOR KEMBALI MELAJU...         \n";
    echo "=========================================\n";
    echo "Kecepatan Motor          : " . $kecepatan_user . " km/jam\n";
    echo "Target Jarak             : " . formatJarakKeKm($jarak_target_user) . "\n";
    echo "Durasi                   : " . $waktu_menit . " Menit (" . $sisa_waktu_detik . " Detik)\n";
    echo "=========================================\n";
    sleep(2); // sleep(2): Menahan/membuat program tertidur diam selama 2 detik agar tulisan di atas sempat dibaca user sebelum layar diclear.
    
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { popen('cls', 'w'); } else { system('clear'); }

    $jarak_tertempuh_sesi_ini = 0;
    $is_mogok = false;  // is_mogok: Variabel boolean untuk menandai apakah motor mogok karena bensin habis di tengah jalan.

   // ================================================================
    // SIMULASI LIVE REAL-TIME
    // ================================================================
    while ($sisa_waktu_detik >= 0) { // LOOPING DETIKAN: Berjalan real-time setiap 1 detik sekali selama sisa waktu masih ada.

        $sudah_sampai = false; // RESET STATUS: Di awal setiap detik, motor diasumsikan belum sampai target jarak.
        
        // VALIDASI AWAL: Cek apakah target jarak sesi ini sebenarnya sudah tercapai/terlewati?
        if ($jarak_tertempuh_sesi_ini >= $jarak_target_user) {
            $jarak_tertempuh_sesi_ini = $jarak_target_user; // LOCK ANGKA: Kunci jarak sesi pas di angka target biar gak kebablasan.
            $sudah_sampai             = true; 
        } else {
            // MOTOR MAJU: Jika belum sampai, tambahkan jarak total & jarak sesi berdasarkan rumus meter per detik.
            $jarak_tempuh_total       = hitungJarakTempuh($jarak_tempuh_total, $kecepatan_mps_hitung);
            $jarak_tertempuh_sesi_ini = hitungJarakTempuh($jarak_tertempuh_sesi_ini, $kecepatan_mps_hitung);
        }

        // KONSUMSI BENSIN: Selama status motor belum sampai tujuan, kurangi bensin secara berkala.
        if (!$sudah_sampai) {
            $bensin_saat_ini = hitungSisaBensin($bensin_saat_ini, $kecepatan_mps_hitung);
            if ($bensin_saat_ini <= 0) {
                $bensin_saat_ini = 0; // ANTI MINUS: Kunci bensin di angka 0 murni jika hasil hitungan di bawah nol.
            }
        }

        // VALIDASI AKHIR: Cek ulang setelah motor maju di atas, apakah tepat di detik ini motor berhasil sampai?
        if ($jarak_tertempuh_sesi_ini >= $jarak_target_user) {
            $jarak_tertempuh_sesi_ini = $jarak_target_user;
            $jarak_tempuh_total       = round($jarak_tempuh_total); //round agar jarak total tidak menghasilkan angka koma tak berujung.
            $sudah_sampai             = true; // LOCK STATUS: Ubah jadi true agar perulangan di bawah bisa langsung di-break.
        } 

        $bar_aktif      = getJumlahBar($bensin_saat_ini, $bensin_awal, $total_bar);
        $tampilan_menit = floor($sisa_waktu_detik / 60);
        $tampilan_detik = $sisa_waktu_detik % 60; 
        
        // sprintf(): Fungsi pencetak teks berformat. Rumus "%02d" artinya memaksa angka satuan memiliki digit 0 di depan (Contoh: angka 9 jadi "09").
        $waktu_format   = sprintf("%02d:%02d", $tampilan_menit, $tampilan_detik);

        // ESCAPE ANCHOR (\e[H): Perintah ANSI Escape Code untuk memaksa kursor terminal melompat kembali ke baris 1 kolom 1 tanpa menghapus layar.
        // Trik ini bikin efek render data speedometer berjalan mulus per detik (Anti Gemetar/Anti Flicker) dibanding pakai system('clear').
        echo "\e[H";

        // VISUALISASI PANEL DISPLAY YAMAHA LEXI CUSTOM
        echo "========================================================\n";
        echo "                   PANEL SPEEDOMETER                    \n";
        echo "========================================================\n";
        // SHORTCUT TERNARY (? :): Jika kondisi kiri benar, cetak bar "◢◤", jika bensin berkurang/salah cetak dua spasi kosong "  ".
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

        if ($bensin_saat_ini <= 0) {
            echo "\n❌ MOGOK! Bensin habis di tengah jalan.\n";
            $is_mogok = true;
            sleep(2);
            break; 
        }

        sleep(1); // sleep(1): Memberi jeda waktu berhenti tepat selama 1 detik penuh agar loop berjalan singkron mengikuti waktu detik asli.
    }

    file_put_contents($file_spidometer, $jarak_tempuh_total);
    file_put_contents($file_bensin, $bensin_saat_ini);

    if ($is_mogok) {
        while (true) {
            echo "\n⛽ Motor berhenti karena bensin habis. Mau isi bensin sekarang? (y/n): ";
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