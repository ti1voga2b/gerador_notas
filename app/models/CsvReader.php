<?php
class CsvReader
{
    public function read($file)
    {
        $rows = [];

        if (($handle = fopen($file, 'r')) !== false) {
            $header = fgetcsv($handle, 1000, ';', '"', '\\');

            if ($header === false) {
                fclose($handle);
                return $rows;
            }

            $header = array_map(function ($column) {
                return trim((string) $column);
            }, $header);

            if (isset($header[0])) {
                $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
            }

            while (($data = fgetcsv($handle, 1000, ';', '"', '\\')) !== false) {
                if ($this->isEmptyRow($data)) {
                    continue;
                }

                $data = $this->normalizeColumns($header, $data);
                $rows[] = array_combine($header, $data);
            }

            fclose($handle);
        }

        return $rows;
    }

    private function normalizeColumns($header, $data)
    {
        $headerCount = count($header);
        $dataCount = count($data);

        if ($dataCount < $headerCount) {
            return array_pad($data, $headerCount, null);
        }

        if ($dataCount > $headerCount) {
            return array_slice($data, 0, $headerCount);
        }

        return $data;
    }

    private function isEmptyRow($data)
    {
        foreach ($data as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
