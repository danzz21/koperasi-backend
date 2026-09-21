<?php

namespace App\Filament\Resources\Superadmin\SystemConfigs\Concerns;

trait DecodesJsonValue
{
    /**
     * Kolom `value` bertipe JSON. Bila user mengetik JSON yang valid,
     * simpan sebagai array/scalar; selain itu simpan sebagai teks apa adanya.
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $raw = $data['value'] ?? null;

        if (is_string($raw)) {
            $decoded = json_decode($raw, true);

            $data['value'] = (json_last_error() === JSON_ERROR_NONE && $decoded !== null)
                ? $decoded
                : $raw;
        }

        return $data;
    }
}
