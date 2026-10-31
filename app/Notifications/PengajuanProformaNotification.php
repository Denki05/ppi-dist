<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Pengajuan proforma baru dari modul AO (estimate dari menu Prospek yang
 * dilanjutkan). Dikirim sekali saat pengajuan pertama kali masuk, bukan pada
 * setiap kiriman ulang selama statusnya masih menunggu.
 */
class PengajuanProformaNotification extends Notification
{
    use Queueable;

    public $pengajuan;

    public function __construct($pengajuan)
    {
        $this->pengajuan = $pengajuan;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $p = $this->pengajuan;

        return [
            'id' => $p->id,
            'code' => $p->estimate_number,
            'customer' => $p->prospect_name,
            'customer_kota' => $p->city,
            'type_transaction' => $p->termin,
            'prospect_name' => $p->prospect_name,
            'perusahaan' => $p->perusahaan,
            'ao_pic' => $p->ao_pic,
            'grand_total' => $p->grand_total,
            'priority' => 'action',
            'created_at' => $p->submitted_at ?: $p->created_at,
        ];
    }
}
