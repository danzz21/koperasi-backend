<style>
.ks-card { transition: transform .2s, box-shadow .2s; }
.ks-card:hover { transform: translateY(-3px); }
.ks-grid-4 { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); gap:.85rem; margin-bottom:1rem; }
.ks-grid-bottom { display:grid; grid-template-columns:2fr 1fr; gap:1rem; margin-bottom:1rem; }
@media(max-width:1100px){ .ks-grid-4{ grid-template-columns:repeat(3,minmax(0,1fr)); } }
@media(max-width:800px) { .ks-grid-4{ grid-template-columns:repeat(2,minmax(0,1fr)); } .ks-grid-bottom{ grid-template-columns:1fr; } }
@media(max-width:480px) { .ks-grid-4{ grid-template-columns:1fr; } }
</style>

<div style="width:100%;box-sizing:border-box;">

    {{-- HEADER --}}
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem;">
        <div>
            <h2 style="margin:0;font-size:1.6rem;font-weight:800;color:#111827;">Dashboard Utama</h2>
            <p style="margin:.2rem 0 0;font-size:.8rem;color:#6b7280;">Ringkasan rinci arus kas, pembiayaan, transaksi umum, dan performa koperasi</p>
        </div>
        <div style="display:flex;align-items:center;gap:.6rem;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:.4rem;background:#fff;padding:.35rem .7rem;border-radius:.65rem;border:1px solid #e5e7eb;font-size:.7rem;">
                <span style="color:#4b5563;font-weight:600;">🔽 Periode Operasional:</span>
                <span style="color:#047857;font-weight:800;">{{ $filterLabel }}</span>
            </div>
            <div style="background:#fff;padding:.35rem .75rem;border-radius:.65rem;border:1px solid #e5e7eb;font-size:.7rem;color:#4b5563;font-weight:600;">
                🕐 {{ now()->format('d M Y, H:i') }} WIB
            </div>
        </div>
    </div>

    {{-- BARIS 1 --}}
    <div class="ks-grid-4">
        <div class="ks-card" style="background:linear-gradient(135deg,#059669,#0f766e);color:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 4px 14px rgba(5,150,105,.3);position:relative;overflow:hidden;display:flex;flex-direction:column;gap:.4rem;min-height:115px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(209,250,229,.9);">Saldo Kas Real (Fisik)</p>
                <div style="padding:.35rem;background:rgba(255,255,255,.2);border-radius:.45rem;flex-shrink:0;">
                    <svg style="width:.9rem;height:.9rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
            </div>
            <p style="margin:0;font-size:1.3rem;font-weight:900;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Rp {{ number_format($kasReal,0,',','.') }}</p>
            <p style="margin:0;font-size:.63rem;color:rgba(209,250,229,.85);">💰 Uang Kas Siap Pakai</p>
        </div>

        <div class="ks-card" style="background:linear-gradient(135deg,#f59e0b,#ea580c);color:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 4px 14px rgba(245,158,11,.3);position:relative;overflow:hidden;display:flex;flex-direction:column;gap:.4rem;min-height:115px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(254,243,199,.9);">Estimasi SHU Berjalan</p>
                <div style="padding:.35rem;background:rgba(255,255,255,.2);border-radius:.45rem;flex-shrink:0;">
                    <svg style="width:.9rem;height:.9rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <p style="margin:0;font-size:1.3rem;font-weight:900;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Rp {{ number_format($shuBerjalan,0,',','.') }}</p>
            <p style="margin:0;font-size:.63rem;color:rgba(254,243,199,.85);">📊 Margin + Expense ({{ $filterLabel }})</p>
        </div>

        <div class="ks-card" style="background:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;border-left:4px solid #06b6d4;display:flex;flex-direction:column;gap:.4rem;min-height:115px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Valuasi Total Aset</p>
                <div style="padding:.35rem;background:#ecfeff;border-radius:.45rem;flex-shrink:0;"><svg style="width:.9rem;height:.9rem;color:#0891b2;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg></div>
            </div>
            <p style="margin:0;font-size:1.3rem;font-weight:900;color:#111827;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Rp {{ number_format($totalAset,0,',','.') }}</p>
            <p style="margin:0;font-size:.63rem;color:#0891b2;font-weight:600;">🏛️ Kas Real + Piutang Pokok</p>
        </div>

        <div class="ks-card" style="background:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;border-left:4px solid #3b82f6;display:flex;flex-direction:column;gap:.4rem;min-height:115px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Total Simpanan</p>
                <div style="padding:.35rem;background:#eff6ff;border-radius:.45rem;flex-shrink:0;"><svg style="width:.9rem;height:.9rem;color:#2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg></div>
            </div>
            <p style="margin:0;font-size:1.3rem;font-weight:900;color:#111827;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Rp {{ number_format($totalSimpanan,0,',','.') }}</p>
            <p style="margin:0;font-size:.63rem;color:#2563eb;font-weight:600;">💰 Pokok + Wajib + Sukarela</p>
        </div>
    </div>

    {{-- BARIS 2 --}}
    <div class="ks-grid-4">
        <div class="ks-card" style="background:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;border-left:4px solid #f59e0b;display:flex;flex-direction:column;gap:.4rem;min-height:115px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Pokok Beredar</p>
                <div style="padding:.35rem;background:#fffbeb;border-radius:.45rem;flex-shrink:0;"><svg style="width:.9rem;height:.9rem;color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg></div>
            </div>
            <p style="margin:0;font-size:1.3rem;font-weight:900;color:#111827;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Rp {{ number_format($sisaPokok,0,',','.') }}</p>
            <p style="margin:0;font-size:.63rem;color:#d97706;font-weight:600;">🤝 Uang Pokok di Anggota</p>
        </div>

        <div class="ks-card" style="background:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;border-left:4px solid #6366f1;display:flex;flex-direction:column;gap:.4rem;min-height:115px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Profit Realisasi Margin</p>
                <div style="padding:.35rem;background:#eef2ff;border-radius:.45rem;flex-shrink:0;"><svg style="width:.9rem;height:.9rem;color:#4f46e5;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg></div>
            </div>
            <p style="margin:0;font-size:1.3rem;font-weight:900;color:#111827;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Rp {{ number_format($realisasiMargin,0,',','.') }}</p>
            <p style="margin:0;font-size:.63rem;color:#4f46e5;font-weight:600;">✅ Margin Masuk Angsuran</p>
        </div>

        <div class="ks-card" style="background:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;border-left:4px solid #10b981;display:flex;flex-direction:column;gap:.4rem;min-height:115px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Pemasukan Operasional</p>
                <div style="padding:.35rem;background:#ecfdf5;border-radius:.45rem;flex-shrink:0;"><svg style="width:.9rem;height:.9rem;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4 4m0 0l-4-4m4 4V3"/></svg></div>
            </div>
            <p style="margin:0;font-size:1.3rem;font-weight:900;color:#059669;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Rp {{ number_format($pemasukanOpr,0,',','.') }}</p>
            <p style="margin:0;font-size:.63rem;color:#059669;font-weight:600;">📅 {{ $filterLabel }}</p>
        </div>

        <div class="ks-card" style="background:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;border-left:4px solid #f43f5e;display:flex;flex-direction:column;gap:.4rem;min-height:115px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Beban Operasional</p>
                <div style="padding:.35rem;background:#fff1f2;border-radius:.45rem;flex-shrink:0;"><svg style="width:.9rem;height:.9rem;color:#e11d48;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg></div>
            </div>
            <p style="margin:0;font-size:1.3rem;font-weight:900;color:#e11d48;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Rp {{ number_format($bebanOpr,0,',','.') }}</p>
            <p style="margin:0;font-size:.63rem;color:#e11d48;font-weight:600;">📅 {{ $filterLabel }}</p>
        </div>
    </div>

    {{-- BARIS 3 --}}
    <div class="ks-grid-4" style="margin-bottom:1.25rem;">
        <div class="ks-card" style="background:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;border-left:4px solid #a855f7;display:flex;flex-direction:column;gap:.4rem;min-height:115px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Tagihan Bulan Ini</p>
                <div style="padding:.35rem;background:#faf5ff;border-radius:.45rem;flex-shrink:0;"><svg style="width:.9rem;height:.9rem;color:#9333ea;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg></div>
            </div>
            <p style="margin:0;font-size:1.3rem;font-weight:900;color:#111827;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Rp {{ number_format($tagihanBulanIni,0,',','.') }}</p>
            <p style="margin:0;font-size:.63rem;color:#9333ea;font-weight:600;">📆 Ekspektasi Arus Masuk</p>
        </div>

        <div class="ks-card" style="background:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;border-left:4px solid #14b8a6;display:flex;flex-direction:column;gap:.4rem;min-height:115px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Potensi Margin Aktif</p>
                <div style="padding:.35rem;background:#f0fdfa;border-radius:.45rem;flex-shrink:0;"><svg style="width:.9rem;height:.9rem;color:#0d9488;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5"/></svg></div>
            </div>
            <p style="margin:0;font-size:1.3rem;font-weight:900;color:#111827;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">Rp {{ number_format($potensiMargin,0,',','.') }}</p>
            <p style="margin:0;font-size:.63rem;color:#0d9488;font-weight:600;">🎯 Target Margin Pinjaman</p>
        </div>

        <div class="ks-card" style="background:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;border-left:4px solid #475569;display:flex;flex-direction:column;gap:.4rem;min-height:115px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Total Anggota Aktif</p>
                <div style="padding:.35rem;background:#f1f5f9;border-radius:.45rem;flex-shrink:0;"><svg style="width:.9rem;height:.9rem;color:#475569;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg></div>
            </div>
            <p style="margin:0;font-size:1.3rem;font-weight:900;color:#111827;">{{ number_format($totalAnggota) }} <span style="font-size:.9rem;color:#6b7280;font-weight:600;">Orang</span></p>
            <p style="margin:0;font-size:.63rem;color:#475569;font-weight:600;">👤 Terverifikasi Aktif</p>
        </div>

        <div class="ks-card" style="background:#fff;padding:1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;border-left:4px solid #7c3aed;display:flex;flex-direction:column;gap:.4rem;min-height:115px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:.4rem;">
                <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Rasio Pembiayaan</p>
                <div style="padding:.35rem;background:#f5f3ff;border-radius:.45rem;flex-shrink:0;"><svg style="width:.9rem;height:.9rem;color:#7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg></div>
            </div>
            <p style="margin:0;font-size:1.3rem;font-weight:900;color:#111827;">{{ $rasio }}%</p>
            <p style="margin:0;font-size:.63rem;color:#7c3aed;font-weight:600;">⚡ Pinjaman vs Simpanan</p>
        </div>
    </div>

    {{-- CHART + PENDING --}}
    <div class="ks-grid-bottom">
        <div style="background:#fff;padding:1.1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;">
            <h3 style="margin:0 0 .85rem;font-size:.9rem;font-weight:700;color:#1f2937;">📈 Perkembangan Simpanan &amp; Pembiayaan Tahun Ini</h3>
            <div style="position:relative;height:250px;">
                <canvas id="ks-chart"></canvas>
            </div>
        </div>

        <div style="background:#fff;padding:1.1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);border:1px solid #f3f4f6;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:.75rem;">
                <h3 style="margin:0;font-size:.9rem;font-weight:700;color:#1f2937;">🔔 Persetujuan Pending</h3>
                <span style="font-size:.68rem;background:#fef3c7;color:#92400e;padding:.15rem .55rem;border-radius:9999px;font-weight:700;">Total: {{ $totalPending }}</span>
            </div>
            <div style="display:flex;flex-direction:column;gap:.4rem;">
                @foreach([
                    ['Anggota Baru','Verifikasi pendaftaran',$pendingAnggota,'#d1fae5','#059669','#059669'],
                    ['Simpanan Sukarela','Konfirmasi setoran',$pendingSukarela,'#dbeafe','#2563eb','#3b82f6'],
                    ['Simpanan Pokok','Verifikasi pokok awal',$pendingPokok,'#e0e7ff','#4f46e5','#6366f1'],
                    ['Pengajuan Pinjaman','Persetujuan akad',$pendingPinjaman,'#fef3c7','#d97706','#f59e0b'],
                    ['Pembayaran Cicilan','Setoran angsuran',$pendingBayar,'#ede9fe','#7c3aed','#a855f7'],
                ] as [$label,$note,$count,$bg,$ic,$ac])
                <div style="display:flex;align-items:center;justify-content:space-between;padding:.5rem .65rem;background:#f9fafb;border:1px solid #f3f4f6;border-radius:.65rem;">
                    <div style="display:flex;align-items:center;gap:.5rem;">
                        <div style="width:28px;height:28px;border-radius:.45rem;background:{{ $bg }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg style="width:.75rem;height:.75rem;color:{{ $ic }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p style="margin:0;font-size:.72rem;font-weight:700;color:#111827;">{{ $label }}</p>
                            <p style="margin:0;font-size:.62rem;color:#6b7280;">{{ $note }}</p>
                        </div>
                    </div>
                    <span style="min-width:26px;text-align:center;padding:.2rem .45rem;font-size:.7rem;font-weight:800;border-radius:.4rem;background:{{ $count>0 ? $ac : '#e5e7eb' }};color:{{ $count>0 ? '#fff' : '#6b7280' }};">{{ $count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

</div>

<script>
(function initKsChart() {
    var attempt = 0;
    function tryDraw() {
        if (typeof Chart === 'undefined') {
            if (++attempt < 50) setTimeout(tryDraw, 200);
            return;
        }
        var el = document.getElementById('ks-chart');
        if (!el) return;
        if (window._ksC) { window._ksC.destroy(); }
        window._ksC = new Chart(el.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! $chartLabels !!},
                datasets: [
                    { label:'Simpanan', data:{!! $chartSimpanan !!}, borderColor:'#10b981', backgroundColor:'rgba(16,185,129,.1)', borderWidth:2.5, tension:.4, fill:true, pointRadius:3, pointHoverRadius:5, pointBackgroundColor:'#10b981' },
                    { label:'Pembiayaan', data:{!! $chartPembiayaan !!}, borderColor:'#3b82f6', backgroundColor:'rgba(59,130,246,.07)', borderWidth:2.5, tension:.4, fill:true, pointRadius:3, pointHoverRadius:5, pointBackgroundColor:'#3b82f6' }
                ]
            },
            options: {
                responsive:true, maintainAspectRatio:false,
                plugins: {
                    legend:{ display:true, position:'top', labels:{ usePointStyle:true, boxWidth:8, font:{size:11} } },
                    tooltip:{ mode:'index', intersect:false, callbacks:{ label:function(c){ return c.dataset.label+': '+new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR',minimumFractionDigits:0}).format(c.parsed.y); } } }
                },
                scales: {
                    x:{ grid:{display:false}, ticks:{font:{size:10}} },
                    y:{ beginAtZero:true, grid:{color:'rgba(0,0,0,.04)'}, ticks:{ font:{size:10}, callback:function(v){ return v>=1e6?'Rp '+(v/1e6).toFixed(1)+'Jt':v>=1e3?'Rp '+(v/1e3).toFixed(0)+'Rb':'Rp '+v; } } }
                },
                interaction:{ mode:'nearest', axis:'x', intersect:false }
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
