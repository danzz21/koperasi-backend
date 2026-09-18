<x-filament-panels::page>
    <div class="ks-report">
        <div class="ks-report-header">
            <div>
                <h1>Laporan &amp; Analisis</h1>
                <p>Ringkasan keuangan koperasi tahun {{ $year }}</p>
            </div>
            <select class="ks-report-select"><option>{{ $year }}</option></select>
        </div>
        <div class="ks-report-grid">
            <div class="ks-report-card ks-report-emerald"><span>Neraca Simpanan</span><strong>Rp {{ number_format($simpanan, 0, ',', '.') }}</strong><small>Total saldo aktif anggota</small></div>
            <div class="ks-report-card ks-report-blue"><span>Pembiayaan Berjalan</span><strong>Rp {{ number_format($pembiayaan, 0, ',', '.') }}</strong><small>Sisa kewajiban anggota</small></div>
            <div class="ks-report-card ks-report-purple"><span>Pemasukan</span><strong>Rp {{ number_format($pemasukan, 0, ',', '.') }}</strong><small>Transaksi kredit tahun berjalan</small></div>
            <div class="ks-report-card ks-report-amber"><span>Laba Bersih</span><strong>Rp {{ number_format($pemasukan - $pengeluaran, 0, ',', '.') }}</strong><small>Pemasukan dikurangi pengeluaran</small></div>
        </div>
        <div class="ks-report-chart">
            <h2>Perkembangan Keuangan Bulanan</h2>
            <div class="ks-report-bars">
                @foreach ($monthly as $month)
                    @php($max = max(1, collect($monthly)->max(fn ($item) => max($item['pemasukan'], $item['pengeluaran']))))
                    <div class="ks-report-month">
                        <div class="ks-report-bar-wrap">
                            <i style="height: {{ ($month['pemasukan'] / $max) * 100 }}%"></i>
                            <b style="height: {{ ($month['pengeluaran'] / $max) * 100 }}%"></b>
                        </div>
                        <small>{{ $month['label'] }}</small>
                    </div>
                @endforeach
            </div>
            <div class="ks-report-legend"><span class="ks-report-in"></span> Pemasukan <span class="ks-report-out"></span> Pengeluaran</div>
        </div>
    </div>
</x-filament-panels::page>
