# 🏗️ Architecture — Koperasi K-Samara

Dokumentasi arsitektur sistem aplikasi Koperasi Simpan Pinjam K-Samara.

---

## 📐 Overview Sistem

```
┌──────────────────────────────────────────────────────────────────┐
│                        KOPERASI K-SAMARA                         │
│                                                                  │
│  ┌──────────────────┐          ┌──────────────────────────────┐  │
│  │  koperasi-frontend│          │      koperasi-backend         │  │
│  │   React 18 + Vite │  REST    │      Laravel 13 + PHP 8.3     │  │
│  │   PWA + Tailwind  │◄────────►│  Filament v5 Admin Panel      │  │
│  │   Zustand + RQ    │  API     │  Laravel Sanctum (Auth)       │  │
│  └──────────────────┘          └──────────────────────────────┘  │
│           │                                    │                  │
│           │                          ┌─────────▼──────────┐      │
│           │                          │   MySQL 8.0 DB      │      │
│           │                          │   koperasi_db       │      │
│           │                          └────────────────────┘      │
│           │                                    │                  │
│           └──────────────── External ──────────┘                  │
│                          ┌──────────────────┐                    │
│                          │  Midtrans Gateway │                    │
│                          │  (Payment)        │                    │
│                          └──────────────────┘                    │
└──────────────────────────────────────────────────────────────────┘
```

---

## 🔧 Stack Teknologi

### Backend

| Layer | Teknologi | Versi | Fungsi |
|---|---|---|---|
| Framework | Laravel | 13.x | Core backend framework |
| PHP | PHP | 8.3 | Runtime language |
| Admin Panel | Filament | 5.7 | UI admin (no-code) |
| Auth API | Laravel Sanctum | 4.3 | Token-based auth anggota |
| HTTP Client | Guzzle | 7.x | External API calls |
| Database ORM | Eloquent | (Laravel built-in) | ORM |
| Queue | Laravel Queue | (Redis/DB) | Background jobs |

### Frontend

| Layer | Teknologi | Versi | Fungsi |
|---|---|---|---|
| Framework | React | 18.3 | UI framework |
| Build Tool | Vite | 6.x | Bundler + dev server |
| Routing | React Router | 7.x | Client-side routing |
| State | Zustand | 5.x | Global auth state |
| Data Fetching | TanStack Query | 5.x | Server state + cache |
| HTTP | Axios | 1.7 | API client |
| Styling | Tailwind CSS | 3.4 | Utility-first CSS |
| Icons | Lucide React | 1.31 | Icon library |
| Charts | Chart.js | 4.4 | Grafik di admin |
| PWA | vite-plugin-pwa | 1.3 | Service worker + manifest |

### Infrastructure

| Komponen | Teknologi |
|---|---|
| Database | MySQL 8.0 |
| Web Server | Nginx |
| SSL | Let's Encrypt |
| Queue Worker | Supervisor |
| Cache | Redis (production) / File (dev) |
| Payment | Midtrans Snap |

---

## 🗂️ Struktur Direktori Backend

