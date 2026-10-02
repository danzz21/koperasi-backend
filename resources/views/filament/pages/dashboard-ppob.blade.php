<x-filament-panels::page>
<style>
    .ks-ppob-wrap { width: 100%; box-sizing: border-box; }
    .ks-switcher {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: #ffffff;
        padding: 0.35rem;
        border-radius: 0.85rem;
        border: 1px solid #e5e7eb;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        margin-bottom: 1.25rem;
        width: fit-content;
    }
    .ks-switcher-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        padding: 0.5rem 1rem;
        border-radius: 0.65rem;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .ks-switcher-active {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        color: #ffffff !important;
        box-shadow: 0 2px 8px rgba(5,150,105,0.25);
    }
    .ks-switcher-inactive {
        color: #4b5563;
        background: transparent;
    }
    .ks-switcher-inactive:hover {
        background: #f3f4f6;
        color: #111827;
    }
    .ks-stat-card {
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }
    .ks-stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 24px rgba(0,0,0,0.08);
    }
    .ks-grid6 {
        display: grid;
        grid-template-columns: repeat(6, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }
    .ks-grid4 {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.75rem;
        margin-bottom: 1.25rem;
    }
    .ks-grid-mid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1rem;
        margin-bottom: 1.25rem;
    }
    .ks-panel {
        background: #ffffff;
        border: 1px solid #f3f4f6;
        border-radius: 1rem;
        box-shadow: 0 1px 4px rgba(0,0,0,0.04);
        padding: 1.2rem;
    }
    .ks-btn-approve {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .25rem .7rem; border-radius: .45rem;
        background: #d1fae5; color: #047857;
        font-size: .65rem; font-weight: 800;
        border: 1px solid #a7f3d0; cursor: pointer;
        transition: background .15s;
    }
    .ks-btn-approve:hover { background: #a7f3d0; }
    .ks-btn-reject {
        display: inline-flex; align-items: center; gap: .3rem;
        padding: .25rem .7rem; border-radius: .45rem;
        background: #ffe4e6; color: #be123c;
        font-size: .65rem; font-weight: 800;
        border: 1px solid #fecdd3; cursor: pointer;
        transition: background .15s;
    }
    .ks-btn-reject:hover { background: #fecdd3; }
    .ks-tbl-wrap { width: 100%; overflow-x: auto; }
    .ks-tbl { width: 100%; border-collapse: collapse; font-size: 0.78rem; text-align: left; }
    .ks-tbl th {
        padding: 0.65rem 0.75rem;
        background: #f9fafb;
        color: #6b7280;
        font-weight: 800;
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        border-bottom: 1px solid #e5e7eb;
    }
    .ks-tbl td {
        padding: 0.65rem 0.75rem;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
    }
    .ks-tbl tbody tr:hover td { background: #f0fdf4; }
    .ks-badge-status {
        display: inline-block;
        padding: 0.2rem 0.55rem;
        border-radius: 9999px;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: capitalize;
    }
    .ks-b-sukses { background: #d1fae5; color: #047857; }
    .ks-b-pending { background: #fef3c7; color: #b45309; }
    .ks-b-gagal { background: #ffe4e6; color: #be123c; }

    @media(max-width: 1200px) {
        .ks-grid6 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .ks-grid4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media(max-width: 800px) {
        .ks-grid6 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ks-grid4 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .ks-grid-mid { grid-template-columns: 1fr; }
    }
    @media(max-width: 480px) {
        .ks-grid6 { grid-template-columns: 1fr; }
        .ks-grid4 { grid-template-columns: 1fr; }
    }
</style>

<div class="ks-ppob-wrap">

    {{-- DUAL DASHBOARD SWITCHER TABS --}}
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:.5rem;">
        <div class="ks-switcher">
            <a href="{{ \App\Filament\Pages\Dashboard::getUrl() }}" class="ks-switcher-btn ks-switcher-inactive">
                <i class="fas fa-landmark" style="font-size:.85rem;color:#059669;"></i>
                <span>Dashboard Utama (Koperasi)</span>
            </a>
            <a href="{{ \App\Filament\Pages\DashboardPpob::getUrl() }}" class="ks-switcher-btn ks-switcher-active">
                <i class="fas fa-bolt" style="font-size:.85rem;color:#fbbf24;"></i>
                <span>Dashboard PPOB &amp; E-Wallet</span>
            </a>
        </div>

        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
            <a href="/admin/ppob-transaksis" style="display:inline-flex;align-items:center;gap:.4rem;padding:.45rem .85rem;border-radius:.65rem;background:#059669;color:#fff;font-size:.72rem;font-weight:700;text-decoration:none;box-shadow:0 2px 6px rgba(5,150,105,.2);">
                <i class="fas fa-list"></i>
                <span>Semua Data Transaksi</span>
            </a>
            <div style="background:#fff;padding:.4rem .75rem;border-radius:.65rem;border:1px solid #e5e7eb;font-size:.7rem;color:#4b5563;font-weight:600;">
                🕐 {{ now()->format('d M Y, H:i') }} WIB
            </div>
        </div>
    </div>

    {{-- HEADER TITLE --}}
    <div style="margin-bottom:1.25rem;">
        <h2 style="margin:0;font-size:1.5rem;font-weight:800;color:#111827;display:flex;align-items:center;gap:.5rem;">
            <span style="color:#059669;">Dashboard PPOB</span> &amp; Layanan Digital
            <span style="font-size:.65rem;font-weight:800;background:#fef3c7;color:#92400e;padding:.2rem .6rem;border-radius:9999px;border:1px solid #fde68a;">E-Wallet &amp; Multi-Biller</span>
        </h2>
        <p style="margin:.2rem 0 0;font-size:.8rem;color:#6b7280;">
            Pantau arus transaksi pulsa, paket data, token listrik PLN, dan top-up e-wallet anggota koperasi secara real-time.
        </p>
    </div>

    {{-- 6 STAT CARDS PPOB --}}
    <div class="ks-grid6">

        {{-- Card 1: Total Transaksi --}}
        <div class="ks-stat-card" style="background:#fff;border:1px solid #f3f4f6;border-left:4px solid #475569;border-radius:.9rem;padding:1rem;display:flex;flex-direction:column;justify-content:space-between;min-height:115px;box-shadow:0 1px 4px rgba(0,0,0,.04);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <div>
                    <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Total Transaksi</p>
                    <p style="margin:.25rem 0 0;font-size:1.35rem;font-weight:900;color:#111827;">{{ number_format($totalTransaksi) }}</p>
                </div>
                <div style="padding:.4rem;background:#f1f5f9;border-radius:.5rem;color:#475569;flex-shrink:0;">
                    <i class="fas fa-receipt" style="font-size:.9rem;"></i>
                </div>
            </div>
            <p style="margin:.3rem 0 0;font-size:.63rem;color:#6b7280;font-weight:600;">📊 Semua waktu</p>
        </div>

        {{-- Card 2: Berhasil --}}
        <div class="ks-stat-card" style="background:#fff;border:1px solid #f3f4f6;border-left:4px solid #10b981;border-radius:.9rem;padding:1rem;display:flex;flex-direction:column;justify-content:space-between;min-height:115px;box-shadow:0 1px 4px rgba(0,0,0,.04);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <div>
                    <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Transaksi Berhasil</p>
                    <p style="margin:.25rem 0 0;font-size:1.35rem;font-weight:900;color:#047857;">{{ number_format($totalBerhasil) }}</p>
                </div>
                <div style="padding:.4rem;background:#ecfdf5;border-radius:.5rem;color:#059669;flex-shrink:0;">
                    <i class="fas fa-check-circle" style="font-size:.9rem;"></i>
                </div>
            </div>
            <p style="margin:.3rem 0 0;font-size:.63rem;color:#059669;font-weight:600;">✅ {{ $successRate }}% Tingkat Sukses</p>
        </div>

        {{-- Card 3: Pending --}}
        <div class="ks-stat-card" style="background:#fff;border:1px solid #f3f4f6;border-left:4px solid #f59e0b;border-radius:.9rem;padding:1rem;display:flex;flex-direction:column;justify-content:space-between;min-height:115px;box-shadow:0 1px 4px rgba(0,0,0,.04);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <div>
                    <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Transaksi Pending</p>
                    <p style="margin:.25rem 0 0;font-size:1.35rem;font-weight:900;color:#d97706;">{{ number_format($totalPending) }}</p>
                </div>
                <div style="padding:.4rem;background:#fffbeb;border-radius:.5rem;color:#d97706;flex-shrink:0;">
                    <i class="fas fa-clock" style="font-size:.9rem;"></i>
                </div>
            </div>
            <p style="margin:.3rem 0 0;font-size:.63rem;color:#d97706;font-weight:600;">⏳ Menunggu proses</p>
        </div>

        {{-- Card 4: Gagal --}}
        <div class="ks-stat-card" style="background:#fff;border:1px solid #f3f4f6;border-left:4px solid #f43f5e;border-radius:.9rem;padding:1rem;display:flex;flex-direction:column;justify-content:space-between;min-height:115px;box-shadow:0 1px 4px rgba(0,0,0,.04);">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <div>
                    <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Transaksi Gagal</p>
                    <p style="margin:.25rem 0 0;font-size:1.35rem;font-weight:900;color:#e11d48;">{{ number_format($totalGagal) }}</p>
                </div>
                <div style="padding:.4rem;background:#fff1f2;border-radius:.5rem;color:#e11d48;flex-shrink:0;">
                    <i class="fas fa-times-circle" style="font-size:.9rem;"></i>
                </div>
            </div>
            <p style="margin:.3rem 0 0;font-size:.63rem;color:#e11d48;font-weight:600;">❌ Batal / Error</p>
        </div>

        {{-- Card 5: Total Omzet --}}
        <div class="ks-stat-card" style="background:linear-gradient(135deg,#7c3aed,#5b21b6);color:#fff;border-radius:.9rem;padding:1rem;display:flex;flex-direction:column;justify-content:space-between;min-height:115px;box-shadow:0 4px 14px rgba(124,58,237,.25);position:relative;overflow:hidden;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;position:relative;z-index:1;">
                <div>
                    <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(237,233,254,.9);">Total Omzet PPOB</p>
                    <p style="margin:.25rem 0 0;font-size:1.25rem;font-weight:900;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Rp {{ number_format($totalOmzet, 0, ',', '.') }}</p>
                </div>
                <div style="padding:.4rem;background:rgba(255,255,255,.2);border-radius:.5rem;color:#fff;flex-shrink:0;">
                    <i class="fas fa-wallet" style="font-size:.9rem;"></i>
                </div>
            </div>
            <p style="margin:.3rem 0 0;font-size:.63rem;color:rgba(237,233,254,.9);position:relative;z-index:1;">📅 Bulan ini: Rp {{ number_format($omzetBulanIni, 0, ',', '.') }}</p>
            <div style="position:absolute;right:-1rem;bottom:-1rem;width:4rem;height:4rem;background:rgba(255,255,255,.1);border-radius:50%;filter:blur(6px);"></div>
        </div>

        {{-- Card 6: Estimasi Margin --}}
        <div class="ks-stat-card" style="background:linear-gradient(135deg,#059669,#0f766e);color:#fff;border-radius:.9rem;padding:1rem;display:flex;flex-direction:column;justify-content:space-between;min-height:115px;box-shadow:0 4px 14px rgba(5,150,105,.25);position:relative;overflow:hidden;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;position:relative;z-index:1;">
                <div>
                    <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(209,250,229,.9);">Estimasi Margin Profit</p>
                    <p style="margin:.25rem 0 0;font-size:1.25rem;font-weight:900;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Rp {{ number_format($estimasiProfit, 0, ',', '.') }}</p>
                </div>
                <div style="padding:.4rem;background:rgba(255,255,255,.2);border-radius:.5rem;color:#fff;flex-shrink:0;">
                    <i class="fas fa-coins" style="font-size:.9rem;"></i>
                </div>
            </div>
            <p style="margin:.3rem 0 0;font-size:.63rem;color:rgba(209,250,229,.9);position:relative;z-index:1;">💰 Pendapatan Fee Koperasi</p>
            <div style="position:absolute;right:-1rem;bottom:-1rem;width:4rem;height:4rem;background:rgba(255,255,255,.1);border-radius:50%;filter:blur(6px);"></div>
        </div>

    </div>

    {{-- BREAKDOWN SECTION & RINGKASAN --}}
    <div class="ks-grid-mid">

        {{-- BREAKDOWN PER PRODUK DIGITAL --}}
        <div class="ks-panel">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                <h3 style="margin:0;font-size:.9rem;font-weight:800;color:#111827;display:flex;align-items:center;gap:.45rem;">
                    <i class="fas fa-th-large" style="color:#059669;"></i>
                    <span>Breakdown Produk Digital &amp; E-Wallet</span>
                </h3>
                <span style="font-size:.65rem;color:#6b7280;font-weight:600;">4 Kategori Layanan</span>
            </div>

            <div style="display:flex;flex-direction:column;gap:.75rem;">
                @foreach($productBreakdown as $prod)
                <div style="background:#f9fafb;border:1px solid #f3f4f6;border-left:4px solid {{ $prod['border'] }};border-radius:.75rem;padding:.75rem .9rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.6rem;">
                    <div style="display:flex;align-items:center;gap:.7rem;">
                        <div style="width:36px;height:36px;border-radius:.6rem;background:#fff;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;color:{{ $prod['border'] }};font-size:.9rem;box-shadow:0 1px 2px rgba(0,0,0,.03);flex-shrink:0;">
                            <i class="{{ $prod['icon'] }}"></i>
                        </div>
                        <div>
                            <p style="margin:0;font-size:.8rem;font-weight:800;color:#1f2937;">{{ $prod['label'] }}</p>
                            <p style="margin:0;font-size:.65rem;color:#6b7280;">
                                Omzet: <strong style="color:#111827;">Rp {{ number_format($prod['omzet'], 0, ',', '.') }}</strong>
                            </p>
                        </div>
                    </div>

                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <span style="font-size:.65rem;font-weight:700;padding:.15rem .45rem;border-radius:.4rem;background:#d1fae5;color:#047857;">✓ {{ $prod['berhasil'] }}</span>
                        @if($prod['pending'] > 0)
                        <span style="font-size:.65rem;font-weight:700;padding:.15rem .45rem;border-radius:.4rem;background:#fef3c7;color:#b45309;">⏳ {{ $prod['pending'] }}</span>
                        @endif
                        <span style="font-size:.72rem;font-weight:800;padding:.2rem .55rem;border-radius:.45rem;background:#fff;border:1px solid #e5e7eb;color:#111827;">
                            {{ $prod['total'] }} Trx
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- AKTIVITAS HARI INI & METODE BAYAR --}}
        <div class="ks-panel" style="display:flex;flex-direction:column;justify-content:space-between;">
            <div>
                <h3 style="margin:0 0 1rem;font-size:.9rem;font-weight:800;color:#111827;display:flex;align-items:center;gap:.45rem;">
                    <i class="fas fa-calendar-day" style="color:#3b82f6;"></i>
                    <span>Aktivitas Hari Ini</span>
                </h3>

                <div style="background:linear-gradient(135deg,#eff6ff 0%,#dbeafe 100%);border:1px solid #bfdbfe;border-radius:.75rem;padding:.9rem;margin-bottom:1rem;">
                    <div style="display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <p style="margin:0;font-size:.62rem;font-weight:800;color:#1e40af;text-transform:uppercase;letter-spacing:.06em;">Transaksi Hari Ini</p>
                            <p style="margin:.2rem 0 0;font-size:1.3rem;font-weight:900;color:#1e3a8a;">{{ number_format($transaksiHariIni) }} <span style="font-size:.8rem;font-weight:600;">Trx</span></p>
                        </div>
                        <div style="text-align:right;">
                            <p style="margin:0;font-size:.62rem;font-weight:800;color:#1e40af;text-transform:uppercase;letter-spacing:.06em;">Omzet Hari Ini</p>
                            <p style="margin:.2rem 0 0;font-size:1rem;font-weight:900;color:#047857;">Rp {{ number_format($omzetHariIni, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <h4 style="margin:0 0 .65rem;font-size:.75rem;font-weight:800;color:#4b5563;text-transform:uppercase;letter-spacing:.06em;">
                    Metode Pembayaran Populer
                </h4>

                <div style="display:flex;flex-direction:column;gap:.4rem;">
                    @forelse($paymentMethods as $pm)
                    <div style="display:flex;justify-content:space-between;align-items:center;padding:.45rem .6rem;background:#f9fafb;border-radius:.5rem;font-size:.72rem;">
                        <span style="font-weight:700;color:#374151;text-transform:capitalize;">
                            <i class="fas fa-credit-card" style="color:#6b7280;margin-right:.35rem;"></i>
                            {{ str_replace('_', ' ', $pm->payment_method ?: 'Saldo Anggota') }}
                        </span>
                        <span style="font-weight:800;color:#059669;">{{ $pm->count }} Trx</span>
                    </div>
                    @empty
                    <div style="padding:.5rem;text-align:center;color:#9ca3af;font-size:.72rem;">
                        Belum ada data metode pembayaran.
                    </div>
                    @endforelse
                </div>
            </div>

            <div style="margin-top:1rem;padding-top:.75rem;border-top:1px solid #f3f4f6;text-align:center;">
                <span style="font-size:.65rem;color:#6b7280;font-weight:600;">
                    🛡️ Terintegrasi Sistem E-Wallet &amp; Saldo Koperasi
                </span>
            </div>
        </div>

    </div>

    {{-- CHART 12 BULAN PPOB --}}
    <div class="ks-panel" style="margin-bottom:1.25rem;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.85rem;flex-wrap:wrap;gap:.5rem;">
            <h3 style="margin:0;font-size:.92rem;font-weight:800;color:#111827;display:flex;align-items:center;gap:.45rem;">
                <i class="fas fa-chart-area" style="color:#7c3aed;"></i>
                <span>Tren Transaksi &amp; Omzet PPOB (12 Bulan)</span>
            </h3>
            <span style="font-size:.68rem;color:#6b7280;background:#f3f4f6;padding:.2rem .55rem;border-radius:.4rem;font-weight:600;">
                Volume Trx vs Nilai Transaksi
            </span>
        </div>
        <div style="position:relative;height:240px;">
            <canvas id="ks-ppob-chart"></canvas>
        </div>
    </div>

    {{-- 10 TRANSAKSI PPOB TERBARU ANGGOTA --}}
    <div class="ks-panel">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.9rem;flex-wrap:wrap;gap:.5rem;">
            <div>
                <h3 style="margin:0;font-size:.92rem;font-weight:800;color:#111827;display:flex;align-items:center;gap:.45rem;">
                    <i class="fas fa-history" style="color:#059669;"></i>
                    <span>10 Transaksi PPOB Anggota Terbaru</span>
                </h3>
                <p style="margin:.15rem 0 0;font-size:.7rem;color:#6b7280;">Semua transaksi digital anggota yang masuk dari aplikasi frontend</p>
            </div>
            <a href="/admin/ppob-transaksis" style="font-size:.72rem;font-weight:800;color:#059669;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;">
                <span>Lihat Semua</span>
                <i class="fas fa-arrow-right" style="font-size:.65rem;"></i>
            </a>
        </div>

        <div class="ks-tbl-wrap">
            <table class="ks-tbl">
                <thead>
                    <tr>
                        <th>Anggota</th>
                        <th>Kategori</th>
                        <th>Produk / Provider</th>
                        <th>No. Tujuan</th>
                        <th style="text-align:right;">Nominal / Harga</th>
                        <th>Metode</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $tx)
                    @php
                        $st = strtolower($tx->status);
                        $badgeCls = match($st) {
                            'sukses', 'berhasil', 'success', 'aktif' => 'ks-b-sukses',
                            'pending', 'menunggu' => 'ks-b-pending',
                            default => 'ks-b-gagal',
                        };
                    @endphp
                    <tr>
                        <td style="font-weight:700;color:#111827;">
                            {{ $tx->nama_anggota ?: ($tx->username_anggota ?: 'Anggota #'.$tx->id_anggota) }}
                        </td>
                        <td>
                            <span style="font-size:.65rem;font-weight:700;text-transform:uppercase;padding:.15rem .45rem;border-radius:.35rem;background:#f3f4f6;color:#4b5563;">
                                {{ str_replace('_', ' ', $tx->jenis_produk) }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight:600;color:#1f2937;">{{ $tx->nama_produk ?: $tx->provider }}</div>
                            @if($tx->provider && $tx->nama_produk)
                            <div style="font-size:.62rem;color:#9ca3af;">{{ $tx->provider }}</div>
                            @endif
                        </td>
                        <td style="font-family:ui-monospace,SFMono-Regular,Menlo,monospace;font-size:.72rem;color:#2563eb;font-weight:600;">
                            {{ $tx->nomor_tujuan }}
                        </td>
                        <td style="text-align:right;font-weight:800;color:#111827;">
                            Rp {{ number_format($tx->harga > 0 ? $tx->harga : $tx->nominal, 0, ',', '.') }}
                        </td>
                        <td style="font-size:.68rem;text-transform:capitalize;color:#4b5563;">
                            {{ str_replace('_', ' ', $tx->payment_method ?: 'Saldo Anggota') }}
                        </td>
                        <td>
                            <span class="ks-badge-status {{ $badgeCls }}">
                                {{ $tx->status }}
                            </span>
                        </td>
                        <td style="font-size:.68rem;color:#9ca3af;white-space:nowrap;">
                            {{ \Illuminate\Support\Carbon::parse($tx->created_at)->format('d M Y H:i') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" style="text-align:center;padding:2rem 1rem;color:#9ca3af;">
                            <i class="fas fa-inbox" style="font-size:1.5rem;margin-bottom:.5rem;display:block;"></i>
                            Belum ada riwayat transaksi PPOB tercatat.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- ═══════════ TRACKING E-WALLET KOPERASI ═══════════ --}}
    <div style="margin-top:1.5rem;padding-top:1.25rem;border-top:2px dashed #e5e7eb;">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.6rem;margin-bottom:.8rem;">
            <h3 style="margin:0;font-size:1rem;font-weight:800;color:#111827;display:flex;align-items:center;gap:.45rem;">
                <i class="fas fa-wallet" style="color:#059669;"></i> Tracking E-Wallet Koperasi
            </h3>
            <span style="font-size:.68rem;background:#fef3c7;color:#92400e;padding:.18rem .6rem;border-radius:9999px;font-weight:800;">
                {{ $ewalletStats['pending'] }} topup menunggu verifikasi
            </span>
        </div>

        <div class="ks-grid4">
            <div class="ks-stat-card" style="background:linear-gradient(135deg,#059669,#0f766e);color:#fff;border-radius:.9rem;padding:1rem;min-height:105px;box-shadow:0 4px 14px rgba(5,150,105,.25);">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(209,250,229,.9);">Total Saldo Terkumpul</p>
                <p style="margin:.3rem 0 0;font-size:1.3rem;font-weight:900;">Rp {{ number_format($ewalletStats['total_saldo'], 0, ',', '.') }}</p>
                <p style="margin:.25rem 0 0;font-size:.62rem;color:rgba(209,250,229,.85);">Dari {{ $ewalletStats['jumlah_dompet'] }} dompet anggota</p>
            </div>

            <div class="ks-stat-card" style="background:#fff;border:1px solid #f3f4f6;border-left:4px solid #10b981;border-radius:.9rem;padding:1rem;min-height:105px;box-shadow:0 1px 4px rgba(0,0,0,.04);">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Topup Masuk (Bulan Ini)</p>
                <p style="margin:.25rem 0 0;font-size:1.3rem;font-weight:900;color:#111827;">Rp {{ number_format($ewalletStats['topup_bulan'], 0, ',', '.') }}</p>
                <p style="margin:.3rem 0 0;font-size:.63rem;color:#10b981;font-weight:700;">Hari ini Rp {{ number_format($ewalletStats['topup_hari'], 0, ',', '.') }}</p>
            </div>

            <div class="ks-stat-card" style="background:#fff;border:1px solid #f3f4f6;border-left:4px solid #f59e0b;border-radius:.9rem;padding:1rem;min-height:105px;box-shadow:0 1px 4px rgba(0,0,0,.04);">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Pemakaian (Bulan Ini)</p>
                <p style="margin:.25rem 0 0;font-size:1.3rem;font-weight:900;color:#111827;">Rp {{ number_format($ewalletStats['pemakaian_bulan'], 0, ',', '.') }}</p>
                <p style="margin:.3rem 0 0;font-size:.63rem;color:#d97706;font-weight:700;">💰 Bayar PPOB dari saldo</p>
            </div>

            <div class="ks-stat-card" style="background:#fff;border:1px solid #f3f4f6;border-left:4px solid #6366f1;border-radius:.9rem;padding:1rem;min-height:105px;box-shadow:0 1px 4px rgba(0,0,0,.04);">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Mutasi Hari Ini</p>
                <p style="margin:.25rem 0 0;font-size:1.3rem;font-weight:900;color:#111827;">{{ $ewalletStats['transaksi_hari'] }}</p>
                <p style="margin:.3rem 0 0;font-size:.63rem;color:#6366f1;font-weight:700;">{{ $ewalletTopupHariIni }} topup baru</p>
            </div>
        </div>

        {{-- PENDING TOPUP TABLE --}}
        @if($ewalletPending->count() > 0)
        <div class="ks-panel" style="margin-top:1rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.85rem;flex-wrap:wrap;gap:.5rem;">
                <h4 style="margin:0;font-size:.88rem;font-weight:800;color:#111827;display:flex;align-items:center;gap:.4rem;">
                    <i class="fas fa-clock" style="color:#f59e0b;"></i>
                    <span>Permintaan Topup Menunggu Verifikasi</span>
                    <span style="background:#fef3c7;color:#92400e;font-size:.62rem;padding:.15rem .5rem;border-radius:9999px;border:1px solid #fde68a;">{{ $ewalletPending->count() }} menunggu</span>
                </h4>
            </div>
            <div class="ks-tbl-wrap">
                <table class="ks-tbl">
                    <thead>
                        <tr>
                            <th>Anggota</th>
                            <th>Nominal</th>
                            <th>Metode</th>
                            <th>Keterangan</th>
                            <th>Waktu</th>
                            <th style="text-align:center;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($ewalletPending as $tp)
                        <tr>
                            <td style="font-weight:700;color:#111827;">
                                {{ $tp->anggota?->nama_lengkap ?: 'Anggota #'.$tp->id_anggota }}
                                <div style="font-size:.62rem;color:#9ca3af;">{{ $tp->anggota?->username }}</div>
                            </td>
                            <td style="font-weight:800;color:#059669;">Rp {{ number_format($tp->nominal, 0, ',', '.') }}</td>
                            <td style="font-size:.7rem;text-transform:capitalize;color:#4b5563;">{{ str_replace('_',' ',$tp->metode ?: 'Transfer Bank') }}</td>
                            <td style="font-size:.7rem;color:#6b7280;max-width:160px;">{{ $tp->keterangan ?: '-' }}</td>
                            <td style="font-size:.68rem;color:#9ca3af;white-space:nowrap;">{{ $tp->created_at->format('d M Y H:i') }}</td>
                            <td style="text-align:center;">
                                <div style="display:flex;gap:.35rem;justify-content:center;">
                                    <button class="ks-btn-approve" wire:click="approveTopup({{ $tp->id }})">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                    <button class="ks-btn-reject" wire:click="rejectTopup({{ $tp->id }})">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- MUTASI TERBARU E-WALLET --}}
        <div class="ks-panel" style="margin-top:1rem;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.85rem;flex-wrap:wrap;gap:.5rem;">
                <h4 style="margin:0;font-size:.88rem;font-weight:800;color:#111827;display:flex;align-items:center;gap:.4rem;">
                    <i class="fas fa-exchange-alt" style="color:#059669;"></i>
                    <span>Mutasi E-Wallet Terbaru</span>
                </h4>
                <span style="font-size:.65rem;color:#6b7280;font-weight:600;">10 Transaksi Terakhir</span>
            </div>
            <div class="ks-tbl-wrap">
                <table class="ks-tbl">
                    <thead>
                        <tr>
                            <th>Anggota</th>
                            <th>Tipe</th>
                            <th style="text-align:right;">Nominal</th>
                            <th>Metode</th>
                            <th>Status</th>
                            <th>Waktu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($ewalletRecent as $ew)
                        @php
                            $tipe = strtolower($ew->tipe);
                            $ewSt = strtolower($ew->status);
                            $ewBadge = match($ewSt) {
                                'sukses' => 'ks-b-sukses',
                                'pending' => 'ks-b-pending',
                                default => 'ks-b-gagal',
                            };
                        @endphp
                        <tr>
                            <td style="font-weight:700;color:#111827;">
                                {{ $ew->anggota?->nama_lengkap ?: 'Anggota #'.$ew->id_anggota }}
                            </td>
                            <td>
                                @if($tipe === 'topup')
                                    <span style="font-size:.65rem;font-weight:800;padding:.15rem .45rem;border-radius:.35rem;background:#d1fae5;color:#047857;">⬆ Topup</span>
                                @else
                                    <span style="font-size:.65rem;font-weight:800;padding:.15rem .45rem;border-radius:.35rem;background:#e0f2fe;color:#0369a1;">⬇ Bayar</span>
                                @endif
                            </td>
                            <td style="text-align:right;font-weight:800;color:{{ $tipe === 'topup' ? '#059669' : '#e11d48' }};">
                                {{ $tipe === 'topup' ? '+' : '-' }}Rp {{ number_format($ew->nominal, 0, ',', '.') }}
                            </td>
                            <td style="font-size:.7rem;color:#4b5563;text-transform:capitalize;">{{ str_replace('_',' ',$ew->metode ?: 'Saldo') }}</td>
                            <td><span class="ks-badge-status {{ $ewBadge }}">{{ $ew->status }}</span></td>
                            <td style="font-size:.68rem;color:#9ca3af;white-space:nowrap;">{{ $ew->created_at->format('d M Y H:i') }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="text-align:center;padding:2rem;color:#9ca3af;">
                                <i class="fas fa-inbox" style="font-size:1.3rem;margin-bottom:.4rem;display:block;"></i>
                                Belum ada mutasi e-wallet tercatat.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

{{-- SCRIPT CHART JS --}}
<script>
(function initPpobChart() {
    var attempt = 0;
    function tryDraw() {
        if (typeof Chart === 'undefined') {
            if (++attempt < 50) setTimeout(tryDraw, 200);
            return;
        }
        var el = document.getElementById('ks-ppob-chart');
        if (!el) return;
        if (window._ksPpobC) { window._ksPpobC.destroy(); }
        window._ksPpobC = new Chart(el.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! $chartLabels !!},
                datasets: [
                    {
                        label: 'Volume Transaksi (Trx)',
                        data: {!! $chartTrx !!},
                        borderColor: '#7c3aed',
                        backgroundColor: 'rgba(124, 58, 237, 0.08)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Total Omzet (Rp)',
                        data: {!! $chartOmzet !!},
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5, 150, 105, 0.08)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 10, font: { size: 11, weight: 'bold' } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                if (ctx.datasetIndex === 1) {
                                    return 'Omzet: Rp ' + new Intl.NumberFormat('id-ID').format(ctx.raw);
                                }
                                return 'Transaksi: ' + ctx.raw + ' Trx';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.03)' },
                        title: { display: true, text: 'Jumlah Transaksi', font: { size: 10 } }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        ticks: {
                            callback: function(v) {
                                if (v >= 1000000) return 'Rp ' + (v / 1000000).toFixed(1) + 'Jt';
                                if (v >= 1000) return 'Rp ' + (v / 1000).toFixed(0) + 'Rb';
                                return 'Rp ' + v;
                            }
                        },
                        title: { display: true, text: 'Omzet', font: { size: 10 } }
                    }
                }
            }
        });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tryDraw);
    } else {
        tryDraw();
    }
})();
</script>
</x-filament-panels::page>
