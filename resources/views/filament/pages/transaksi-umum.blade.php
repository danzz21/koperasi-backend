<x-filament-panels::page>

{{-- ── HEADER ── --}}
<div style="margin-bottom:1.25rem;">
    <h2 style="margin:0;font-size:1.6rem;font-weight:800;color:#111827;display:flex;align-items:center;gap:.5rem;">
        🔄 Transaksi Umum
    </h2>
    <p style="margin:.2rem 0 0;font-size:.82rem;color:#6b7280;">Kelola pemasukan dan pengeluaran operasional</p>
</div>

{{-- ── 4 STAT CARDS ── --}}
<div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1.5rem;">

    {{-- Saldo Kas Utama --}}
    <div style="background:linear-gradient(135deg,#059669,#0f766e);color:#fff;padding:1.1rem;border-radius:.9rem;box-shadow:0 4px 14px rgba(5,150,105,.25);display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;">
        <div>
            <p style="margin:0;font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:rgba(209,250,229,.9);">Saldo Kas Utama</p>
            <h3 style="margin:.3rem 0 .2rem;font-size:1.4rem;font-weight:900;white-space:nowrap;">Rp {{ number_format($totalKas,0,',','.') }}</h3>
            <p style="margin:0;font-size:.65rem;color:rgba(209,250,229,.85);">Total Sisa Saldo Kumulatif</p>
        </div>
        <div style="padding:.45rem;background:rgba(255,255,255,.2);border-radius:.5rem;flex-shrink:0;">
            <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
        </div>
    </div>

    {{-- Arus Kas Bulan Ini --}}
    <div style="background:#fff;padding:1.1rem;border-radius:.9rem;border:1px solid #f3f4f6;border-left:4px solid #3b82f6;box-shadow:0 1px 5px rgba(0,0,0,.06);display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;">
        <div>
            <p style="margin:0;font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Arus Kas Bulan Ini</p>
            <h3 style="margin:.3rem 0 .2rem;font-size:1.4rem;font-weight:900;color:{{ $arusBulan >= 0 ? '#2563eb' : '#dc2626' }};white-space:nowrap;">Rp {{ number_format($arusBulan,0,',','.') }}</h3>
            <p style="margin:0;font-size:.65rem;color:#6b7280;">{{ $bulanLabel }} &nbsp;<strong>{{ $arusBulan >= 0 ? 'Surplus' : 'Defisit' }}</strong></p>
        </div>
        <div style="padding:.45rem;background:#eff6ff;border-radius:.5rem;flex-shrink:0;">
            <svg style="width:1.1rem;height:1.1rem;color:#2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
        </div>
    </div>

    {{-- Pemasukan --}}
    <div style="background:#fff;padding:1.1rem;border-radius:.9rem;border:1px solid #f3f4f6;border-left:4px solid #10b981;box-shadow:0 1px 5px rgba(0,0,0,.06);display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;">
        <div>
            <p style="margin:0;font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Pemasukan</p>
            <h3 style="margin:.3rem 0 .2rem;font-size:1.4rem;font-weight:900;color:#059669;white-space:nowrap;">Rp {{ number_format($pemasukanBulan,0,',','.') }}</h3>
            <p style="margin:0;font-size:.65rem;color:#059669;">Bulan Ini ({{ $bulanLabel }}) &nbsp;Total: Rp {{ number_format($pemasukanBulan,0,',','.') }}</p>
        </div>
        <div style="padding:.45rem;background:#ecfdf5;border-radius:.5rem;flex-shrink:0;">
            <svg style="width:1.1rem;height:1.1rem;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4 4m0 0l-4-4m4 4V3"/></svg>
        </div>
    </div>

    {{-- Pengeluaran --}}
    <div style="background:#fff;padding:1.1rem;border-radius:.9rem;border:1px solid #f3f4f6;border-left:4px solid #f43f5e;box-shadow:0 1px 5px rgba(0,0,0,.06);display:flex;justify-content:space-between;align-items:flex-start;gap:.5rem;">
        <div>
            <p style="margin:0;font-size:.62rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Pengeluaran</p>
            <h3 style="margin:.3rem 0 .2rem;font-size:1.4rem;font-weight:900;color:#e11d48;white-space:nowrap;">Rp {{ number_format($pengeluaranBulan,0,',','.') }}</h3>
            <p style="margin:0;font-size:.65rem;color:#e11d48;">Bulan Ini ({{ $bulanLabel }}) &nbsp;Total: Rp {{ number_format($pengeluaranBulan,0,',','.') }}</p>
        </div>
        <div style="padding:.45rem;background:#fff1f2;border-radius:.5rem;flex-shrink:0;">
            <svg style="width:1.1rem;height:1.1rem;color:#e11d48;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l4-4m0 0l4 4m-4-4v18"/></svg>
        </div>
    </div>

