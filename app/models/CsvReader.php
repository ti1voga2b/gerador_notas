<?php
class CsvReader
{
    public function read($file)
    {
        $rows = [];

        if (($handle = fopen($file, "r")) !== false) {
            $header = fgetcsv($handle, 1000, ";");

            while (($data = fgetcsv($handle, 1000, ";")) !== false) {
                $rows[] = array_combine($header, $data);
            }

            fclose($handle);
        }

        return $rows;
    }
}
