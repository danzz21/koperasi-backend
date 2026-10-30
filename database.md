# 🗄️ Database Guide — Koperasi K-Samara

Panduan praktis pengelolaan database untuk developer dan tim teknis Koperasi K-Samara.

---

## ⚙️ Konfigurasi Koneksi

### Development

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=koperasi_db
DB_USERNAME=root
DB_PASSWORD=
```

### Production

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=koperasi_db
DB_USERNAME=koperasi_user
DB_PASSWORD=STRONG_PASSWORD_HERE
```

### Membuat database baru

```sql
CREATE DATABASE koperasi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'koperasi_user'@'localhost' IDENTIFIED BY 'STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON koperasi_db.* TO 'koperasi_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 🚀 Migration Commands

### Jalankan semua migrasi

```bash
php artisan migrate
```

### Fresh install (hapus semua tabel & ulang)

```bash
php artisan migrate:fresh
# ⚠️ DANGER: menghapus semua data!
```

### Fresh + Seeder

```bash
php artisan migrate:fresh --seed
```

### Cek status migrasi

```bash
php artisan migrate:status
```

### Rollback 1 batch terakhir

```bash
php artisan migrate:rollback
```

### Rollback N step

```bash
php artisan migrate:rollback --step=3
```

---

## 📋 Daftar Migration (Kronologis)

| # | File | Fungsi |
|---|---|---|
| 1 | `0001_01_01_000000_create_users_table` | Tabel users dasar (Laravel default) |
| 2 | `0001_01_01_000001_create_cache_table` | Cache database |
| 3 | `0001_01_01_000002_create_jobs_table` | Queue jobs |
| 4 | `2026_01_01_000000_add_google_fields_to_users` | Kolom Google OAuth |
| 5 | `2026_01_01_000001_create_passkeys_table` | WebAuthn passkey |
| 6 | `2026_07_16_..._create_personal_access_tokens` | Sanctum tokens |
| 7–11 | `2026_07_20_..._create_table_*` | Tabel legacy (simpanan, pinjaman, angsuran, transaksi, akad) |
| 12 | `2026_08_19_000000_add_profile_columns_to_users` | Profil anggota (NIK, no_hp, alamat, foto) |
| 13 | `2026_08_19_000100_add_tgl_lahir_to_users` | Tanggal lahir |
| 14–18 | `2026_08_19_050638_..._create_*` | Tabel utama (simpanans, pinjamen, cicilans, pembayarans) |
| 19 | `2026_09_10_..._create_ppob_and_payment_tables` | Tabel PPOB & payment |
| 20 | `2026_09_11_..._add_member_query_indexes` | Index performa |
| 21 | `2026_09_18_..._create_audit_logs_table` | Log audit |
| 22 | `2026_09_18_..._create_system_configs_table` | Konfigurasi sistem |
| 23 | `2026_09_18_..._add_superadmin_role` | Role superadmin |
| 24 | `2026_09_21_..._add_profile_completed_at` | Tracking onboarding |
| 25 | `2026_09_21_..._add_pending_status_to_pinjamen` | Status pending pinjaman |
| 26 | `2026_09_21_..._add_rejected_status_to_simpanans` | Status ditolak simpanan |
| 27 | `2026_09_26_..._create_ewallets_tables` | E-Wallet koperasi |
| 28 | `2026_09_27_..._add_gateway_fields_to_ewallet_transactions` | Field gateway e-wallet |
| 29 | `2026_10_18_..._create_notifications_table` | Notifikasi anggota |
| 30 | `2026_10_18_..._add_cicilan_reference_to_pembayarans` | Relasi cicilan-pembayaran |
| 31 | `2026_10_18_..._create_withdrawal_requests_table` | Permintaan penarikan dana |
| 32 | `2026_10_18_..._extend_loan_and_savings_statuses` | Extend enum status |

---

## 🌱 Seeder

### Menjalankan seeder

```bash
php artisan db:seed
# atau spesifik:
php artisan db:seed --class=AdminUserSeeder
php artisan db:seed --class=SystemConfigSeeder
```

### Membuat seeder baru

```bash
php artisan make:seeder NamaSeeder
```

### Contoh seeder — Admin User

```php
// database/seeders/AdminUserSeeder.php
User::create([
    'nama_lengkap' => 'Admin Koperasi',
    'username'     => 'admin',
    'email'        => 'admin@koperasi.com',
    'password'     => Hash::make('password123'),
    'role'         => 'admin',
    'status'       => 'aktif',
]);
```

---

## 🔧 Query Umum (Tips Developer)

### Dashboard — Total Simpanan per Anggota

```sql
SELECT
    u.nama_lengkap,
    SUM(CASE WHEN s.jenis = 'pokok'    THEN s.nominal ELSE 0 END) AS sim_pokok,
    SUM(CASE WHEN s.jenis = 'wajib'    THEN s.nominal ELSE 0 END) AS sim_wajib,
    SUM(CASE WHEN s.jenis = 'sukarela' THEN s.nominal ELSE 0 END) AS sim_sukarela,
    SUM(s.nominal) AS total
