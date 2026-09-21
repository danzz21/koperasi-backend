@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');
@endphp

<x-filament-panels::page>
<style>
    .kss-wrap { width:100%; box-sizing:border-box; }
    .kss-card { transition: transform .2s, box-shadow .2s; }
    .kss-card:hover { transform: translateY(-3px); }
    .kss-grid3 { display:grid; grid-template-columns:repeat(3,minmax(0,1fr)); gap:.85rem; margin-bottom:1.25rem; }
    .kss-grid2 { display:grid; grid-template-columns:repeat(2,minmax(0,1fr)); gap:1rem; }
    .kss-panel { background:#fff; border:1px solid #f3f4f6; border-radius:.9rem; box-shadow:0 1px 5px rgba(0,0,0,.06); padding:1.1rem 1.2rem; margin-bottom:1.25rem; }
    .kss-panel h3 { margin:0 0 .9rem; font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:#6b7280; }
    .kss-tbl { width:100%; border-collapse:collapse; font-size:.8rem; }
    .kss-tbl td { padding:.5rem 0; border-bottom:1px solid #f3f4f6; color:#4b5563; }
    .kss-tbl td:last-child { text-align:right; font-weight:700; color:#111827; }
    .kss-tbl tr:last-child td { border-bottom:none; }
    .kss-row-total td { background:#ecfdf5; font-weight:900 !important; color:#047857 !important; padding-left:.5rem; padding-right:.5rem; }
    .kss-row-grand td { background:#f5f3ff; font-weight:900 !important; color:#6d28d9 !important; padding-left:.5rem; padding-right:.5rem; }
    @media(max-width:900px){ .kss-grid3{ grid-template-columns:1fr; } .kss-grid2{ grid-template-columns:1fr; } }
</style>

<div class="kss-wrap">
    {{-- BARIS 1: RINGKASAN --}}
    <div class="kss-grid3">
        <div class="kss-card" style="background:linear-gradient(135deg,#059669,#0f766e);color:#fff;padding:1.1rem;border-radius:.9rem;box-shadow:0 4px 14px rgba(5,150,105,.3);min-height:105px;display:flex;flex-direction:column;gap:.35rem;">
            <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(209,250,229,.9);">Total Pendapatan {{ $year }}</p>
            <p style="margin:0;font-size:1.35rem;font-weight:900;">{{ $rp($totalPendapatan) }}</p>
            <p style="margin:0;font-size:.63rem;color:rgba(209,250,229,.85);">Margin, bagi hasil &amp; PPOB</p>
        </div>

        <div class="kss-card" style="background:linear-gradient(135deg,#f43f5e,#b91c1c);color:#fff;padding:1.1rem;border-radius:.9rem;box-shadow:0 4px 14px rgba(244,63,94,.3);min-height:105px;display:flex;flex-direction:column;gap:.35rem;">
            <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(254,226,226,.9);">Total Beban</p>
            <p style="margin:0;font-size:1.35rem;font-weight:900;">{{ $rp($totalBeban) }}</p>
            <p style="margin:0;font-size:.63rem;color:rgba(254,226,226,.9);">Beban operasional</p>
        </div>

        <div class="kss-card" style="background:linear-gradient(135deg,#7c3aed,#4c1d95);color:#fff;padding:1.1rem;border-radius:.9rem;box-shadow:0 4px 14px rgba(124,58,237,.3);min-height:105px;display:flex;flex-direction:column;gap:.35rem;">
            <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(237,233,254,.9);">Laba Bersih</p>
            <p style="margin:0;font-size:1.35rem;font-weight:900;">{{ $rp($labaBersih) }}</p>
            <p style="margin:0;font-size:.63rem;color:rgba(237,233,254,.9);">Pendapatan − beban</p>
        </div>
    </div>

    {{-- LABA RUGI --}}
    <div class="kss-panel">
        <h3>Laporan Laba Rugi — {{ $year }}</h3>

        <table class="kss-tbl">
            <tbody>
                <tr>
                    <td>Margin Murabahah (estimasi 10%)</td>
                    <td>{{ $rp($marginMurabahah) }}</td>
                </tr>
                <tr>
                    <td>Bagi Hasil Mudharabah (estimasi 60%)</td>
                    <td>{{ $rp($shuMudharabah) }}</td>
                </tr>
                <tr>
                    <td>Pendapatan PPOB (margin harga jual)</td>
                    <td>{{ $rp($pendapatanPpob) }}</td>
                </tr>
                <tr class="kss-row-total">
                    <td>Total Pendapatan</td>
                    <td>{{ $rp($totalPendapatan) }}</td>
                </tr>
                <tr>
                    <td>Beban Operasional</td>
                    <td style="color:#e11d48;">({{ $rp($bebanOperasional) }})</td>
                </tr>
                <tr class="kss-row-grand">
                    <td>Laba Bersih</td>
                    <td>{{ $rp($labaBersih) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- NERACA --}}
    <div class="kss-grid2">
        <div class="kss-panel" style="margin-bottom:0;">
            <h3>Aset</h3>
            <table class="kss-tbl">
                <tbody>
                    <tr>
                        <td>Kas</td>
                        <td>{{ $rp($kas) }}</td>
                    </tr>
                    <tr>
                        <td>Piutang Pembiayaan</td>
                        <td>{{ $rp($piutang) }}</td>
                    </tr>
                    <tr class="kss-row-total">
                        <td>Total Aset</td>
                        <td>{{ $rp($totalAset) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="kss-panel" style="margin-bottom:0;">
            <h3>Kewajiban &amp; Ekuitas</h3>
            <table class="kss-tbl">
                <tbody>
                    <tr>
                        <td>Simpanan Anggota (Kewajiban)</td>
                        <td>{{ $rp($kewajiban) }}</td>
                    </tr>
                    <tr>
                        <td>Ekuitas / SHU</td>
                        <td>{{ $rp($ekuitas) }}</td>
                    </tr>
                    <tr class="kss-row-grand">
                        <td>Total Kewajiban + Ekuitas</td>
                        <td>{{ $rp($kewajiban + $ekuitas) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-filament-panels::page>
