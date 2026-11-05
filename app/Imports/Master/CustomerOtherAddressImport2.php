<?php

namespace App\Imports\Master;

use App\Entities\Master\CustomerOtherAddress;
use App\Services\MemberSyncService;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use DB;

class CustomerOtherAddressImport2 implements ToCollection, WithHeadingRow, WithStartRow, SkipsOnFailure, SkipsOnError
{
    use SkipsFailures, SkipsErrors;

    public $error;
    public $success;

    public function collection(Collection $rows)
    {
        $collect_error = [];
        $collect_success = [];
        $sync_payloads = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2;
                $id = trim((string) ($row['id'] ?? ''));

                if ($id === '') {
                    $collect_error[] = "Baris {$rowNumber}: id kosong.";
                    continue;
                }

                $member = CustomerOtherAddress::find($id);

                if (!$member) {
                    $collect_error[] = "Member dengan ID {$id} tidak ditemukan.";
                    continue;
                }

                // Hanya kolom editable yang diisi. Kosong = tidak diubah.
                // Heading 'sm/am' diformat Excel menjadi 'smam' (Str::slug).
                $data = [];
                $fieldMap = [
                    'sm'      => ['smam', 'sm_am'],
                    'ams'     => ['asm'],
                    'pic'     => ['pic'],
                    'officer' => ['officer'],
                ];

                foreach ($fieldMap as $column => $keys) {
                    foreach ($keys as $key) {
                        $value = $row[$key] ?? null;
                        if ($value !== null && trim((string) $value) !== '') {
                            $data[$column] = trim((string) $value);
                            break;
                        }
                    }
                }

                $isDefault = $this->parseDefault($row['is_default_member'] ?? null);
                if ($isDefault === false) {
                    $collect_error[] = "Baris {$rowNumber}: is_default_member harus 1 atau 0.";
                    continue;
                }
                // Nilai default yang sama dengan data lama tidak perlu ditulis.
                if ($isDefault !== null && (int) $member->member_default !== $isDefault) {
                    $data['member_default'] = $isDefault;
                }

                // Baris tanpa isian dan bukan default = tidak diubah.
                // is_default_member = 1 tetap diproses agar parent tersinkron.
                if (empty($data) && $isDefault !== 1) {
                    continue;
                }

                if (!empty($data)) {
                    $data['updated_at'] = now();
                    DB::table('master_customer_other_addresses')
                        ->where('id', $member->id)
                        ->update($data);
                }

                // Default member: samakan sm/asm/pic di master_customers.
                // Pakai nilai dari Excel; kalau kosong pakai nilai member saat ini
                // agar parent tetap ikut ter-update ketika is_default_member = 1.
                if ($isDefault === 1) {
                    $customerData = [];
                    $effective = [
                        'sm'  => $data['sm'] ?? $member->sm,
                        'ams' => $data['ams'] ?? $member->ams,
                    ];

                    if (!empty($effective['sm'])) {
                        $customerData['sm'] = $effective['sm'];
                    }
                    if (!empty($effective['ams'])) {
                        $customerData['asm'] = $effective['ams'];
                    }
                    if (!empty($data['pic'])) {
                        $customerData['pic'] = $data['pic'];
                    } elseif (!empty($member->pic)) {
                        $customerData['pic'] = $member->pic;
                    }

                    if (!empty($customerData)) {
                        $customerData['updated_at'] = now();
                        DB::table('master_customers')
                            ->where('id', $member->customer_id)
                            ->update($customerData);
                    }
                }

                $payload = [
                    'member_id'   => (string) $member->id,
                    'customer_id' => (int) $member->customer_id,
                ];
                foreach (['sm' => 'sm', 'ams' => 'ams', 'pic' => 'pic', 'officer' => 'officer'] as $column) {
                    if (isset($data[$column])) {
                        $payload[$column] = $data[$column];
                    }
                }
                if ($isDefault !== null) {
                    $payload['is_default_member'] = $isDefault;
                }
                $sync_payloads[] = $payload;

                $collect_success[] = $member->name;
            }

            if (empty($collect_success)) {
                $collect_success[] = 'Tidak ada data yang diubah.';
            }

            if (empty($collect_error)) {
                $collect_error[] = 'No failed import.';
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating collection: ' . $e->getMessage());
            $this->error = [$e->getMessage()];
            return;
        }

        // Push ke CRM setelah data lokal tersimpan (best-effort).
        foreach ($sync_payloads as $payload) {
            MemberSyncService::push($payload);
        }

        $this->error = $collect_error;
        $this->success = $collect_success;
    }

    /**
     * @return int|null|false null = kolom kosong (tidak diubah), false = nilai tidak valid
     */
    private function parseDefault($value)
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $value = strtolower(trim((string) $value));

        if (in_array($value, ['1', 'yes', 'y', 'true'], true)) {
            return 1;
        }

        if (in_array($value, ['0', 'no', 'n', 'false'], true)) {
            return 0;
        }

        return false;
    }

    public function startRow(): int
    {
        return 2;
    }
}
