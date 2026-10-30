<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Pinjaman;
use App\Models\Simpanan;
use App\Models\User;
use App\Support\AuditLogger;

class AdminApprovalService
{
    public function approveMember(User $member): bool
    {
        $approved = PendingApprovalActions::approve(PendingApprovalActions::ANGGOTA, $member);
        if ($approved) {
            AuditLogger::log('anggota.verify', $member, ['status' => 'pending'], ['status' => 'aktif']);
        }

        return $approved;
    }

    public function rejectMember(User $member): bool
    {
        $rejected = PendingApprovalActions::reject(PendingApprovalActions::ANGGOTA, $member);
        if ($rejected) {
            AuditLogger::log('anggota.reject', $member, ['status' => 'pending'], ['status' => 'nonaktif']);
        }

        return $rejected;
    }

    public function approveLoan(Pinjaman $loan, string $jenis): bool
    {
        $oldStatus = $loan->status;
        $approved = PendingApprovalActions::approve(PendingApprovalActions::PINJAMAN, $loan);
        if ($approved) {
            AuditLogger::log('pinjaman.approve', $loan, ['status' => $oldStatus, 'jenis' => $jenis], ['status' => 'disetujui']);
        }

        return $approved;
    }

    public function rejectLoan(Pinjaman $loan, string $jenis): bool
    {
        $oldStatus = $loan->status;
        $rejected = PendingApprovalActions::reject(PendingApprovalActions::PINJAMAN, $loan);
        if ($rejected) {
            AuditLogger::log('pinjaman.reject', $loan, ['status' => $oldStatus, 'jenis' => $jenis], ['status' => 'ditolak']);
        }

        return $rejected;
    }

    public function disburseLoan(Pinjaman $loan): bool
    {
        $disbursed = PendingApprovalActions::disbursePinjaman($loan);
        if ($disbursed) {
            AuditLogger::log('pinjaman.disburse', $loan, ['status' => 'disetujui'], ['status' => 'aktif']);
        }

        return $disbursed;
    }

    public function approveSavings(Simpanan $saving, string $jenis): bool
    {
        $approved = PendingApprovalActions::approve($jenis, $saving);
        if ($approved) {
            AuditLogger::log('simpanan.approve', $saving, ['status' => 'tidak_aktif', 'jenis' => $jenis], ['status' => 'aktif']);
        }

        return $approved;
    }

    public function rejectSavings(Simpanan $saving, string $jenis): bool
    {
        $rejected = PendingApprovalActions::reject($jenis, $saving);
        if ($rejected) {
            AuditLogger::log('simpanan.reject', $saving, ['status' => 'tidak_aktif', 'jenis' => $jenis], ['status' => 'ditolak']);
        }

        return $rejected;
    }

    public function approvePayment(Pembayaran $payment): bool
    {
        $approved = PendingApprovalActions::approve(PendingApprovalActions::PEMBAYARAN, $payment);
        if ($approved) {
            AuditLogger::log('pembayaran.verifikasi', $payment, ['status' => 'pending'], ['status' => 'diverifikasi']);
        }

        return $approved;
    }

    public function rejectPayment(Pembayaran $payment): bool
    {
        $rejected = PendingApprovalActions::reject(PendingApprovalActions::PEMBAYARAN, $payment);
        if ($rejected) {
            AuditLogger::log('pembayaran.tolak', $payment, ['status' => 'pending'], ['status' => 'gagal']);
        }

        return $rejected;
    }
}
