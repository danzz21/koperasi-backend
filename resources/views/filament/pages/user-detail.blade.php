<x-filament-panels::page>
<style>
.ks-stats-card { transition: all .3s cubic-bezier(.4,0,.2,1); }
.ks-stats-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px rgba(16,185,129,.12); }
.ks-tab-active { background: linear-gradient(135deg,#059669,#0d9488); color:#fff; box-shadow: 0 4px 12px rgba(13,148,136,.25); }
.ks-tab-inactive { background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; }
.ks-tab-inactive:hover { background:#f1f5f9; color:#0f172a; }
.ks-trx-item { transition: all .2s ease; }
.ks-trx-item:hover { background-color:#f0fdf4; border-color:#bbf7d0; }
</style>

@php
    $user    = $user;
    $status  = strtolower($user->status ?? 'pending');
    $statusBadgeStyle = match($status) {
        'aktif'    => 'background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;',
        'nonaktif' => 'background:#fee2e2;color:#b91c1c;border:1px solid #fca5a5;',
        default    => 'background:#fef9c3;color:#854d0e;border:1px solid #fde047;',
    };
    $initial = strtoupper(substr($user->nama_lengkap ?? 'A', 0, 1));
    $editUrl = \App\Filament\Resources\Users\UserResource::getUrl('edit', ['record' => $user->id]);
    $listUrl = \App\Filament\Resources\Users\UserResource::getUrl('index');
@endphp


{{-- ── PROFILE HEADER ── --}}
<div style="background:#fff;border-radius:1rem;padding:1.25rem 1.5rem;margin-bottom:1.25rem;border:1px solid rgba(16,185,129,.15);box-shadow:0 4px 16px rgba(0,0,0,.05);position:relative;overflow:hidden;">
    <div style="position:absolute;top:0;right:0;width:8rem;height:8rem;background:rgba(5,150,105,.04);border-radius:50%;filter:blur(2rem);margin:-2rem -2rem 0 0;pointer-events:none;"></div>

    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:1.5rem;flex-wrap:wrap;position:relative;z-index:1;">
        <div style="display:flex;align-items:center;gap:1.25rem;flex-wrap:wrap;">

            {{-- Foto --}}
            <div style="width:80px;height:80px;border-radius:1rem;border:3px solid #fff;box-shadow:0 4px 12px rgba(0,0,0,.1);overflow:hidden;background:#065f46;color:#fbbf24;display:flex;align-items:center;justify-content:center;font-size:2rem;font-weight:900;flex-shrink:0;">
                @if($user->foto && file_exists(public_path('uploads/'.$user->foto)))
                    <img src="{{ asset('uploads/'.$user->foto) }}" alt="Foto" style="width:100%;height:100%;object-fit:cover;">
                @else
                    {{ $initial }}
                @endif
            </div>

            {{-- Info --}}
            <div>
                <div style="display:flex;align-items:center;gap:.75rem;flex-wrap:wrap;margin-bottom:.4rem;">
                    <h1 style="margin:0;font-size:1.4rem;font-weight:900;color:#111827;line-height:1.2;">{{ $user->nama_lengkap ?? '-' }}</h1>
                    <span style="padding:.2rem .7rem;border-radius:9999px;font-size:.68rem;font-weight:800;text-transform:uppercase;{{ $statusBadgeStyle }}">{{ ucfirst($status) }}</span>
                </div>

                <div style="display:flex;flex-wrap:wrap;gap:.5rem 1.5rem;font-size:.75rem;color:#4b5563;margin-bottom:.3rem;">
                    <span>🪪 No. Anggota: <strong style="color:#111827;">{{ $user->nomor_anggota ?? '-' }}</strong></span>
                    <span>🔍 NIK: <strong style="color:#111827;">{{ $user->nomor_ktp ?? '-' }}</strong></span>
                    <span>📅 Bergabung: <strong style="color:#111827;">{{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</strong></span>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:.5rem 1.5rem;font-size:.72rem;color:#6b7280;">
                    <span>👤 Username: <strong style="color:#374151;">{{ $user->username ?? '-' }}</strong></span>
                    <span>📱 {{ $user->nomor_hp ?? '-' }}</span>
                    <span>✉️ {{ $user->email ?? '-' }}</span>
                </div>
            </div>
        </div>

        {{-- Status Verifikasi --}}
        <div style="background:#f9fafb;padding:.9rem 1.1rem;border-radius:.75rem;border:1px solid #f3f4f6;text-align:right;min-width:140px;">
            <p style="margin:0;font-size:.62rem;font-weight:700;color:#9ca3af;text-transform:uppercase;letter-spacing:.07em;">Status Verifikasi</p>
            <p style="margin:.3rem 0 0;font-size:.82rem;font-weight:800;color:#059669;">🛡️ Terverifikasi Sistem</p>
        </div>
    </div>
</div>

{{-- ── 4 STAT CARDS ── --}}
<div style="display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1.25rem;">

    <div class="ks-stats-card" style="background:#fff;padding:1rem 1.1rem;border-radius:.9rem;border:1px solid #f3f4f6;border-left:4px solid #10b981;box-shadow:0 1px 5px rgba(0,0,0,.05);display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
        <div>
            <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Total Simpanan</p>
            <h3 style="margin:.3rem 0 0;font-size:1.15rem;font-weight:900;color:#111827;white-space:nowrap;">Rp {{ number_format($totalSimpanan,0,',','.') }}</h3>
        </div>
        <div style="padding:.6rem;background:#ecfdf5;border-radius:.6rem;flex-shrink:0;">
            <svg style="width:1.1rem;height:1.1rem;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
        </div>
    </div>

    <div class="ks-stats-card" style="background:#fff;padding:1rem 1.1rem;border-radius:.9rem;border:1px solid #f3f4f6;border-left:4px solid #3b82f6;box-shadow:0 1px 5px rgba(0,0,0,.05);display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
        <div>
            <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Total Pembiayaan</p>
            <h3 style="margin:.3rem 0 0;font-size:1.15rem;font-weight:900;color:#111827;white-space:nowrap;">Rp {{ number_format($totalPembiayaan,0,',','.') }}</h3>
        </div>
        <div style="padding:.6rem;background:#eff6ff;border-radius:.6rem;flex-shrink:0;">
            <svg style="width:1.1rem;height:1.1rem;color:#2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>

    <div class="ks-stats-card" style="background:#fff;padding:1rem 1.1rem;border-radius:.9rem;border:1px solid #f3f4f6;border-left:4px solid #f59e0b;box-shadow:0 1px 5px rgba(0,0,0,.05);display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
        <div>
            <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Sisa Angsuran</p>
            <h3 style="margin:.3rem 0 0;font-size:1.15rem;font-weight:900;color:#111827;">{{ $sisaAngsuran }} Bulan</h3>
        </div>
        <div style="padding:.6rem;background:#fffbeb;border-radius:.6rem;flex-shrink:0;">
            <svg style="width:1.1rem;height:1.1rem;color:#d97706;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>

    <div class="ks-stats-card" style="background:#fff;padding:1rem 1.1rem;border-radius:.9rem;border:1px solid #f3f4f6;border-left:4px solid #14b8a6;box-shadow:0 1px 5px rgba(0,0,0,.05);display:flex;align-items:center;justify-content:space-between;gap:.75rem;">
        <div>
            <p style="margin:0;font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.06em;color:#9ca3af;">Estimasi Bagi Hasil</p>
            <h3 style="margin:.3rem 0 0;font-size:1.15rem;font-weight:900;color:#059669;white-space:nowrap;">Rp {{ number_format($bagiHasil,0,',','.') }}</h3>
        </div>
        <div style="padding:.6rem;background:#f0fdfa;border-radius:.6rem;flex-shrink:0;">
            <svg style="width:1.1rem;height:1.1rem;color:#0d9488;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
    </div>
</div>

{{-- ── DETAIL PROFIL & DOKUMEN ── --}}
<div style="background:#fff;border-radius:1rem;overflow:hidden;border:1px solid #f3f4f6;box-shadow:0 1px 5px rgba(0,0,0,.05);margin-bottom:1.25rem;">
    <div style="background:linear-gradient(90deg,#065f46,#0f766e);padding:.9rem 1.25rem;display:flex;justify-content:space-between;align-items:center;">
        <h3 style="margin:0;font-size:.9rem;font-weight:700;color:#fff;display:flex;align-items:center;gap:.4rem;">
            👤 Detail Profil & Dokumen Berkas
        </h3>
        <span style="font-size:.68rem;background:rgba(255,255,255,.2);color:#fff;padding:.2rem .65rem;border-radius:9999px;font-weight:600;">Sesuai Database</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1.5rem;padding:1.25rem;">

        {{-- Kolom 1: Data Pribadi --}}
        <div>
            <h4 style="margin:0 0 .75rem;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#059669;border-bottom:1px solid #f3f4f6;padding-bottom:.5rem;">🪪 Data Pribadi & Kontak</h4>
            <div style="display:flex;flex-direction:column;gap:.35rem;font-size:.78rem;">
                @foreach([
                    ['NIK (No. KTP)', $user->nomor_ktp ?? '-'],
                    ['Jenis Kelamin', $user->jenis_kelamin ?? '-'],
                    ['Pekerjaan', $user->pekerjaan ?? '-'],
                    ['No. HP Utama', $user->nomor_hp ?? '-'],
                    ['No. HP Keluarga', $user->nomor_hp_keluarga ?? '-'],
                    ['Email', $user->email ?? '-'],
                    ['No. Rekening', $user->no_rek ?? '-'],
                ] as [$lbl, $val])
                <div style="display:flex;justify-content:space-between;padding:.3rem 0;border-bottom:1px solid #f9fafb;">
                    <span style="color:#6b7280;">{{ $lbl }}:</span>
                    <span style="font-weight:700;color:#111827;text-align:right;max-width:55%;">{{ $val }}</span>
                </div>
                @endforeach

                @if($user->alamat)
                <div style="margin-top:.25rem;">
                    <span style="color:#6b7280;display:block;margin-bottom:.25rem;">Alamat:</span>
                    <p style="margin:0;font-weight:600;color:#111827;background:#f9fafb;padding:.5rem .65rem;border-radius:.5rem;border:1px solid #f3f4f6;font-size:.75rem;line-height:1.5;">{{ $user->alamat }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Kolom 2: Rekening Bank --}}
        <div>
            <h4 style="margin:0 0 .75rem;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#059669;border-bottom:1px solid #f3f4f6;padding-bottom:.5rem;">🏦 Rekening Bank Pembayaran</h4>
            <div style="background:linear-gradient(135deg,#ecfdf5,#f0fdfa);padding:1rem;border-radius:.75rem;border:1px solid #a7f3d0;display:flex;flex-direction:column;gap:.75rem;">
                <div>
                    <p style="margin:0;font-size:.62rem;font-weight:800;text-transform:uppercase;color:#065f46;">Jenis / Nama Bank</p>
                    <p style="margin:.2rem 0 0;font-size:.9rem;font-weight:900;color:#064e3b;">{{ $user->jenis_bank ? strtoupper($user->jenis_bank) : 'Belum diisi' }}</p>
                </div>
                <div>
                    <p style="margin:0;font-size:.62rem;font-weight:800;text-transform:uppercase;color:#065f46;">Nomor Rekening</p>
                    <p style="margin:.2rem 0 0;font-size:1rem;font-weight:900;letter-spacing:.08em;color:#111827;font-family:monospace;">{{ $user->no_rek ?? '-' }}</p>
                </div>
                <div>
                    <p style="margin:0;font-size:.62rem;font-weight:800;text-transform:uppercase;color:#065f46;">Atas Nama Rekening</p>
                    <p style="margin:.2rem 0 0;font-size:.82rem;font-weight:700;color:#374151;">{{ $user->atasnama_rekening ?? '-' }}</p>
                </div>
            </div>
        </div>

        {{-- Kolom 3: Foto Dokumen --}}
        <div>
            <h4 style="margin:0 0 .75rem;font-size:.68rem;font-weight:800;text-transform:uppercase;letter-spacing:.07em;color:#059669;border-bottom:1px solid #f3f4f6;padding-bottom:.5rem;">🖼️ Berkas Upload Diri & KTP</h4>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:.5rem;">
                @foreach([
                    ['KTP', '#059669', $user->foto_ktp ?? null],
                    ['Pas Foto', '#2563eb', $user->foto ?? null],
                    ['Selfie KTP', '#9333ea', $user->foto_diri_ktp ?? null],
                ] as [$label, $color, $fotoPath])
                <div style="background:#f9fafb;padding:.5rem;border-radius:.65rem;border:1px solid #e5e7eb;text-align:center;">
                    <p style="margin:0 0 .35rem;font-size:.6rem;font-weight:700;color:#374151;">{{ $label }}</p>
                    <div style="height:80px;background:#f3f4f6;border-radius:.5rem;overflow:hidden;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;">
                        @if($fotoPath && file_exists(public_path('uploads/'.$fotoPath)))
                            <img src="{{ asset('uploads/'.$fotoPath) }}" alt="{{ $label }}" style="width:100%;height:100%;object-fit:cover;cursor:pointer;" onclick="window.open('{{ asset('uploads/'.$fotoPath) }}','_blank')">
                        @else
                            <div style="text-align:center;color:#9ca3af;">
                                <svg style="width:1.25rem;height:1.25rem;margin:0 auto .2rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span style="font-size:.6rem;">Kosong</span>
                            </div>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- ── TABS RIWAYAT KEUANGAN ── --}}
<div style="background:#fff;border-radius:1rem;overflow:hidden;border:1px solid #f3f4f6;box-shadow:0 1px 5px rgba(0,0,0,.05);" x-data="{ tab: 'transaksi' }">

    {{-- Tab Buttons --}}
    <div style="background:#f8fafc;padding:.6rem .75rem;border-bottom:1px solid #e5e7eb;display:flex;flex-wrap:wrap;gap:.4rem;">
        @foreach([
            ['transaksi', '🔄 Riwayat Transaksi'],
            ['pembiayaan', '💳 Pembiayaan'],
            ['simpanan', '🏦 Simpanan'],
            ['angsuran', '📅 Jadwal Angsuran'],
        ] as [$key, $label])
        <button
            x-on:click="tab = '{{ $key }}'"
            :class="tab === '{{ $key }}' ? 'ks-tab-active' : 'ks-tab-inactive'"
            style="padding:.5rem 1rem;border-radius:.65rem;font-size:.75rem;font-weight:700;border:none;cursor:pointer;transition:all .2s;"
        >{{ $label }}</button>
        @endforeach
    </div>

    <div style="padding:1.25rem;">

        {{-- Tab: Transaksi --}}
        <div x-show="tab === 'transaksi'" x-cloak>
            <h4 style="margin:0 0 .85rem;font-size:.85rem;font-weight:700;color:#111827;">Mutasi Transaksi Anggota</h4>
            @if($riwayat->count())
            <div style="display:flex;flex-direction:column;gap:.5rem;">
                @foreach($riwayat as $trx)
                @php $isIn = ($trx->status ?? '') === 'selesai'; @endphp
                <div class="ks-trx-item" style="border:1px solid #e5e7eb;border-radius:.75rem;padding:.75rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;">
                    <div style="display:flex;align-items:center;gap:.75rem;">
                        <div style="padding:.5rem;border-radius:.6rem;background:{{ $isIn ? '#d1fae5' : '#fee2e2' }};color:{{ $isIn ? '#059669' : '#dc2626' }};flex-shrink:0;">
                            <svg style="width:.9rem;height:.9rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($isIn)
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 17l-4 4m0 0l-4-4m4 4V3"/>
                                @else
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7l4-4m0 0l4 4m-4-4v18"/>
                                @endif
                            </svg>
                        </div>
                        <div>
                            <p style="margin:0;font-size:.78rem;font-weight:700;color:#111827;">Pembayaran #{{ $trx->id }}</p>
                            <p style="margin:.15rem 0 0;font-size:.68rem;color:#6b7280;">{{ \Carbon\Carbon::parse($trx->tgl_pembayaran ?? $trx->created_at)->format('d M Y • H:i') }} WIB</p>
                        </div>
                    </div>
                    <div style="text-align:right;">
                        <p style="margin:0;font-size:.8rem;font-weight:800;color:{{ $isIn ? '#059669' : '#dc2626' }};">{{ $isIn ? '+' : '-' }}Rp {{ number_format($trx->nominal,0,',','.') }}</p>
                        <p style="margin:.1rem 0 0;font-size:.65rem;color:#6b7280;text-transform:uppercase;font-weight:700;">{{ ucfirst($trx->status ?? '-') }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div style="text-align:center;padding:2rem;color:#9ca3af;">
                <svg style="width:2.5rem;height:2.5rem;margin:0 auto .5rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                <p style="font-size:.8rem;">Belum ada riwayat transaksi</p>
            </div>
            @endif
        </div>

        {{-- Tab: Pembiayaan --}}
        <div x-show="tab === 'pembiayaan'" x-cloak>
            <h4 style="margin:0 0 .85rem;font-size:.85rem;font-weight:700;color:#111827;">Daftar Akad Pembiayaan</h4>
            @if($pinjamanAktif->count())
            <div style="display:flex;flex-direction:column;gap:.75rem;">
                @foreach($pinjamanAktif as $p)
                <div style="border:1px solid #e5e7eb;border-radius:.75rem;padding:1rem;transition:border-color .2s;" onmouseover="this.style.borderColor='#6ee7b7';" onmouseout="this.style.borderColor='#e5e7eb';">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:.75rem;">
                        <div>
                            <h5 style="margin:0;font-size:.85rem;font-weight:800;color:#111827;">{{ ucfirst($p->tipe ?? 'Pinjaman') }}</h5>
                            <p style="margin:.15rem 0 0;font-size:.7rem;color:#6b7280;">ID: #{{ $p->id }}</p>
                        </div>
                        <span style="background:#d1fae5;color:#065f46;border:1px solid #6ee7b7;padding:.2rem .65rem;border-radius:9999px;font-size:.68rem;font-weight:700;">{{ ucfirst($p->status ?? 'aktif') }}</span>
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:.75rem;border-top:1px solid #f3f4f6;padding-top:.75rem;font-size:.75rem;">
                        <div><span style="color:#9ca3af;display:block;">Nominal</span><strong style="color:#111827;">Rp {{ number_format($p->nominal,0,',','.') }}</strong></div>
                        <div><span style="color:#9ca3af;display:block;">Tenor</span><strong style="color:#111827;">{{ $p->tenor ?? '-' }} Bulan</strong></div>
                        <div><span style="color:#9ca3af;display:block;">Angsuran/Bln</span><strong style="color:#111827;">Rp {{ number_format($p->angsuran ?? 0,0,',','.') }}</strong></div>
                        <div><span style="color:#9ca3af;display:block;">Sisa Pinjaman</span><strong style="color:#d97706;">Rp {{ number_format($p->sisa_pinjaman ?? 0,0,',','.') }}</strong></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div style="text-align:center;padding:2rem;color:#9ca3af;">
                <svg style="width:2.5rem;height:2.5rem;margin:0 auto .5rem;opacity:.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p style="font-size:.8rem;">Belum ada pembiayaan aktif</p>
            </div>
            @endif
        </div>

        {{-- Tab: Simpanan --}}
        <div x-show="tab === 'simpanan'" x-cloak>
            <h4 style="margin:0 0 .85rem;font-size:.85rem;font-weight:700;color:#111827;">Rincian Simpanan Koperasi</h4>
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:1rem;">
                @foreach([
                    ['Simpanan Pokok', $simpananPokok, '#10b981', '#ecfdf5', 'Sesuai Ketentuan', '#059669'],
                    ['Simpanan Wajib', $simpananWajib, '#3b82f6', '#eff6ff', 'Rutin Setiap Bulan', '#2563eb'],
                    ['Simpanan Sukarela', $simpananSukarela, '#14b8a6', '#f0fdfa', 'Bisa Ditarik', '#0d9488'],
                ] as [$lbl, $val, $border, $bg, $note, $noteColor])
                <div style="border:1px solid #e5e7eb;padding:1rem;border-radius:.75rem;border-left:4px solid {{ $border }};">
                    <p style="margin:0;font-size:.65rem;font-weight:800;text-transform:uppercase;color:#9ca3af;letter-spacing:.06em;">{{ $lbl }}</p>
                    <p style="margin:.4rem 0 .5rem;font-size:1.1rem;font-weight:900;color:#111827;">Rp {{ number_format($val,0,',','.') }}</p>
                    <p style="margin:0;font-size:.68rem;font-weight:700;color:{{ $noteColor }};">✓ {{ $note }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Tab: Angsuran --}}
        <div x-show="tab === 'angsuran'" x-cloak>
            <h4 style="margin:0 0 .85rem;font-size:.85rem;font-weight:700;color:#111827;">Jadwal Angsuran Mendatang</h4>
            @if($cicilans->count())
            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:.78rem;">
                    <thead>
                        <tr style="background:#065f46;color:#fff;">
                            <th style="padding:.65rem 1rem;text-align:left;font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;">No</th>
                            <th style="padding:.65rem 1rem;text-align:left;font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;">Cicilan Ke</th>
                            <th style="padding:.65rem 1rem;text-align:left;font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;">Nominal</th>
                            <th style="padding:.65rem 1rem;text-align:left;font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;">Jatuh Tempo</th>
                            <th style="padding:.65rem 1rem;text-align:center;font-size:.68rem;font-weight:700;letter-spacing:.06em;text-transform:uppercase;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($cicilans as $i => $c)
                        <tr style="border-bottom:1px solid #f3f4f6;" onmouseover="this.style.background='#f0fdf4';" onmouseout="this.style.background='transparent';">
                            <td style="padding:.6rem 1rem;color:#6b7280;">{{ $i + 1 }}</td>
                            <td style="padding:.6rem 1rem;font-weight:700;color:#111827;">Cicilan #{{ $c->cicilan_ke ?? ($i+1) }}</td>
                            <td style="padding:.6rem 1rem;font-weight:700;color:#111827;">Rp {{ number_format($c->nominal,0,',','.') }}</td>
                            <td style="padding:.6rem 1rem;color:#374151;">{{ $c->tgl_tempo ? \Carbon\Carbon::parse($c->tgl_tempo)->format('d M Y') : '-' }}</td>
                            <td style="padding:.6rem 1rem;text-align:center;">
                                @php $st = $c->status ?? 'belumbayar'; @endphp
                                <span style="padding:.2rem .65rem;border-radius:9999px;font-size:.65rem;font-weight:700;background:{{ $st==='terbayar' ? '#d1fae5' : ($st==='denda' ? '#fee2e2' : '#fef9c3') }};color:{{ $st==='terbayar' ? '#065f46' : ($st==='denda' ? '#b91c1c' : '#854d0e') }};">{{ ucfirst($st) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div style="text-align:center;padding:2rem;color:#9ca3af;">
                <svg style="width:2.5rem;height:2.5rem;margin:0 auto .5rem;opacity:.5;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p style="font-size:.8rem;">Tidak ada cicilan yang belum terbayar</p>
            </div>
            @endif
        </div>

    </div>
</div>

</x-filament-panels::page>
