<?php /** @var array $config */ ?>
<?php $withSpeedtestJs = true; ?>
<section class="space-y-6">
    <div>
        <h1 class="text-2xl font-semibold">Speedtest starten</h1>
        <p class="text-ink-muted mt-1 text-sm">
            Ping, Download und Upload werden gegen diesen Server gemessen.
        </p>
    </div>

    <div class="glass p-5 space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div id="g-ping" class="gauge-wrap gauge-ping">
                <div class="gauge-text">
                    <div class="gauge-label">Ping</div>
                    <div class="gauge-value mono">&mdash;</div>
                    <div class="gauge-unit">ms &middot; Jitter <span id="m-jitter">&mdash;</span></div>
                </div>
            </div>
            <div id="g-down" class="gauge-wrap gauge-down">
                <div class="gauge-text">
                    <div class="gauge-label">Download</div>
                    <div class="gauge-value mono">&mdash;</div>
                    <div class="gauge-unit">Mbit/s</div>
                </div>
            </div>
            <div id="g-up" class="gauge-wrap gauge-up">
                <div class="gauge-text">
                    <div class="gauge-label">Upload</div>
                    <div class="gauge-value mono">&mdash;</div>
                    <div class="gauge-unit">Mbit/s</div>
                </div>
            </div>
        </div>

        <div class="h-2 rounded bg-surface-2 overflow-hidden">
            <div id="m-bar" class="h-full bg-accent transition-all" style="width:0%"></div>
        </div>

        <div id="m-status" class="text-sm text-ink-muted min-h-[1.25rem]">Bereit.</div>

        <div class="flex flex-wrap gap-3 items-center">
            <input id="m-label"
                   type="text"
                   maxlength="80"
                   placeholder="Optionales Label (z.B. 'Wohnzimmer LAN')"
                   class="input flex-1 min-w-[12rem]">
            <button id="m-start" class="btn-primary">
                Test starten
            </button>
        </div>
    </div>

    <div id="m-result" class="hidden glass p-4 text-sm" style="border-color:var(--accent);">
        Ergebnis gespeichert. Link:
        <a id="m-result-link" href="#" class="underline font-medium text-accent"></a>
    </div>
</section>
