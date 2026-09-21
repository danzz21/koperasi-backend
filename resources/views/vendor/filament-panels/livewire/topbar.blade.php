@php
    use Filament\Support\Facades\FilamentView;
    use Filament\View\PanelsRenderHook;

    // Hitung pending counts
    $pendingAnggota   = \Illuminate\Support\Facades\DB::table('users')->where('role','anggota')->where('status','pending')->count();
    $pendingSukarela  = \Illuminate\Support\Facades\DB::table('simpanans')->where('status','tidak_aktif')->where('jenis','sukarela')->count();
    $pendingPokok     = \Illuminate\Support\Facades\DB::table('simpanans')->where('status','tidak_aktif')->where('jenis','pokok')->count();
    $pendingPinjaman  = \Illuminate\Support\Facades\DB::table('pinjamen')->where('status','pending')->count();
    $pendingBayar     = \Illuminate\Support\Facades\DB::table('pembayarans')->where('status','pending')->count();
    $totalPending     = $pendingAnggota + $pendingSukarela + $pendingPokok + $pendingPinjaman + $pendingBayar;

    $topbarIsSuperadmin = filament()->getCurrentPanel()?->getId() === 'superadmin';
@endphp

<div class="fi-topbar-ctn">
    {{-- TOP ACCENT BAR --}}
    <div style="height:4px;width:100%;background:linear-gradient(90deg,#065f46,#059669,#fbbf24);"></div>

    <nav class="fi-topbar" style="background:#fff;border-bottom:1px solid #d1fae5;box-shadow:0 2px 10px rgba(6,78,59,.06);display:flex;align-items:center;justify-content:space-between;padding:.75rem 1.5rem;position:relative;">

        {{ FilamentView::renderHook(PanelsRenderHook::TOPBAR_START) }}

        {{-- Hamburger untuk mobile --}}
        <button
            x-data="{}"
            x-on:click="$store.sidebar.open()"
            x-show="! $store.sidebar.isOpen"
            x-cloak
            aria-label="Open sidebar"
            style="display:none;background:none;border:none;cursor:pointer;padding:.25rem;margin-right:.5rem;"
            class="lg:hidden"
        >
            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- BRAND / LOGO --}}
        <div style="display:flex;align-items:center;gap:1rem;">
            <div style="width:48px;height:48px;border-radius:.75rem;background:#fff;display:flex;align-items:center;justify-content:center;border:1px solid #d1fae5;box-shadow:0 1px 4px rgba(6,78,59,.08);overflow:hidden;flex-shrink:0;">
                <div style="width:100%;height:100%;background:#047857;color:#fbbf24;font-weight:900;font-size:1.1rem;display:flex;align-items:center;justify-content:center;">KS</div>
            </div>
            <div>
                <div style="display:flex;align-items:center;gap:.5rem;">
                    <h1 style="margin:0;font-size:1.2rem;font-weight:800;color:#1f2937;line-height:1;">
                        {{ $topbarIsSuperadmin ? 'Superadmin' : 'Koperasi Syariah' }} <span style="color:#047857;">K-Samara</span>
                    </h1>
                    <span style="background:#fef3c7;color:#92400e;font-size:.6rem;font-weight:800;padding:.15rem .5rem;border-radius:9999px;border:1px solid #fcd34d;letter-spacing:.08em;text-transform:uppercase;">Syariah</span>
                </div>
                <p style="margin:.15rem 0 0;font-size:.7rem;color:#6b7280;font-weight:500;">{{ $topbarIsSuperadmin ? 'Panel Superadmin' : 'Dashboard Administrasi' }}</p>
            </div>
        </div>

        {{-- RIGHT SIDE: Notif + User --}}
        <div style="display:flex;align-items:center;gap:1rem;">

            {{-- NOTIFICATION BELL --}}
            @if (! $topbarIsSuperadmin)
            <div x-data="{ open: false }" style="position:relative;">
                <button
                    x-on:click="open = !open"
                    x-on:click.outside="open = false"
                    style="width:40px;height:40px;border-radius:.75rem;background:#f9fafb;border:1px solid #e5e7eb;display:flex;align-items:center;justify-content:center;cursor:pointer;position:relative;color:#4b5563;transition:all .2s;"
                    onmouseover="this.style.background='#ecfdf5';this.style.color='#059669';"
                    onmouseout="this.style.background='#f9fafb';this.style.color='#4b5563';"
                >
                    <svg style="width:1.1rem;height:1.1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    @if($totalPending > 0)
                    <span style="position:absolute;top:-4px;right:-4px;display:flex;width:20px;height:20px;">
                        <span style="animation:ping 1.5s cubic-bezier(0,0,.2,1) infinite;position:absolute;display:inline-flex;width:100%;height:100%;border-radius:9999px;background:#f87171;opacity:.75;"></span>
                        <span style="position:relative;display:inline-flex;border-radius:9999px;width:20px;height:20px;background:#dc2626;color:#fff;font-size:.6rem;font-weight:800;align-items:center;justify-content:center;border:2px solid #fff;">
                            {{ $totalPending > 99 ? '99+' : $totalPending }}
                        </span>
                    </span>
                    @endif
                </button>

                {{-- Dropdown Notifikasi --}}
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 translate-y-0"
                    x-transition:leave-end="opacity-0 translate-y-1"
                    x-cloak
                    style="position:absolute;right:0;top:calc(100% + .5rem);width:320px;z-index:9999;background:#fff;border-radius:1rem;box-shadow:0 20px 40px rgba(0,0,0,.12);border:1px solid #f0fdf4;overflow:hidden;"
                >
                    <div style="padding:.75rem 1rem;background:linear-gradient(90deg,#065f46,#0f766e);color:#fff;display:flex;justify-content:space-between;align-items:center;">
                        <div style="display:flex;align-items:center;gap:.5rem;">
                            <svg style="width:.9rem;height:.9rem;color:#fbbf24;" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/></svg>
                            <span style="font-size:.7rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase;">Pengajuan Masuk</span>
                        </div>
                        <span style="background:#fbbf24;color:#064e3b;font-size:.65rem;font-weight:800;padding:.2rem .5rem;border-radius:9999px;">{{ $totalPending }} Menunggu</span>
                    </div>

                    <div style="max-height:320px;overflow-y:auto;divide-y:1px solid #f3f4f6;">
                        @php
                        $notifs = [
                            ['label'=>'Verifikasi Anggota Baru','note'=>'Pendaftaran akun anggota','count'=>$pendingAnggota,'color'=>'#059669','bg'=>'#d1fae5','url'=>'/admin/users'],
                            ['label'=>'Simpanan Pokok','note'=>'Setoran pokok registrasi','count'=>$pendingPokok,'color'=>'#4f46e5','bg'=>'#e0e7ff','url'=>'/admin/simpanans'],
                            ['label'=>'Simpanan Sukarela','note'=>'Setoran simpanan sukarela','count'=>$pendingSukarela,'color'=>'#2563eb','bg'=>'#dbeafe','url'=>'/admin/simpanans'],
                            ['label'=>'Pengajuan Pinjaman','note'=>'Permohonan akad pembiayaan','count'=>$pendingPinjaman,'color'=>'#d97706','bg'=>'#fef3c7','url'=>'/admin/pinjamen'],
                            ['label'=>'Pembayaran Cicilan','note'=>'Konfirmasi angsuran pinjaman','count'=>$pendingBayar,'color'=>'#7c3aed','bg'=>'#ede9fe','url'=>'/admin/pembayarans'],
                        ];
                        @endphp
                        @foreach($notifs as $n)
                        <a href="{{ $n['url'] }}" style="display:flex;align-items:center;justify-content:space-between;padding:.65rem 1rem;text-decoration:none;border-bottom:1px solid #f3f4f6;transition:background .15s;" onmouseover="this.style.background='#f0fdf4';" onmouseout="this.style.background='transparent';">
                            <div style="display:flex;align-items:center;gap:.6rem;">
                                <div style="width:32px;height:32px;border-radius:.5rem;background:{{ $n['bg'] }};display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                    <svg style="width:.9rem;height:.9rem;color:{{ $n['color'] }};" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <p style="font-size:.72rem;font-weight:700;color:#111827;margin:0;">{{ $n['label'] }}</p>
                                    <p style="font-size:.62rem;color:#6b7280;margin:0;">{{ $n['note'] }}</p>
                                </div>
                            </div>
                            <span style="padding:.2rem .5rem;font-size:.7rem;font-weight:800;border-radius:.4rem;background:{{ $n['count']>0 ? $n['color'] : '#e5e7eb' }};color:{{ $n['count']>0 ? '#fff' : '#6b7280' }};">{{ $n['count'] }}</span>
                        </a>
                        @endforeach
                    </div>

                    <div style="background:#f9fafb;padding:.4rem;text-align:center;border-top:1px solid #f3f4f6;">
                        <span style="font-size:.62rem;color:#6b7280;font-weight:600;">Klik kategori untuk memproses pengajuan</span>
                    </div>
                </div>
            </div>
            @endif

            <div style="width:1px;height:28px;background:#e5e7eb;"></div>

            {{-- USER DROPDOWN --}}
            <div x-data="{ open: false }" style="position:relative;">
                <button
                    x-on:click="open = !open"
                    x-on:click.outside="open = false"
                    style="display:flex;align-items:center;gap:.6rem;padding:.35rem .6rem;border-radius:.75rem;background:transparent;border:none;cursor:pointer;transition:background .2s;"
                    onmouseover="this.style.background='#ecfdf5';" onmouseout="this.style.background='transparent';"
                >
                    <div style="width:36px;height:36px;border-radius:.75rem;background:#047857;color:#fbbf24;border:2px solid #fbbf24;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:.8rem;flex-shrink:0;">
                        <svg style="width:.9rem;height:.9rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div style="text-align:left;display:none;" class="md:block" style="display:block;">
                        <p style="font-size:.72rem;font-weight:700;color:#111827;line-height:1.2;margin:0;">{{ filament()->auth()->user()?->nama_lengkap ?? 'Pengguna' }}</p>
                        <p style="font-size:.62rem;color:#059669;margin:0;">{{ filament()->auth()->user()?->role === 'superadmin' ? 'Superadmin' : 'Administrator' }}</p>
                    </div>
                    <svg style="width:.75rem;height:.75rem;color:#9ca3af;transition:transform .2s;" :style="open ? 'transform:rotate(180deg)' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                {{-- Dropdown User --}}
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 translate-y-1"
                    x-transition:enter-end="opacity-100 translate-y-0"
                    x-cloak
                    style="position:absolute;right:0;top:calc(100% + .35rem);width:210px;z-index:9999;background:#fff;border-radius:1rem;box-shadow:0 16px 32px rgba(0,0,0,.1);border:1px solid #f3f4f6;overflow:hidden;"
                >
                    <div style="padding:.6rem 1rem .5rem;border-bottom:1px solid #f3f4f6;background:#f9fafb;border-radius:1rem 1rem 0 0;">
                        <p style="font-size:.72rem;font-weight:700;color:#111827;margin:0;">{{ filament()->auth()->user()?->nama_lengkap ?? 'Pengguna' }}</p>
                        <p style="font-size:.62rem;color:#6b7280;margin:0;word-break:break-all;">{{ filament()->auth()->user()?->email ?? 'admin@ksamara.com' }}</p>
                    </div>

                    <div style="padding:.35rem .4rem;">
                        <a href="{{ filament()->getUrl() }}" style="display:flex;align-items:center;gap:.5rem;padding:.45rem .6rem;border-radius:.6rem;font-size:.72rem;font-weight:600;color:#374151;text-decoration:none;transition:background .15s;" onmouseover="this.style.background='#ecfdf5';this.style.color='#047857';" onmouseout="this.style.background='transparent';this.style.color='#374151';">
                            <svg style="width:.9rem;height:.9rem;color:#059669;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            Dashboard
                        </a>

                        <hr style="margin:.25rem 0;border-color:#f3f4f6;">

                        <form method="POST" action="{{ filament()->getLogoutUrl() }}">
                            @csrf
                            <button type="submit" style="width:100%;display:flex;align-items:center;gap:.5rem;padding:.45rem .6rem;border-radius:.6rem;font-size:.72rem;font-weight:700;color:#dc2626;background:none;border:none;cursor:pointer;text-align:left;transition:background .15s;" onmouseover="this.style.background='#fef2f2';" onmouseout="this.style.background='transparent';">
                                <svg style="width:.9rem;height:.9rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>

        {{ FilamentView::renderHook(PanelsRenderHook::TOPBAR_END) }}
    </nav>

    <x-filament-actions::modals />
</div>

<style>
@keyframes ping {
    75%, 100% { transform: scale(2); opacity: 0; }
}
</style>