```
koperasi-backend/
├── app/
│   ├── Filament/
│   │   ├── Pages/                  # Halaman custom Filament
│   │   │   ├── Dashboard.php       # Dashboard utama koperasi
│   │   │   ├── DashboardPpob.php   # Dashboard PPOB & E-Wallet
│   │   │   ├── LaporanAnalisis.php # Laporan keuangan
│   │   │   ├── PendingAnggota.php  # Persetujuan anggota
│   │   │   ├── PendingPinjaman.php # Persetujuan pinjaman
│   │   │   ├── PendingSukarela.php # Persetujuan simpanan sukarela
│   │   │   ├── PendingSimpananPokok.php
│   │   │   └── PendingPembayaran.php
│   │   └── Resources/              # Filament CRUD resources
│   │       ├── Users/UserResource.php
│   │       ├── Simpanans/SimpananResource.php
│   │       ├── Pinjamen/PinjamanResource.php
│   │       ├── Cicilans/CicilanResource.php
│   │       ├── Pembayarans/PembayaranResource.php
│   │       ├── Transaksis/TransaksiResource.php
│   │       ├── PpobTransaksis/PpobTransaksiResource.php
│   │       └── Akads/AkadResource.php
│   │
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/                # REST API controllers
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── DashboardController.php
│   │   │   │   ├── SimpananController.php
│   │   │   │   ├── PinjamanController.php
│   │   │   │   ├── CicilanController.php
│   │   │   │   ├── PpobController.php
│   │   │   │   ├── EwalletController.php
│   │   │   │   ├── PaymentGatewayController.php
│   │   │   │   ├── ProfilController.php
│   │   │   │   ├── OnboardingController.php
│   │   │   │   ├── WithdrawalController.php
│   │   │   │   ├── Admin/          # Admin-specific APIs
│   │   │   │   └── Superadmin/     # Superadmin APIs
│   │   └── Middleware/
│   │       ├── EnsureAdminRole.php
│   │       ├── EnsureProfileCompleted.php
│   │       └── RoleMiddleware.php
│   │
│   ├── Models/                     # Eloquent Models
│   │   ├── User.php
│   │   ├── Simpanan.php
│   │   ├── Pinjaman.php
│   │   ├── Cicilan.php
│   │   ├── Pembayaran.php
│   │   ├── Transaksi.php
│   │   ├── PpobTransaksi.php
│   │   ├── Ewallet.php
│   │   ├── EwalletTransaction.php
│   │   ├── Akad.php
│   │   ├── AuditLog.php
│   │   ├── Passkey.php
│   │   ├── SystemConfig.php
│   │   └── WithdrawalRequest.php
│   │
│   ├── Services/                   # Business logic layer
│   │   ├── AuthService.php
│   │   ├── SimpananService.php
│   │   ├── PinjamanService.php
│   │   ├── CicilanService.php
│   │   ├── PpobService.php
│   │   ├── EwalletService.php
│   │   ├── PaymentGatewayService.php
│   │   ├── MidtransService.php
│   │   ├── DashboardService.php
│   │   ├── FinancialReportService.php
│   │   └── ...
│   │
│   ├── Support/                    # Utility helpers
│   │   ├── EwalletService.php      # Core ewallet logic (dengan DB lock)
│   │   ├── PendingApprovals.php    # Single-source pending counters
│   │   ├── AuditLogger.php
│   │   ├── AuthUserPayload.php
│   │   └── WebAuthn/               # Passkey implementation
│   │
│   └── Providers/
│       ├── AppServiceProvider.php
│       └── Filament/
│           ├── AdminPanelProvider.php
│           └── SuperadminPanelProvider.php
│
├── database/
│   ├── migrations/                 # 31 migration files
│   └── seeders/
│
├── resources/
│   └── views/
│       ├── filament/
│       │   ├── pages/              # Custom blade views
│       │   │   ├── dashboard.blade.php
│       │   │   ├── dashboard-ppob.blade.php
│       │   │   └── pending-*.blade.php
│       │   └── widgets/
│       │       └── admin-dashboard-wireframe.blade.php
│       └── vendor/filament-panels/ # Overridden Filament views
│
└── public/
    └── css/
        └── admin-theme.css         # Custom theme untuk admin
```

---

## 🗂️ Struktur Direktori Frontend

```
koperasi-frontend/
└── src/
    ├── api/
    │   └── axios.js                # Base axios instance
    ├── components/
    │   ├── anggota/
    │   │   ├── MemberHeader.jsx    # Header profil anggota
    │   │   └── PpobProductPage.jsx # Shared PPOB product grid
    │   ├── BottomNav.jsx           # Navigasi bawah
    │   ├── Header.jsx
    │   ├── InstallBanner.jsx       # PWA install prompt
    │   ├── GoogleLoginButton.jsx
    │   └── PasskeyButton.jsx
    ├── hooks/
    │   ├── useGoogleSignIn.js
    │   ├── useInstallPrompt.js     # PWA install hook
    │   └── usePasskey.js
    ├── layouts/
    │   └── AnggotaLayout.jsx       # Layout wrapper dengan BottomNav
    ├── lib/
    │   ├── api.js                  # Axios dengan interceptors
    │   ├── postLogin.js
    │   └── webauthn.js
    ├── pages/
    │   ├── auth/
    │   │   ├── LoginPage.jsx
    │   │   ├── RegisterPage.jsx
    │   │   ├── ForgotPassword.jsx
    │   │   └── PasskeySetupModal.jsx
    │   └── anggota/
    │       ├── DashboardPage.jsx
    │       ├── SimpananPage.jsx
    │       ├── PinjamanPage.jsx
    │       ├── CicilanPage.jsx
    │       ├── ProfilPage.jsx
    │       ├── EditProfilPage.jsx
    │       ├── OnboardingPage.jsx
    │       ├── ppob/
    │       │   ├── PpobPage.jsx        # PPOB home
    │       │   ├── PulsaPage.jsx
    │       │   ├── DataPage.jsx
    │       │   ├── ListrikPage.jsx
    │       │   ├── EwalletPage.jsx     # E-Wallet koperasi
    │       │   ├── ConfirmationPage.jsx
    │       │   ├── StatusPage.jsx
    │       │   ├── RiwayatPage.jsx     # Riwayat PPOB
    │       │   └── RiwayatEwalletPage.jsx
    │       └── payment/
    │           ├── CicilanPage.jsx
    │           ├── SimpananPage.jsx
    │           ├── StatusPage.jsx
    │           └── RiwayatPage.jsx
    ├── store/
    │   └── authStore.js            # Zustand auth store
    ├── App.jsx                     # Root component + routing
    └── main.jsx
```

