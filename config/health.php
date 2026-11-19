<?php

return [
    /*
     * Set false di lingkungan tanpa cron/scheduler (mis. PC lokal yang
     * diakses lewat tunnel) supaya heartbeat tidak memunculkan warning palsu.
     * Di server produksi biarkan true (default).
     */
    'expect_scheduler' => env('HEALTH_EXPECT_SCHEDULER', true),

    'scheduler_stale_minutes' => (int) env('HEALTH_SCHEDULER_STALE_MINUTES', 15),
];
