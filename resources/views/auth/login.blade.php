<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Cuaca Monitor</title>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,200..800&display=swap" rel="stylesheet">
    <script>
        (function() {
            const t = localStorage.getItem("theme-dashboard");
            const dark = t === 'dark' || (!t && window.matchMedia('(prefers-color-scheme: dark)').matches);
            document.documentElement.classList.toggle('dark', dark);
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-background font-ui flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        {{-- Logo --}}
        <div class="mb-8 flex flex-col items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-card border border-border">
                <x-heroicon-o-signal class="h-6 w-6 text-foreground" />
            </div>
            <div class="text-center">
                <h1 class="text-xl font-semibold text-foreground">Cuaca Monitor</h1>
                <p class="text-sm text-muted-foreground">Masuk ke akun Anda</p>
            </div>
        </div>

        {{-- Form --}}
        <div class="rounded-xl border border-border bg-card p-6 shadow-sm">
            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <label for="email" class="text-sm font-medium text-foreground">Email</label>
                    <input
                        id="email"
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        class="w-full rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring transition"
                        placeholder="admin@cuaca.test"
                    >
                    @error('email')
                        <p class="text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1.5">
                    <label for="password" class="text-sm font-medium text-foreground">Password</label>
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        class="w-full rounded-lg border border-border bg-input px-3 py-2 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring transition"
                        placeholder="••••••••"
                    >
                    @error('password')
                        <p class="text-xs text-rose-500">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-2">
                    <input id="remember" type="checkbox" name="remember" class="h-4 w-4 rounded border-border">
                    <label for="remember" class="text-sm text-muted-foreground">Ingat saya</label>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90"
                >
                    Masuk
                </button>
            </form>
        </div>

        <p class="mt-4 text-center text-xs text-muted-foreground">
            Sistem Monitoring Cuaca — Ketapang
        </p>
    </div>
</body>
</html>