---

## 🔐 Authentication Flow

```
┌──────────┐    POST /api/auth/login     ┌──────────────┐
│  Anggota │ ─────────────────────────► │   Laravel     │
│ (React)  │                            │   Sanctum     │
│          │ ◄───── Bearer Token ─────── │               │
└──────────┘                            └──────────────┘
     │                                         │
     │ Token disimpan localStorage             │
     │                                         │ Middleware:
     │ Setiap request:                         │ - EnsureProfileCompleted
     │ Authorization: Bearer {token}           │ - RoleMiddleware
     │                                         │
     ▼                                         ▼
┌──────────┐                            ┌──────────────┐
│ Zustand  │ ◄──── refreshUser() ──────► │  /api/me     │
│ authStore│                            │              │
└──────────┘                            └──────────────┘

Auth Methods:
1. Email + Password (standar)
2. Google OAuth (GoogleAuthService)
3. Passkey / WebAuthn (PasskeyAuthService)
```

---

## 💰 Alur Transaksi PPOB

```
Anggota                   Frontend              Backend              Midtrans
   │                          │                    │                    │
   │── Pilih produk ─────────►│                    │                    │
   │                          │── POST /ppob/order►│                    │
   │                          │                    │── Create order ───►│
   │                          │                    │◄── Snap Token ─────│
   │                          │◄── order_id ───────│                    │
   │                          │                    │                    │
   │                          │── GET /ppob/status►│                    │
   │◄── Status tampil ────────│◄── {status} ───────│                    │
   │                          │                    │◄── Webhook notify ─│
   │                          │                    │    (payment done)  │
```

---

## 🏦 Alur E-Wallet Koperasi

```
Anggota            Frontend          Backend             Admin
   │                   │                │                  │
   │── Topup form ─────►│                │                  │
   │                   │── POST topup ──►│                  │
   │                   │                │── EwalletService  │
   │                   │                │   buatPermintaan()│
   │                   │◄── pending ────│                  │
   │                   │                │                  │
   │                   │                │◄── Admin approve ─│
   │                   │                │    setujuiTopup() │
   │                   │                │── saldo += nominal│
   │◄── Saldo bertambah │◄── sukses ─────│                  │
```

---

## 🔑 Role & Authorization

| Role | Panel | Kemampuan |
|---|---|---|
| `anggota` | Frontend PWA | Lihat data sendiri, PPOB, bayar cicilan, kelola e-wallet |
| `admin` | `/admin` (Filament) | Verifikasi anggota, kelola simpanan/pinjaman, approve e-wallet |
| `superadmin` | `/superadmin` (Filament) | Full access + manajemen user admin |

---

## 📡 API Structure

Semua API diawali `/api/`:

| Prefix | Target | Auth |
|---|---|---|
| `/api/anggota/*` | Fitur anggota | Bearer Token |
| `/api/admin/*` | Fitur admin (via AJAX) | Bearer Token (admin role) |
| `/api/auth/*` | Login, register, passkey | Public |
| `/api/payment/*` | Payment gateway callbacks | Public (Midtrans webhook) |
