<?php

namespace App\Http\Controllers\Superuser\Utility;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Mail\CustomMail;
use Illuminate\Support\Facades\Auth;
use App\Entities\Account\User;
use Config;

class MailController extends Controller
{
    public function index()
    {
        return view('superuser.utility.emails.index');
    }

    public function create()
    {
        return view('superuser.utility.emails.form');
    }

    public function sendEmail(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $user = User::find(Auth::id());

        // dd($user);

        if (!$user || !$user->smtp_host) {
            return back()->with('error', 'Akun email belum dikonfigurasi.');
        }

        // Set konfigurasi SMTP secara dinamis
        Config::set('mail.mailers.smtp', [
            'transport' => 'smtp',
            'host' => $user->smtp_host,
            'port' => $user->smtp_port,
            'username' => $user->smtp_username,
            'password' => $user->smtp_password,
            'encryption' => $user->smtp_encryption,
        ]);

        try {
            Mail::to($request->to)->send(new CustomMail($request->subject, $request->message));
            return redirect()->route('superuser.utility.settings.emails.create')->with('success', 'Email berhasil dikirim!');
        } catch (\Exception $e) {
            dd($e);
            return back()->with('error', 'Gagal mengirim email: ' . $e->getMessage());
        }
    }
}