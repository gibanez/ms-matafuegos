import { rmSync } from 'node:fs';
import { fileURLToPath, URL } from 'node:url';

const targets = [
    '../public/site-assets',
    '../public/brand',
    '../public/index.html',
    '../public/favicon.svg',
    '../public/robots.txt',
];

for (const target of targets) {
    rmSync(fileURLToPath(new URL(target, import.meta.url)), {
        recursive: true,
        force: true,
    });
}
