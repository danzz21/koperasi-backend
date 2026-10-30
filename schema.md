# 📊 Database Schema — Koperasi K-Samara

Dokumentasi lengkap skema database MySQL untuk sistem Koperasi K-Samara.

---

## 🗺️ Entity Relationship Diagram

```
users ─────────────────────────────────────────────────────────────┐
  │ (1)                                                             │
  │                                                                 │
  ├─(1:N)──► simpanans          ├─(1:N)──► cicilans                │
  ├─(1:N)──► pinjamen           ├─(1:N)──► pembayarans              │
  ├─(1:N)──► transaksis         ├─(1:N)──► ppob_transaksi           │
  ├─(1:1)──► ewallets           ├─(1:N)──► ewallet_transactions     │
  ├─(1:N)──► passkeys           ├─(1:N)──► audit_logs              │
  ├─(1:N)──► withdrawal_requests                                    │
  └─(1:N)──► notifications                                         │
                                                                    │
pinjamen ──(1:N)──► akads                                          │
pinjamen ──(1:N)──► cicilans ──(1:N)──► pembayarans               │
simpanans ──(1:N)──► pembayarans                                   │
```

---

## 📋 Tabel Detail

### `users` — Data Anggota & Admin

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL | Primary key, auto increment |
| `nama_lengkap` | varchar(255) | NOT NULL | Nama lengkap |
| `username` | varchar(255) | NOT NULL UNIQUE | Username unik |
| `email` | varchar(255) | NOT NULL UNIQUE | Email |
| `email_verified_at` | timestamp | NULL | Waktu verifikasi email |
| `password` | varchar(255) | NULL | Hashed password (nullable untuk Google login) |
| `role` | enum('anggota','admin','superadmin') | NOT NULL DEFAULT 'anggota' | Role pengguna |
| `no_hp` | varchar(20) | NULL | Nomor HP |
| `nik` | varchar(20) | NULL | NIK KTP |
| `alamat` | text | NULL | Alamat lengkap |
| `tgl_lahir` | date | NULL | Tanggal lahir |
| `foto_profil` | varchar(255) | NULL | Path foto profil |
| `google_id` | varchar(255) | NULL | Google OAuth ID |
| `google_avatar` | varchar(255) | NULL | URL avatar Google |
| `needs_onboarding` | boolean | NOT NULL DEFAULT false | Perlu lengkapi profil |
| `profile_completed_at` | timestamp | NULL | Waktu profil dilengkapi |
| `status` | enum('pending','aktif','ditolak','nonaktif') | NOT NULL DEFAULT 'pending' | Status keanggotaan |
| `remember_token` | varchar(100) | NULL | Remember me token |
| `created_at` | timestamp | NULL | |
| `updated_at` | timestamp | NULL | |

**Indexes:** `email` (unique), `username` (unique), `google_id`, `role`

---

### `simpanans` — Simpanan Anggota

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL | Primary key |
| `id_anggota` | bigint UNSIGNED | NOT NULL | FK → users.id |
| `jenis` | enum('pokok','wajib','sukarela') | NOT NULL | Jenis simpanan |
| `nominal` | decimal(15,2) | NOT NULL | Jumlah simpanan |
| `status` | enum('pending','aktif','ditolak','lunas') | NOT NULL DEFAULT 'pending' | Status |
| `tanggal` | date | NULL | Tanggal setoran |
| `keterangan` | text | NULL | Catatan |
| `created_at` | timestamp | NULL | |
| `updated_at` | timestamp | NULL | |

**Indexes:** `(id_anggota, status)`, `(id_anggota, jenis)`

---

### `pinjamen` — Pinjaman Anggota

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL | Primary key |
| `id_anggota` | bigint UNSIGNED | NOT NULL | FK → users.id |
| `jenis` | enum('al_qordh','murobahah','mudhorobah') | NOT NULL | Jenis akad pinjaman |
| `nominal` | decimal(15,2) | NOT NULL | Pokok pinjaman |
| `margin` | decimal(5,2) | NOT NULL DEFAULT 0 | Margin/bagi hasil (%) |
| `tenor` | int | NOT NULL | Jumlah bulan cicilan |
| `status` | enum('pending','disetujui','aktif','lunas','ditolak','macet') | NOT NULL DEFAULT 'pending' | Status pinjaman |
| `tgl_pengajuan` | date | NULL | Tanggal pengajuan |
| `tgl_disetujui` | date | NULL | Tanggal persetujuan |
| `keterangan` | text | NULL | Catatan admin |
| `created_at` | timestamp | NULL | |
| `updated_at` | timestamp | NULL | |

**Indexes:** `(id_anggota, status)`

---

