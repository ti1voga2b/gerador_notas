<?php

class NfcomService
{
    public function groupInvoices($rows)
    {
        $groups = [];

        foreach ($rows as $row) {
            $invoiceLine = $this->normalizeRow($row);

            if ($invoiceLine === null) {
                continue;
            }

            $groupKey = md5($invoiceLine['recipient_document'] . '|' . $invoiceLine['recipient_name']);

            if (!isset($groups[$groupKey])) {
                $groups[$groupKey] = [
                    'key' => $groupKey,
                    'recipient_name' => $invoiceLine['recipient_name'],
                    'recipient_document' => $invoiceLine['recipient_document'],
                    'lines' => [],
                ];
            }

            $groups[$groupKey]['lines'][] = $invoiceLine;
        }

        foreach ($groups as &$group) {
            $group['summary'] = $this->buildSummary($group['lines']);
            $group['invoice_number'] = $this->buildInvoiceNumber($group['recipient_document']);
            $group['access_key'] = $this->buildAccessKey($group);
        }

        unset($group);

        return array_values($groups);
    }

    public function renderPdf($invoice)
    {
        $pdf = new NfcomPdf();
        $pdf->AliasNbPages();
        $pdf->SetAutoPageBreak(true, 14);
        $pdf->SetTitle($pdf->encode('NFCom'));
        $pdf->AddPage();

        $this->renderHeader($pdf, $invoice);
        $this->renderRecipient($pdf, $invoice);
        $this->renderTable($pdf, $invoice);
        $this->renderSummary($pdf, $invoice);

        return $pdf->Output('S');
    }

    private function normalizeRow($row)
    {
        $subscriber = trim((string) ($row['Assinante / Documento'] ?? ''));

        if ($subscriber === '') {
            return null;
        }

        list($recipientName, $recipientDocument) = $this->splitSubscriber($subscriber);
        $plan = trim((string) ($row['Plano'] ?? ''));
        $ddd = preg_replace('/\D/', '', (string) ($row['DDD'] ?? ''));
        $number = preg_replace('/\D/', '', (string) ($row['Número'] ?? ''));
        $simcard = trim((string) ($row['Simcard'] ?? ''));
        $internet = (float) ($row['Internet (GB)'] ?? 0);
        $zeroRating = (float) ($row['Zero-Rating (GB)'] ?? 0);
        $telefonia = (float) ($row['Telefonia (MIN)'] ?? 0);
        $sms = (float) ($row['SMS'] ?? 0);

        return [
            'recipient_name' => $recipientName,
            'recipient_document' => $recipientDocument,
            'plan' => $plan,
            'ddd' => $ddd,
            'number' => $number,
            'simcard' => rtrim($simcard, "'"),
            'internet_gb' => $internet,
            'zero_rating_gb' => $zeroRating,
            'telefonia_min' => $telefonia,
            'sms' => $sms,
            'amount' => $this->resolvePlanAmount($plan),
        ];
    }

    private function splitSubscriber($subscriber)
    {
        $parts = explode('|', $subscriber);
        $name = trim($parts[0] ?? '');
        $document = preg_replace('/\D/', '', (string) ($parts[1] ?? ''));

        return [$name, $document];
    }

    private function resolvePlanAmount($plan)
    {
        $plan = strtolower($plan);

        if (preg_match('/plano\s*(\d+)\s*gb/', $plan, $matches)) {
            $gb = (int) $matches[1];
            $prices = [
                1 => 19.99,
                2 => 24.99,
                5 => 34.99,
                8 => 39.99,
                15 => 59.99,
                20 => 64.99,
            ];

            if (isset($prices[$gb])) {
                return $prices[$gb];
            }
        }

        return 0.0;
    }

    private function buildSummary($lines)
    {
        $summary = [
            'count' => count($lines),
            'total_amount' => 0.0,
            'total_internet_gb' => 0.0,
            'total_zero_rating_gb' => 0.0,
            'total_telefonia_min' => 0.0,
            'total_sms' => 0.0,
        ];

        foreach ($lines as $line) {
            $summary['total_amount'] += (float) $line['amount'];
            $summary['total_internet_gb'] += (float) $line['internet_gb'];
            $summary['total_zero_rating_gb'] += (float) $line['zero_rating_gb'];
            $summary['total_telefonia_min'] += (float) $line['telefonia_min'];
            $summary['total_sms'] += (float) $line['sms'];
        }

        return $summary;
    }

