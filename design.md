# 🎨 Design System — Koperasi K-Samara

Panduan desain UI/UX yang konsisten untuk seluruh tampilan aplikasi Koperasi K-Samara, mencakup frontend anggota (PWA) dan panel admin (Filament).

---

## 🎨 Color Palette

### Primary Colors

| Nama | HEX | Tailwind | Penggunaan |
|---|---|---|---|
| **Emerald Primary** | `#059669` | `emerald-600` | CTA, highlight utama, tombol utama |
| **Emerald Dark** | `#047857` | `emerald-700` | Hover state, border aktif |
| **Emerald Light** | `#d1fae5` | `emerald-100` | Background badge sukses |
| **Teal** | `#0f766e` | `teal-700` | Gradient pair emerald |

### Accent Colors

| Nama | HEX | Tailwind | Penggunaan |
|---|---|---|---|
| **Amber Gold** | `#f59e0b` | `amber-500` | Badge pending, warning, highlight |
| **Amber Dark** | `#b45309` | `amber-700` | Teks badge pending |
| **Amber Light** | `#fef3c7` | `amber-100` | Background badge pending |

### Semantic Colors

| Nama | HEX | Penggunaan |
|---|---|---|
| **Sukses** | `#10b981` / `#d1fae5` | Status berhasil, saldo positif |
| **Pending** | `#f59e0b` / `#fef3c7` | Menunggu verifikasi |
| **Gagal/Error** | `#e11d48` / `#ffe4e6` | Transaksi gagal, error form |
| **Info** | `#3b82f6` / `#dbeafe` | Informasi umum |
| **Purple E-Wallet** | `#7c3aed` / `#ede9fe` | Fitur E-Wallet koperasi |

### Neutral Colors

| Nama | HEX | Tailwind | Penggunaan |
|---|---|---|---|
| **Gray 900** | `#111827` | `gray-900` | Teks judul utama |
| **Gray 700** | `#374151` | `gray-700` | Teks body |
| **Gray 500** | `#6b7280` | `gray-500` | Teks sekunder, caption |
| **Gray 100** | `#f3f4f6` | `gray-100` | Background card tipis |
| **White** | `#ffffff` | `white` | Background card utama |

---

## 🔤 Typography

### Frontend Anggota (PWA)

```css
/* Base font: Inter (dari Tailwind default) */
body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }

/* Scale */
--text-2xl: 1.5rem / font-weight: 900   → Nominal besar (saldo, total)
--text-xl:  1.25rem / font-weight: 800  → Header halaman
--text-base: 1rem / font-weight: 700    → Nama produk, label kartu
--text-sm:  0.875rem / font-weight: 600 → Body teks
--text-xs:  0.75rem / font-weight: 500  → Caption, label form
--text-[10px]: 0.625rem / font-weight: 800 → Badge label
```

### Panel Admin (Filament)

```css
/* Stat card numbers */
font-size: 1.35rem; font-weight: 900;

/* Section headers */
font-size: 0.92rem; font-weight: 800;

/* Table headers */
font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.06em;

/* Badge labels */
font-size: 0.65rem; font-weight: 800;
```

---

## 🃏 Komponen UI — Frontend Anggota

### Gradient Cards

```jsx
{/* Card Simpanan */}
<div className="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-4 text-white shadow-lg">

{/* Card Pinjaman */}
<div className="bg-gradient-to-br from-cyan-500 to-blue-700 rounded-2xl p-4 text-white shadow-lg">

{/* Card E-Wallet */}
<div className="bg-gradient-to-br from-violet-500 to-purple-700 rounded-2xl p-4 text-white shadow-lg">

{/* Card PPOB Header */}
<div style={{ background: 'linear-gradient(135deg, #5b21b6 0%, #7c3aed 100%)' }}>
```

### Status Badges

```jsx
{/* Sukses */}
<span className="bg-emerald-100 text-emerald-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
  Berhasil
</span>

{/* Pending */}
<span className="bg-amber-100 text-amber-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
  Menunggu
</span>

{/* Gagal */}
<span className="bg-red-100 text-red-700 text-[10px] font-bold px-2 py-0.5 rounded-full">
  Gagal
</span>
```

### Menu Icon Grid

```jsx
{/* 3-kolom grid untuk menu layanan */}
<div className="grid grid-cols-3 gap-3">
  <button className="bg-white rounded-xl p-3 shadow-sm border border-gray-100 hover:shadow-md transition active:scale-95">
    <div className="flex justify-center mb-2">
      <Icon className="w-6 h-6" style={{ color: '#10b981' }} />
    </div>
    <p className="text-xs font-bold text-gray-900">Label</p>
  </button>
</div>

{/* 4-kolom grid untuk menu PPOB */}
<div className="grid grid-cols-4 gap-3">
  <Link className="flex flex-col items-center gap-2 bg-white rounded-2xl p-3 shadow-sm">
    <div className="w-12 h-12 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-xl">
      <i className="fas fa-sim-card"></i>
    </div>
    <span className="text-[11px] font-semibold text-gray-700">Isi Pulsa</span>
  </Link>
</div>
```

### Bottom Navigation

