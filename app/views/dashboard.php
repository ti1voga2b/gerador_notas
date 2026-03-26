<h2>Dashboard</h2>

<p>Bem-vindo, <?php echo htmlspecialchars($user ?? ''); ?>.</p>

<?php if (!empty($message)) : ?>
    <p><?php echo htmlspecialchars($message); ?></p>
<?php endif; ?>

<h3>Upload CSV</h3>

<form method="POST" action="<?php echo htmlspecialchars(url('/upload')); ?>" enctype="multipart/form-data">
    <input type="file" name="csv" accept=".csv,.xlsx">
    <button type="submit">Enviar</button>
</form>

<?php if (!empty($invoices)) : ?>
    <h3>NFCom para download</h3>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Documento</th>
                <th>Linhas</th>
                <th>Total</th>
                <th>Baixar</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($invoices as $invoice) : ?>
                <tr>
                    <td><?php echo htmlspecialchars($invoice['recipient_name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($invoice['recipient_document'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars((string) ($invoice['summary']['count'] ?? 0)); ?></td>
                    <td>R$ <?php echo htmlspecialchars(number_format((float) ($invoice['summary']['total_amount'] ?? 0), 2, ',', '.')); ?></td>
                    <td>
                        <a href="<?php echo htmlspecialchars(url('/nfcom/download') . '?invoice=' . urlencode($invoice['key'] ?? '')); ?>">
                            Baixar PDF
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php if (!empty($rows)) : ?>
    <h3>Preview</h3>

    <pre><?php echo htmlspecialchars(print_r($rows, true)); ?></pre>
<?php endif; ?>
