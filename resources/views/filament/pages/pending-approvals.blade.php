<x-filament-panels::page>
    {{-- Halaman "Persetujuan Pending": dipakai bersama oleh semua kategori
         (anggota, simpanan sukarela/pokok, pinjaman, pembayaran). --}}
    <div class="rounded-xl border border-gray-200 bg-white p-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="grid h-10 w-10 place-items-center rounded-lg bg-amber-100 text-lg" aria-hidden="true">
                🔔
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-bold text-gray-800">Persetujuan Pending</p>
                <p class="text-xs text-gray-500">
                    Setujui atau tolak pengajuan di bawah. Data yang sudah diproses
                    otomatis hilang dari daftar ini dan jumlah pada kartu dashboard ikut turun.
                </p>
            </div>
            <a
                href="{{ \App\Filament\Pages\Dashboard::getUrl() }}"
                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-xs font-semibold text-gray-700 transition hover:bg-gray-50"
            >
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                </svg>
                Kembali ke Dashboard
            </a>
        </div>
    </div>

    {{ $this->table }}
</x-filament-panels::page>
