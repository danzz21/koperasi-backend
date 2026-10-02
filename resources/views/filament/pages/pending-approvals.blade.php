<x-filament-panels::page>
    {{-- Halaman "Persetujuan Pending": dipakai bersama oleh semua kategori
         (anggota, simpanan sukarela/pokok, pinjaman, pembayaran). --}}
    <style>
        .ks-pending-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 1rem;
            padding: 1rem 1.25rem;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.04);
            margin-bottom: 1.25rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }
        .ks-pending-left {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            flex: 1;
            min-width: 260px;
        }
        .ks-pending-icon-box {
            width: 42px;
            height: 42px;
            border-radius: 0.75rem;
            background: #fef3c7;
            color: #d97706;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
            border: 1px solid #fde68a;
        }
        .ks-pending-title {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 800;
            color: #1f2937;
            line-height: 1.2;
        }
        .ks-pending-desc {
            margin: 0.25rem 0 0;
            font-size: 0.75rem;
            color: #6b7280;
            line-height: 1.35;
        }
        .ks-pending-btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.9rem;
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 0.65rem;
            font-size: 0.75rem;
            font-weight: 700;
            color: #374151;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            white-space: nowrap;
            cursor: pointer;
        }
        .ks-pending-btn-back:hover {
            background: #ecfdf5;
            border-color: #a7f3d0;
            color: #047857;
            transform: translateX(-2px);
        }
        .ks-pending-btn-back svg,
        .ks-pending-btn-back i {
            width: 14px;
            height: 14px;
            max-width: 14px;
            max-height: 14px;
            font-size: 0.75rem;
            flex-shrink: 0;
            display: inline-block;
            vertical-align: middle;
        }
    </style>

    <div class="ks-pending-card">
        <div class="ks-pending-left">
            <div class="ks-pending-icon-box">
                <i class="fas fa-bell" style="font-size:1.1rem;color:#d97706;"></i>
            </div>
            <div>
                <p class="ks-pending-title">Persetujuan Pending</p>
                <p class="ks-pending-desc">
                    Setujui atau tolak pengajuan di bawah. Data yang sudah diproses otomatis hilang dari daftar ini dan jumlah pada kartu dashboard ikut turun.
                </p>
            </div>
        </div>
        <div>
            <a href="{{ \App\Filament\Pages\Dashboard::getUrl() }}" class="ks-pending-btn-back">
                <svg style="width:14px;height:14px;display:inline-block;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                <span>Kembali ke Dashboard</span>
            </a>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
