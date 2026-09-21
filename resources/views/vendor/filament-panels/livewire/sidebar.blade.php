@php
    $isSidebarCollapsibleOnDesktop = filament()->isSidebarCollapsibleOnDesktop();
    $isSidebarFullyCollapsibleOnDesktop = filament()->isSidebarFullyCollapsibleOnDesktop();
    $currentUrl = request()->url();
    $currentPath = request()->path();

    if (! function_exists('ksIsActive')) {
    function ksIsActive(array $paths): bool {
        $current = request()->path();
        foreach ($paths as $p) {
            if ($current === ltrim($p, '/') || str_starts_with($current, ltrim($p, '/') . '/')) {
                return true;
            }
        }
        return false;
    }
    }

    $panelId      = filament()->getCurrentPanel()?->getId();
    $isSuperadmin = $panelId === 'superadmin';

    $authUser = filament()->auth()->user();
    $authName = $authUser?->nama_lengkap ?: ($authUser?->username ?? 'Pengguna');
    $authRole = $authUser?->role === 'superadmin' ? 'Superadmin' : 'Administrator';

    $navLabels = $isSuperadmin
        ? ['utama' => 'Menu Utama', 'sistem' => 'Keamanan & Audit', 'digital' => 'Monitoring']
        : ['utama' => 'Menu Utama', 'sistem' => 'Laporan & Sistem', 'digital' => 'Layanan Digital'];

    $navAdmin = [
        'utama' => [
            ['label'=>'Dashboard Utama','icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6','url'=>'/admin'],
            ['label'=>'Manajemen Anggota','icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z','url'=>'/admin/users'],
            ['label'=>'Manajemen Simpanan','icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z','url'=>'/admin/simpanans'],
            ['label'=>'Manajemen Pinjaman','icon'=>'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z','url'=>'/admin/pinjamen'],
            ['label'=>'Manajemen Angsuran','icon'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z','url'=>'/admin/cicilans'],
            ['label'=>'Pembayaran','icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z','url'=>'/admin/pembayarans'],
        ],
        'sistem' => [
            ['label'=>'Laporan & Analisis','icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z','url'=>'/admin'],
            ['label'=>'Pengaturan','icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z','url'=>'/admin'],
        ],
        'digital' => [
            ['label'=>'Manajemen PPOB','icon'=>'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z','url'=>'/admin/ppob-transaksis'],
            ['label'=>'Payment Gateway','icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4','url'=>'/admin'],
        ],
    ];

    // ── Menu khusus panel Superadmin ────────────────────────────────────────
    $navSuperadmin = [
        'utama' => [
            ['label'=>'Dashboard Superadmin','icon'=>'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z','url'=>'/superadmin/superadmin-dashboard'],
            ['label'=>'Kelola User','icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z','url'=>'/superadmin/users'],
            ['label'=>'Konfigurasi Sistem','icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z','url'=>'/superadmin/system-configs'],
        ],
        'sistem' => [
            ['label'=>'Laporan Keuangan','icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z','url'=>'/superadmin/laporan-keuangan'],
            ['label'=>'Log Aktivitas','icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4','url'=>'/superadmin/audit-logs'],
        ],
        'digital' => [
            ['label'=>'Semua Transaksi','icon'=>'M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4','url'=>'/superadmin/monitor-transaksi'],
        ],
    ];

    $nav = $isSuperadmin ? $navSuperadmin : $navAdmin;
@endphp

<div>
    <div
        x-data="{}"
        @if ($isSidebarCollapsibleOnDesktop || $isSidebarFullyCollapsibleOnDesktop)
            x-cloak
        @else
            x-cloak="-lg"
        @endif
        x-bind:class="{ 'fi-sidebar-open': $store.sidebar.isOpen }"
        id="fi-main-sidebar"
        class="fi-sidebar fi-main-sidebar"
        style="background:linear-gradient(180deg,#064e3b 0%,#065f46 52%,#134e4a 100%) !important;border-right:1px solid rgba(16,185,129,.35);box-shadow:8px 0 24px rgba(6,78,59,.15);display:flex;flex-direction:column;justify-content:space-between;min-height:100vh;"
    >
        {{-- SIDEBAR HEADER / LOGO --}}
        <div>
            <div style="padding:1rem 1.25rem;border-bottom:1px solid rgba(167,243,208,.18);">
                <div style="display:flex;align-items:center;gap:.6rem;">
                    <div style="width:36px;height:36px;border-radius:.6rem;background:#047857;color:#fbbf24;display:flex;align-items:center;justify-content:center;font-weight:900;font-size:.85rem;border:2px solid rgba(251,191,36,.4);flex-shrink:0;">KS</div>
                    <div>
                        <p style="margin:0;font-size:.85rem;font-weight:800;color:#fff;line-height:1.1;">K-Samara</p>
                        <p style="margin:0;font-size:.6rem;color:rgba(167,243,208,.8);">{{ $isSuperadmin ? 'Panel Superadmin' : 'Koperasi Syariah' }}</p>
                    </div>
                </div>
            </div>

            {{-- NAVIGATION --}}
            <nav style="padding:.75rem .625rem;">

                {{-- Menu Utama --}}
                <p style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:rgba(167,243,208,.75);padding:.25rem .5rem .5rem;margin:0;">{{ $navLabels['utama'] }}</p>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:2px;">
                    @foreach($nav['utama'] as $item)
                    @php $active = ksIsActive([$item['url']]); @endphp
                    <li style="position:relative;">
                        @if($active)
                        <div style="position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:1.5rem;background:linear-gradient(180deg,#fbbf24,#f59e0b);border-radius:0 4px 4px 0;box-shadow:0 0 8px rgba(251,191,36,.6);"></div>
                        @endif
                        <a href="{{ $item['url'] }}" style="display:flex;align-items:center;gap:.65rem;padding:.5rem .65rem .5rem .75rem;border-radius:.65rem;text-decoration:none;font-size:.78rem;font-weight:{{ $active ? '700' : '500' }};color:{{ $active ? '#fff' : 'rgba(236,253,245,.75)' }};background:{{ $active ? 'linear-gradient(90deg,rgba(5,150,105,.95),rgba(5,150,105,.35))' : 'transparent' }};transition:all .2s;"
                            onmouseover="if(!{{ $active ? 'true' : 'false' }})this.style.background='rgba(16,185,129,.25)';this.style.color='#fff';"
                            onmouseout="if(!{{ $active ? 'true' : 'false' }})this.style.background='transparent';@if(!$active)this.style.color='rgba(236,253,245,.75)';@endif">
                            <svg style="width:1rem;height:1rem;flex-shrink:0;color:{{ $active ? '#fbbf24' : 'rgba(167,243,208,.7)' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                            </svg>
                            <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $item['label'] }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>

                {{-- Laporan & Sistem --}}
                <p style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:rgba(167,243,208,.75);padding:1rem .5rem .5rem;margin:0;">{{ $navLabels['sistem'] }}</p>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:2px;">
                    @foreach($nav['sistem'] as $item)
                    @php $active = ksIsActive([$item['url']]); @endphp
                    <li>
                        <a href="{{ $item['url'] }}" style="display:flex;align-items:center;gap:.65rem;padding:.5rem .65rem;border-radius:.65rem;text-decoration:none;font-size:.78rem;font-weight:{{ $active ? '700' : '500' }};color:{{ $active ? '#fff' : 'rgba(236,253,245,.75)' }};background:{{ $active ? 'linear-gradient(90deg,rgba(5,150,105,.95),rgba(5,150,105,.35))' : 'transparent' }};transition:all .2s;"
                            onmouseover="this.style.background='rgba(16,185,129,.25)';this.style.color='#fff';"
                            onmouseout="if(!{{ $active ? 'true' : 'false' }})this.style.background='transparent';@if(!$active)this.style.color='rgba(236,253,245,.75)';@endif">
                            <svg style="width:1rem;height:1rem;flex-shrink:0;color:{{ $active ? '#fbbf24' : 'rgba(167,243,208,.7)' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                            </svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>

                {{-- Layanan Digital --}}
                <p style="font-size:.6rem;font-weight:800;text-transform:uppercase;letter-spacing:.1em;color:rgba(167,243,208,.75);padding:1rem .5rem .5rem;margin:0;">{{ $navLabels['digital'] }}</p>
                <ul style="list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:2px;">
                    @foreach($nav['digital'] as $item)
                    @php $active = ksIsActive([$item['url']]); @endphp
                    <li>
                        <a href="{{ $item['url'] }}" style="display:flex;align-items:center;gap:.65rem;padding:.5rem .65rem;border-radius:.65rem;text-decoration:none;font-size:.78rem;font-weight:{{ $active ? '700' : '500' }};color:{{ $active ? '#fff' : 'rgba(236,253,245,.75)' }};background:{{ $active ? 'linear-gradient(90deg,rgba(5,150,105,.95),rgba(5,150,105,.35))' : 'transparent' }};transition:all .2s;"
                            onmouseover="this.style.background='rgba(16,185,129,.25)';this.style.color='#fff';"
                            onmouseout="if(!{{ $active ? 'true' : 'false' }})this.style.background='transparent';@if(!$active)this.style.color='rgba(236,253,245,.75)';@endif">
                            <svg style="width:1rem;height:1rem;flex-shrink:0;color:{{ $active ? '#fbbf24' : 'rgba(167,243,208,.7)' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                            </svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                    @endforeach
                </ul>

            </nav>
        </div>

        {{-- BOTTOM USER CARD --}}
        <div style="margin:.75rem;padding:.75rem;border-radius:.75rem;background:rgba(6,78,59,.4);border:1px solid rgba(16,185,129,.2);">
            <div style="display:flex;align-items:center;gap:.6rem;">
                <div style="width:32px;height:32px;border-radius:9999px;background:#047857;border:2px solid #fbbf24;display:flex;align-items:center;justify-content:center;color:#fbbf24;font-weight:800;font-size:.7rem;flex-shrink:0;">
                    {{ strtoupper(mb_substr($authName, 0, 1)) }}
                </div>
                <div style="flex:1;min-width:0;">
                    <p style="font-size:.72rem;font-weight:700;color:#fff;margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $authName }}</p>
                    <p style="font-size:.62rem;color:rgba(167,243,208,.8);margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $authRole }} &middot; {{ $authUser?->email }}</p>
                </div>
            </div>
        </div>

    </div>

    <x-filament-actions::modals />
</div>
