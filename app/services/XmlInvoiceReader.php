<?php

class XmlInvoiceReader
{
    public function read($file, $originalName = '')
    {
        $extension = strtolower(pathinfo($originalName ?: $file, PATHINFO_EXTENSION));

        if ($extension === 'zip') {
            return $this->readZip($file);
        }

        return $this->readXmlFile($file);
    }

    private function readZip($file)
    {
        $zip = new ZipArchive();
        $documents = [];

        if ($zip->open($file) !== true) {
            return [];
        }

        for ($index = 0; $index < $zip->numFiles; $index++) {
            $entryName = $zip->getNameIndex($index);

            if (!is_string($entryName) || strtolower(pathinfo($entryName, PATHINFO_EXTENSION)) !== 'xml') {
                continue;
            }

            $content = $zip->getFromIndex($index);

            if (!is_string($content) || trim($content) === '') {
                continue;
            }

            $parsed = $this->parseXml($content, $entryName);

            if ($parsed !== null) {
                $documents[] = $parsed;
            }
        }

        $zip->close();

        return $documents;
    }

    private function readXmlFile($file)
    {
        $content = @file_get_contents($file);

        if (!is_string($content) || trim($content) === '') {
            return [];
        }

        $parsed = $this->parseXml($content, basename($file));

        return $parsed === null ? [] : [$parsed];
    }

    private function parseXml($content, $sourceName)
    {
        $xml = @simplexml_load_string($content);

        if ($xml === false) {
            return null;
        }

        $document = [
            'source_name' => $sourceName,
            'access_key' => $this->resolveAccessKey($xml),
            'invoice_number' => $this->firstValue($xml, [
                "//*[local-name()='ide']/*[local-name()='nNF']",
                "//*[local-name()='ide']/*[local-name()='nNFCom']",
                "//*[local-name()='infNFCom']/*[local-name()='ide']/*[local-name()='nNF']",
            ]),
            'series' => $this->firstValue($xml, [
                "//*[local-name()='ide']/*[local-name()='serie']",
            ]),
            'model' => $this->firstValue($xml, [
                "//*[local-name()='ide']/*[local-name()='mod']",
            ]),
            'issued_at' => $this->firstValue($xml, [
                "//*[local-name()='ide']/*[local-name()='dhEmi']",
                "//*[local-name()='ide']/*[local-name()='dEmi']",
            ]),
            'issuer_name' => $this->firstValue($xml, [
                "//*[local-name()='emit']/*[local-name()='xNome']",
            ]),
            'issuer_document' => $this->onlyDigits($this->firstValue($xml, [
                "//*[local-name()='emit']/*[local-name()='CNPJ']",
                "//*[local-name()='emit']/*[local-name()='CPF']",
            ])),
            'issuer_ie' => $this->firstValue($xml, [
                "//*[local-name()='emit']/*[local-name()='IE']",
            ]),
            'recipient_name' => $this->firstValue($xml, [
                "//*[local-name()='dest']/*[local-name()='xNome']",
            ]),
            'recipient_document' => $this->onlyDigits($this->firstValue($xml, [
                "//*[local-name()='dest']/*[local-name()='CNPJ']",
                "//*[local-name()='dest']/*[local-name()='CPF']",
            ])),
            'total_amount' => $this->parseDecimal($this->firstValue($xml, [
                "//*[local-name()='ICMSTot']/*[local-name()='vNF']",
                "//*[local-name()='total']/*[local-name()='vNF']",
                "//*[local-name()='vNF']",
            ])),
        ];

        if ($document['recipient_document'] === '' && $document['access_key'] === '') {
            return null;
        }

        return $document;
    }

    private function resolveAccessKey($xml)
    {
        $fromNode = $this->firstValue($xml, [
            "//*[local-name()='protNFe']//*[local-name()='infProt']/*[local-name()='chNFe']",
            "//*[local-name()='protNFCom']//*[local-name()='infProt']/*[local-name()='chNFCom']",
            "//*[local-name()='infProt']/*[local-name()='chNFe']",
            "//*[local-name()='infProt']/*[local-name()='chNFCom']",
        ]);

        if ($fromNode !== '') {
            return $this->onlyDigits($fromNode);
        }

        $idValue = $this->firstValue($xml, [
            "//*[local-name()='infNFe']/@Id",
            "//*[local-name()='infNFCom']/@Id",
        ]);

        return $this->onlyDigits($idValue);
    }

    private function firstValue($xml, array $expressions)
    {
        foreach ($expressions as $expression) {
            $result = $xml->xpath($expression);

            if (!$result || !isset($result[0])) {
                continue;
            }

            $value = trim((string) $result[0]);

            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }

    private function onlyDigits($value)
    {
        return preg_replace('/\D/', '', (string) $value);
    }

    private function parseDecimal($value)
    {
        $value = trim((string) $value);

        if ($value === '') {
            return 0.0;
        }

        return (float) str_replace(',', '.', $value);
    }
}