FROM users u
LEFT JOIN simpanans s ON s.id_anggota = u.id AND s.status = 'aktif'
WHERE u.role = 'anggota'
GROUP BY u.id, u.nama_lengkap
ORDER BY total DESC;
```

### Pinjaman Aktif + Sisa Cicilan

```sql
SELECT
    u.nama_lengkap,
    p.jenis,
    p.nominal AS pokok,
    p.tenor,
    COUNT(c.id) AS total_cicilan,
    SUM(CASE WHEN c.status = 'belum' THEN c.nominal ELSE 0 END) AS sisa_kewajiban
FROM pinjamen p
JOIN users u ON u.id = p.id_anggota
LEFT JOIN cicilans c ON c.id_pinjaman = p.id
WHERE p.status = 'aktif'
GROUP BY p.id, u.nama_lengkap, p.jenis, p.nominal, p.tenor;
```

### Anggota Pending Persetujuan

```sql
-- Anggota belum diverifikasi
SELECT id, nama_lengkap, email, created_at
FROM users
WHERE status = 'pending' AND role = 'anggota'
ORDER BY created_at ASC;

-- Simpanan pending
SELECT s.*, u.nama_lengkap
FROM simpanans s JOIN users u ON u.id = s.id_anggota
WHERE s.status = 'pending'
ORDER BY s.created_at ASC;

-- Pinjaman pending
SELECT p.*, u.nama_lengkap
FROM pinjamen p JOIN users u ON u.id = p.id_anggota
WHERE p.status = 'pending'
ORDER BY p.created_at ASC;
```

### Transaksi PPOB per Bulan

```sql
SELECT
    DATE_FORMAT(created_at, '%Y-%m') AS bulan,
    jenis_produk,
    COUNT(*) AS total_transaksi,
    SUM(CASE WHEN status IN ('sukses','berhasil','success','aktif') THEN harga ELSE 0 END) AS omzet,
    SUM(CASE WHEN status IN ('sukses','berhasil','success','aktif') THEN 1 ELSE 0 END) AS berhasil
FROM ppob_transaksi
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 12 MONTH)
GROUP BY bulan, jenis_produk
ORDER BY bulan DESC, total_transaksi DESC;
```

### E-Wallet Pending Verifikasi

```sql
SELECT
    et.*,
    u.nama_lengkap,
    u.username
