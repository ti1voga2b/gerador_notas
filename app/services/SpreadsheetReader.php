<?php

class SpreadsheetReader
{
    public function read($file, $originalName = '')
    {
        $extension = strtolower(pathinfo($originalName ?: $file, PATHINFO_EXTENSION));

        if ($extension === 'xlsx') {
            return (new XlsxReader())->read($file);
        }

        return (new CsvReader())->read($file);
    }
}
