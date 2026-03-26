<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Petit+Formal+Script&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        ice: '#f7f5f2',
                        rosewood: '#b24a4a',
                        blush: '#e8c7c3',
                        clay: '#6f4b4b'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        script: ['Petit Formal Script', 'cursive']
                    },
                    boxShadow: {
                        soft: '0 24px 70px rgba(178, 74, 74, 0.12)'
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-screen bg-ice font-sans text-stone-800">
    <div class="relative overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-80 bg-gradient-to-b from-blush/70 to-transparent"></div>
        <div class="absolute -left-24 top-28 h-72 w-72 rounded-full bg-rosewood/10 blur-3xl"></div>
        <div class="absolute right-0 top-0 h-80 w-80 rounded-full bg-[#f1dbd8] blur-3xl"></div>

        <main class="relative mx-auto max-w-7xl px-6 py-10">
            <section class="mb-8 flex flex-col gap-6 rounded-[2rem] border border-white/70 bg-white/80 p-8 shadow-soft backdrop-blur lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <p class="mb-3 text-sm font-semibold uppercase tracking-[0.35em] text-rosewood">Gerador de NFCom</p>
                    <h1 class="font-script text-4xl text-rosewood md:text-5xl">Painel de emissao</h1>
                    <p class="mt-4 max-w-2xl text-base leading-7 text-stone-600">
                        Bem-vindo, <?php echo htmlspecialchars($user ?? ''); ?>. Faça upload da planilha para gerar os PDFs das notas com visual organizado e download rápido.
                    </p>
                </div>

                <div class="flex flex-col items-start gap-3 lg:items-end">
                    <div class="rounded-3xl bg-rosewood/8 px-5 py-4 text-sm text-clay">
                        <p class="font-semibold text-rosewood"><?php echo htmlspecialchars((string) count($invoices ?? [])); ?> notas prontas</p>
                        <p class="mt-1">CSV, XLSX, XML e ZIP suportados.</p>
                    </div>

                    <a
                        href="<?php echo htmlspecialchars(url('/logout')); ?>"
                        class="inline-flex rounded-2xl border border-rosewood/20 bg-white px-4 py-2 text-sm font-semibold text-rosewood transition hover:border-rosewood/35 hover:bg-rosewood/5"
                    >
                        Sair
                    </a>
                </div>
            </section>

            <?php if (!empty($message)) : ?>
                <section class="mb-6 rounded-2xl border border-rosewood/15 bg-white/90 px-5 py-4 text-sm font-medium text-rosewood shadow-sm">
                    <?php echo htmlspecialchars($message); ?>
                </section>
            <?php endif; ?>

            <section class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
                <div class="rounded-[2rem] border border-white/70 bg-white/85 p-7 shadow-soft backdrop-blur">
                    <h2 class="text-2xl font-semibold text-stone-800">Importar arquivos</h2>
                    <p class="mt-2 text-sm leading-6 text-stone-500">
                        Use o XML como base fiscal da nota e a planilha para complementar o detalhamento de consumo.
                    </p>

                    <form method="POST" action="<?php echo htmlspecialchars(url('/upload')); ?>" enctype="multipart/form-data" class="mt-8 space-y-5">
                        <div class="rounded-3xl border border-dashed border-rosewood/25 bg-ice px-5 py-8 text-center">
                            <p class="text-sm font-medium text-clay">Planilha de detalhamento (CSV ou XLSX)</p>
                            <input
                                type="file"
                                name="spreadsheet"
                                accept=".csv,.xlsx"
                                class="mx-auto mt-4 block w-full max-w-sm rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-600 file:mr-4 file:rounded-xl file:border-0 file:bg-rosewood file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#9e4040]"
                            >
                        </div>

                        <div class="rounded-3xl border border-dashed border-rosewood/25 bg-ice px-5 py-8 text-center">
                            <p class="text-sm font-medium text-clay">Arquivo fiscal (XML ou ZIP com XMLs)</p>
                            <input
                                type="file"
                                name="fiscal"
                                accept=".xml,.zip"
                                class="mx-auto mt-4 block w-full max-w-sm rounded-2xl border border-stone-200 bg-white px-4 py-3 text-sm text-stone-600 file:mr-4 file:rounded-xl file:border-0 file:bg-rosewood file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#9e4040]"
                            >
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-2xl bg-rosewood px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#9e4040] focus:outline-none focus:ring-4 focus:ring-rosewood/20"
                        >
                            Processar arquivo
                        </button>
                    </form>
                </div>

                <div class="rounded-[2rem] border border-white/70 bg-white/85 p-7 shadow-soft backdrop-blur">
                    <div class="mb-5 flex items-center justify-between gap-4">
                        <div>
                            <h2 class="text-2xl font-semibold text-stone-800">NFCom para download</h2>
                            <p class="mt-2 text-sm text-stone-500">Cada cliente gera uma nota pronta para baixar.</p>
                        </div>
                    </div>

                    <?php if (!empty($invoices)) : ?>
                        <div class="overflow-hidden rounded-3xl border border-stone-200">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-stone-200 text-sm">
                                    <thead class="bg-stone-50 text-left text-xs uppercase tracking-[0.18em] text-stone-500">
                                        <tr>
                                            <th class="px-4 py-4">Cliente</th>
                                            <th class="px-4 py-4">Nota</th>
                                            <th class="px-4 py-4">Documento</th>
                                            <th class="px-4 py-4">Base</th>
                                            <th class="px-4 py-4 text-center">Linhas</th>
                                            <th class="px-4 py-4 text-right">Total</th>
                                            <th class="px-4 py-4 text-center">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-stone-100 bg-white">
                                        <?php foreach ($invoices as $invoice) : ?>
                                            <tr class="hover:bg-stone-50/70">
                                                <td class="px-4 py-4">
                                                    <p class="font-semibold text-stone-800"><?php echo htmlspecialchars($invoice['recipient_name'] ?? ''); ?></p>
                                                </td>
                                                <td class="px-4 py-4 text-stone-600"><?php echo htmlspecialchars((string) ($invoice['invoice_number'] ?? '')); ?></td>
                                                <td class="px-4 py-4 text-stone-600"><?php echo htmlspecialchars($invoice['recipient_document'] ?? ''); ?></td>
                                                <td class="px-4 py-4 text-stone-600"><?php echo htmlspecialchars($invoice['match_status_label'] ?? 'Planilha'); ?></td>
                                                <td class="px-4 py-4 text-center text-stone-600"><?php echo htmlspecialchars((string) ($invoice['summary']['count'] ?? 0)); ?></td>
                                                <td class="px-4 py-4 text-right font-semibold text-rosewood">
                                                    R$ <?php echo htmlspecialchars(number_format((float) ($invoice['summary']['total_amount'] ?? 0), 2, ',', '.')); ?>
                                                </td>
                                                <td class="px-4 py-4 text-center">
                                                    <a
                                                        href="<?php echo htmlspecialchars(url('/nfcom/download') . '?invoice=' . urlencode($invoice['key'] ?? '')); ?>"
                                                        class="inline-flex rounded-xl bg-rosewood px-4 py-2 text-xs font-semibold uppercase tracking-[0.18em] text-white transition hover:bg-[#9e4040]"
                                                    >
                                                        Baixar PDF
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    <?php else : ?>
                        <div class="rounded-3xl border border-dashed border-stone-200 bg-stone-50 px-6 py-12 text-center text-sm leading-7 text-stone-500">
                            Quando você importar uma planilha, as notas prontas para download vão aparecer aqui.
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <?php if (!empty($rows)) : ?>
                <section class="mt-6 rounded-[2rem] border border-white/70 bg-white/85 p-7 shadow-soft backdrop-blur">
                    <h2 class="text-2xl font-semibold text-stone-800">Preview dos dados</h2>
                    <p class="mt-2 text-sm text-stone-500">Visualização rápida das linhas lidas do arquivo.</p>

                    <div class="mt-5 overflow-hidden rounded-3xl bg-stone-900 p-1">
                        <pre class="max-h-[30rem] overflow-auto rounded-[1.4rem] bg-stone-950/95 p-5 text-xs leading-6 text-stone-100"><?php echo htmlspecialchars(print_r($rows, true)); ?></pre>
                    </div>
                </section>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
