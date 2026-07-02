<?php

namespace App\Libraries;

class ExportService
{
    public function csv(array $headers, array $rows): string
    {
        $out = fopen('php://temp', 'r+');
        fputcsv($out, $headers);

        foreach ($rows as $row) {
            fputcsv($out, $row);
        }

        rewind($out);
        $csv = stream_get_contents($out);
        fclose($out);

        return $csv ?: '';
    }

    public function downloadResponse(string $filename, string $content, string $mime = 'text/csv'): \CodeIgniter\HTTP\ResponseInterface
    {
        return service('response')
            ->setHeader('Content-Type', $mime)
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($content);
    }
}
