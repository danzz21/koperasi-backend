<?php

use App\Models\Cicilan;
use App\Support\MemberNotifier;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('koperasi:remind-installments', function () {
    $installments = Cicilan::query()
        ->with('anggota')
        ->whereIn('status', ['belumbayar', 'denda'])
        ->whereDate('tgl_tempo', '<=', now()->addDays(3)->toDateString())
        ->get();

    foreach ($installments as $installment) {
        $stage = $installment->tgl_tempo->lt(now()->startOfDay()) ? 'overdue' : 'due_soon';
        $alreadySent = DB::table('notifications')
            ->where('notifiable_type', get_class($installment->anggota))
            ->where('notifiable_id', $installment->id_anggota)
            ->where('type', 'cicilan.reminder')
            ->where('data->installment_id', $installment->id)
            ->where('data->reminder_stage', $stage)
            ->exists();

        if ($alreadySent) {
            continue;
        }

        MemberNotifier::send(
            $installment->anggota,
            $stage === 'overdue' ? 'Cicilan terlambat' : 'Pengingat jatuh tempo cicilan',
            $stage === 'overdue'
                ? "Cicilan ke-{$installment->cicilan_ke} sudah melewati jatuh tempo."
                : "Cicilan ke-{$installment->cicilan_ke} jatuh tempo pada {$installment->tgl_tempo->format('d/m/Y')}.",
            'cicilan.reminder',
            '/anggota/cicilan',
            ['installment_id' => $installment->id, 'reminder_stage' => $stage],
        );
    }

    $this->info("Pengingat diproses untuk {$installments->count()} cicilan.");
})->purpose('Kirim pengingat jatuh tempo cicilan');

Schedule::command('koperasi:remind-installments')->dailyAt('08:00');
