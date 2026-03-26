<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar</title>
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
                        soft: '0 22px 60px rgba(178, 74, 74, 0.12)'
                    }
                }
            }
        };
    </script>
</head>
<body class="min-h-screen bg-ice font-sans text-stone-800">
    <div class="relative overflow-hidden">
        <div class="absolute inset-x-0 top-0 h-72 bg-gradient-to-b from-blush/70 to-transparent"></div>
        <div class="absolute -left-20 top-24 h-56 w-56 rounded-full bg-rosewood/10 blur-3xl"></div>
        <div class="absolute right-0 top-10 h-72 w-72 rounded-full bg-[#f0d7d4] blur-3xl"></div>

        <main class="relative mx-auto flex min-h-screen max-w-6xl items-center px-6 py-12">
            <div class="grid w-full gap-10 lg:grid-cols-[1.1fr_0.9fr] lg:items-center">
                <section class="max-w-xl">
                    <p class="mb-3 text-sm font-semibold uppercase tracking-[0.35em] text-rosewood">Gerador de NFCom</p>
                    <h1 class="font-script text-4xl leading-tight text-rosewood md:text-5xl">Acesso ao portal</h1>
                    <p class="mt-6 text-lg leading-8 text-stone-600">
                        Entre para importar planilhas, gerar as NFCom em PDF e organizar seus documentos com um fluxo bem mais leve.
                    </p>
                </section>

                <section class="rounded-[2rem] border border-white/70 bg-white/85 p-8 shadow-soft backdrop-blur md:p-10">
                    <div class="mb-8">
                        <h2 class="text-2xl font-semibold text-stone-800">Login</h2>
                        <p class="mt-2 text-sm text-stone-500">Use suas credenciais para continuar.</p>
                    </div>

                    <?php if (!empty($error)) : ?>
                        <div class="mb-6 rounded-2xl border border-rosewood/20 bg-rosewood/10 px-4 py-3 text-sm font-medium text-rosewood">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="<?php echo htmlspecialchars(url('/')); ?>" class="space-y-5">
                        <div>
                            <label for="user" class="mb-2 block text-sm font-medium text-clay">Usuário</label>
                            <input
                                id="user"
                                name="user"
                                type="text"
                                placeholder="Digite seu e-mail ou usuário"
                                class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm outline-none transition focus:border-rosewood focus:bg-white focus:ring-4 focus:ring-rosewood/10"
                            >
                        </div>

                        <div>
                            <label for="pass" class="mb-2 block text-sm font-medium text-clay">Senha</label>
                            <input
                                id="pass"
                                name="pass"
                                type="password"
                                placeholder="Digite sua senha"
                                class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm outline-none transition focus:border-rosewood focus:bg-white focus:ring-4 focus:ring-rosewood/10"
                            >
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-2xl bg-rosewood px-4 py-3 text-sm font-semibold text-white transition hover:bg-[#9e4040] focus:outline-none focus:ring-4 focus:ring-rosewood/20"
                        >
                            Entrar
                        </button>
                    </form>
                </section>
            </div>
        </main>
    </div>
</body>
</html>
