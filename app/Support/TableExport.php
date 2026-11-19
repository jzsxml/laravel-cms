<?php

namespace App\Support;

use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TableExport
{
    /**
     * @param  array<int, array<string, mixed>|object>  $rows
     * @param  array<int, string>  $fields
     */
    public static function xlsxResponse(array $rows, array $fields, string $filename = 'export'): StreamedResponse
    {
        $safeName = preg_replace('/[^\w\x{4e00}-\x{9fa5}\-]+/u', '_', $filename) ?: 'export';

        return new StreamedResponse(function () use ($rows, $fields) {
            $writer = new Writer;
            $writer->openToFile('php://output');
            $writer->addRow(Row::fromValues($fields));
            foreach ($rows as $row) {
                $line = [];
                foreach ($fields as $f) {
                    $line[] = self::cellValue(is_array($row) ? ($row[$f] ?? '') : ($row->{$f} ?? ''));
                }
                $writer->addRow(Row::fromValues($line));
            }
            $writer->close();
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$safeName.'.xlsx"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    public static function cellValue(mixed $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_scalar($value)) {
            return (string) $value;
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE) ?: '';
    }
}
