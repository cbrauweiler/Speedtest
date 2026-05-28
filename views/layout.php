<?php /** @var string $__content */ /** @var array $config */ ?>
<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? $config['app_name']) ?></title>
    <link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'><text y='14' font-size='14'>⚡</text></svg>">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Tailwind so verdrahten, dass die semantischen Farben auf die CSS-Variablen aus theme.css zeigen.
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        bg:           'var(--bg)',
                        surface:      'var(--surface)',
                        'surface-2':  'var(--surface-2)',
                        line:         'var(--line)',
                        ink:          'var(--ink)',
                        'ink-muted':  'var(--ink-muted)',
                        'ink-subtle': 'var(--ink-subtle)',
                        accent:       'var(--accent)',
                        'accent-soft':'var(--accent-soft)',
                        'accent-ink': 'var(--accent-ink)',
                        danger:       'var(--danger)',
                        'danger-soft':'var(--danger-soft)',
                    }
                }
            }
        };
    </script>
    <link rel="stylesheet" href="<?= e(url('/assets/theme.css')) ?>">
    <script>
        // Frühest mögliches Setzen des Themes, vor dem ersten Paint, damit kein Flash entsteht.
        (function () {
            try {
                var saved = localStorage.getItem('speedtest-theme');
                var pref = saved || (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-theme', pref);
            } catch (_) {}
        })();
    </script>
</head>
<body class="min-h-screen bg-bg text-ink">

<?php
// Aktiven Nav-Punkt bestimmen (Pfad ohne base_path).
$__navPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
if (($config['base_path'] ?? '') !== '' && str_starts_with($__navPath, $config['base_path'])) {
    $__navPath = substr($__navPath, strlen($config['base_path'])) ?: '/';
}
$__isTest    = $__navPath === '/' || str_starts_with($__navPath, '/r/');
$__isHistory = $__navPath === '/history';
?>
<header class="border-b border-line bg-surface">
    <div class="max-w-4xl mx-auto px-4 py-3 flex items-center gap-4">
        <a href="<?= e(url('/')) ?>" class="font-semibold text-lg flex items-center gap-2">
            <span class="text-accent">⚡</span><?= e($config['app_name']) ?>
        </a>
        <nav class="flex gap-2 ml-auto items-center">
            <a href="<?= e(url('/')) ?>" class="nav-btn<?= $__isTest ? ' is-active' : '' ?>">Test</a>
            <a href="<?= e(url('/history')) ?>" class="nav-btn<?= $__isHistory ? ' is-active' : '' ?>">Verlauf</a>
            <button id="theme-toggle"
                    type="button"
                    class="nav-btn ml-1"
                    title="Hell/Dunkel umschalten"
                    aria-label="Theme umschalten">
                <span data-theme-icon="light" class="hidden">☀</span>
                <span data-theme-icon="dark" class="hidden">☾</span>
            </button>
        </nav>
    </div>
</header>

<main class="max-w-4xl mx-auto px-4 py-8">
    <?= $__content ?>
</main>

<footer class="max-w-4xl mx-auto px-4 py-6 text-xs text-ink-subtle flex flex-wrap items-center gap-x-3 gap-y-1">
    <span>&copy; <?= date('Y') ?> Christian Brauweiler</span>
    <span class="text-ink-subtle/60">&middot;</span>
    <span><?= e($config['app_name']) ?> v<?= e($config['app_version'] ?? '0.0.0') ?></span>
    <?php if (!empty($config['repo_url'])): ?>
        <span class="text-ink-subtle/60">&middot;</span>
        <a href="<?= e($config['repo_url']) ?>"
           target="_blank" rel="noopener"
           class="inline-flex items-center gap-1 hover:text-accent">
            <svg viewBox="0 0 16 16" width="14" height="14" fill="currentColor" aria-hidden="true">
                <path d="M8 0C3.58 0 0 3.58 0 8a8 8 0 0 0 5.47 7.59c.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.01 8.01 0 0 0 16 8c0-4.42-3.58-8-8-8Z"/>
            </svg>
            GitHub
        </a>
    <?php endif; ?>
</footer>

<script>
    // Theme-Toggle
    (function () {
        var btn = document.getElementById('theme-toggle');
        if (!btn) return;
        function paint() {
            var cur = document.documentElement.getAttribute('data-theme') || 'light';
            document.querySelectorAll('[data-theme-icon]').forEach(function (el) {
                el.classList.toggle('hidden', el.getAttribute('data-theme-icon') === cur);
            });
        }
        btn.addEventListener('click', function () {
            var cur = document.documentElement.getAttribute('data-theme') || 'light';
            var next = cur === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            try { localStorage.setItem('speedtest-theme', next); } catch (_) {}
            paint();
        });
        paint();
    })();
</script>

<?php if (!empty($withSpeedtestJs)): ?>
<script>window.SPEEDTEST_BASE = <?= json_encode($config['base_path']) ?>;</script>
<script src="<?= e(url('/assets/speedtest.js')) ?>"></script>
<?php endif; ?>

</body>
</html>