### `cicilans` — Jadwal Cicilan Pinjaman

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL | Primary key |
| `id_pinjaman` | bigint UNSIGNED | NOT NULL | FK → pinjamen.id |
| `id_anggota` | bigint UNSIGNED | NOT NULL | FK → users.id (denormalisasi) |
| `ke` | int | NOT NULL | Urutan cicilan (1, 2, 3, …) |
| `nominal` | decimal(15,2) | NOT NULL | Nominal cicilan per bulan |
| `jatuh_tempo` | date | NOT NULL | Tanggal jatuh tempo |
| `tgl_bayar` | date | NULL | Tanggal realisasi pembayaran |
| `status` | enum('belum','lunas','terlambat') | NOT NULL DEFAULT 'belum' | Status cicilan |
| `created_at` | timestamp | NULL | |
| `updated_at` | timestamp | NULL | |

**Indexes:** `(id_pinjaman)`, `(id_anggota, status)`, `(jatuh_tempo)`

---

### `pembayarans` — Riwayat Pembayaran

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL | Primary key |
| `id_anggota` | bigint UNSIGNED | NOT NULL | FK → users.id |
| `id_simpanan` | bigint UNSIGNED | NULL | FK → simpanans.id |
| `id_cicilan` | bigint UNSIGNED | NULL | FK → cicilans.id |
| `jenis` | enum('simpanan_wajib','simpanan_sukarela','cicilan') | NOT NULL | Jenis pembayaran |
| `nominal` | decimal(15,2) | NOT NULL | Jumlah bayar |
| `metode` | varchar(50) | NULL | Metode pembayaran |
| `order_id` | varchar(100) | NULL UNIQUE | ID transaksi Midtrans |
| `status` | enum('pending','lunas','gagal') | NOT NULL DEFAULT 'pending' | Status pembayaran |
| `tgl_bayar` | date | NULL | |
| `created_at` | timestamp | NULL | |
| `updated_at` | timestamp | NULL | |

---

### `transaksis` — Transaksi Umum (Ledger)

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL | Primary key |
| `id_anggota` | bigint UNSIGNED | NOT NULL | FK → users.id |
| `jenis` | varchar(50) | NOT NULL | Jenis transaksi |
| `nominal` | decimal(15,2) | NOT NULL | Jumlah |
| `keterangan` | text | NULL | Deskripsi |
| `created_at` | timestamp | NULL | |
| `updated_at` | timestamp | NULL | |

---

### `ppob_transaksi` — Transaksi PPOB

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL | Primary key |
| `id_anggota` | bigint UNSIGNED | NOT NULL | FK → users.id |
| `order_id` | varchar(100) | NOT NULL UNIQUE | ID order unik |
| `jenis_produk` | enum('pulsa','paket_data','token_listrik','ewallet') | NOT NULL | Kategori produk |
| `kode_produk` | varchar(50) | NULL | Kode produk provider |
| `nama_produk` | varchar(100) | NULL | Nama produk |
| `provider` | varchar(50) | NULL | Provider (Telkomsel, PLN, dll) |
| `nomor_tujuan` | varchar(30) | NOT NULL | No HP / No Meter tujuan |
| `nominal` | decimal(12,2) | NOT NULL | Nilai produk |
| `harga` | decimal(12,2) | NOT NULL | Harga jual ke anggota |
| `payment_method` | varchar(50) | NULL | Metode bayar |
| `status` | varchar(30) | NOT NULL DEFAULT 'pending' | Status transaksi |
| `midtrans_snap_token` | text | NULL | Snap token Midtrans |
| `midtrans_payment_url` | text | NULL | URL pembayaran Midtrans |
| `created_at` | timestamp | NULL | |
| `updated_at` | timestamp | NULL | |

**Indexes:** `order_id` (unique), `(id_anggota)`, `(status)`, `(jenis_produk)`

---

### `ewallets` — Saldo E-Wallet per Anggota

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL | Primary key |
| `id_anggota` | bigint UNSIGNED | NOT NULL UNIQUE | FK → users.id (1:1) |
| `saldo` | decimal(15,2) | NOT NULL DEFAULT 0 | Saldo e-wallet saat ini |
| `created_at` | timestamp | NULL | |
| `updated_at` | timestamp | NULL | |

**Indexes:** `id_anggota` (unique)

---

### `ewallet_transactions` — Riwayat Mutasi E-Wallet

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL | Primary key |
| `id_anggota` | bigint UNSIGNED | NOT NULL | FK → users.id |
| `tipe` | enum('topup','bayar') | NOT NULL | Jenis mutasi |
| `status` | enum('pending','sukses','gagal') | NOT NULL | Status transaksi |
| `nominal` | decimal(15,2) | NOT NULL | Jumlah transaksi |
| `metode` | varchar(50) | NULL | Metode topup (transfer_bca, dll) |
| `ref_id` | varchar(100) | NULL UNIQUE | Referensi ID unik |
| `balance_after` | decimal(15,2) | NULL | Saldo setelah transaksi |
| `keterangan` | text | NULL | Catatan |
| `created_at` | timestamp | NULL | |
| `updated_at` | timestamp | NULL | |

**Indexes:** `ref_id` (unique), `(id_anggota, status)`

---

