// Speedtest-Runner: Ping / Download / Upload gegen die eigene API.
// Pragmatisch: feste Phasen, parallele Streams, getrimmte Mittelwerte.

(() => {
    const BASE = (window.SPEEDTEST_BASE || '').replace(/\/$/, '');
    const api = (path) => BASE + '/api/' + path;
    const $ = (id) => document.getElementById(id);
    const ui = {
        jitter: $('m-jitter'),
        bar: $('m-bar'),
        status: $('m-status'),
        start: $('m-start'),
        label: $('m-label'),
        result: $('m-result'),
        resultLink: $('m-result-link'),
    };

    const CONFIG = {
        pingSamples: 12,
        downSizeMb: 25,
        downStreams: 4,
        downDurationMs: 10000,
        upDurationMs: 8000,
        upChunkBytes: 256 * 1024,
        upConcurrent: 3,
    };

    // Halbkreis-Gauges. Stops sind die Skala-Stützstellen; Mapping ist
    // stückweise linear in Stop-Index-Raum, sodass z.B. 50 Mbit und 800 Mbit
    // beide gut ablesbar im Bogen liegen.
    const GAUGES = {
        ping: { stops: [0, 10, 25, 50, 100, 200, 500],              decimals: 0, labelEvery: 2 },
        down: { stops: [0, 1, 5, 10, 25, 50, 100, 250, 500, 1000],  decimals: 1, labelEvery: 3 },
        up:   { stops: [0, 1, 5, 10, 25, 50, 100, 250, 500, 1000],  decimals: 1, labelEvery: 3 },
    };
    const SVG_NS = 'http://www.w3.org/2000/svg';
    // Vergrößerter Zeichenbereich, damit die Skalenlabels lesbar sind.
    const VB_W = 260, VB_H = 175;
    const CX = VB_W / 2, CY = 140, R_ARC = 100;
    const ARC_LEN = Math.PI * R_ARC;

    function polar(angleDeg, r) {
        const rad = angleDeg * Math.PI / 180;
        return { x: CX + r * Math.cos(rad), y: CY - r * Math.sin(rad) };
    }
    function valueToFraction(value, stops) {
        if (!Number.isFinite(value) || value <= stops[0]) return 0;
        if (value >= stops[stops.length - 1]) return 1;
        for (let i = 1; i < stops.length; i++) {
            if (value <= stops[i]) {
                const seg = (value - stops[i - 1]) / (stops[i] - stops[i - 1]);
                return (i - 1 + seg) / (stops.length - 1);
            }
        }
        return 1;
    }

    function buildGauge(name, cfg) {
        const root = document.getElementById('g-' + name);
        if (!root) return;
        const svg = document.createElementNS(SVG_NS, 'svg');
        svg.setAttribute('viewBox', `0 0 ${VB_W} ${VB_H}`);
        svg.setAttribute('class', 'gauge-svg');

        const a = polar(180, R_ARC), b = polar(0, R_ARC);
        const arcPath = `M${a.x} ${a.y} A${R_ARC} ${R_ARC} 0 0 1 ${b.x} ${b.y}`;

        const track = document.createElementNS(SVG_NS, 'path');
        track.setAttribute('d', arcPath);
        track.setAttribute('class', 'gauge-track');
        svg.appendChild(track);

        const arc = document.createElementNS(SVG_NS, 'path');
        arc.setAttribute('d', arcPath);
        arc.setAttribute('class', 'gauge-arc');
        arc.setAttribute('stroke-dasharray', ARC_LEN);
        arc.setAttribute('stroke-dashoffset', ARC_LEN);
        svg.appendChild(arc);

        cfg.stops.forEach((stop, i) => {
            const frac = i / (cfg.stops.length - 1);
            const angle = 180 - frac * 180;
            const p1 = polar(angle, R_ARC - 10);
            const p2 = polar(angle, R_ARC - 20);
            const tick = document.createElementNS(SVG_NS, 'line');
            tick.setAttribute('x1', p1.x); tick.setAttribute('y1', p1.y);
            tick.setAttribute('x2', p2.x); tick.setAttribute('y2', p2.y);
            tick.setAttribute('class', 'gauge-tick');
            svg.appendChild(tick);

            const isEnd = i === 0 || i === cfg.stops.length - 1;
            if (isEnd || i % cfg.labelEvery === 0) {
                const lp = polar(angle, R_ARC + 12);
                const text = document.createElementNS(SVG_NS, 'text');
                text.setAttribute('x', lp.x);
                text.setAttribute('y', lp.y);
                text.setAttribute('class', 'gauge-tick-label');
                text.setAttribute('text-anchor', i === 0 ? 'start' : i === cfg.stops.length - 1 ? 'end' : 'middle');
                text.setAttribute('dominant-baseline', 'middle');
                text.textContent = stop;
                svg.appendChild(text);
            }
        });

        // SVG vor dem .gauge-text-Overlay einfügen, damit das Overlay darüber liegt.
        root.insertBefore(svg, root.firstChild);
        root._arc = arc;
        root._valueEl = root.querySelector('.gauge-value');
    }

    function setGauge(name, value) {
        const root = document.getElementById('g-' + name);
        if (!root || !root._arc) return;
        const cfg = GAUGES[name];
        const frac = valueToFraction(value, cfg.stops);
        root._arc.setAttribute('stroke-dashoffset', ARC_LEN * (1 - frac));
        if (root._valueEl) {
            root._valueEl.textContent = Number.isFinite(value) ? value.toFixed(cfg.decimals) : '—';
        }
    }
    function resetGauge(name) {
        const root = document.getElementById('g-' + name);
        if (!root || !root._arc) return;
        root._arc.setAttribute('stroke-dashoffset', ARC_LEN);
        if (root._valueEl) root._valueEl.textContent = '—';
    }

    Object.entries(GAUGES).forEach(([name, cfg]) => buildGauge(name, cfg));

    const setStatus = (txt) => { ui.status.textContent = txt; };
    const setBar = (pct) => { ui.bar.style.width = Math.max(0, Math.min(100, pct)) + '%'; };
    const fmt = (n, d = 1) => Number.isFinite(n) ? n.toFixed(d) : '—';

    async function runPing() {
        setStatus('Messe Ping …');
        const samples = [];
        try { await fetch(api('ping'), { cache: 'no-store' }); } catch {}

        for (let i = 0; i < CONFIG.pingSamples; i++) {
            const t0 = performance.now();
            try {
                const r = await fetch(api('ping?_=' + Math.random()), { cache: 'no-store' });
                await r.text();
                samples.push(performance.now() - t0);
            } catch {
                samples.push(NaN);
            }
            setBar(5 + (i / CONFIG.pingSamples) * 15);
        }
        const valid = samples.filter(Number.isFinite).sort((a, b) => a - b);
        const trimmed = valid.slice(0, Math.max(1, valid.length - 1));
        const avg = trimmed.reduce((a, b) => a + b, 0) / trimmed.length;
        const jitter = trimmed.reduce((acc, x) => acc + Math.abs(x - avg), 0) / trimmed.length;

        setGauge('ping', avg);
        ui.jitter.textContent = fmt(jitter, 1);
        return { ping_ms: avg, jitter_ms: jitter, samples };
    }

    async function runDownload() {
        setStatus('Messe Download …');
        const url = api('download?size=' + CONFIG.downSizeMb);
        const deadline = performance.now() + CONFIG.downDurationMs;
        let totalBytes = 0;
        const start = performance.now();

        const tick = setInterval(() => {
            const elapsed = performance.now() - start;
            const mbps = (totalBytes * 8) / 1e6 / (elapsed / 1000);
            setGauge('down', mbps);
            setBar(20 + (elapsed / CONFIG.downDurationMs) * 40);
        }, 200);

        const controller = new AbortController();

        async function streamOnce() {
            const r = await fetch(url + '&_=' + Math.random(), {
                cache: 'no-store',
                signal: controller.signal,
            });
            const reader = r.body.getReader();
            while (true) {
                const { done, value } = await reader.read();
                if (done) return;
                totalBytes += value.byteLength;
                if (performance.now() > deadline) {
                    try { controller.abort(); } catch {}
                    return;
                }
            }
        }

        async function worker() {
            while (performance.now() < deadline) {
                try { await streamOnce(); } catch { return; }
            }
        }

        await Promise.all(Array.from({ length: CONFIG.downStreams }, () => worker()));
        clearInterval(tick);

        const elapsed = performance.now() - start;
        const mbps = (totalBytes * 8) / 1e6 / (elapsed / 1000);
        setGauge('down', mbps);
        return { down_mbps: mbps, down_bytes: totalBytes, down_ms: Math.round(elapsed) };
    }

    function makeChunk(size) {
        const buf = new Uint8Array(size);
        crypto.getRandomValues(buf.subarray(0, Math.min(size, 4096)));
        for (let i = 4096; i < size; i++) buf[i] = (i * 2654435761) & 0xff;
        return buf;
    }

    async function runUpload() {
        setStatus('Messe Upload …');
        const chunk = makeChunk(CONFIG.upChunkBytes);
        const deadline = performance.now() + CONFIG.upDurationMs;
        let totalBytes = 0;
        const start = performance.now();

        const tick = setInterval(() => {
            const elapsed = performance.now() - start;
            const mbps = (totalBytes * 8) / 1e6 / (elapsed / 1000);
            setGauge('up', mbps);
            setBar(60 + (elapsed / CONFIG.upDurationMs) * 35);
        }, 200);

        async function uploadOnce() {
            await fetch(api('upload?_=' + Math.random()), {
                method: 'POST',
                headers: { 'Content-Type': 'application/octet-stream' },
                body: chunk,
                cache: 'no-store',
            });
            totalBytes += chunk.byteLength;
        }

        async function worker() {
            while (performance.now() < deadline) {
                try { await uploadOnce(); } catch { return; }
            }
        }

        await Promise.all(Array.from({ length: CONFIG.upConcurrent }, () => worker()));
        clearInterval(tick);

        const elapsed = performance.now() - start;
        const mbps = (totalBytes * 8) / 1e6 / (elapsed / 1000);
        setGauge('up', mbps);
        return { up_mbps: mbps, up_bytes: totalBytes, up_ms: Math.round(elapsed) };
    }

    async function saveResult(payload) {
        const r = await fetch(api('results'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload),
        });
        if (!r.ok) throw new Error('save_failed');
        return r.json();
    }

    async function runAll() {
        ui.start.disabled = true;
        ui.start.classList.add('opacity-50', 'cursor-not-allowed');
        ui.result.classList.add('hidden');
        resetGauge('ping'); resetGauge('down'); resetGauge('up');
        ui.jitter.textContent = '—';
        setBar(0);

        try {
            const ping = await runPing();
            const down = await runDownload();
            const up = await runUpload();
            setBar(100);
            setStatus('Speichere …');

            const payload = {
                label: ui.label.value.trim() || null,
                ping_ms: ping.ping_ms,
                jitter_ms: ping.jitter_ms,
                down_mbps: down.down_mbps,
                up_mbps: up.up_mbps,
                down_bytes: down.down_bytes,
                up_bytes: up.up_bytes,
                down_ms: down.down_ms,
                up_ms: up.up_ms,
                raw: { ping_samples: ping.samples, config: CONFIG },
            };
            const saved = await saveResult(payload);

            ui.resultLink.textContent = location.origin + saved.url;
            ui.resultLink.href = saved.url;
            ui.result.classList.remove('hidden');
            setStatus('Fertig.');
        } catch (err) {
            setStatus('Fehler: ' + (err?.message || err));
        } finally {
            ui.start.disabled = false;
            ui.start.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    if (ui.start) {
        ui.start.addEventListener('click', runAll);
    }
})();
