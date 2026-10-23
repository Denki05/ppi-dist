<?php

require __DIR__ . '/../../vendor/autoload.php';

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\Table\TableExtension;

$markdown = file_get_contents(__DIR__ . '/README.md');
$converter = new CommonMarkConverter([
    'html_input' => 'allow',
    'allow_unsafe_links' => false,
]);
$converter->getEnvironment()->addExtension(new TableExtension());
$content = (string) $converter->convertToHtml($markdown);
$content = preg_replace('/^<h1>.*?<\/h1>\s*/s', '', $content);
$content = preg_replace('/^<p>.*?<\/p>\s*/s', '', $content, 1);
$content = str_replace('<h2>Lampiran</h2>', '<div class="page-break"></div><h2>Lampiran</h2>', $content);
$css = file_get_contents(__DIR__ . '/style.css');

$html = '<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Panduan Pendaftaran, Verifikasi, dan Login</title>
<style>' . $css . '</style>
</head>
<body>
<div class="cover">
  <div class="cover-kicker">Portal Modul PPI</div>
  <h1>Panduan Pendaftaran, Verifikasi, dan Login</h1>
  <p class="cover-sub">Untuk kandidat/user dan administrator</p>
  <div class="cover-meta">Versi 1.0 &middot; 22 Oktober 2026</div>
</div>
<main>' . $content . '</main>
</body>
</html>';

file_put_contents(__DIR__ . '/panduan.html', $html);
echo "panduan.html dibuat" . PHP_EOL;