    private function buildInvoiceNumber($document)
    {
        return str_pad((string) ((int) substr(md5($document), 0, 8)), 9, '0', STR_PAD_LEFT);
    }

    private function buildAccessKey($invoice)
    {
        $issuerCnpj = preg_replace('/\D/', '', (string) ($_ENV['NF_ISSUER_CNPJ'] ?? '34490277000161'));
        $document = preg_replace('/\D/', '', (string) $invoice['recipient_document']);
        $seed = preg_replace('/\D/', '', date('ym') . $issuerCnpj . substr($document, -8) . count($invoice['lines']) . '62');

        return substr(str_pad($seed, 44, '0'), 0, 44);
    }

    private function renderHeader($pdf, $invoice)
    {
        $issuerName = $_ENV['NF_ISSUER_NAME'] ?? 'VOGA INOVACOES TECNOLOGICAS LTDA';
        $issuerCnpj = $_ENV['NF_ISSUER_CNPJ'] ?? '34.490.277/0001-61';
        $issuerIe = $_ENV['NF_ISSUER_IE'] ?? '490.458.900-54';
        $model = $_ENV['NF_MODEL'] ?? '62';
        $series = $_ENV['NF_SERIE'] ?? '1';

        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(120, 8, $pdf->encode($issuerName), 0, 0, 'L');
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(70, 8, $pdf->encode('NFCom'), 0, 1, 'R');

        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(95, 6, $pdf->encode('CNPJ: ' . $issuerCnpj), 0, 0, 'L');
        $pdf->Cell(95, 6, $pdf->encode('Modelo ' . $model . ' | Serie ' . $series . ' | N ' . $invoice['invoice_number']), 0, 1, 'R');
        $pdf->Cell(95, 6, $pdf->encode('IE: ' . $issuerIe), 0, 0, 'L');
        $pdf->Cell(95, 6, $pdf->encode('Emissao: ' . date('d/m/Y H:i')), 0, 1, 'R');
        $pdf->Ln(2);

        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(190, 6, $pdf->encode('CHAVE DE ACESSO'), 1, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(190, 7, $pdf->encode(trim(chunk_split($invoice['access_key'], 4, ' '))), 1, 1, 'L');
        $pdf->Ln(3);
    }

    private function renderRecipient($pdf, $invoice)
    {
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(190, 7, $pdf->encode('DADOS DO DESTINATARIO'), 1, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(140, 7, $pdf->encode('RAZAO SOCIAL: ' . $invoice['recipient_name']), 1, 0, 'L');
        $pdf->Cell(50, 7, $pdf->encode('CNPJ: ' . $this->formatDocument($invoice['recipient_document'])), 1, 1, 'L');
        $pdf->Ln(3);
    }

    private function renderTable($pdf, $invoice)
    {
        $pdf->SetFont('Arial', 'B', 9);
        $pdf->Cell(190, 7, $pdf->encode('DETALHAMENTO DOS SERVICOS (' . count($invoice['lines']) . ' LINHAS)'), 1, 1, 'L');
        $this->renderTableHeader($pdf);

        $index = 1;

        foreach ($invoice['lines'] as $line) {
            if ($pdf->GetY() > 260) {
                $pdf->AddPage();
                $this->renderTableHeader($pdf);
            }

            $pdf->SetFont('Arial', '', 8);
            $pdf->Cell(10, 6, $pdf->encode((string) $index), 1, 0, 'C');
            $pdf->Cell(12, 6, $pdf->encode($line['ddd']), 1, 0, 'C');
            $pdf->Cell(24, 6, $pdf->encode($line['number']), 1, 0, 'C');
            $pdf->Cell(58, 6, $pdf->encode($this->truncate($line['plan'], 34)), 1, 0, 'L');
            $pdf->Cell(20, 6, $pdf->encode($this->formatNumber($line['internet_gb'])), 1, 0, 'R');
            $pdf->Cell(20, 6, $pdf->encode($this->formatNumber($line['zero_rating_gb'])), 1, 0, 'R');
            $pdf->Cell(20, 6, $pdf->encode($this->formatNumber($line['telefonia_min'])), 1, 0, 'R');
            $pdf->Cell(10, 6, $pdf->encode($this->formatNumber($line['sms'], 1)), 1, 0, 'R');
            $pdf->Cell(16, 6, $pdf->encode($this->formatCurrency($line['amount'])), 1, 1, 'R');
            $index++;
        }
    }

    private function renderTableHeader($pdf)
    {
        $pdf->SetFont('Arial', 'B', 8);
        $pdf->Cell(10, 7, '#', 1, 0, 'C');
        $pdf->Cell(12, 7, 'DDD', 1, 0, 'C');
        $pdf->Cell(24, 7, $pdf->encode('NUMERO'), 1, 0, 'C');
        $pdf->Cell(58, 7, 'PLANO', 1, 0, 'C');
        $pdf->Cell(20, 7, 'INTERNET', 1, 0, 'C');
        $pdf->Cell(20, 7, 'ZERO', 1, 0, 'C');
        $pdf->Cell(20, 7, 'VOZ', 1, 0, 'C');
        $pdf->Cell(10, 7, 'SMS', 1, 0, 'C');
        $pdf->Cell(16, 7, 'VALOR', 1, 1, 'C');
    }

    private function renderSummary($pdf, $invoice)
    {
        $summary = $invoice['summary'];

        if ($pdf->GetY() > 240) {
            $pdf->AddPage();
        }

        $pdf->Ln(4);
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(190, 7, $pdf->encode('RESUMO DA FATURA'), 1, 1, 'L');
        $pdf->SetFont('Arial', '', 10);
        $pdf->Cell(95, 7, $pdf->encode('Total de linhas: ' . $summary['count']), 1, 0, 'L');
        $pdf->Cell(95, 7, $pdf->encode('Valor total: ' . $this->formatCurrency($summary['total_amount'])), 1, 1, 'L');
        $pdf->Cell(95, 7, $pdf->encode('Internet: ' . $this->formatNumber($summary['total_internet_gb']) . ' GB'), 1, 0, 'L');
        $pdf->Cell(95, 7, $pdf->encode('Zero-rating: ' . $this->formatNumber($summary['total_zero_rating_gb']) . ' GB'), 1, 1, 'L');
        $pdf->Cell(95, 7, $pdf->encode('Telefonia: ' . $this->formatNumber($summary['total_telefonia_min']) . ' MIN'), 1, 0, 'L');
        $pdf->Cell(95, 7, $pdf->encode('SMS: ' . $this->formatNumber($summary['total_sms'], 1)), 1, 1, 'L');
    }

    private function formatDocument($document)
    {
        $digits = preg_replace('/\D/', '', (string) $document);

        if (strlen($digits) !== 14) {
            return $document;
        }

        return substr($digits, 0, 2) . '.' . substr($digits, 2, 3) . '.' . substr($digits, 5, 3) . '/' . substr($digits, 8, 4) . '-' . substr($digits, 12, 2);
    }

    private function formatCurrency($value)
    {
        return 'R$ ' . number_format((float) $value, 2, ',', '.');
    }

    private function formatNumber($value, $decimals = 2)
    {
        return number_format((float) $value, $decimals, ',', '.');
    }

    private function truncate($text, $length)
    {
        $text = trim((string) $text);

        if (strlen($text) <= $length) {
            return $text;
        }

        return substr($text, 0, $length - 3) . '...';
    }
}

class NfcomPdf extends FPDF
{
    public function Footer()
    {
        $this->SetY(-10);
        $this->SetFont('Arial', '', 8);
        $this->Cell(0, 5, $this->encode('Pagina ' . $this->PageNo() . ' de {nb}'), 0, 0, 'R');
    }

    public function encode($text)
    {
        return iconv('UTF-8', 'windows-1252//TRANSLIT', (string) $text);
    }
}
