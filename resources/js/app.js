import './bootstrap';

import Chart from 'chart.js/auto';
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

document.querySelectorAll('[data-dashboard-chart]').forEach((canvas) => {
    const source = document.getElementById(canvas.dataset.chartSource);

    if (! source) {
        return;
    }

    new Chart(canvas, JSON.parse(source.textContent));
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') {
        return;
    }

    const quickExitTarget = document.querySelector('[data-quick-exit-url]')?.getAttribute('data-quick-exit-url');

    if (quickExitTarget) {
        window.location.replace(quickExitTarget);
    }
});