```jsx
<nav className="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-100 z-50 shadow-[0_-2px_12px_rgba(0,0,0,0.06)]">
  {/* Warna aktif: text-emerald-600 */}
  {/* Warna inaktif: text-gray-400 */}
  {/* Indikator aktif: w-8 h-0.5 bg-emerald-500 di atas icon */}
</nav>
```

### Header Mobile

```jsx
<div className="header-mobile px-5 py-4">
  {/* Background: linear-gradient emerald → teal */}
  {/* Tinggi: auto, konten: back button + title + action button */}
</div>
```

---

## 🖥️ Komponen UI — Panel Admin (Filament)

### Stat Cards

```css
/* Standard stat card */
.ks-stat-card {
  background: #ffffff;
  border: 1px solid #f3f4f6;
  border-left: 4px solid [accent-color];
  border-radius: 0.9rem;
  padding: 1rem;
  min-height: 115px;
  box-shadow: 0 1px 4px rgba(0,0,0,0.04);
  transition: transform 0.22s ease, box-shadow 0.22s ease;
}

/* Gradient stat card (highlight) */
background: linear-gradient(135deg, #059669, #0f766e);
box-shadow: 0 4px 14px rgba(5,150,105,0.25);
```

### Dashboard Switcher Tabs

```css
.ks-switcher {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 0.85rem;
  padding: 0.35rem;
  width: fit-content;
}

.ks-switcher-active {
  background: linear-gradient(135deg, #059669 0%, #047857 100%);
  color: #ffffff;
  box-shadow: 0 2px 8px rgba(5,150,105,0.25);
  border-radius: 0.65rem;
  padding: 0.5rem 1rem;
}
```

### Table Design

```css
.ks-tbl th {
  background: #f9fafb;
  color: #6b7280;
  font-weight: 800;
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 0.06em;
}

.ks-tbl tbody tr:hover td { background: #f0fdf4; }
```

---

## 📐 Spacing & Layout

### Frontend Anggota

| Elemen | Value |
|---|---|
| Page padding | `px-4` (16px) |
| Card gap | `space-y-3` (12px) |
| Section padding bottom | `90px` (BottomNav height) |
| Border radius card | `rounded-2xl` (16px) |
| Border radius button | `rounded-xl` (12px) |
| Icon size (menu) | `w-6 h-6` (24px) |
| Icon size (PPOB menu) | `w-12 h-12` (48px) |

### Admin Panel

| Grid | Breakpoint |
|---|---|
| `ks-grid6` (6 kolom) | Full → 3 kol (1200px) → 2 kol (800px) → 1 kol (480px) |
| `ks-grid4` (4 kolom) | Full → 2 kol (1200px) → 2 kol (800px) → 1 kol (480px) |
| `ks-grid-mid` (2/3 + 1/3) | Full → 1 kol (800px) |

---

## 🎭 Iconography

### Frontend: Lucide React

```jsx
import { Wallet, HandCoins, PiggyBank, Receipt, Smartphone, Zap, Plus, History, ArrowLeft, Check, LoaderCircle } from 'lucide-react'

// Ukuran standar
<Icon size={18} />   // Dalam form/tombol kecil
<Icon size={20} />   // List item icon
<Icon size={24} />   // Menu grid icon (w-6 h-6)
<Icon size={48} />   // Empty state illustration
```

### Admin: Font Awesome + Hero Icons

```html
<!-- Font Awesome (via CDN) -->
<i class="fas fa-wallet"></i>
<i class="fas fa-receipt"></i>
<i class="fas fa-bolt"></i>
<i class="fas fa-sim-card"></i>
<i class="fas fa-chart-area"></i>

<!-- Hero Icons (Filament native) -->
heroicon-o-bolt
heroicon-o-chart-bar
heroicon-o-users
```

---

## 📱 Responsive Design

### Breakpoints (Frontend PWA)

Aplikasi dirancang **mobile-first** karena ini adalah PWA untuk anggota koperasi.

| Breakpoint | Target |
|---|---|
| `< 480px` | HP kecil (utama) |
| `480px – 768px` | HP besar / tablet kecil |
| `> 768px` | Tablet / desktop (fallback) |

### Breakpoints (Admin Panel)

Admin panel dirancang untuk **desktop-first** dengan graceful degradation ke tablet.

| Breakpoint | Behavior |
|---|---|
| `> 1200px` | Full layout, grid 6 kolom |
| `800px – 1200px` | Grid 3 kolom |
| `< 800px` | Grid 2 kolom, sidebar collapse |
| `< 480px` | Single column |

---

## ✅ Design Principles

1. **Mobile-first untuk anggota** — Semua halaman anggota harus nyaman di HP 360px
2. **Consistent green theme** — Emerald sebagai warna utama di semua halaman
3. **Clear status feedback** — Setiap aksi punya loading, success, dan error state
4. **Financial clarity** — Angka nominal selalu diformat `Rp 1.000.000` (ID locale)
5. **Accessible tap targets** — Minimum 44px untuk tombol interaktif di mobile
6. **Progressive disclosure** — Info detail hanya tampil saat dibutuhkan
