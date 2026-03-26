<?php

class UploadController
{
    public function index()
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . url('/'));
            exit;
        }

        Render::view('dashboard', [
            'user' => $_SESSION['user'],
            'invoices' => $_SESSION['invoices'] ?? [],
        ]);
    }

    public function upload()
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . url('/'));
            exit;
        }

        $message = 'Selecione uma planilha CSV/XLSX, um XML/ZIP fiscal, ou envie ambos.';
        $rows = [];
        $invoices = [];
        $fiscalDocuments = [];

        if (!empty($_FILES['spreadsheet']['tmp_name']) && is_uploaded_file($_FILES['spreadsheet']['tmp_name'])) {
            $spreadsheetReader = new SpreadsheetReader();
            $rows = $spreadsheetReader->read($_FILES['spreadsheet']['tmp_name'], $_FILES['spreadsheet']['name'] ?? '');
        }

        if (!empty($_FILES['fiscal']['tmp_name']) && is_uploaded_file($_FILES['fiscal']['tmp_name'])) {
            $this->ensureXmlInvoiceReaderLoaded();
            $xmlInvoiceReader = new XmlInvoiceReader();
            $fiscalDocuments = $xmlInvoiceReader->read($_FILES['fiscal']['tmp_name'], $_FILES['fiscal']['name'] ?? '');
        }

        if (empty($rows) && empty($fiscalDocuments)) {
            unset($_SESSION['invoices']);
        } else {
            $nfcomService = new NfcomService();
            $invoices = $nfcomService->buildInvoices($rows, $fiscalDocuments);

            if (empty($invoices)) {
                $message = 'Os arquivos foram enviados, mas nenhuma NFCom valida foi identificada.';
                unset($_SESSION['invoices']);
            } else {
                $_SESSION['invoices'] = $invoices;
                $message = $this->buildUploadMessage($rows, $fiscalDocuments, $invoices);
            }
        }

        Render::view('dashboard', [
            'user' => $_SESSION['user'],
            'message' => $message,
            'rows' => $rows,
            'invoices' => $invoices,
        ]);
    }

    public function downloadNfcom()
    {
        if (empty($_SESSION['user'])) {
            header('Location: ' . url('/'));
            exit;
        }

        $invoiceKey = $_GET['invoice'] ?? '';
        $invoices = $_SESSION['invoices'] ?? [];

        foreach ($invoices as $invoice) {
            if (($invoice['key'] ?? '') !== $invoiceKey) {
                continue;
            }

            $nfcomService = new NfcomService();
            $content = $nfcomService->renderPdf($invoice);
            $fileName = 'nfcom_' . preg_replace('/[^a-z0-9]+/i', '_', strtolower($invoice['recipient_name'])) . '.pdf';

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $fileName . '"');
            header('Content-Length: ' . strlen($content));
            echo $content;
            return;
        }

        http_response_code(404);
        echo 'NFCom nao encontrada.';
    }

    private function buildUploadMessage($rows, $fiscalDocuments, $invoices)
    {
        $parts = [];

        if (!empty($rows)) {
            $parts[] = count($rows) . ' linhas de planilha';
        }

        if (!empty($fiscalDocuments)) {
            $parts[] = count($fiscalDocuments) . ' XML fiscais';
        }

        $matchedCount = 0;

        foreach ($invoices as $invoice) {
            if (($invoice['match_status'] ?? '') === 'matched') {
                $matchedCount++;
            }
        }

        $message = 'Arquivos processados: ' . implode(' e ', $parts) . '. ';
        $message .= count($invoices) . ' notas prontas para consulta.';

        if ($matchedCount > 0) {
            $message .= ' ' . $matchedCount . ' notas foram conciliadas com XML + planilha.';
        }

        return $message;
    }

    private function ensureXmlInvoiceReaderLoaded()
    {
        if (class_exists('XmlInvoiceReader', false)) {
            return;
        }

        require_once dirname(__DIR__) . '/services/XmlInvoiceReader.php';
    }
}
