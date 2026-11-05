<?php

namespace App\Console\Commands;

use App\Entities\Master\Customer;
use App\Entities\Master\CustomerOtherAddress;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ReparentHeavenScent extends Command
{
    protected $signature = 'customer:sync-heaven-scent
                            {--fix : Terapkan perubahan (tanpa flag ini hanya pratinjau)}';

    protected $description = 'Pindahkan, rename, dan renumber member Heaven Scent C/S sekaligus sinkronkan referensi transaksi';

    // Member yang tetap di parent lama
    private $fixed = [
        '92.1' => ['parent' => 92, 'name' => 'Heaven Scent (C)', 'default' => true, 'allowed' => ['Heaven Scent 1', 'Heaven Scent (C)']],
        '92.3' => ['parent' => 92, 'name' => 'Heaven Scent (C SATELIT)', 'default' => false, 'allowed' => ['Heaven Scent 2', 'Heaven Scent (C SATELIT)']],
    ];

    // Member yang pindah + renumber
    private $moved = [
        '92.5' => ['id' => '180.1', 'parent' => 180, 'name' => 'Heaven Scent (S)', 'default' => true, 'allowed' => ['Heaven Scent 5', 'Heaven Scent (S)']],
        '92.2' => ['id' => '180.2', 'parent' => 180, 'name' => 'Heaven Scent (B)', 'default' => false, 'allowed' => ['Heaven Scent 4', 'Heaven Scent (B)']],
        '92.4' => ['id' => '180.3', 'parent' => 180, 'name' => 'Heaven Scent (G)', 'default' => false, 'allowed' => ['Heaven Scent 3', 'Heaven Scent (G)']],
    ];

    private $tables = ['penjualan_so', 'penjualan_do', 'finance_invoicing'];

    public function handle()
    {
        foreach ([92 => 'Heaven Scent (C)', 180 => 'Heaven Scent (S)'] as $id => $name) {
            if (!Customer::where('id', $id)->where('name', $name)->exists()) {
                $this->error("Parent {$id} ({$name}) tidak ditemukan.");
                return 1;
            }
        }

        foreach ($this->fixed as $id => $target) {
            if (!$this->validateMember($id, $target['allowed'])) {
                return 1;
            }
        }

        $columns = DB::select(
            "SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = ? AND COLUMN_NAME IN ('customer_other_address_id', 'member_id', 'other_address_id', 'member_id_hasil')
             AND TABLE_NAME <> 'master_customer_other_addresses'",
            [DB::getDatabaseName()]
        );

        $moves = [];
        $replacements = [];

        foreach ($this->moved as $oldId => $target) {
            $old = CustomerOtherAddress::find($oldId);
            $new = CustomerOtherAddress::find($target['id']);

            if ($old && $new) {
                if ($target['id'] !== '180.1' || $new->name !== 'Heaven Scent (S)') {
                    $this->error("ID {$target['id']} sudah dipakai ({$new->name}), sedangkan {$oldId} juga ada. Renumber dibatalkan.");
                    return 1;
                }

                $refs = $this->referencesTo('180.1', $columns);

                if (!empty($refs)) {
                    $this->error('Baris 180.1 (Heaven Scent (S)) masih dipakai transaksi: ' . json_encode($refs));
                    $this->line('Pilih dulu: pertahankan 180.1 sebagai member S, atau pakai ID baru untuk Heaven Scent 5.');
                    return 1;
                }

                $replacements[] = '180.1';
            } elseif (!$old && $new) {
                if ($new->name !== $target['name'] || (string) $new->customer_id !== (string) $target['parent']) {
                    $this->error("ID {$target['id']} sudah dipakai member lain: {$new->name} (parent {$new->customer_id}). Renumber dibatalkan.");
                    return 1;
                }
            } elseif (!$old && !$new) {
                $this->error("Member {$oldId} maupun {$target['id']} tidak ditemukan.");
                return 1;
            }

            if ($old) {
                if (!$this->validateMember($oldId, $target['allowed'])) {
                    return 1;
                }

                $moves[$oldId] = $target;
            }
        }

        $remapRefs = [];

        foreach ($columns as $col) {
            $count = DB::table($col->TABLE_NAME)
                ->whereIn($col->COLUMN_NAME, array_keys($moves))
                ->count();

            if ($count > 0) {
                $remapRefs["{$col->TABLE_NAME}.{$col->COLUMN_NAME}"] = $count;
            }
        }

        $finals = $this->fixed;
        foreach ($this->moved as $target) {
            $finals[$target['id']] = $target;
        }

        $rows = [];
        $syncCount = array_fill_keys($this->tables, 0) + ['report_type_brand' => 0];

        foreach ($this->fixed as $id => $target) {
            $rows[] = $this->previewRow(null, $id, $target, $syncCount);
        }

        foreach ($this->moved as $oldId => $target) {
            $rows[] = $this->previewRow($oldId, $target['id'], $target, $syncCount, !isset($moves[$oldId]));
        }

        $this->table(['ID Lama', 'ID Baru', 'Nama Baru', 'Parent', 'Default', 'SO', 'DO', 'Invoice'], $rows);

        $this->line(empty($replacements)
            ? 'Baris lama 180.1 tidak ada / sudah aman.'
            : 'Baris 180.1 lama (Heaven Scent (S)) akan dihapus permanen karena tidak punya transaksi.');
        $this->line('Referensi yang diremap ke ID baru: ' . json_encode($remapRefs));
        $this->line('Baris customer_id yang disinkronkan: ' . json_encode($syncCount));

        if (!$this->option('fix')) {
            $this->warn('Pratinjau saja. Jalankan ulang dengan --fix untuk menerapkan.');
            return 0;
        }

        DB::beginTransaction();

        try {
            foreach ($replacements as $replacementId) {
                DB::table('master_customer_other_addresses')->where('id', $replacementId)->delete();
            }

            $renumbered = [];

            foreach ($moves as $oldId => $target) {
                DB::table('master_customer_other_addresses')->where('id', $oldId)->update(['id' => $target['id']]);
                $renumbered[$oldId] = $target['id'];
            }

            foreach ($finals as $id => $target) {
                DB::table('master_customer_other_addresses')->where('id', $id)->update([
                    'customer_id' => $target['parent'],
                    'name' => $target['name'],
                    'status' => CustomerOtherAddress::STATUS['ACTIVE'],
                ]);
            }

            foreach ($columns as $col) {
                foreach ($renumbered as $oldId => $newId) {
                    DB::table($col->TABLE_NAME)
                        ->where($col->COLUMN_NAME, $oldId)
                        ->update([$col->COLUMN_NAME => $newId]);
                }
            }

            foreach ([92 => '92.1', 180 => '180.1'] as $parentId => $defaultId) {
                CustomerOtherAddress::where('customer_id', $parentId)->update(['member_default' => 0]);
                DB::table('master_customer_other_addresses')->where('id', $defaultId)->update(['member_default' => 1]);
            }

            $maxSuffix = 0;
            foreach (DB::table('master_customer_other_addresses')->where('customer_id', 180)->pluck('id') as $id) {
                $maxSuffix = max($maxSuffix, (int) substr($id, strpos($id, '.') + 1));
            }
            DB::table('master_customers')->where('id', 180)->update(['count_member' => $maxSuffix]);

            foreach ($this->tables as $table) {
                foreach ($finals as $id => $target) {
                    DB::table($table)
                        ->where('customer_other_address_id', $id)
                        ->where('customer_id', '<>', $target['parent'])
                        ->update(['customer_id' => $target['parent'], 'updated_at' => now()]);
                }
            }

            foreach ($finals as $id => $target) {
                DB::table('report_customer_type_brand')
                    ->where('other_address_id', $id)
                    ->where('customer_id', '<>', $target['parent'])
                    ->update(['customer_id' => $target['parent']]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Gagal: ' . $e->getMessage());
            return 1;
        }

        $final = CustomerOtherAddress::whereIn('customer_id', [92, 180])
            ->orderBy('customer_id')->orderBy('id')
            ->get(['id', 'customer_id', 'name', 'member_default', 'status']);

        $this->table(['ID', 'Parent', 'Nama', 'Default', 'Status'], $final->map(function ($m) {
            return [$m->id, $m->customer_id, $m->name, $m->member_default, $m->status];
        })->all());

        $this->info('Selesai. Perpindahan, rename, renumber, dan sinkronisasi referensi sudah diterapkan.');

        return 0;
    }

    private function validateMember($id, array $allowed)
    {
        $member = CustomerOtherAddress::find($id);

        if (!$member) {
            $this->error("Member {$id} tidak ditemukan.");
            return false;
        }

        if (!in_array($member->name, $allowed, true)) {
            $this->error("Member {$id} bernama '{$member->name}', tidak sesuai (diharapkan: " . implode(' / ', $allowed) . ').');
            return false;
        }

        return true;
    }

    private function referencesTo($id, array $columns)
    {
        $refs = [];

        foreach ($columns as $col) {
            $count = DB::table($col->TABLE_NAME)->where($col->COLUMN_NAME, $id)->count();

            if ($count > 0) {
                $refs["{$col->TABLE_NAME}.{$col->COLUMN_NAME}"] = $count;
            }
        }

        return $refs;
    }

    private function previewRow($movedFrom, $finalId, $target, array &$syncCount, $applied = false)
    {
        $currentId = ($movedFrom && !$applied) ? $movedFrom : $finalId;
        $counts = [];

        foreach ($this->tables as $table) {
            $count = DB::table($table)
                ->where('customer_other_address_id', $currentId)
                ->where('customer_id', '<>', $target['parent'])
                ->count();

            $counts[] = $count;
            $syncCount[$table] += $count;
        }

        $syncCount['report_type_brand'] += DB::table('report_customer_type_brand')
            ->where('other_address_id', $currentId)
            ->where('customer_id', '<>', $target['parent'])
            ->count();

        return array_merge([
            $movedFrom ? ($applied ? $movedFrom . ' (sudah)' : $movedFrom) : '-',
            $finalId,
            $target['name'],
            $target['parent'],
            $target['default'] ? 'YES' : 'NO',
        ], $counts);
    }
}
