<?php

namespace App\Enrichment\Drivers;

use App\Enrichment\Contracts\Driver;
use App\Enrichment\Drivers\Concerns\BuildsUserPrompt;

/**
 * Driver untuk provider berformat chat/completions OpenAI
 * (Groq, OpenRouter, Mistral, NVIDIA NIM, Cerebras, ModelScope, dll).
 *
 * Driver ini tidak punya pencarian web: sources akan kosong dan hasil
 * otomatis masuk "Perlu Review". Cocok untuk pembanding kualitas data.
 */
class OpenAiCompatibleDriver implements Driver
{
    use BuildsUserPrompt;

    protected $config;

    public function __construct(array $config)
    {
        if (empty($config['api_key'])) {
            $label = isset($config['label']) ? $config['label'] : 'OpenAI-compatible';
            $envKey = isset($config['env_key']) ? ' atau .env ' . $config['env_key'] : '';
            throw new \InvalidArgumentException('API key ' . $label . ' belum diisi (Pengaturan > Enrichment' . $envKey . ')');
        }

        $this->config = array_merge([
            'name' => 'openai-compatible',
            'base_url' => '',
            'path' => '/chat/completions',
            'model' => '',
            'temperature' => 0.1,
            'timeout' => 120,
            'json_mode' => true,
            'max_tokens' => 4096,
            'use_search' => false,
            'no_search_note' => '',
            'system_prompt' => '',
            'user_template' => '',
            'json_template' => '',
        ], $config);
    }

    public function name()
    {
        return $this->config['name'];
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
            'use_search' => false,
        ];

        $prefix = ucfirst($this->config['name']) . ' API: ';

        $systemPrompt = (string) $this->config['system_prompt'];
        if (trim((string) $this->config['no_search_note']) !== '') {
            $systemPrompt .= "\n\n" . $this->config['no_search_note'];
        }

        $payload = [
            'model' => $this->config['model'],
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $this->buildUserPrompt($brand, $searah, $internalNotes)],
            ],
            'temperature' => (float) $this->config['temperature'],
            'max_tokens' => (int) $this->config['max_tokens'],
        ];
        if ($this->config['json_mode']) {
            $payload['response_format'] = ['type' => 'json_object'];
        }

        $url = rtrim($this->config['base_url'], '/') . '/' . ltrim($this->config['path'], '/');

        $headers = [
            'Content-Type: application/json',
            $this->config['auth_header'],
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => (int) $this->config['timeout'],
        ]);
        $body = curl_exec($ch);
        $errno = curl_errno($ch);
        $errorMessage = curl_error($ch);
        $httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $result['http_status'] = $httpStatus;

        if ($errno !== 0) {
            $result['error'] = 'Koneksi ke ' . $this->config['name'] . ' gagal: ' . $errorMessage;

            return $result;
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            $result['error'] = 'Respons bukan JSON valid (HTTP ' . $httpStatus . ')';
            $result['raw'] = mb_substr((string) $body, 0, 2000);

            return $result;
        }

        if (isset($decoded['error']['message'])) {
            $result['error'] = $prefix . $decoded['error']['message'];
            $result['raw'] = $body;

            return $result;
        }

        if (isset($decoded['detail'])) {
            $result['error'] = $prefix . $decoded['detail'];
            $result['raw'] = $body;

            return $result;
        }

        if ($httpStatus >= 400) {
            $result['error'] = $prefix . 'HTTP ' . $httpStatus . ' - ' . mb_substr((string) $body, 0, 300);
            $result['raw'] = $body;

            return $result;
        }

        $text = isset($decoded['choices'][0]['message']['content']) ? $decoded['choices'][0]['message']['content'] : '';
        if (is_array($text)) {
            $text = implode('', array_filter($text, 'is_string'));
        }
        $result['raw'] = $text;

        if (trim((string) $text) === '') {
            $finishReason = isset($decoded['choices'][0]['finish_reason']) ? $decoded['choices'][0]['finish_reason'] : 'UNKNOWN';
            $result['error'] = $prefix . 'respons kosong (finish_reason: ' . $finishReason . ')';

            return $result;
        }

        $json = self::extractJson($text);
        if ($json === null) {
            $result['error'] = 'Tidak menemukan objek JSON di respons ' . $this->config['name'];

            return $result;
        }

        $data = json_decode($json, true);
        if (!is_array($data)) {
            $result['error'] = 'JSON di respons ' . $this->config['name'] . ' tidak valid';

            return $result;
        }

        $result['success'] = true;
        $result['data'] = $data;

        return $result;
    }
}
