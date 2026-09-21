@php
    $rp = fn ($n) => 'Rp ' . number_format((float) $n, 0, ',', '.');

    $badgeClass = fn ($status) => match ($status) {
        'success', 'selesai', 'aktif', 'terbayar', 'diverifikasi' => 'kss-b-ok',
        'pending'                                                   => 'kss-b-wait',
        'gagal', 'ditolak', 'nonaktif'                               => 'kss-b-no',
        default                                                     => 'kss-b-neutral',
    };
@endphp

<x-filament-panels::page>
<style>
    .kss-wrap { width:100%; box-sizing:border-box; }
    .kss-panel { background:#fff; border:1px solid #f3f4f6; border-radius:.9rem; box-shadow:0 1px 5px rgba(0,0,0,.06); padding:1.1rem 1.2rem; margin-bottom:1.1rem; }
    .kss-panel h3 { margin:0 0 .9rem; font-size:.72rem; font-weight:800; letter-spacing:.08em; text-transform:uppercase; color:#6b7280; }
    .kss-scroll { width:100%; overflow-x:auto; }
    .kss-tbl { width:100%; border-collapse:collapse; font-size:.8rem; min-width:640px; }
    .kss-tbl th { text-align:left; padding:.5rem .6rem; font-size:.62rem; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#9ca3af; border-bottom:1px solid #e5e7eb; white-space:nowrap; }
    .kss-tbl td { padding:.55rem .6rem; border-bottom:1px solid #f3f4f6; color:#4b5563; white-space:nowrap; }
    .kss-tbl tbody tr:last-child td { border-bottom:none; }
    .kss-tbl tbody tr:hover td { background:#fafafa; }
    .kss-num { text-align:right; font-weight:800; color:#111827; }
    .kss-name { font-weight:700; color:#111827; }
    .kss-empty-row td { text-align:center; color:#9ca3af; padding:1rem 0; }
    .kss-badge { display:inline-block; padding:.15rem .5rem; border-radius:9999px; font-size:.63rem; font-weight:800; text-transform:capitalize; }
    .kss-b-ok { background:#d1fae5; color:#047857; }
    .kss-b-wait { background:#fef3c7; color:#b45309; }
    .kss-b-no { background:#ffe4e6; color:#be123c; }
    .kss-b-neutral { background:#f3f4f6; color:#4b5563; }
</style>

<div class="kss-wrap">
    {{-- SIMPANAN --}}
    <div class="kss-panel">
        <h3>Transaksi Simpanan Terbaru</h3>
        <div class="kss-scroll">
            <table class="kss-tbl">
                <thead>
                    <tr>
                        <th>Anggota</th>
                        <th>Jenis</th>
                        <th style="text-align:right;">Nominal</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($simpanan as $row)
                        <tr>
                            <td class="kss-name">{{ $row->nama_lengkap ?? '-' }}</td>
                            <td>{{ ucfirst($row->jenis) }}</td>
                            <td class="kss-num">{{ $rp($row->nominal) }}</td>
                            <td><span class="kss-badge {{ $badgeClass($row->status) }}">{{ $row->status }}</span></td>
                            <td style="font-size:.7rem;color:#9ca3af;">
                                {{ \Illuminate\Support\Carbon::parse($row->created_at)->format('d M Y H:i') }}
                            </td>
                        </tr>
                    @empty
                        <tr class="kss-empty-row"><td colspan="5">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
{{-- PEMBIAYAAN --}}
    <div class="kss-panel">
        <h3>Transaksi Pembiayaan Terbaru</h3>
        <div class="kss-scroll">
            <table class="kss-tbl">
                <thead>
                    <tr>
                        <th>Anggota</th>
                        <th>Akad</th>
                        <th style="text-align:right;">Nominal</th>
                        <th style="text-align:right;">Sisa</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pinjaman as $row)
                        <tr>
                            <td class="kss-name">{{ $row->nama_lengkap ?? '-' }}</td>
                            <td>{{ ucfirst($row->tipe) }}</td>
                            <td class="kss-num">{{ $rp($row->nominal) }}</td>
                            <td style="text-align:right;">{{ $rp($row->sisa_pinjaman) }}</td>
                            <td><span class="kss-badge {{ $badgeClass($row->status) }}">{{ $row->status }}</span></td>
                        </tr>
                    @empty
                        <tr class="kss-empty-row"><td colspan="5">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PPOB --}}
    <div class="kss-panel">
        <h3>Transaksi PPOB Terbaru</h3>
        <div class="kss-scroll">
            <table class="kss-tbl">
                <thead>
                    <tr>
                        <th>Anggota</th>
                        <th>Produk</th>
                        <th>Tujuan</th>
                        <th style="text-align:right;">Harga Jual</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ppob as $row)
                        <tr>
                            <td class="kss-name">{{ $row->nama_lengkap ?? '-' }}</td>
                            <td>{{ $row->nama_produk }}</td>
                            <td>{{ $row->nomor_tujuan }}</td>
                            <td class="kss-num">{{ $rp($row->harga) }}</td>
                            <td><span class="kss-badge {{ $badgeClass($row->status) }}">{{ $row->status }}</span></td>
                        </tr>
                    @empty
                        <tr class="kss-empty-row"><td colspan="5">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- PEMBAYARAN --}}
    <div class="kss-panel" style="margin-bottom:0;">
        <h3>Setoran / Pembayaran Terbaru</h3>
        <div class="kss-scroll">
            <table class="kss-tbl">
                <thead>
                    <tr>
                        <th>Anggota</th>
                        <th>Referensi</th>
                        <th>Jenis</th>
                        <th>Metode</th>
                        <th style="text-align:right;">Nominal</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pembayaran as $row)
                        <tr>
                            <td class="kss-name">{{ $row->nama_lengkap ?? '-' }}</td>
                            <td style="font-family:ui-monospace,Menlo,monospace;font-size:.7rem;">{{ $row->no_referensi }}</td>
                            <td>{{ ucfirst($row->jenis) }}</td>
                            <td>{{ ucfirst($row->metode) }}</td>
                            <td class="kss-num">{{ $rp($row->nominal) }}</td>
                            <td><span class="kss-badge {{ $badgeClass($row->status) }}">{{ $row->status }}</span></td>
                        </tr>
                    @empty
                        <tr class="kss-empty-row"><td colspan="6">Belum ada data.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</x-filament-panels::page>
