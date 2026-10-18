<?php
$mdPath = __DIR__ . '/../Documentos/SegundoParcial_AvanceProyecto_TecnoFix.md';
$htmlPath = __DIR__ . '/../Documentos/SegundoParcial_AvanceProyecto_TecnoFix.html';
$pdfPath = __DIR__ . '/../Documentos/SegundoParcial_AvanceProyecto_TecnoFix.pdf';

$mdContent = file_get_contents($mdPath);

// Convert simple markdown headings and tables to HTML
function mdToHtml($text) {
    // Headers
    $text = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $text);
    $text = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $text);
    $text = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $text);
    $text = preg_replace('/^#### (.*$)/m', '<h4>$1</h4>', $text);
    
    // Bold and Code
    $text = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text);
    $text = preg_replace('/`(.*?)`/s', '<code>$1</code>', $text);
    
    // Links
    $text = preg_replace('/\[(.*?)\]\((.*?)\)/s', '<a href="$2">$1</a>', $text);
    
    // Lists
    $text = preg_replace('/^\* (.*$)/m', '<li>$1</li>', $text);
    $text = preg_replace('/^(\d+)\. (.*$)/m', '<li>$2</li>', $text);

    // Code blocks
    $text = preg_replace_callback('/```(\w*)\n(.*?)```/s', function($matches) {
        return '<pre><code class="language-'.$matches[1].'">'.htmlspecialchars($matches[2]).'</code></pre>';
    }, $text);

    // Horizontal rule
    $text = preg_replace('/^---$/m', '<hr>', $text);
    
    // Paragraphs
    $lines = explode("\n", $text);
    $inTable = false;
    $out = [];
    foreach ($lines as $line) {
        $trim = trim($line);
        if (strpos($trim, '|') === 0) {
            if (!$inTable) {
                $out[] = '<table border="1" cellspacing="0" cellpadding="6">';
                $inTable = true;
            }
            if (strpos($trim, '|---') !== false || strpos($trim, '| ---') !== false) {
                continue; // Skip separator line
            }
            $cols = array_map('trim', explode('|', trim($trim, '|')));
            $out[] = '<tr>';
            foreach ($cols as $col) {
                $out[] = '<td>' . $col . '</td>';
            }
            $out[] = '</tr>';
        } else {
            if ($inTable) {
                $out[] = '</table>';
                $inTable = false;
            }
            $out[] = $line;
        }
    }
    if ($inTable) $out[] = '</table>';

    return implode("\n", $out);
}

$parsedBody = mdToHtml($mdContent);

$fullHtml = '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Segundo Parcial TecnoFix</title>
<style>
    body { font-family: "Segoe UI", Arial, sans-serif; margin: 40px; color: #1e293b; line-height: 1.6; }
    h1 { color: #2563eb; border-bottom: 2px solid #2563eb; padding-bottom: 8px; }
    h2 { color: #0f172a; border-bottom: 1px solid #cbd5e1; padding-bottom: 6px; margin-top: 24px; }
    h3 { color: #1e40af; margin-top: 18px; }
    table { width: 100%; border-collapse: collapse; margin: 16px 0; font-size: 13px; }
    th, td { border: 1px solid #cbd5e1; padding: 8px 12px; text-align: left; }
    th { background-color: #f1f5f9; }
    tr:nth-child(even) { background-color: #f8fafc; }
    code { background-color: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-family: Consolas, monospace; font-size: 13px; color: #0f172a; }
    pre { background-color: #0f172a; color: #f8fafc; padding: 14px; border-radius: 6px; overflow-x: auto; }
    pre code { background: none; color: inherit; padding: 0; }
    blockquote { background-color: #f1f5f9; border-left: 4px solid #2563eb; margin: 16px 0; padding: 10px 16px; }
    a { color: #2563eb; text-decoration: underline; font-weight: bold; }
    hr { border: none; border-top: 1px solid #cbd5e1; margin: 24px 0; }
</style>
</head>
<body>' . $parsedBody . '</body>
</html>';

file_put_contents($htmlPath, $fullHtml);
echo "HTML_GENERADO: $htmlPath\n";
