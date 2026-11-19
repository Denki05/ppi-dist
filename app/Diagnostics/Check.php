<?php

namespace App\Diagnostics;

abstract class Check
{
    protected $limit = 50;
    protected $findings = [];
    protected $truncated = false;
    protected $sinceDays = 180;

    public function setSinceDays($days)
    {
        $this->sinceDays = max(0, (int) $days);
    }

    protected function since()
    {
        return now()->subDays($this->sinceDays)->startOfDay();
    }

    protected function hasSince()
    {
        return $this->sinceDays > 0;
    }

    abstract public function key();

    abstract public function label();

    abstract public function run();

    public function reset()
    {
        $this->findings = [];
        $this->truncated = false;
    }

    public function findings()
    {
        return $this->findings;
    }

    public function isTruncated()
    {
        return $this->truncated;
    }

    /**
     * Dipakai runner saat run() melempar exception, supaya kegagalan satu
     * check tetap tampil sebagai temuan dan check lain terus berjalan.
     */
    public function addFailure($message)
    {
        $this->add('critical', 'Check gagal dijalankan: ' . $message, $this->key());
    }

    protected function add($level, $message, $ref = null)
    {
        if (count($this->findings) >= $this->limit) {
            $this->truncated = true;
            return;
        }

        $this->findings[] = [
            'level' => $level,
            'message' => $message,
            'ref' => $ref,
        ];
    }
}
