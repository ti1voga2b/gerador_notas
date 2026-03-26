<?php

class XlsxReader
{
    public function read($file)
    {
        $zip = new ZipArchive();

        if ($zip->open($file) !== true) {
            return [];
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $sharedStringsXml = $zip->getFromName('xl/sharedStrings.xml');
        $zip->close();

        if ($sheetXml === false) {
            return [];
        }

        $sharedStrings = $this->parseSharedStrings($sharedStringsXml);
        $xml = simplexml_load_string($sheetXml);

        if ($xml === false) {
            return [];
        }

        $xml->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $rows = $xml->xpath('//main:sheetData/main:row');

        if (!$rows) {
            return [];
        }

        $parsedRows = [];

        foreach ($rows as $row) {
            $parsedRows[] = $this->parseRow($row, $sharedStrings);
        }

        $headers = $parsedRows[1] ?? [];
        $dataRows = array_slice($parsedRows, 2);
        $result = [];

        foreach ($dataRows as $row) {
            if ($this->isEmptyRow($row)) {
                continue;
            }

            $normalized = array_slice(array_pad($row, count($headers), null), 0, count($headers));
            $result[] = array_combine($headers, $normalized);
        }

        return $result;
    }

    private function parseSharedStrings($sharedStringsXml)
    {
        if ($sharedStringsXml === false) {
            return [];
        }

        $xml = simplexml_load_string($sharedStringsXml);

        if ($xml === false) {
            return [];
        }

        $xml->registerXPathNamespace('main', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
        $items = $xml->xpath('//main:si');
        $strings = [];

        if ($items === false) {
            return [];
        }

        foreach ($items as $item) {
            $textNodes = $item->xpath(".//*[local-name()='t']");
            $text = '';

            if ($textNodes === false) {
                $strings[] = $text;
                continue;
            }

            foreach ($textNodes as $textNode) {
                $text .= (string) $textNode;
            }

            $strings[] = $text;
        }

        return $strings;
    }

    private function parseRow($row, $sharedStrings)
    {
        $values = [];
        $cells = $row->c ?? [];

        foreach ($cells as $cell) {
            $reference = (string) ($cell['r'] ?? '');
            $index = $this->columnIndexFromReference($reference);
            $values[$index] = $this->parseCellValue($cell, $sharedStrings);
        }

        if (empty($values)) {
            return [];
        }

        $maxIndex = max(array_keys($values));
        $rowValues = [];

        for ($index = 0; $index <= $maxIndex; $index++) {
            $rowValues[] = $values[$index] ?? null;
        }

        return $rowValues;
    }

    private function parseCellValue($cell, $sharedStrings)
    {
        $type = (string) ($cell['t'] ?? '');

        if ($type === 'inlineStr') {
            return trim((string) ($cell->is->t ?? ''));
        }

        if ($type === 's') {
            $sharedIndex = (int) ($cell->v ?? 0);
            return trim((string) ($sharedStrings[$sharedIndex] ?? ''));
        }

        $value = (string) ($cell->v ?? '');

        if ($value === '') {
            return '';
        }

        if (is_numeric($value)) {
            if (str_contains($value, '.')) {
                return (float) $value;
            }

            return (string) $value;
        }

        return trim($value);
    }

    private function columnIndexFromReference($reference)
    {
        $column = preg_replace('/[^A-Z]/', '', strtoupper($reference));
        $index = 0;

        for ($i = 0; $i < strlen($column); $i++) {
            $index = ($index * 26) + (ord($column[$i]) - 64);
        }

        return max(0, $index - 1);
    }

    private function isEmptyRow($row)
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
