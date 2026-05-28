<?php
/** @var array $config */
/** @var array|null $r */
/** @var bool|null $notFound */
$title = ($notFound ?? false) ? 'Ergebnis nicht gefunden' : 'Ergebnis · ' . $config['app_name'];
?>
<?php if ($notFound ?? false): ?>
    <div class="rounded-xl border border-danger bg-danger-soft p-6">
        <h1 class="text-xl font-semibold">Ergebnis nicht gefunden</h1>
        <p class="text-ink-muted mt-2 text-sm">Die ID existiert nicht oder das Ergebnis wurde gelöscht.</p>
        <a href="<?= e(url('/')) ?>" class="inline-block mt-4 text-accent hover:underline">Neuen Test starten</a>
    </div>
<?php else: ?>
    <section class="space-y-6">
        <div>
            <div class="text-xs uppercase tracking-wider text-ink-muted">Ergebnis</div>
            <h1 class="text-2xl font-semibold mt-1">
                <?= e($r['label'] ?: 'Speedtest vom ' . date('d.m.Y H:i', (int)$r['created_at'])) ?>
            </h1>
            <div class="text-xs text-ink-subtle mono mt-1">ID: <?= e($r['id']) ?></div>
        </div>

        <div class="grid grid-cols-3 gap-4">
            <div class="rounded-xl border border-line bg-surface p-4">
                <div class="text-xs uppercase tracking-wider text-ink-muted">Ping</div>
                <div class="mono text-3xl font-semibold mt-1"><?= e(format_ms($r['ping_ms'] !== null ? (float)$r['ping_ms'] : null)) ?></div>
                <div class="text-xs text-ink-subtle">ms &middot; Jitter <?= e(format_ms($r['jitter_ms'] !== null ? (float)$r['jitter_ms'] : null)) ?></div>
            </div>
            <div class="rounded-xl border border-line bg-surface p-4">
                <div class="text-xs uppercase tracking-wider text-ink-muted">Download</div>
                <div class="mono text-3xl font-semibold mt-1"><?= e(format_mbps($r['down_mbps'] !== null ? (float)$r['down_mbps'] : null)) ?></div>
                <div class="text-xs text-ink-subtle">Mbit/s</div>
            </div>
            <div class="rounded-xl border border-line bg-surface p-4">
                <div class="text-xs uppercase tracking-wider text-ink-muted">Upload</div>
                <div class="mono text-3xl font-semibold mt-1"><?= e(format_mbps($r['up_mbps'] !== null ? (float)$r['up_mbps'] : null)) ?></div>
                <div class="text-xs text-ink-subtle">Mbit/s</div>
            </div>
        </div>

        <div class="rounded-xl border border-line bg-surface p-5 text-sm divide-y divide-line">
            <div class="flex justify-between py-2"><span class="text-ink-muted">Zeitpunkt</span><span class="mono"><?= e(date('Y-m-d H:i:s', (int)$r['created_at'])) ?></span></div>
            <div class="flex justify-between py-2"><span class="text-ink-muted">Download-Daten</span><span class="mono"><?= e(number_format(((int)$r['down_bytes']) / 1024 / 1024, 1, ',', '.')) ?> MB in <?= e((string)(int)$r['down_ms']) ?> ms</span></div>
            <div class="flex justify-between py-2"><span class="text-ink-muted">Upload-Daten</span><span class="mono"><?= e(number_format(((int)$r['up_bytes']) / 1024 / 1024, 1, ',', '.')) ?> MB in <?= e((string)(int)$r['up_ms']) ?> ms</span></div>
            <div class="flex justify-between py-2 gap-4"><span class="text-ink-muted">User-Agent</span><span class="mono text-xs text-right truncate"><?= e($r['user_agent']) ?></span></div>
        </div>

        <div class="flex gap-3">
            <a href="<?= e(url('/')) ?>" class="px-4 py-2 rounded bg-accent hover:opacity-90 text-white font-medium text-sm">Neuen Test starten</a>
            <a href="<?= e(url('/history')) ?>" class="px-4 py-2 rounded border border-line hover:border-accent text-sm">Verlauf</a>
        </div>
    </section>
<?php endif; ?>
