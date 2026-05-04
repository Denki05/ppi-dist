<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsAndInvitationsTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Tabel Master Event (Sangat bersih, hanya menyimpan Rules/Tanggal)
        Schema::create('master_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->date('event_start_date');
            $table->date('event_end_date');
            $table->date('invitation_start_date');
            $table->date('invitation_end_date');
            $table->boolean('is_global')->default(true); // Penanda Flow 3: Berlaku untuk semua customer
            $table->string('status')->default('active'); // active / inactive
            $table->timestamps();
        });

        // 2. Tabel Log Pengiriman Undangan (Hanya menyimpan yang SUDAH dikirim)
        Schema::create('master_event_invitations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('event_id')->nullable();
            
            $table->unsignedBigInteger('customer_id'); 
            $table->enum('customer_type', ['E', 'P']); // E = Existing, P = Prospek
            $table->string('officer'); // Siapa yang mengirim (berdasarkan data customer)
            
            // Kita tidak butuh kolom status 'pending', karena masuk ke tabel ini artinya sudah 'sent'
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamps();
            
            // Mencegah 1 customer dikirimi 2 undangan untuk event yang sama (Mencegah duplikat)
            $table->unique(['event_id', 'customer_id', 'customer_type'], 'unique_invitation');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('master_event_invitations');
        Schema::dropIfExists('master_events');
    }
}
