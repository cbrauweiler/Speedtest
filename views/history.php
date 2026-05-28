<?php
/** @var array $config */
/** @var array $rows */
$title = 'Verlauf · ' . $config['app_name'];
?>
<section class="space-y-4">
    <h1 class="text-2xl font-semibold">Verlauf</h1>
    <?php if (empty($rows)): ?>
        <p class="text-ink-muted text-sm">Noch keine Tests vorhanden.</p>
    <?php else: ?>
        <div class="rounded-xl border border-line bg-surface overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-surface-2 text-xs uppercase tracking-wider text-ink-muted">
                    <tr>
                        <th class="text-left px-4 py-2">Zeitpunkt</th>
                        <th class="text-left px-4 py-2">Label</th>
                        <th class="text-right px-4 py-2">Ping</th>
                        <th class="text-right px-4 py-2">Down</th>
                        <th class="text-right px-4 py-2">Up</th>
                        <th class="px-4 py-2"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    <?php foreach ($rows as $row): ?>
                        <tr class="hover:bg-surface-2">
                            <td class="px-4 py-2 mono text-xs"><?= e(date('Y-m-d H:i', (int)$row['created_at'])) ?></td>
                            <td class="px-4 py-2 truncate max-w-[14rem]"><?= e($row['label'] ?: '—') ?></td>
                            <td class="px-4 py-2 text-right mono"><?= e($row['ping_ms'] !== null ? format_ms((float)$row['ping_ms'], 0) . ' ms' : '—') ?></td>
                            <td class="px-4 py-2 text-right mono"><?= e(format_mbps($row['down_mbps'] !== null ? (float)$row['down_mbps'] : null)) ?></td>
                            <td class="px-4 py-2 text-right mono"><?= e(format_mbps($row['up_mbps'] !== null ? (float)$row['up_mbps'] : null)) ?></td>
                            <td class="px-4 py-2 text-right">
                                <a href="<?= e(url('/r/' . $row['id'])) ?>" class="text-accent hover:underline">öffnen</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