FROM ewallet_transactions et
JOIN users u ON u.id = et.id_anggota
WHERE et.tipe = 'topup' AND et.status = 'pending'
ORDER BY et.created_at ASC;
```

### Laporan Neraca Koperasi

```sql
SELECT
    -- ASET
    (SELECT COALESCE(SUM(nominal), 0) FROM simpanans WHERE status = 'aktif') AS total_simpanan,
    (SELECT COALESCE(SUM(nominal), 0) FROM pinjamen WHERE status IN ('aktif','disetujui')) AS total_pinjaman,
    (SELECT COALESCE(SUM(saldo), 0) FROM ewallets) AS total_saldo_ewallet,
    -- PENDAPATAN PPOB
    (SELECT COALESCE(SUM(harga), 0) FROM ppob_transaksi WHERE status IN ('sukses','berhasil','success','aktif')) AS omzet_ppob,
    -- ANGGOTA
    (SELECT COUNT(*) FROM users WHERE role = 'anggota' AND status = 'aktif') AS jumlah_anggota_aktif,
    (SELECT COUNT(*) FROM users WHERE role = 'anggota' AND status = 'pending') AS anggota_pending;
```

---

## 💾 Backup & Restore

### Backup database

```bash
# Backup full
mysqldump -u root -p koperasi_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Backup dengan gzip
mysqldump -u root -p koperasi_db | gzip > backup_$(date +%Y%m%d).sql.gz

# Backup tabel tertentu saja
mysqldump -u root -p koperasi_db users simpanans pinjamen > backup_core.sql
```

### Restore database

```bash
mysql -u root -p koperasi_db < backup_20261029.sql

# Dari gzip
gunzip < backup_20261029.sql.gz | mysql -u root -p koperasi_db
```

### Backup otomatis (cron)

```bash
# /etc/cron.d/koperasi-backup
0 2 * * * root /usr/bin/mysqldump -u koperasi_user -pPASSWORD koperasi_db | gzip > /var/backups/koperasi/koperasi_$(date +\%Y\%m\%d).sql.gz && find /var/backups/koperasi/ -name "*.sql.gz" -mtime +30 -delete
```

---

## 🔍 Maintenance Queries

### Cek ukuran tabel

```sql
SELECT
    table_name,
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb,
    table_rows
FROM information_schema.TABLES
WHERE table_schema = 'koperasi_db'
ORDER BY (data_length + index_length) DESC;
```

### Cek index yang ada

```sql
SHOW INDEX FROM ppob_transaksi;
SHOW INDEX FROM ewallet_transactions;
```

### Analisis query lambat

```sql
-- Aktifkan slow query log (di my.cnf):
-- slow_query_log = 1
-- slow_query_log_file = /var/log/mysql/slow.log
-- long_query_time = 2

-- Lihat query yang berjalan sekarang
SHOW PROCESSLIST;

-- Explain sebuah query
EXPLAIN SELECT * FROM ppob_transaksi WHERE id_anggota = 1 AND status = 'sukses';
```

### Reset Auto Increment (setelah delete)

```sql
ALTER TABLE ppob_transaksi AUTO_INCREMENT = 1;
```

---

## ⚠️ Hal-hal Penting

> [!CAUTION]
> **Jangan pernah jalankan `migrate:fresh` di production!** Ini akan menghapus semua data.

> [!WARNING]
> Sebelum update status pinjaman/simpanan secara manual di DB, pastikan ada entri di `audit_logs` untuk keperluan audit trail.

> [!NOTE]
> Kolom `balance_after` di `ewallet_transactions` adalah snapshot saldo setelah transaksi — diisi otomatis oleh `EwalletService`. Jangan diubah manual.

> [!TIP]
> Gunakan `DB::transaction()` setiap kali mengubah saldo e-wallet atau status pinjaman, untuk menghindari inkonsistensi data akibat concurrent requests.

---

## 🛡️ Database Security Checklist

- [ ] User MySQL production **tidak** menggunakan `root`
- [ ] Password MySQL minimal 16 karakter dengan kombinasi
- [ ] Akses MySQL hanya dari `localhost` (bukan 0.0.0.0)
- [ ] `DB_PASSWORD` tidak pernah di-commit ke git
- [ ] Backup berjalan otomatis setiap hari
- [ ] Backup disimpan di lokasi berbeda dari server (offsite)
- [ ] Tidak ada kolom password yang disimpan plaintext
- [ ] Slow query log diaktifkan untuk monitoring
