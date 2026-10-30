<x-filament-panels::page>
<style>
.ks-ptab { padding:.45rem 1rem;border-radius:.65rem;font-size:.78rem;font-weight:700;border:1.5px solid #e5e7eb;background:#fff;color:#6b7280;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;transition:all .2s;text-decoration:none; }
.ks-ptab:hover { border-color:#6ee7b7;color:#065f46; }
.ks-ptab.active { background:#059669;border-color:#047857;color:#fff;box-shadow:0 3px 10px rgba(5,150,105,.25); }
.ks-pstat { transition:all .3s;cursor:default; }
.ks-pstat:hover { transform:translateY(-3px);box-shadow:0 10px 24px rgba(0,0,0,.1)!important; }
</style>

{{-- ── HEADER ── --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem;">
    <div>
        <h2 style="margin:0;font-size:1.6rem;font-weight:800;color:#111827;display:flex;align-items:center;gap:.5rem;">
            💳 Manajemen Pembiayaan
        </h2>
        <p style="margin:.2rem 0 0;font-size:.82rem;color:#6b7280;">Kelola dan pantau seluruh pengajuan serta rincian pembiayaan anggota.</p>
    </div>
    <a href="{{ $createUrl }}" style="display:inline-flex;align-items:center;gap:.4rem;padding:.55rem 1.1rem;background:#059669;border-radius:.65rem;font-size:.82rem;font-weight:700;color:#fff;text-decoration:none;box-shadow:0 3px 12px rgba(5,150,105,.3);transition:background .2s;"
       onmouseover="this.style.background='#047857';" onmouseout="this.style.background='#059669';">
        <svg style="width:.9rem;height:.9rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Peminjaman Baru
    </a>
</div>

{{-- ── 3 STAT CARDS ── --}}
<div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem;margin-bottom:1.25rem;">

    {{-- Pinjaman Aktif --}}
    <div class="ks-pstat" style="background:#fff;padding:1.1rem 1.25rem;border-radius:.9rem;border:1px solid #f3f4f6;box-shadow:0 1px 5px rgba(0,0,0,.06);display:flex;align-items:center;gap:1rem;">
        <div style="width:44px;height:44px;border-radius:.75rem;background:#d1fae5;color:#059669;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg style="width:1.3rem;height:1.3rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p style="margin:0;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Pinjaman Aktif</p>
            <h3 style="margin:.2rem 0 .1rem;font-size:1.4rem;font-weight:900;color:#111827;">{{ number_format($aktif) }}</h3>
            <p style="margin:0;font-size:.68rem;color:#059669;font-weight:600;">Total Rp {{ number_format($totalNominal,0,',','.') }}</p>
        </div>
    </div>

    {{-- Menunggu Persetujuan --}}
    <div class="ks-pstat" style="background:#fff;padding:1.1rem 1.25rem;border-radius:.9rem;border:1px solid #f3f4f6;box-shadow:0 1px 5px rgba(0,0,0,.06);display:flex;align-items:center;gap:1rem;">
        <div style="width:44px;height:44px;border-radius:.75rem;background:#fef9c3;color:#d97706;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg style="width:1.3rem;height:1.3rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p style="margin:0;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Menunggu Persetujuan</p>
            <h3 style="margin:.2rem 0 .1rem;font-size:1.4rem;font-weight:900;color:#111827;">{{ number_format($pending) }}</h3>
            <p style="margin:0;font-size:.68rem;color:#d97706;font-weight:600;">Pengajuan Baru</p>
        </div>
    </div>

    {{-- Mendekati Jatuh Tempo --}}
    <div class="ks-pstat" style="background:#fff;padding:1.1rem 1.25rem;border-radius:.9rem;border:1px solid #f3f4f6;box-shadow:0 1px 5px rgba(0,0,0,.06);display:flex;align-items:center;gap:1rem;">
        <div style="width:44px;height:44px;border-radius:.75rem;background:#fee2e2;color:#dc2626;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg style="width:1.3rem;height:1.3rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <div>
            <p style="margin:0;font-size:.65rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Mendekati Jatuh Tempo</p>
            <h3 style="margin:.2rem 0 .1rem;font-size:1.4rem;font-weight:900;color:#111827;">{{ number_format($jatuhTempo) }}</h3>
            <p style="margin:0;font-size:.68rem;color:#dc2626;font-weight:600;">3 Hari ke Depan</p>
        </div>
    </div>

</div>

{{-- ── TABLE SECTION ── --}}
<div style="background:#fff;border-radius:1rem;border:1px solid #f3f4f6;box-shadow:0 1px 5px rgba(0,0,0,.06);overflow:hidden;">

    {{-- Tab + Search Bar --}}
    <div style="padding:.75rem 1rem;border-bottom:1px solid #f3f4f6;background:#f9fafb;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;">

        {{-- Tabs --}}
        <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
            @php
            $tabs = [
                ['semua',      'semua',      '☰ Semua Akad', $totalAll],
                ['qard',       'qard',       '👐 Qard',       $perTipe->get('qard')->jumlah ?? 0],
                ['murabahah',  'murabahah',  '🛍️ Murabahah',  $perTipe->get('murabahah')->jumlah ?? 0],
                ['mudharabah', 'mudharabah', '💼 Mudharabah', $perTipe->get('mudharabah')->jumlah ?? 0],
            ];
            @endphp
            @foreach($tabs as [$key, $val, $label, $count])
            <a href="?tab={{ $val }}&search={{ $search }}"
               class="ks-ptab {{ $activeTab === $val ? 'active' : '' }}">
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
                    style="padding:.45rem .75rem .45rem 2.1rem;border:1px solid #e5e7eb;border-radius:.65rem;font-size:.78rem;width:220px;outline:none;transition:border-color .2s;"
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
                    @foreach(['NO','NAMA ANGGOTA','AKAD','POKOK PINJAMAN','MARGIN (10%)','TOTAL KEWAJIBAN','TENOR','STATUS','AKSI'] as $th)
                    <th style="padding:.75rem 1rem;font-size:.68rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;text-align:{{ in_array($th,['NO','TENOR','STATUS','AKSI']) ? 'center' : 'left' }};white-space:nowrap;">{{ $th }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($pinjamans as $i => $p)
                @php
                    $margin = (float)$p->nominal * (($p->bunga_per_bulan ?? 0) / 100) * ($p->tenor ?? 0);
                    $total  = (float)$p->nominal + $margin;
                    $statusStyle = match($p->status) {
                        'aktif'   => ['bg'=>'#d1fae5','color'=>'#065f46'],
                        'lunas'   => ['bg'=>'#dbeafe','color'=>'#1e40af'],
                        'pending' => ['bg'=>'#fef9c3','color'=>'#854d0e'],
                        'ditolak' => ['bg'=>'#fee2e2','color'=>'#b91c1c'],
                        default   => ['bg'=>'#f3f4f6','color'=>'#374151'],
                    };
                    $tipeStyle = match($p->tipe) {
                        'qard'       => ['bg'=>'#d1fae5','color'=>'#065f46','border'=>'#6ee7b7'],
                        'murabahah'  => ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd'],
                        'mudharabah' => ['bg'=>'#fef9c3','color'=>'#854d0e','border'=>'#fde047'],
                        default      => ['bg'=>'#f3f4f6','color'=>'#374151','border'=>'#d1d5db'],
                    };
                    $editUrl = \App\Filament\Resources\Pinjamen\PinjamanResource::getUrl('edit', ['record' => $p->id]);
                    $no = ($pinjamans->currentPage() - 1) * $pinjamans->perPage() + $i + 1;
                @endphp
                <tr style="border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background='#f0fdf4';" onmouseout="this.style.background='transparent';">

                    <td style="padding:.7rem 1rem;text-align:center;color:#6b7280;font-weight:600;">{{ $no }}</td>

                    <td style="padding:.7rem 1rem;">
                        <p style="margin:0;font-weight:700;color:#111827;">{{ $p->nama_lengkap }}</p>
                    </td>

                    <td style="padding:.7rem 1rem;">
                        <span style="padding:.2rem .6rem;border-radius:.35rem;font-size:.7rem;font-weight:800;text-transform:uppercase;background:{{ $tipeStyle['bg'] }};color:{{ $tipeStyle['color'] }};border:1px solid {{ $tipeStyle['border'] }};">
                            {{ strtoupper($p->tipe ?? '-') }}
                        </span>
                    </td>

                    <td style="padding:.7rem 1rem;font-weight:700;color:#111827;">
                        Rp {{ number_format($p->nominal,0,',','.') }}
                    </td>

                    <td style="padding:.7rem 1rem;color:#374151;">
                        Rp {{ number_format($margin,0,',','.') }}
                        <span style="font-size:.68rem;color:#9ca3af;">({{ $p->bunga_per_bulan ?? 0 }}%)</span>
                    </td>

                    <td style="padding:.7rem 1rem;font-weight:800;color:#111827;">
                        Rp {{ number_format($total,0,',','.') }}
                    </td>

                    <td style="padding:.7rem 1rem;text-align:center;color:#374151;white-space:nowrap;">
                        {{ $p->tenor ?? '-' }} Bln
                    </td>

                    <td style="padding:.7rem 1rem;text-align:center;">
                        <span style="padding:.25rem .7rem;border-radius:9999px;font-size:.7rem;font-weight:700;background:{{ $statusStyle['bg'] }};color:{{ $statusStyle['color'] }};">
                            {{ ucfirst($p->status) }}
                        </span>
                    </td>

                    <td style="padding:.7rem 1rem;text-align:center;">
                        <div style="display:inline-flex;gap:.35rem;">
                            <a href="{{ $editUrl }}" style="padding:.3rem .65rem;background:#2563eb;color:#fff;border-radius:.45rem;font-size:.72rem;font-weight:700;text-decoration:none;transition:background .15s;white-space:nowrap;"
                               onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                                👁️ Detail
                            </a>
                            <button onclick="if(confirm('Hapus data pinjaman ini?')) alert('Fitur hapus segera diimplementasikan')"
                                style="padding:.3rem .65rem;background:#dc2626;color:#fff;border:none;border-radius:.45rem;font-size:.72rem;font-weight:700;cursor:pointer;transition:background .15s;white-space:nowrap;"
                                onmouseover="this.style.background='#b91c1c';" onmouseout="this.style.background='#dc2626';">
                                🗑️ Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center;padding:2.5rem;color:#9ca3af;">
                        <svg style="width:2.5rem;height:2.5rem;margin:0 auto .5rem;opacity:.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p style="font-size:.85rem;">Tidak ada data pinjaman</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($pinjamans->hasPages())
    <div style="padding:.75rem 1.25rem;border-top:1px solid #f3f4f6;background:#f9fafb;display:flex;justify-content:space-between;align-items:center;font-size:.78rem;color:#6b7280;flex-wrap:wrap;gap:.5rem;">
        <span>Menampilkan {{ $pinjamans->firstItem() }}–{{ $pinjamans->lastItem() }} dari {{ $pinjamans->total() }} data</span>
        <div style="display:flex;gap:.35rem;">
            @if($pinjamans->onFirstPage())
                <span style="padding:.3rem .65rem;border-radius:.45rem;background:#f3f4f6;color:#9ca3af;">← Prev</span>
            @else
                <a href="{{ $pinjamans->previousPageUrl() }}&tab={{ $activeTab }}&search={{ $search }}" style="padding:.3rem .65rem;border-radius:.45rem;background:#fff;border:1px solid #e5e7eb;color:#374151;text-decoration:none;">← Prev</a>
            @endif

            @foreach($pinjamans->getUrlRange(max(1,$pinjamans->currentPage()-2), min($pinjamans->lastPage(),$pinjamans->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}&tab={{ $activeTab }}&search={{ $search }}"
               style="padding:.3rem .65rem;border-radius:.45rem;text-decoration:none;{{ $page==$pinjamans->currentPage() ? 'background:#059669;color:#fff;' : 'background:#fff;border:1px solid #e5e7eb;color:#374151;' }}">{{ $page }}</a>
            @endforeach

            @if($pinjamans->hasMorePages())
                <a href="{{ $pinjamans->nextPageUrl() }}&tab={{ $activeTab }}&search={{ $search }}" style="padding:.3rem .65rem;border-radius:.45rem;background:#fff;border:1px solid #e5e7eb;color:#374151;text-decoration:none;">Next →</a>
            @else
                <span style="padding:.3rem .65rem;border-radius:.45rem;background:#f3f4f6;color:#9ca3af;">Next →</span>
            @endif
        </div>
    </div>
    @endif

</div>

</x-filament-panels::page>