</div>

{{-- ── TABLE SECTION ── --}}
<div style="background:#fff;border-radius:1rem;border:1px solid #f3f4f6;box-shadow:0 1px 5px rgba(0,0,0,.06);overflow:hidden;">

    {{-- Title + Actions --}}
    <div style="padding:1rem 1.25rem;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;">
        <h3 style="margin:0;font-size:1rem;font-weight:700;color:#111827;">Riwayat Transaksi</h3>
        <div style="display:flex;gap:.5rem;">
            <a href="{{ route('filament.admin.resources.pembayarans.create') }}" style="padding:.4rem .9rem;background:#059669;color:#fff;border-radius:.6rem;font-size:.78rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:.3rem;"
               onmouseover="this.style.background='#047857';" onmouseout="this.style.background='#059669';">
                <svg style="width:.8rem;height:.8rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                + Pemasukan
            </a>
        </div>
    </div>

    {{-- Filter Bar --}}
    <form method="GET" style="padding:.85rem 1.25rem;background:#f9fafb;border-bottom:1px solid #f3f4f6;display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">

        <div>
            <label style="display:block;font-size:.68rem;font-weight:700;color:#4b5563;margin-bottom:.25rem;">Jenis:</label>
            <select name="jenis" style="padding:.45rem .75rem;border:1px solid #e5e7eb;border-radius:.6rem;font-size:.78rem;background:#fff;outline:none;">
                <option value="" {{ !$jenis ? 'selected' : '' }}>Semua Jenis</option>
                <option value="cicilan"  {{ $jenis==='cicilan'  ? 'selected' : '' }}>Cicilan</option>
                <option value="simpanan" {{ $jenis==='simpanan' ? 'selected' : '' }}>Simpanan</option>
                <option value="ppob"     {{ $jenis==='ppob'     ? 'selected' : '' }}>PPOB</option>
            </select>
        </div>

        <div>
            <label style="display:block;font-size:.68rem;font-weight:700;color:#4b5563;margin-bottom:.25rem;">Cari:</label>
            <input type="text" name="cari" value="{{ $cari }}" placeholder="Deskripsi..."
                style="padding:.45rem .75rem;border:1px solid #e5e7eb;border-radius:.6rem;font-size:.78rem;outline:none;width:160px;"
                onfocus="this.style.borderColor='#059669';" onblur="this.style.borderColor='#e5e7eb';">
        </div>

        <div>
            <label style="display:block;font-size:.68rem;font-weight:700;color:#4b5563;margin-bottom:.25rem;">Bulan:</label>
            <input type="month" name="bulan" value="{{ $bulan }}"
                style="padding:.45rem .75rem;border:1px solid #e5e7eb;border-radius:.6rem;font-size:.78rem;outline:none;">
        </div>

        <div style="display:flex;gap:.4rem;margin-top:1.1rem;">
            <button type="submit" style="padding:.45rem .9rem;background:#2563eb;color:#fff;border:none;border-radius:.6rem;font-size:.78rem;font-weight:700;cursor:pointer;">
                🔍 Cari
            </button>
            <a href="{{ route('filament.admin.pages.transaksi-umum') }}" style="padding:.45rem .9rem;background:#f3f4f6;color:#374151;border-radius:.6rem;font-size:.78rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:.25rem;">
                🔄 Reset
            </a>
        </div>
    </form>

    {{-- Table --}}
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.8rem;">
            <thead>
                <tr style="background:#065f46;color:#fff;">
                    @foreach(['TANGGAL','ANGGOTA','DESKRIPSI','KATEGORI','JUMLAH','JENIS','STATUS','AKSI'] as $th)
                    <th style="padding:.75rem 1rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;text-align:{{ in_array($th,['JUMLAH']) ? 'right' : (in_array($th,['JENIS','STATUS','AKSI']) ? 'center' : 'left') }};white-space:nowrap;">{{ $th }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($transaksis as $trx)
                @php
                    $jenisColor = match($trx->jenis) {
                        'cicilan'  => ['bg'=>'#d1fae5','color'=>'#065f46'],
                        'simpanan' => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                        'ppob'     => ['bg'=>'#fef9c3','color'=>'#854d0e'],
                        default    => ['bg'=>'#f3f4f6','color'=>'#374151'],
                    };
                    $statusColor = match($trx->status) {
                        'selesai'      => ['bg'=>'#d1fae5','color'=>'#065f46'],
                        'diverifikasi' => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                        'pending'      => ['bg'=>'#fef9c3','color'=>'#854d0e'],
                        'gagal'        => ['bg'=>'#fee2e2','color'=>'#b91c1c'],
                        default        => ['bg'=>'#f3f4f6','color'=>'#374151'],
                    };
                    $tgl = $trx->tgl_pembayaran ?? $trx->created_at;
                @endphp
                <tr style="border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background='#f0fdf4';" onmouseout="this.style.background='transparent';">

                    <td style="padding:.7rem 1rem;color:#374151;white-space:nowrap;font-size:.78rem;">
                        {{ $tgl ? \Carbon\Carbon::parse($tgl)->format('d M Y') : '-' }}
                    </td>

                    <td style="padding:.7rem 1rem;font-weight:600;color:#111827;">
                        {{ $trx->nama_lengkap ?? '-' }}
                    </td>

                    <td style="padding:.7rem 1rem;color:#374151;max-width:200px;">
                        <span style="display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            {{ $trx->keterangan ?: ($trx->no_referensi ?: '-') }}
                        </span>
                    </td>

                    <td style="padding:.7rem 1rem;">
                        <span style="padding:.2rem .6rem;border-radius:.35rem;font-size:.7rem;font-weight:700;text-transform:capitalize;background:{{ $jenisColor['bg'] }};color:{{ $jenisColor['color'] }};">
                            {{ ucfirst($trx->jenis) }}
                        </span>
                    </td>

                    <td style="padding:.7rem 1rem;text-align:right;font-weight:800;color:#059669;font-size:.82rem;white-space:nowrap;">
                        +Rp {{ number_format($trx->nominal,0,',','.') }}
                    </td>

                    <td style="padding:.7rem 1rem;text-align:center;">
                        <span style="padding:.2rem .6rem;border-radius:.35rem;font-size:.7rem;font-weight:700;background:#ecfdf5;color:#065f46;">
                            Kredit
                        </span>
                    </td>

                    <td style="padding:.7rem 1rem;text-align:center;">
                        <span style="padding:.2rem .6rem;border-radius:9999px;font-size:.7rem;font-weight:700;background:{{ $statusColor['bg'] }};color:{{ $statusColor['color'] }};">
                            {{ ucfirst($trx->status) }}
                        </span>
                    </td>

                    <td style="padding:.7rem 1rem;text-align:center;">
                        <a href="{{ route('filament.admin.resources.pembayarans.edit', ['record' => $trx->id]) }}"
                           style="padding:.3rem .6rem;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:.45rem;font-size:.72rem;color:#374151;text-decoration:none;transition:background .15s;"
                           onmouseover="this.style.background='#e5e7eb';" onmouseout="this.style.background='#f3f4f6';">
                            ✏️ Edit
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:2.5rem;color:#9ca3af;">
                        <svg style="width:2.5rem;height:2.5rem;margin:0 auto .5rem;opacity:.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <p style="font-size:.85rem;">Belum ada transaksi</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($transaksis->hasPages())
    <div style="padding:.75rem 1.25rem;border-top:1px solid #f3f4f6;background:#f9fafb;display:flex;justify-content:space-between;align-items:center;font-size:.78rem;color:#6b7280;flex-wrap:wrap;gap:.5rem;">
        <span>Menampilkan {{ $transaksis->firstItem() }}–{{ $transaksis->lastItem() }} dari {{ $transaksis->total() }} data</span>
        <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
            @if($transaksis->onFirstPage())
                <span style="padding:.3rem .65rem;border-radius:.45rem;background:#f3f4f6;color:#9ca3af;">← Prev</span>
            @else
                <a href="{{ $transaksis->previousPageUrl() }}&jenis={{ $jenis }}&bulan={{ $bulan }}&cari={{ $cari }}" style="padding:.3rem .65rem;border-radius:.45rem;background:#fff;border:1px solid #e5e7eb;color:#374151;text-decoration:none;">← Prev</a>
            @endif
            @foreach($transaksis->getUrlRange(max(1,$transaksis->currentPage()-2), min($transaksis->lastPage(),$transaksis->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}&jenis={{ $jenis }}&bulan={{ $bulan }}&cari={{ $cari }}"
               style="padding:.3rem .65rem;border-radius:.45rem;text-decoration:none;{{ $page==$transaksis->currentPage() ? 'background:#059669;color:#fff;' : 'background:#fff;border:1px solid #e5e7eb;color:#374151;' }}">{{ $page }}</a>
            @endforeach
            @if($transaksis->hasMorePages())
                <a href="{{ $transaksis->nextPageUrl() }}&jenis={{ $jenis }}&bulan={{ $bulan }}&cari={{ $cari }}" style="padding:.3rem .65rem;border-radius:.45rem;background:#fff;border:1px solid #e5e7eb;color:#374151;text-decoration:none;">Next →</a>
            @else
                <span style="padding:.3rem .65rem;border-radius:.45rem;background:#f3f4f6;color:#9ca3af;">Next →</span>
            @endif
        </div>
    </div>
    @endif

</div>

</x-filament-panels::page>
