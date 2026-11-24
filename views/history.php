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
        <div class="glass overflow-hidden">
            <table class="table-glass">
                <thead>
                    <tr>
                        <th>Zeitpunkt</th>
                        <th>Label</th>
                        <th style="text-align:right">Ping</th>
                        <th style="text-align:right">Down</th>
                        <th style="text-align:right">Up</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td class="mono text-xs"><?= e(date('Y-m-d H:i', (int)$row['created_at'])) ?></td>
                            <td class="truncate max-w-[14rem]"><?= e($row['label'] ?: '—') ?></td>
                            <td class="mono" style="text-align:right"><?= e($row['ping_ms'] !== null ? format_ms((float)$row['ping_ms'], 0) . ' ms' : '—') ?></td>
                            <td class="mono" style="text-align:right"><?= e(format_mbps($row['down_mbps'] !== null ? (float)$row['down_mbps'] : null)) ?></td>
                            <td class="mono" style="text-align:right"><?= e(format_mbps($row['up_mbps'] !== null ? (float)$row['up_mbps'] : null)) ?></td>
                            <td style="text-align:right">
                                <a href="<?= e(url('/r/' . $row['id'])) ?>" class="text-accent hover:underline">öffnen</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
