<x-filament-panels::page>
<style>
.ks-atab { padding:.45rem 1rem;border-radius:.65rem;font-size:.78rem;font-weight:700;border:1.5px solid #e5e7eb;background:#fff;color:#6b7280;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;transition:all .2s;text-decoration:none; }
.ks-atab:hover { border-color:#6ee7b7;color:#065f46; }
.ks-atab.active { background:#059669;border-color:#047857;color:#fff;box-shadow:0 3px 10px rgba(5,150,105,.25); }

/* Progress bar */
.ks-progress-wrap { width:120px;background:#e5e7eb;border-radius:9999px;height:6px;overflow:hidden; }
.ks-progress-fill { height:6px;border-radius:9999px;transition:width .4s ease; }
</style>

{{-- ── HEADER ── --}}
<div style="margin-bottom:1.25rem;">
    <h2 style="margin:0;font-size:1.6rem;font-weight:800;color:#111827;display:flex;align-items:center;gap:.5rem;">
        📅 Manajemen Angsuran
    </h2>
    <p style="margin:.2rem 0 0;font-size:.82rem;color:#6b7280;">Kelola, bayar, dan pantau rincian status tenor serta riwayat angsuran anggota.</p>
</div>

{{-- ── TABLE SECTION ── --}}
<div style="background:#fff;border-radius:1rem;border:1px solid #f3f4f6;box-shadow:0 1px 5px rgba(0,0,0,.06);overflow:hidden;">

    {{-- Tab + Search Bar --}}
    <div style="padding:.75rem 1rem;border-bottom:1px solid #f3f4f6;background:#f9fafb;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;">

        {{-- Tabs --}}
        <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
            @php
            $tabs = [
                ['qard',       '👐 Qard',       $perTipe->get('qard')->jumlah       ?? 0],
                ['murabahah',  '🛍️ Murabahah',  $perTipe->get('murabahah')->jumlah  ?? 0],
                ['mudharabah', '💼 Mudharabah', $perTipe->get('mudharabah')->jumlah ?? 0],
            ];
            @endphp
            @foreach($tabs as [$val, $label, $count])
            <a href="?tab={{ $val }}&search={{ $search }}"
               class="ks-atab {{ $activeTab === $val ? 'active' : '' }}">
                {{ $label }}
                <span style="padding:.1rem .45rem;border-radius:9999px;font-size:.65rem;font-weight:800;background:{{ $activeTab===$val ? 'rgba(255,255,255,.3)' : '#f3f4f6' }};color:{{ $activeTab===$val ? '#fff' : '#374151' }};">{{ $count }}</span>
            </a>
            @endforeach
        </div>

        {{-- Search --}}
        <form method="GET" style="display:flex;align-items:center;gap:.5rem;">
            <input type="hidden" name="tab" value="{{ $activeTab }}">
            <div style="position:relative;">
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Cari nama anggota..."
                    style="padding:.45rem .75rem .45rem 2.1rem;border:1px solid #e5e7eb;border-radius:.65rem;font-size:.78rem;width:220px;outline:none;"
                    onfocus="this.style.borderColor='#059669';" onblur="this.style.borderColor='#e5e7eb';">
                <svg style="position:absolute;left:.6rem;top:50%;transform:translateY(-50%);width:.85rem;height:.85rem;color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.8rem;">
            <thead>
                <tr style="background:#065f46;color:#fff;">
                    <th style="padding:.75rem 1rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;text-align:left;min-width:160px;">ANGGOTA</th>
                    <th style="padding:.75rem 1rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;text-align:center;white-space:nowrap;">TGL AKAD</th>
                    <th style="padding:.75rem 1rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;text-align:right;white-space:nowrap;">PINJAMAN / BLN</th>
                    <th style="padding:.75rem 1rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;text-align:left;min-width:160px;">PROGRES TENOR</th>
                    <th style="padding:.75rem 1rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;text-align:right;white-space:nowrap;">TERBAYAR</th>
                    <th style="padding:.75rem 1rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;text-align:right;white-space:nowrap;">SISA TAGIHAN</th>
                    <th style="padding:.75rem 1rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;text-align:center;">STATUS</th>
                    <th style="padding:.75rem 1rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;text-align:center;">AKSI</th>
                </tr>
            </thead>
            <tbody>
                @forelse($angsurans as $row)
                @php
                    $tenor       = (int) ($row->tenor ?? 1);
                    $lunas       = (int) ($row->cicilan_lunas ?? 0);
                    $progres     = $tenor > 0 ? round(($lunas / $tenor) * 100) : 0;
                    $terbayar    = (float) ($row->terbayar_nominal ?? 0);
                    $sisa        = (float) ($row->sisa_nominal ?? $row->nominal ?? 0);
                    $angsuran    = (float) ($row->angsuran ?? 0);
                    $isLunas     = $row->status === 'lunas' || $progres >= 100;

                    $statusStyle = match(true) {
                        $isLunas              => ['bg'=>'#d1fae5','color'=>'#065f46','label'=>'Lunas'],
                        $row->status==='aktif'=> ['bg'=>'#dbeafe','color'=>'#1e40af','label'=>'Aktif'],
                        $row->status==='pending'=>['bg'=>'#fef9c3','color'=>'#854d0e','label'=>'Pending'],
                        default               => ['bg'=>'#f3f4f6','color'=>'#374151','label'=>ucfirst($row->status)],
                    };

                    $progresColor = $progres >= 100 ? '#059669' : ($progres >= 50 ? '#3b82f6' : '#f59e0b');
                    $cicilan_ke = $row->cicilan_ke ?? ($lunas + 1);
                @endphp
                <tr style="border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background='#f0fdf4';" onmouseout="this.style.background='transparent';">

                    {{-- ANGGOTA --}}
                    <td style="padding:.75rem 1rem;">
                        <p style="margin:0;font-weight:700;color:#111827;font-size:.82rem;">{{ $row->nama_lengkap }}</p>
                        @if($row->username)
                        <p style="margin:.1rem 0 0;font-size:.65rem;color:#6b7280;">
                            <svg style="width:.65rem;height:.65rem;display:inline;margin-right:.2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2"/></svg>
                            {{ $row->username }}
                        </p>
                        @endif
                    </td>

                    {{-- TGL AKAD --}}
                    <td style="padding:.75rem 1rem;text-align:center;color:#374151;white-space:nowrap;font-size:.78rem;">
                        {{ $row->tgl_akad ? \Carbon\Carbon::parse($row->tgl_akad)->format('d M Y') : '-' }}
                    </td>

                    {{-- PINJAMAN / BLN --}}
                    <td style="padding:.75rem 1rem;text-align:right;">
                        <p style="margin:0;font-weight:800;color:#111827;font-size:.85rem;">Rp {{ number_format($row->nominal,0,',','.') }}</p>
                        <p style="margin:.1rem 0 0;font-size:.68rem;color:#6b7280;">Rp {{ number_format($angsuran,0,',','.') }}/bln</p>
                    </td>

                    {{-- PROGRES TENOR --}}
                    <td style="padding:.75rem 1rem;">
                        <div style="display:flex;align-items:center;gap:.5rem;min-width:140px;">
                            <div>
                                <p style="margin:0 0 .3rem;font-size:.72rem;font-weight:700;color:#374151;">
                                    Ke-{{ $cicilan_ke }}
                                    <span style="color:#9ca3af;font-weight:400;">{{ $lunas }}/{{ $tenor }} bln ({{ $progres }}%)</span>
                                </p>
                                <div class="ks-progress-wrap">
                                    <div class="ks-progress-fill" style="width:{{ $progres }}%;background:{{ $progresColor }};"></div>
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- TERBAYAR --}}
                    <td style="padding:.75rem 1rem;text-align:right;font-weight:700;color:#059669;font-size:.82rem;">
                        Rp {{ number_format($terbayar,0,',','.') }}
                    </td>

                    {{-- SISA TAGIHAN --}}
                    <td style="padding:.75rem 1rem;text-align:right;font-weight:800;color:#dc2626;font-size:.82rem;">
                        Rp {{ number_format($sisa,0,',','.') }}
                    </td>

                    {{-- STATUS --}}
                    <td style="padding:.75rem 1rem;text-align:center;">
                        <span style="padding:.25rem .7rem;border-radius:9999px;font-size:.7rem;font-weight:700;background:{{ $statusStyle['bg'] }};color:{{ $statusStyle['color'] }};">
                            {{ $statusStyle['label'] }}
                        </span>
                    </td>

                    {{-- AKSI --}}
                    <td style="padding:.75rem 1rem;text-align:center;">
                        <div style="display:inline-flex;gap:.35rem;align-items:center;">
                            {{-- Detail --}}
                            <a href="{{ route('filament.admin.resources.pinjamen.edit', ['record' => $row->id]) }}"
                               style="padding:.3rem .5rem;background:#f3f4f6;border:1px solid #e5e7eb;border-radius:.45rem;color:#374151;text-decoration:none;transition:background .15s;"
                               title="Lihat Detail"
                               onmouseover="this.style.background='#e5e7eb';" onmouseout="this.style.background='#f3f4f6';">
                                <svg style="width:.9rem;height:.9rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>

                            {{-- Bayar / Lunas --}}
                            @if($isLunas)
                            <span style="padding:.3rem .8rem;background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;border-radius:.45rem;font-size:.72rem;font-weight:700;display:inline-flex;align-items:center;gap:.25rem;">
                                ✅ Lunas
                            </span>
                            @else
                            <a href="{{ route('filament.admin.resources.pembayarans.create') }}"
                               style="padding:.3rem .8rem;background:#059669;color:#fff;border-radius:.45rem;font-size:.72rem;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:.25rem;transition:background .15s;white-space:nowrap;"
                               onmouseover="this.style.background='#047857';" onmouseout="this.style.background='#059669';">
                                <svg style="width:.75rem;height:.75rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                Bayar
                            </a>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align:center;padding:2.5rem;color:#9ca3af;">
                        <svg style="width:2.5rem;height:2.5rem;margin:0 auto .5rem;opacity:.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <p style="font-size:.85rem;">Tidak ada data angsuran untuk akad {{ ucfirst($activeTab) }}</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($angsurans->hasPages())
    <div style="padding:.75rem 1.25rem;border-top:1px solid #f3f4f6;background:#f9fafb;display:flex;justify-content:space-between;align-items:center;font-size:.78rem;color:#6b7280;flex-wrap:wrap;gap:.5rem;">
        <span>Menampilkan {{ $angsurans->firstItem() }}–{{ $angsurans->lastItem() }} dari {{ $angsurans->total() }} data</span>
        <div style="display:flex;gap:.35rem;flex-wrap:wrap;">
            @if($angsurans->onFirstPage())
                <span style="padding:.3rem .65rem;border-radius:.45rem;background:#f3f4f6;color:#9ca3af;">← Prev</span>
            @else
                <a href="{{ $angsurans->previousPageUrl() }}&tab={{ $activeTab }}&search={{ $search }}" style="padding:.3rem .65rem;border-radius:.45rem;background:#fff;border:1px solid #e5e7eb;color:#374151;text-decoration:none;">← Prev</a>
            @endif

            @foreach($angsurans->getUrlRange(max(1,$angsurans->currentPage()-2), min($angsurans->lastPage(),$angsurans->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}&tab={{ $activeTab }}&search={{ $search }}"
               style="padding:.3rem .65rem;border-radius:.45rem;text-decoration:none;{{ $page==$angsurans->currentPage() ? 'background:#059669;color:#fff;' : 'background:#fff;border:1px solid #e5e7eb;color:#374151;' }}">{{ $page }}</a>
            @endforeach

            @if($angsurans->hasMorePages())
                <a href="{{ $angsurans->nextPageUrl() }}&tab={{ $activeTab }}&search={{ $search }}" style="padding:.3rem .65rem;border-radius:.45rem;background:#fff;border:1px solid #e5e7eb;color:#374151;text-decoration:none;">Next →</a>
            @else
                <span style="padding:.3rem .65rem;border-radius:.45rem;background:#f3f4f6;color:#9ca3af;">Next →</span>
            @endif
        </div>
    </div>
    @endif

</div>

</x-filament-panels::page>
