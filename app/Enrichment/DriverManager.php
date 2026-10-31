<?php

namespace App\Enrichment;

class DriverManager
{
    /**
     * Buat driver enrichment aktif (atau driver bernama tertentu).
     * Prioritas: settings database (halaman Pengaturan) > config file.
     */
    public static function make($name = null)
    {
        $name = $name ?: setting('enrichment.driver', config('enrichment.default_driver'));
        $config = config('enrichment.drivers.' . $name);

        if ($config === null) {
            throw new \InvalidArgumentException('Driver enrichment "' . $name . '" tidak dikenal. Periksa config/enrichment.php');
        }

        $settings = setting('enrichment.credentials.' . $name, []);
        $apiKey = (is_array($settings) && isset($settings['api_key']) && $settings['api_key'] !== '')
            ? $settings['api_key']
            : env($config['env_key']);

        $config['api_key'] = $apiKey;
        $config['auth_header'] = 'Authorization: Bearer ' . $apiKey;
        $config['name'] = $name;
        $config['model'] = setting('enrichment.model.' . $name, $config['model']);
        $config['use_search'] = !empty($config['supports_search']) && (bool) setting('enrichment.use_search', true);
        $config['no_search_note'] = config('enrichment.no_search_note');
        $config['system_prompt'] = config('enrichment.system_prompt');
        $config['user_template'] = config('enrichment.user_template');
        $config['json_template'] = config('enrichment.json_template');

        $class = $config['class'];

        return new $class($config);
    }

    public static function activeName()
    {
        return setting('enrichment.driver', config('enrichment.default_driver'));
    }

    public static function available()
    {
        $drivers = [];
        foreach (config('enrichment.drivers', []) as $name => $config) {
            $drivers[$name] = isset($config['label']) ? $config['label'] : $name;
        }

        return $drivers;
    }
}
