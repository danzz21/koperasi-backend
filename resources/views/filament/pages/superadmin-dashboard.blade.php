<x-filament-panels::page>
<style>
    .kss-wrap { width:100%; box-sizing:border-box; }
    .kss-card { transition: transform .2s, box-shadow .2s; }
    .kss-card:hover { transform: translateY(-3px); }
    .kss-grid4 { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.85rem; margin-bottom:1rem; }
    .kss-grid3 { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.85rem; margin-bottom:1.25rem; }
    .kss-grid-bottom { display:grid; grid-template-columns:2fr 1fr; gap:1rem; }
    .kss-panel { background:#fff; border:1px solid #f3f4f6; border-radius:.9rem; box-shadow:0 1px 5px rgba(0,0,0,.06); padding:1.1rem 1.2rem; }
    .kss-panel h3 { margin:0 0 .9rem; font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:#6b7280; }
    .kss-log-row { display:flex; align-items:flex-start; justify-content:space-between; gap:.9rem; padding:.6rem 0; border-bottom:1px solid #f3f4f6; }
    .kss-log-row:last-child { border-bottom:none; }
    .kss-mono { font-family:ui-monospace,SFMono-Regular,Menlo,monospace; font-size:.68rem; color:#7c3aed; font-weight:700; }
    .kss-badge { display:inline-block; padding:.15rem .5rem; border-radius:9999px; font-size:.65rem; font-weight:800; background:#f3e8ff; color:#6d28d9; }
    .kss-empty { margin:0; font-size:.78rem; color:#9ca3af; }
    @media(max-width:1100px){ .kss-grid4{ grid-template-columns:repeat(2,minmax(0,1fr)); } .kss-grid3{ grid-template-columns:repeat(2,minmax(0,1fr)); } }
    @media(max-width:800px){ .kss-grid3{ grid-template-columns:1fr; } .kss-grid-bottom{ grid-template-columns:1fr; } }
    @media(max-width:520px){ .kss-grid4{ grid-template-columns:1fr; } }
</style>

<div class="kss-wrap">
    {{-- HEADER CHIPS --}}
    <div style="display:flex;align-items:center;justify-content:flex-end;gap:.6rem;flex-wrap:wrap;margin-bottom:1rem;">
        <div style="display:flex;align-items:center;gap:.4rem;background:#fff;padding:.35rem .7rem;border-radius:.65rem;border:1px solid #e5e7eb;font-size:.7rem;">
            <span style="color:#4b5563;font-weight:600;">🔐 Akses Tertinggi</span>
            <span style="color:#7c3aed;font-weight:800;">SUPERADMIN</span>
        </div>
        <div style="background:#fff;padding:.35rem .75rem;border-radius:.65rem;border:1px solid #e5e7eb;font-size:.7rem;color:#4b5563;font-weight:600;">
            {{ now()->format('d M Y, H:i') }} WIB
        </div>
    </div>

    {{-- BARIS 1: STAT UTAMA --}}
    <div class="kss-grid4">
        <div class="kss-card" style="background:linear-gradient(135deg,#7c3aed,#5b21b6);color:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 4px 14px rgba(124,58,237,.3);display:flex;flex-direction:column;gap:.35rem;min-height:118px;">
            <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(237,233,254,.9);">Total User Sistem</p>
            <p style="margin:0;font-size:1.5rem;font-weight:900;">{{ number_format($totalUser, 0, ',', '.') }}</p>
            <p style="margin:0;font-size:.63rem;color:rgba(237,233,254,.85);">
                👤 {{ $totalSuperadmin }} superadmin &middot; {{ $totalAdmin }} admin &middot; {{ $totalAnggota }} anggota
            </p>
        </div>

        <div class="kss-card" style="background:linear-gradient(135deg,#059669,#0f766e);color:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 4px 14px rgba(5,150,105,.3);display:flex;flex-direction:column;gap:.35rem;min-height:118px;">
            <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(209,250,229,.9);">Total Simpanan Aktif</p>
            <p style="margin:0;font-size:1.15rem;font-weight:900;">Rp {{ number_format($totalSimpanan, 0, ',', '.') }}</p>
            <p style="margin:0;font-size:.63rem;color:rgba(209,250,229,.85);">💰 Pokok, wajib &amp; sukarela</p>
        </div>

        <div class="kss-card" style="background:linear-gradient(135deg,#f59e0b,#ea580c);color:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 4px 14px rgba(245,158,11,.3);display:flex;flex-direction:column;gap:.35rem;min-height:118px;">
            <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(254,243,199,.9);">Sisa Pembiayaan</p>
            <p style="margin:0;font-size:1.15rem;font-weight:900;">Rp {{ number_format($totalPinjaman, 0, ',', '.') }}</p>
            <p style="margin:0;font-size:.63rem;color:rgba(254,243,199,.9);">📄 Pinjaman berstatus aktif</p>
        </div>

        <div class="kss-card" style="background:linear-gradient(135deg,#0ea5e9,#0e7490);color:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 4px 14px rgba(14,165,233,.3);display:flex;flex-direction:column;gap:.35rem;min-height:118px;">
            <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(224,242,254,.9);">Aktivitas Hari Ini</p>
            <p style="margin:0;font-size:1.5rem;font-weight:900;">{{ number_format($totalAuditHariIni, 0, ',', '.') }}</p>
            <p style="margin:0;font-size:.63rem;color:rgba(224,242,254,.9);"> {{ number_format($totalAudit, 0, ',', '.') }} total tercatat</p>
        </div>
    </div>

    {{-- BARIS 2: RINGKASAN KEUANGAN --}}
    <div class="kss-grid3">
        <div class="kss-card" style="background:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;border-left:4px solid #10b981;display:flex;flex-direction:column;gap:.35rem;min-height:100px;">
            <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Arus Transaksi (Keseluruhan)</p>
            <p style="margin:0;font-size:1.15rem;font-weight:900;color:#047857;">Rp {{ number_format($totalTransaksi, 0, ',', '.') }}</p>
            <p style="margin:0;font-size:.63rem;color:#059669;font-weight:600;">📊 Seluruh mutasi tercatat</p>
        </div>

        <div class="kss-card" style="background:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;border-left:4px solid #a855f7;display:flex;flex-direction:column;gap:.35rem;min-height:100px;">
            <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Volume Transaksi PPOB</p>
            <p style="margin:0;font-size:1.15rem;font-weight:900;color:#7c3aed;">Rp {{ number_format($totalPpob, 0, ',', '.') }}</p>
            <p style="margin:0;font-size:.63rem;color:#9333ea;font-weight:600;">📱 Layanan digital anggota</p>
        </div>

        <div class="kss-card" style="background:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;border-left:4px solid #f43f5e;display:flex;flex-direction:column;gap:.35rem;min-height:100px;">
            <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Pengguna Sistem</p>
            <p style="margin:0;font-size:1.15rem;font-weight:900;color:#e11d48;">{{ number_format($totalUser, 0, ',', '.') }} akun</p>
            <p style="margin:0;font-size:.63rem;color:#e11d48;font-weight:600;">🔑 Seluruh role terdaftar</p>
        </div>
    </div>

    {{-- BARIS GRAFIK: tren aktivitas + komposisi pengguna --}}
    <div class="kss-grid-bottom" style="margin-bottom:1.25rem;">
        <div class="kss-panel" style="margin-bottom:0;">
            <h3>Tren Aktivitas Sistem &amp; Transaksi (12 Bulan)</h3>
            <div style="position:relative;height:260px;">
                <canvas id="kss-trend"></canvas>
            </div>
        </div>

        <div class="kss-panel" style="margin-bottom:0;">
            <h3>Komposisi Pengguna</h3>
            <div style="position:relative;height:200px;">
                <canvas id="kss-komposisi"></canvas>
            </div>
            <div style="display:flex;justify-content:center;gap:.9rem;margin-top:.7rem;font-size:.7rem;color:#4b5563;font-weight:600;">
                <span><i style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#7c3aed;margin-right:.25rem;"></i>Superadmin ({{ $totalSuperadmin }})</span>
                <span><i style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#059669;margin-right:.25rem;"></i>Admin ({{ $totalAdmin }})</span>
                <span><i style="display:inline-block;width:10px;height:10px;border-radius:3px;background:#3b82f6;margin-right:.25rem;"></i>Anggota ({{ $totalAnggota }})</span>
            </div>
        </div>
    </div>

    {{-- BARIS 3: AUDIT TRAIL --}}
    <div class="kss-grid-bottom">
        <div class="kss-panel">
            <h3>Audit Trail Terbaru</h3>

            @if ($aktivitasTerbaru->isEmpty())
                <p class="kss-empty">Belum ada aktivitas yang tercatat.</p>
            @else
                @foreach ($aktivitasTerbaru as $log)
                    <div class="kss-log-row">
                        <div>
                            <div style="font-size:.82rem;font-weight:700;color:#111827;">{{ $log->actor_name }}</div>
                            <div style="margin-top:.15rem;font-size:.7rem;color:#6b7280;">
                                <span class="kss-mono">{{ $log->action }}</span>
                                @if ($log->question_type)
                                    &middot; {{ $log->question_type }}#{{ $log->record_id }}
                                @endif
                            </div>
                        </div>
                        <div style="flex-shrink:0;text-align:right;font-size:.68rem;color:#9ca3af;">
                            {{ $log->created_at?->diffForHumans() }}
                            <div style="font-size:.63rem;">{{ $log->ip_address }}</div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>

        <div class="kss-panel">
            <h3>Aksi Paling Sering</h3>

            @if ($topActions->isEmpty())
                <p class="kss-empty">Belum ada data.</p>
            @else
                <div style="display:flex;flex-direction:column;gap:.7rem;">
                    @foreach ($topActions as $row)
                        <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;">
                            <span class="kss-mono" style="color:#374151;">{{ $row->action }}</span>
                            <span class="kss-badge">{{ $row->total }}x</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<script>
(function initKssCharts() {
    var attempt = 0;
    function tryDraw() {
        if (typeof Chart === 'undefined') {
            if (++attempt < 50) setTimeout(tryDraw, 200);
            return;
        }

        var trendEl = document.getElementById('kss-trend');
        if (trendEl) {
            if (window._kssTrend) { window._kssTrend.destroy(); }
            window._kssTrend = new Chart(trendEl.getContext('2d'), {
                type: 'line',
                data: {
                    labels: {!! $chartLabels !!},
                    datasets: [
                        { label:'Aktivitas Sistem', data:{!! $chartAktivitas !!}, borderColor:'#7c3aed', backgroundColor:'rgba(124,58,237,.12)', borderWidth:2.5, tension:.4, fill:true, pointRadius:3, pointHoverRadius:5, pointBackgroundColor:'#7c3aed' },
                        { label:'Transaksi', data:{!! $chartTransaksi !!}, borderColor:'#059669', backgroundColor:'rgba(5,150,105,.10)', borderWidth:2.5, tension:.4, fill:true, pointRadius:3, pointHoverRadius:5, pointBackgroundColor:'#059669' }
                    ]
                },
                options: {
                    responsive:true, maintainAspectRatio:false,
                    plugins: {
                        legend:{ display:true, position:'top', labels:{ usePointStyle:true, boxWidth:8, font:{size:11} } },
                        tooltip:{ mode:'index', intersect:false }
                    },
                    scales: {
                        x:{ grid:{display:false}, ticks:{font:{size:10}} },
                        y:{ beginAtZero:true, precision:0, grid:{color:'rgba(0,0,0,.04)'}, ticks:{ font:{size:10} } }
                    },
                    interaction:{ mode:'nearest', axis:'x', intersect:false }
                }
            });
        }

        var komposisiEl = document.getElementById('kss-komposisi');
        if (komposisiEl) {
            if (window._kssKomposisi) { window._kssKomposisi.destroy(); }
            window._kssKomposisi = new Chart(komposisiEl.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Superadmin', 'Admin', 'Anggota'],
                    datasets: [{
                        data: {!! $userComposition !!},
                        backgroundColor: ['#7c3aed', '#059669', '#3b82f6'],
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        hoverOffset: 6
                    }]
                },
                options: {
                    responsive:true, maintainAspectRatio:false, cutout:'62%',
                    plugins: {
                        legend:{ display:false },
                        tooltip:{ callbacks:{ label:function(c){ return ' '+c.label+': '+c.parsed+' akun'; } } }
                    }
                }
            });
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', tryDraw);
    } else {
        tryDraw();
    }
})();
</script>
</x-filament-panels::page>
