<?php

namespace App\Services;

use Throwable;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use App\Entities\Account\Superuser;
use App\Notifications\ErrorMonitorNotification;

class ErrorMonitorService
{
    const NOTIFY_COOLDOWN_MINUTES = 10;
    const MAX_TRACE_LENGTH = 8000;

    /**
     * Catat error live ke dev_error_events dan kirim notifikasi ke Developer.
     * Tidak pernah melempar exception supaya tidak mengganggu request asli.
     */
    public function record(Throwable $e, $request = null)
    {
        if ($this->shouldIgnore($e)) {
            return;
        }

        try {
            $fingerprint = $this->fingerprint($e, $request);
            $now = now();
            $existing = DB::table('dev_error_events')
                ->where('fingerprint', $fingerprint)
                ->where('is_resolved', 0)
                ->orderBy('id', 'desc')
                ->first();

            if ($existing) {
                DB::table('dev_error_events')->where('id', $existing->id)->update([
                    'occurrences' => (int) $existing->occurrences + 1,
                    'last_seen_at' => $now,
                    'message' => $this->shortMessage($e),
                    'url' => $this->url($request),
                    'updated_at' => $now,
                ]);
                $eventId = $existing->id;
                $occurrences = (int) $existing->occurrences + 1;
            } else {
                $eventId = DB::table('dev_error_events')->insertGetId([
                    'fingerprint' => $fingerprint,
                    'exception_class' => get_class($e),
                    'message' => $this->shortMessage($e),
                    'file' => $this->shortPath($e->getFile()),
                    'line' => $e->getLine(),
                    'method' => $request ? $request->method() : null,
                    'route' => $request && $request->route() ? $request->route()->getName() : null,
                    'url' => $this->url($request),
                    'user_label' => $request && $request->user() ? ($request->user()->name ?? $request->user()->username ?? null) : null,
                    'user_type' => $request && $request->user() ? get_class($request->user()) : null,
                    'user_id' => $request && $request->user() ? $request->user()->id : null,
                    'input_keys' => $this->inputKeys($request),
                    'trace' => mb_substr($e->getTraceAsString(), 0, self::MAX_TRACE_LENGTH),
                    'occurrences' => 1,
                    'first_seen_at' => $now,
                    'last_seen_at' => $now,
                    'is_resolved' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
                $occurrences = 1;
            }

            $this->notify($fingerprint, [
                'event_id' => $eventId,
                'exception_class' => get_class($e),
                'message' => $this->shortMessage($e),
                'file' => $this->shortPath($e->getFile()),
                'line' => $e->getLine(),
                'url' => $this->url($request),
                'occurrences' => $occurrences,
                'last_seen_at' => $now->toDateTimeString(),
            ]);
        } catch (Throwable $inner) {
            // Diamkan: kegagalan monitor tidak boleh memicu error baru.
        }
    }

    protected function shouldIgnore(Throwable $e)
    {
        if ($e instanceof ValidationException
            || $e instanceof AuthenticationException
            || $e instanceof AuthorizationException
            || $e instanceof ModelNotFoundException
            || $e instanceof TokenMismatchException
        ) {
            return true;
        }

        if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface) {
            return $e->getStatusCode() < 500;
        }

        return false;
    }

    protected function fingerprint(Throwable $e, $request)
    {
        $route = $request && $request->route() ? $request->route()->getName() : null;
        $path = $request ? $request->method() . ' ' . $request->path() : 'cli';

        return sha1(get_class($e) . '|' . $e->getFile() . '|' . $e->getLine() . '|' . $route . '|' . $path);
    }

    protected function shortMessage(Throwable $e)
    {
        return mb_substr($e->getMessage(), 0, 2000);
    }

    protected function shortPath($file)
    {
        return str_replace(base_path() . DIRECTORY_SEPARATOR, '', (string) $file);
    }

    protected function url($request)
    {
        if (!$request) {
            return null;
        }

        return mb_substr($request->method() . ' ' . $request->url(), 0, 500);
    }

    protected function inputKeys($request)
    {
        if (!$request) {
            return null;
        }

        $keys = array_keys($request->except(['password', 'password_confirmation', '_token', 'otp', 'token']));

        return mb_substr(json_encode($keys), 0, 1000);
    }

    protected function notify($fingerprint, array $payload)
    {
        $cacheKey = 'dev_error_notified:' . $fingerprint;
        if (!Cache::add($cacheKey, 1, now()->addMinutes(self::NOTIFY_COOLDOWN_MINUTES))) {
            return;
        }

        $recipients = Superuser::role(['Developer', 'SuperAdmin'])->get();
        if ($recipients->isEmpty()) {
            $recipients = Superuser::where('is_superuser', 1)->get();
        }

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new ErrorMonitorNotification($payload));
            DB::table('dev_error_events')->where('id', $payload['event_id'])->update([
                'notified_at' => now(),
            ]);
        }
    }
}
