<x-filament-panels::page>
<style>
.ks-tab-btn { padding:.5rem 1.1rem;border-radius:.65rem;font-size:.78rem;font-weight:700;border:1.5px solid #e5e7eb;background:#fff;color:#6b7280;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;transition:all .2s; }
.ks-tab-btn:hover { border-color:#6ee7b7;color:#065f46; }
.ks-tab-btn.active { background:#059669;border-color:#047857;color:#fff;box-shadow:0 3px 10px rgba(5,150,105,.25); }
.ks-stat { transition:all .3s; cursor:default; }
.ks-stat:hover { transform:translateY(-3px); box-shadow:0 10px 24px rgba(0,0,0,.1)!important; }
</style>

{{-- ── HEADER ── --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.75rem;margin-bottom:1.25rem;">
    <div>
        <h2 style="margin:0;font-size:1.6rem;font-weight:800;color:#111827;display:flex;align-items:center;gap:.5rem;">
        <i class="fas fa-coins" style="color:#059669;font-size:1.4rem;"></i> Manajemen Simpanan
    </h2>
        <p style="margin:.2rem 0 0;font-size:.82rem;color:#6b7280;">Kelola simpanan anggota koperasi</p>
    </div>
</div>

{{-- ── TAB FILTER ── --}}
<div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1.25rem;">
    @foreach([
        ['pokok',   'heroicon-o-circle-stack', '🪙 Pokok'],
        ['wajib',   'heroicon-o-document-text', '📋 Wajib'],
        ['sukarela','heroicon-o-heart',         '💜 Sukarela'],
        ['semua',   'heroicon-o-list-bullet',   '☰ Semua'],
    ] as [$key, $icon, $label])
    <a href="?tab={{ $key }}&search={{ $search }}&anggota_id={{ $anggotaId }}"
       class="ks-tab-btn {{ $activeTab === $key ? 'active' : '' }}">
        {{ $label }}
    </a>
    @endforeach
</div>

{{-- ── STAT CARDS ── --}}
<div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1.5rem;">

    {{-- Pokok --}}
    <div class="ks-stat" style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);border:1.5px solid #6ee7b7;padding:1.1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(5,150,105,.1);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <p style="margin:0;font-size:.68rem;font-weight:700;color:#065f46;">Simpanan Pokok</p>
                <h3 style="margin:.3rem 0 .15rem;font-size:1.5rem;font-weight:900;color:#065f46;line-height:1.1;">Rp {{ number_format($pokok->total ?? 0,0,',','.') }}</h3>
                <p style="margin:0;font-size:.68rem;color:#059669;">Total dari {{ $pokok->jumlah ?? 0 }} anggota</p>
            </div>
            <div style="padding:.55rem;background:rgba(5,150,105,.15);border-radius:.6rem;">
                <svg style="width:1.2rem;height:1.2rem;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
            </div>
        </div>
        <p style="margin:.55rem 0 0;font-size:.68rem;color:#059669;font-weight:600;">Lunas: {{ $lunasPokok }} anggota</p>
    </div>

    {{-- Wajib --}}
    <div class="ks-stat" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border:1.5px solid #93c5fd;padding:1.1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(37,99,235,.1);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <p style="margin:0;font-size:.68rem;font-weight:700;color:#1e40af;">Simpanan Wajib</p>
                <h3 style="margin:.3rem 0 .15rem;font-size:1.5rem;font-weight:900;color:#1e40af;line-height:1.1;">Rp {{ number_format($wajib->total ?? 0,0,',','.') }}</h3>
                <p style="margin:0;font-size:.68rem;color:#2563eb;">Akumulasi wajib</p>
            </div>
            <div style="padding:.55rem;background:rgba(37,99,235,.12);border-radius:.6rem;">
                <svg style="width:1.2rem;height:1.2rem;color:#2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            </div>
        </div>
        <p style="margin:.55rem 0 0;font-size:.68rem;color:#2563eb;font-weight:600;">Rutin setiap bulan</p>
    </div>

    {{-- Sukarela --}}
    <div class="ks-stat" style="background:linear-gradient(135deg,#faf5ff,#ede9fe);border:1.5px solid #c4b5fd;padding:1.1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(124,58,237,.1);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <p style="margin:0;font-size:.68rem;font-weight:700;color:#6d28d9;">Simpanan Sukarela</p>
                <h3 style="margin:.3rem 0 .15rem;font-size:1.5rem;font-weight:900;color:#6d28d9;line-height:1.1;">Rp {{ number_format($sukarela->total ?? 0,0,',','.') }}</h3>
                <p style="margin:0;font-size:.68rem;color:#7c3aed;">Akumulasi sukarela</p>
            </div>
            <div style="padding:.55rem;background:rgba(124,58,237,.12);border-radius:.6rem;">
                <svg style="width:1.2rem;height:1.2rem;color:#7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/></svg>
            </div>
        </div>
        <p style="margin:.55rem 0 0;font-size:.68rem;color:#7c3aed;font-weight:600;">Opsional & fleksibel</p>
    </div>

    {{-- Total --}}
    <div class="ks-stat" style="background:#fff;border:1.5px solid #e5e7eb;padding:1.1rem;border-radius:.9rem;box-shadow:0 1px 5px rgba(0,0,0,.06);">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <p style="margin:0;font-size:.68rem;font-weight:700;color:#6b7280;">Total Semua Simpanan</p>
                <h3 style="margin:.3rem 0 .15rem;font-size:1.5rem;font-weight:900;color:#111827;line-height:1.1;">Rp {{ number_format($totalSimpanan,0,',','.') }}</h3>
                <p style="margin:0;font-size:.68rem;color:#6b7280;">Akumulasi keseluruhan</p>
            </div>
            <div style="padding:.55rem;background:#f3f4f6;border-radius:.6rem;">
                <svg style="width:1.2rem;height:1.2rem;color:#374151;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            </div>
        </div>
        <p style="margin:.55rem 0 0;font-size:.68rem;color:#6b7280;font-weight:600;">3 jenis simpanan</p>
    </div>

</div>

{{-- ── TABLE SECTION ── --}}
<div style="background:#fff;border-radius:1rem;border:1px solid #f3f4f6;box-shadow:0 1px 5px rgba(0,0,0,.06);overflow:hidden;">

    {{-- Table Header --}}
    <div style="padding:1rem 1.25rem;border-bottom:1px solid #f3f4f6;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:.75rem;">
        <div>
            <h3 style="margin:0;font-size:1rem;font-weight:700;color:#111827;">Transaksi Simpanan</h3>
            <p style="margin:.15rem 0 0;font-size:.75rem;color:#6b7280;">Data simpanan {{ $activeTab === 'semua' ? 'semua jenis' : $activeTab }}</p>
        </div>
        <a href="{{ $createUrl }}" style="display:inline-flex;align-items:center;gap:.4rem;padding:.5rem 1rem;background:#059669;border-radius:.65rem;font-size:.78rem;font-weight:700;color:#fff;text-decoration:none;box-shadow:0 3px 10px rgba(5,150,105,.3);transition:all .2s;"
           onmouseover="this.style.background='#047857';" onmouseout="this.style.background='#059669';">
            <svg style="width:.85rem;height:.85rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Input Simpanan
        </a>
    </div>

    {{-- Search & Filter Bar --}}
    <form method="GET" style="padding:.9rem 1.25rem;background:#f9fafb;border-bottom:1px solid #f3f4f6;display:flex;gap:.75rem;flex-wrap:wrap;align-items:flex-end;">
        <input type="hidden" name="tab" value="{{ $activeTab }}">

        <div style="flex:1;min-width:200px;">
            <label style="display:block;font-size:.68rem;font-weight:700;color:#4b5563;margin-bottom:.3rem;">🔽 Cari Anggota</label>
            <div style="position:relative;">
                <input type="text" name="search" value="{{ $search }}"
                    placeholder="Nama atau nomor KTP..."
                    style="width:100%;padding:.5rem .75rem .5rem 2.25rem;border:1px solid #e5e7eb;border-radius:.65rem;font-size:.8rem;box-sizing:border-box;outline:none;transition:border-color .2s;"
                    onfocus="this.style.borderColor='#059669';" onblur="this.style.borderColor='#e5e7eb';">
                <svg style="position:absolute;left:.65rem;top:50%;transform:translateY(-50%);width:.9rem;height:.9rem;color:#9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <div style="flex:1;min-width:200px;">
            <label style="display:block;font-size:.68rem;font-weight:700;color:#4b5563;margin-bottom:.3rem;">☰ Pilih Anggota</label>
            <select name="anggota_id" style="width:100%;padding:.5rem .75rem;border:1px solid #e5e7eb;border-radius:.65rem;font-size:.8rem;background:#fff;outline:none;box-sizing:border-box;">
                <option value="">Semua Anggota</option>
                @foreach($anggotaList as $a)
                    <option value="{{ $a->id }}" {{ $anggotaId == $a->id ? 'selected' : '' }}>{{ $a->nama_lengkap }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" style="padding:.5rem 1.1rem;background:#2563eb;border:none;border-radius:.65rem;font-size:.78rem;font-weight:700;color:#fff;cursor:pointer;display:inline-flex;align-items:center;gap:.4rem;white-space:nowrap;transition:background .2s;"
            onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
            <svg style="width:.85rem;height:.85rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Filter
        </button>
    </form>

    {{-- Table --}}
    <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.8rem;">
            <thead>
                <tr style="background:#065f46;color:#fff;">
                    <th style="padding:.7rem 1rem;text-align:left;font-size:.68rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;white-space:nowrap;">📅 Transaksi Terakhir</th>
                    <th style="padding:.7rem 1rem;text-align:left;font-size:.68rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;">👤 Nama Anggota</th>
                    <th style="padding:.7rem 1rem;text-align:center;font-size:.68rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;">🏷️ Jenis</th>
                    <th style="padding:.7rem 1rem;text-align:right;font-size:.68rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;">💰 Total Simpanan</th>
                    <th style="padding:.7rem 1rem;text-align:center;font-size:.68rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;white-space:nowrap;">✅ Status / Catatan</th>
                    <th style="padding:.7rem 1rem;text-align:center;font-size:.68rem;font-weight:700;letter-spacing:.07em;text-transform:uppercase;">⚙️ Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($simpanans as $row)
                @php
                    $jenisColor = match($row->jenis) {
                        'pokok'    => ['bg'=>'#d1fae5','color'=>'#065f46','border'=>'#6ee7b7'],
                        'wajib'    => ['bg'=>'#dbeafe','color'=>'#1e40af','border'=>'#93c5fd'],
                        'sukarela' => ['bg'=>'#ede9fe','color'=>'#6d28d9','border'=>'#c4b5fd'],
                        default    => ['bg'=>'#f3f4f6','color'=>'#374151','border'=>'#d1d5db'],
                    };
                    $statusStyle = match($row->status) {
                        'aktif'       => ['bg'=>'#d1fae5','color'=>'#065f46'],
                        'tidak_aktif' => ['bg'=>'#fee2e2','color'=>'#b91c1c'],
                        'ditolak'     => ['bg'=>'#fee2e2','color'=>'#b91c1c'],
                        default       => ['bg'=>'#fef9c3','color'=>'#854d0e'],
                    };
                    $editUrl = \App\Filament\Resources\Simpanans\SimpananResource::getUrl('edit', ['record' => $row->id]);
                @endphp
                <tr style="border-bottom:1px solid #f3f4f6;transition:background .15s;" onmouseover="this.style.background='#f0fdf4';" onmouseout="this.style.background='transparent';">

                    <td style="padding:.75rem 1rem;color:#374151;white-space:nowrap;">
                        {{ $row->updated_at ? \Carbon\Carbon::parse($row->updated_at)->format('d M Y') : '-' }}
                    </td>

                    <td style="padding:.75rem 1rem;">
                        <p style="margin:0;font-weight:700;color:#111827;">{{ $row->nama_lengkap }}</p>
                        @if($row->keterangan)
                        <a href="#" style="font-size:.68rem;color:#2563eb;text-decoration:none;">📌 {{ Str::limit($row->keterangan, 30) }}</a>
                        @endif
                    </td>

                    <td style="padding:.75rem 1rem;text-align:center;">
                        <span style="padding:.25rem .65rem;border-radius:9999px;font-size:.7rem;font-weight:700;background:{{ $jenisColor['bg'] }};color:{{ $jenisColor['color'] }};border:1px solid {{ $jenisColor['border'] }};">
                            {{ ucfirst($row->jenis) }}
                        </span>
                    </td>

                    <td style="padding:.75rem 1rem;text-align:right;font-weight:700;color:#111827;white-space:nowrap;">
                        Rp {{ number_format($row->nominal,0,',','.') }}
                    </td>

                    <td style="padding:.75rem 1rem;text-align:center;">
                        <span style="padding:.25rem .65rem;border-radius:9999px;font-size:.7rem;font-weight:700;background:{{ $statusStyle['bg'] }};color:{{ $statusStyle['color'] }};display:inline-flex;align-items:center;gap:.25rem;">
                            @if($row->status === 'tidak_aktif')
                            🔄 Dicicil (Rp {{ number_format($row->nominal,0,',','.') }})
                            @elseif($row->status === 'aktif')
                            ✅ Aktif
                            @else
                            ❌ {{ ucfirst($row->status) }}
                            @endif
                        </span>
                    </td>

                    <td style="padding:.75rem 1rem;text-align:center;">
                        <div style="display:inline-flex;gap:.4rem;">
                            <a href="{{ $editUrl }}" style="padding:.3rem .7rem;background:#2563eb;color:#fff;border-radius:.45rem;font-size:.72rem;font-weight:700;text-decoration:none;transition:background .15s;display:inline-flex;align-items:center;gap:.25rem;"
                               onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                                👁️ Detail
                            </a>
                            <button onclick="if(confirm('Hapus simpanan ini?')) window.location='{{ route('filament.admin.resources.simpanans.index') }}?delete={{ $row->id }}'"
                                style="padding:.3rem .7rem;background:#dc2626;color:#fff;border:none;border-radius:.45rem;font-size:.72rem;font-weight:700;cursor:pointer;transition:background .15s;display:inline-flex;align-items:center;gap:.25rem;"
                                onmouseover="this.style.background='#b91c1c';" onmouseout="this.style.background='#dc2626';">
                                🗑️ Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align:center;padding:2.5rem;color:#9ca3af;">
                        <svg style="width:2.5rem;height:2.5rem;margin:0 auto .5rem;opacity:.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <p style="font-size:.85rem;">Tidak ada data simpanan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($simpanans->hasPages())
    <div style="padding:.75rem 1.25rem;border-top:1px solid #f3f4f6;background:#f9fafb;display:flex;justify-content:space-between;align-items:center;font-size:.78rem;color:#6b7280;flex-wrap:wrap;gap:.5rem;">
        <span>Menampilkan {{ $simpanans->firstItem() }}–{{ $simpanans->lastItem() }} dari {{ $simpanans->total() }} data</span>
        <div style="display:flex;gap:.35rem;">
            @if($simpanans->onFirstPage())
                <span style="padding:.3rem .65rem;border-radius:.45rem;background:#f3f4f6;color:#9ca3af;">← Prev</span>
            @else
                <a href="{{ $simpanans->previousPageUrl() }}&tab={{ $activeTab }}&search={{ $search }}&anggota_id={{ $anggotaId }}" style="padding:.3rem .65rem;border-radius:.45rem;background:#fff;border:1px solid #e5e7eb;color:#374151;text-decoration:none;">← Prev</a>
            @endif

            @foreach($simpanans->getUrlRange(max(1,$simpanans->currentPage()-2), min($simpanans->lastPage(),$simpanans->currentPage()+2)) as $page => $url)
                <a href="{{ $url }}&tab={{ $activeTab }}&search={{ $search }}&anggota_id={{ $anggotaId }}"
                   style="padding:.3rem .65rem;border-radius:.45rem;text-decoration:none;{{ $page == $simpanans->currentPage() ? 'background:#059669;color:#fff;' : 'background:#fff;border:1px solid #e5e7eb;color:#374151;' }}">
                    {{ $page }}
                </a>
            @endforeach

            @if($simpanans->hasMorePages())
                <a href="{{ $simpanans->nextPageUrl() }}&tab={{ $activeTab }}&search={{ $search }}&anggota_id={{ $anggotaId }}" style="padding:.3rem .65rem;border-radius:.45rem;background:#fff;border:1px solid #e5e7eb;color:#374151;text-decoration:none;">Next →</a>
            @else
                <span style="padding:.3rem .65rem;border-radius:.45rem;background:#f3f4f6;color:#9ca3af;">Next →</span>
            @endif
        </div>
    </div>
    @endif

</div>

</x-filament-panels::page>
