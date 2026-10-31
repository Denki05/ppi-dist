<?php

namespace App\Enrichment\Drivers;

use App\Enrichment\Contracts\Driver;
use App\Enrichment\Drivers\Concerns\BuildsUserPrompt;

class GeminiDriver implements Driver
{
    use BuildsUserPrompt;

    protected $config;
    protected $lastRawText;

    public function __construct(array $config)
    {
        if (empty($config['api_key'])) {
            throw new \InvalidArgumentException('API key Gemini belum diisi (Pengaturan > Enrichment atau .env)');
        }

        $this->config = array_merge([
            'model' => 'gemini-3.8-flash',
            'use_search' => true,
            'temperature' => 0.1,
            'endpoint' => 'https://generativelanguage.googleapis.com/v1beta',
            'timeout' => 120,
            'user_template' => '',
            'json_template' => '',
        ], $config);
    }

    public function name()
    {
        return 'gemini';
    }

    public function buildUserPrompt($brand, $searah, $internalNotes)
    {
        $internalNotes = trim((string) $internalNotes);
        if ($internalNotes === '' || in_array(mb_strtolower($internalNotes), ['tidak ada', 'none', '-'], true)) {
            $internalNotes = 'TIDAK ADA';
        }

        return str_replace(
            ['{{brand}}', '{{searah}}', '{{internal_notes}}', '{{json_template}}'],
            [(string) $brand, (string) $searah, $internalNotes, $this->config['json_template']],
            $this->config['user_template']
        );
    }

    public function enrich($brand, $searah, $internalNotes)
    {
        $result = [
            'success' => false,
            'data' => null,
            'error' => null,
            'http_status' => null,
            'raw' => null,
            'model' => $this->config['model'],
            'use_search' => (bool) $this->config['use_search'],
        ];

        // Interactions API: endpoint pengganti generateContent untuk akun baru Gemini.
        $payload = [
            'model' => $this->config['model'],
            'system_instruction' => $this->config['system_prompt'] . (empty($this->config['use_search']) && !empty($this->config['no_search_note']) ? "\n\n" . $this->config['no_search_note'] : ''),
            'input' => $this->buildUserPrompt($brand, $searah, $internalNotes),
            'generation_config' => ['temperature' => (float) $this->config['temperature']],
        ];
        if ($this->config['use_search']) {
            $payload['tools'] = [['type' => 'google_search']];
        }

        $url = $this->config['endpoint'] . '/interactions?key=' . rawurlencode($this->config['api_key']);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_TIMEOUT => (int) $this->config['timeout'],
        ]);
        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $errorMessage = curl_error($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result['http_status'] = $httpStatus;

        if ($errno !== 0) {
            $result['error'] = 'Koneksi ke Gemini gagal: ' . $errorMessage;

            return $result;
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            $result['error'] = 'Respons bukan JSON valid (HTTP ' . $httpStatus . ')';
            $result['raw'] = mb_substr((string) $body, 0, 2000);

            return $result;
        }

        if (isset($decoded['error']['message'])) {
            $result['error'] = 'Gemini API: ' . $decoded['error']['message'];
            $result['raw'] = $body;

            return $result;
        }

        $text = $this->extractOutputText($decoded);
        $this->lastRawText = $text;
        $result['raw'] = $text;

        if (trim($text) === '') {
            $status = isset($decoded['status']) ? $decoded['status'] : 'UNKNOWN';
            $result['error'] = 'Gemini mengembalikan teks kosong (status: ' . $status . ')';

            return $result;
        }

        $json = self::extractJson($text);
        if ($json === null) {
            $result['error'] = 'Tidak menemukan objek JSON di respons Gemini';

            return $result;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            $result['error'] = 'JSON di respons Gemini tidak valid';

            return $result;
        }

        $result['success'] = true;
        $result['data'] = $data;

        return $result;
    }

    /**
     * Ambil teks jawaban dari steps Interactions API (type "model_output").
     */
    protected function extractOutputText(array $decoded)
    {
        $texts = [];
        $steps = isset($decoded['steps']) && is_array($decoded['steps']) ? $decoded['steps'] : [];
        foreach ($steps as $step) {
            if (!isset($step['type']) || $step['type'] !== 'model_output' || !isset($step['content']) || !is_array($step['content'])) {
                continue;
            }
            foreach ($step['content'] as $part) {
                if (isset($part['text'])) {
                    $texts[] = $part['text'];
                }
            }
        }

        return implode('', $texts);
    }

    public function lastRawText()
    {
        return $this->lastRawText;
    }

    public static function extractJson($text)
    {
        $start = strpos($text, '{');
        $end = strrpos($text, '}');
        if ($start === false || $end === false || $end <= $start) {
            return null;
        }

        return substr($text, $start, $end - $start + 1);
    }
}