### `akads` — Akad Pinjaman

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL | Primary key |
| `id_pinjaman` | bigint UNSIGNED | NOT NULL | FK → pinjamen.id |
| `id_anggota` | bigint UNSIGNED | NOT NULL | FK → users.id |
| `nomor_akad` | varchar(50) | NULL UNIQUE | Nomor dokumen akad |
| `tanggal` | date | NULL | Tanggal akad |
| `keterangan` | text | NULL | |
| `created_at` | timestamp | NULL | |
| `updated_at` | timestamp | NULL | |

---

### `passkeys` — WebAuthn / Passkey

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL | Primary key |
| `id_user` | bigint UNSIGNED | NOT NULL | FK → users.id |
| `credential_id` | text | NOT NULL | WebAuthn credential ID |
| `public_key` | text | NOT NULL | Public key (COSE format) |
| `sign_count` | bigint UNSIGNED | NOT NULL DEFAULT 0 | Replay attack counter |
| `device_name` | varchar(100) | NULL | Nama perangkat |
| `last_used_at` | timestamp | NULL | |
| `created_at` | timestamp | NULL | |
| `updated_at` | timestamp | NULL | |

---

### `audit_logs` — Log Aktivitas

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL | Primary key |
| `user_id` | bigint UNSIGNED | NULL | FK → users.id (nullable untuk system) |
| `action` | varchar(100) | NOT NULL | Nama aksi (create, update, approve, dll) |
| `model_type` | varchar(100) | NULL | Class model yang terpengaruh |
| `model_id` | bigint UNSIGNED | NULL | ID record yang terpengaruh |
| `before` | json | NULL | Data sebelum perubahan |
| `after` | json | NULL | Data setelah perubahan |
| `ip_address` | varchar(45) | NULL | IP address |
| `user_agent` | text | NULL | Browser/client info |
| `created_at` | timestamp | NULL | |

---

### `system_configs` — Konfigurasi Sistem

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL | Primary key |
| `key` | varchar(100) | NOT NULL UNIQUE | Kunci konfigurasi |
| `value` | text | NULL | Nilai konfigurasi |
| `keterangan` | text | NULL | Deskripsi konfigurasi |
| `created_at` | timestamp | NULL | |
| `updated_at` | timestamp | NULL | |

---

### `withdrawal_requests` — Penarikan Dana

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL | Primary key |
| `id_anggota` | bigint UNSIGNED | NOT NULL | FK → users.id |
| `nominal` | decimal(15,2) | NOT NULL | Jumlah penarikan |
| `bank` | varchar(50) | NULL | Nama bank tujuan |
| `no_rekening` | varchar(30) | NULL | Nomor rekening tujuan |
| `atas_nama` | varchar(100) | NULL | Nama pemilik rekening |
| `status` | enum('pending','disetujui','ditolak','diproses') | NOT NULL DEFAULT 'pending' | |
| `catatan_admin` | text | NULL | |
| `created_at` | timestamp | NULL | |
| `updated_at` | timestamp | NULL | |

---

### `notifications` — Notifikasi Anggota

| Kolom | Tipe | Nullable | Keterangan |
|---|---|---|---|
| `id` | char(36) | NOT NULL | UUID primary key |
| `type` | varchar(255) | NOT NULL | Class notifikasi |
| `notifiable_type` | varchar(255) | NOT NULL | Model target |
| `notifiable_id` | bigint UNSIGNED | NOT NULL | ID target |
| `data` | text | NOT NULL | JSON payload |
| `read_at` | timestamp | NULL | Waktu dibaca |
| `created_at` | timestamp | NULL | |
| `updated_at` | timestamp | NULL | |

---

## 🔗 Foreign Key Summary

```
simpanans.id_anggota         → users.id (CASCADE DELETE)
pinjamen.id_anggota          → users.id (CASCADE DELETE)
cicilans.id_pinjaman         → pinjamen.id (CASCADE DELETE)
cicilans.id_anggota          → users.id
pembayarans.id_anggota       → users.id
pembayarans.id_cicilan       → cicilans.id
ppob_transaksi.id_anggota    → users.id
ewallets.id_anggota          → users.id (CASCADE DELETE)
ewallet_transactions.id_anggota → users.id (CASCADE DELETE)
akads.id_pinjaman            → pinjamen.id
passkeys.id_user             → users.id (CASCADE DELETE)
withdrawal_requests.id_anggota → users.id
```

---

## 📈 Index Strategy

```sql
-- Query paling sering untuk dashboard admin
CREATE INDEX idx_simpanan_status ON simpanans(status);
CREATE INDEX idx_pinjaman_status ON pinjamen(status);
CREATE INDEX idx_cicilan_jatuh_tempo ON cicilans(jatuh_tempo);
CREATE INDEX idx_ppob_jenis ON ppob_transaksi(jenis_produk);
CREATE INDEX idx_ppob_status ON ppob_transaksi(status);
CREATE INDEX idx_ewallet_anggota ON ewallets(id_anggota);

-- Composite index untuk filter per anggota
CREATE INDEX idx_simpanan_anggota_jenis ON simpanans(id_anggota, jenis);
CREATE INDEX idx_ewallet_trx ON ewallet_transactions(id_anggota, status);
```
