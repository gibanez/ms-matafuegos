<script setup>
defineProps({
    compact: { type: Boolean, default: false },
});
</script>

<template>
    <div
        class="relative isolate overflow-hidden rounded-[2rem] bg-ink-950 shadow-soft ring-1 ring-white/10"
        :class="compact ? 'aspect-[4/5]' : 'aspect-[4/5] min-h-[420px]'"
    >
        <div class="absolute inset-0 bg-grid opacity-60"></div>
        <div
            class="absolute -right-20 -top-20 h-80 w-80 rounded-full bg-brand-600/30 blur-3xl"
        ></div>
        <div
            class="absolute -bottom-24 -left-16 h-72 w-72 rounded-full bg-brand-500/15 blur-3xl"
        ></div>

        <svg
            viewBox="0 0 520 560"
            class="relative z-10 h-full w-full"
            role="img"
            aria-label="Matafuego MS con control de presión y etiqueta de seguridad"
        >
            <defs>
                <linearGradient id="tank" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#ff6262" />
                    <stop offset="38%" stop-color="#e11d26" />
                    <stop offset="100%" stop-color="#8e0c15" />
                </linearGradient>
                <linearGradient id="metal" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="#2a313a" />
                    <stop offset="100%" stop-color="#0d1117" />
                </linearGradient>
                <linearGradient id="glass" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="#e9f4ff" stop-opacity="0.9" />
                    <stop offset="100%" stop-color="#83a6c9" stop-opacity="0.2" />
                </linearGradient>
                <filter id="tank-shadow" x="-30%" y="-20%" width="160%" height="160%">
                    <feDropShadow dx="0" dy="22" stdDeviation="20" flood-color="#000" flood-opacity="0.45" />
                </filter>
                <filter id="soft-shadow" x="-40%" y="-40%" width="180%" height="180%">
                    <feGaussianBlur stdDeviation="16" />
                </filter>
            </defs>

            <g opacity="0.18" fill="none" stroke="#ffffff">
                <circle cx="260" cy="280" r="192" />
                <circle cx="260" cy="280" r="150" stroke-dasharray="5 12" />
            </g>

            <g opacity="0.28" fill="#d9141e">
                <path
                    d="M116 422c-24-60 2-98 35-123-2 35 15 46 29 34 10-9 13-30 7-51 37 31 51 71 34 107-15 33-55 55-105 33Z"
                />
                <path
                    d="M405 408c20-47 3-74-20-94 2 25-10 34-20 26-7-6-9-20-5-35-26 22-35 51-24 77 10 24 38 40 69 26Z"
                />
            </g>

            <ellipse cx="260" cy="492" rx="142" ry="23" fill="#000" opacity="0.55" filter="url(#soft-shadow)" />

            <g filter="url(#tank-shadow)">
                <path
                    d="M305 139c54 23 76 76 78 123l5 163c2 48-35 89-83 89h-90c-48 0-86-39-84-88l4-164c2-47 24-100 78-123Z"
                    fill="#10151b"
                    opacity="0.65"
                    transform="translate(10 18)"
                />

                <path
                    d="M186 168h148c19 0 34 15 34 34v218c0 41-33 74-74 74h-68c-41 0-74-33-74-74V202c0-19 15-34 34-34Z"
                    fill="url(#tank)"
                />
                <path
                    d="M194 169h55v283c0 21-14 39-34 42-8 1-16 0-23-3-22-9-36-30-36-56V202c0-16 11-30 26-33h12Z"
                    fill="#ffffff"
                    opacity="0.15"
                />
                <rect x="156" y="425" width="230" height="20" rx="10" fill="#750911" />
                <rect x="162" y="447" width="218" height="12" rx="6" fill="#4f060c" />
                <rect x="186" y="270" width="168" height="6" rx="3" fill="#ffffff" opacity="0.18" />

                <rect x="229" y="113" width="62" height="62" rx="12" fill="url(#metal)" />
                <rect x="219" y="161" width="82" height="14" rx="7" fill="#080b0f" />
                <rect x="217" y="102" width="86" height="18" rx="9" fill="#161c23" />
                <rect x="242" y="82" width="36" height="24" rx="7" fill="#242c35" />
                <path d="M237 86h92c9 0 16 7 16 16v3h-108Z" fill="#151b22" />
                <path d="M251 76h40c6 0 11 5 11 11H240c0-6 5-11 11-11Z" fill="#3a444f" />

                <path
                    d="M319 112c65 1 99 50 85 112-8 36-31 62-65 77"
                    fill="none"
                    stroke="#0b0f14"
                    stroke-width="24"
                    stroke-linecap="round"
                />
                <path
                    d="M319 112c65 1 99 50 85 112-8 36-31 62-65 77"
                    fill="none"
                    stroke="#252d36"
                    stroke-width="12"
                    stroke-linecap="round"
                />
                <g transform="translate(327 294) rotate(18)">
                    <rect x="0" y="0" width="63" height="22" rx="10" fill="#0b0f14" />
                    <rect x="48" y="4" width="15" height="14" rx="4" fill="#3c4651" />
                </g>

                <g transform="translate(340 130)">
                    <circle r="27" fill="#0b0f14" />
                    <circle r="20" fill="url(#glass)" />
                    <path d="M0-20A20 20 0 0 1 17 10L0 0Z" fill="#16a34a" />
                    <circle r="4" fill="#0b0f14" />
                    <path d="M0 0l12-9" stroke="#0b0f14" stroke-width="2.5" stroke-linecap="round" />
                </g>

                <g transform="translate(260 343)">
                    <rect x="-91" y="-100" width="182" height="186" rx="22" fill="#f7f5f0" />
                    <rect x="-82" y="-91" width="164" height="168" rx="16" fill="none" stroke="#d3d7dc" />
                    <text
                        x="0"
                        y="-37"
                        text-anchor="middle"
                        font-family="Barlow Condensed, Arial Narrow, sans-serif"
                        font-size="66"
                        font-weight="900"
                        fill="#b60f19"
                        letter-spacing="1"
                    >
                        MS
                    </text>
                    <text
                        x="0"
                        y="2"
                        text-anchor="middle"
                        font-family="Inter, Arial, sans-serif"
                        font-size="13"
                        font-weight="800"
                        fill="#151a20"
                        letter-spacing="3.4"
                    >
                        MATAFUEGOS
                    </text>
                    <rect x="-38" y="22" width="76" height="24" rx="12" fill="#12813b" />
                    <text
                        x="0"
                        y="39"
                        text-anchor="middle"
                        font-family="Inter, Arial, sans-serif"
                        font-size="12"
                        font-weight="800"
                        fill="#ffffff"
                        letter-spacing="2"
                    >
                        ABC
                    </text>
                    <rect x="-56" y="60" width="112" height="5" rx="2.5" fill="#d3d7dc" />
                    <rect x="-42" y="72" width="84" height="4" rx="2" fill="#e4e7ea" />
                </g>
            </g>

            <g fill="#ffffff">
                <circle cx="130" cy="170" r="3" opacity="0.7" />
                <circle cx="406" cy="218" r="2" opacity="0.5" />
                <circle cx="118" cy="310" r="2" opacity="0.45" />
                <circle cx="422" cy="350" r="3" opacity="0.55" />
            </g>
        </svg>

        <div class="absolute left-5 top-5 z-20 rounded-2xl border border-white/10 bg-white/10 px-4 py-3 backdrop-blur-md">
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white/50">Control de presión</p>
            <p class="mt-1 flex items-center gap-2 text-sm font-bold text-white">
                <span class="h-2 w-2 rounded-full bg-safety-500"></span>
                Listo para usar
            </p>
        </div>

        <div class="absolute bottom-5 right-5 z-20 rounded-2xl bg-brand-600 px-4 py-3 text-white shadow-glow">
            <p class="text-[10px] font-bold uppercase tracking-[0.2em] text-white">Servicio</p>
            <p class="mt-1 text-sm font-bold">A domicilio</p>
        </div>
    </div>
</template>
