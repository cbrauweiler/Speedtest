<?php $title = '404 · ' . $config['app_name']; ?>
<section class="space-y-4">
    <h1 class="text-2xl font-semibold">Seite nicht gefunden</h1>
    <p class="text-ink-muted text-sm">Die angeforderte Seite existiert nicht.</p>
    <a href="<?= e(url('/')) ?>" class="text-accent hover:underline">Zur Startseite</a>
</section>
