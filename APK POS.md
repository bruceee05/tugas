# Project Kasir PHP

## 1. Kasir BBM
Fitur:
- Pilih BBM
- Hitung liter
- Cetak struk

---

## 2. Kasir POS Restoran
Fitur:
- Pilih menu
- Isi nama
- Keranjang belanja
- Hitung total
- Kembalian
- Cetak struk

---

## 3. Simulasi Odometer Motor (CLI)
Fitur:
- Input target jarak (meter)
- Input kecepatan (km/jam)
- Simulasi perjalanan real-time di terminal
- Konsumsi bensin otomatis
- Indikator bensin berbentuk bar (█)
- Sistem odometer (total jarak tersimpan)
- Penyimpanan data perjalanan ke file (`jarak_total.txt`)
- Status perjalanan (sampai tujuan / bensin habis)
- Tampilan dashboard terminal real-time

Konsep yang digunakan:
- File handling (simpan data odometer)
- Looping (`while`)
- Function (indikator bensin)
- Input CLI (`fgets(STDIN)`)
- Konversi kecepatan (km/jam → m/s)
- ANSI escape code (real-time terminal update)
