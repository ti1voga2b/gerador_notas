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

        $message = 'Selecione um arquivo CSV ou XLSX valido.';
        $rows = [];
        $invoices = [];

        if (!empty($_FILES['csv']['tmp_name']) && is_uploaded_file($_FILES['csv']['tmp_name'])) {
            $spreadsheetReader = new SpreadsheetReader();
            $rows = $spreadsheetReader->read($_FILES['csv']['tmp_name'], $_FILES['csv']['name'] ?? '');

            if (empty($rows)) {
                $message = 'Arquivo enviado, mas nenhuma linha valida foi encontrada.';
                unset($_SESSION['invoices']);
            } else {
                $nfcomService = new NfcomService();
                $invoices = $nfcomService->groupInvoices($rows);
                $_SESSION['invoices'] = $invoices;
                $message = 'Arquivo processado com sucesso. Escolha abaixo a NFCom que deseja baixar.';
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
}
