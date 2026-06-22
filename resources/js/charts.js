import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

export function complianceBarChart(chartData) {
    return {
        chart: null,

        init() {
            const ctx = this.$refs.complianceChart;
            if (!ctx) return;

            const labels = chartData.map(d => d.label);
            const scores = chartData.map(d => d.score);
            const colors = scores.map(s => s >= 70 ? '#10b981' : (s >= 40 ? '#f59e0b' : '#ef4444'));

            this.chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Skor Kepatuhan',
                        data: scores,
                        backgroundColor: colors,
                        borderColor: colors.map(c => c),
                        borderWidth: 1,
                        borderRadius: 8,
                        maxBarThickness: 48,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#071833',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 11 },
                            padding: 12,
                            cornerRadius: 12,
                            callbacks: {
                                label: function(ctx) {
                                    return `Skor: ${ctx.parsed.y}%`;
                                },
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            ticks: {
                                callback: v => v + '%',
                                font: { size: 11 },
                                color: '#667085',
                            },
                            grid: { color: '#e7eaf0' },
                        },
                        x: {
                            ticks: {
                                font: { size: 10 },
                                color: '#667085',
                                maxRotation: 45,
                            },
                            grid: { display: false },
                        },
                    },
                },
            });
        },

        destroy() {
            if (this.chart) this.chart.destroy();
        },
    };
}

export function scoreDistChart(distData) {
    return {
        chart: null,

        init() {
            const ctx = this.$refs.distChart;
            if (!ctx) return;

            this.chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Sesuai (≥70)', 'Perlu Ditinjau (40-69)', 'Tidak Sesuai (<40)'],
                    datasets: [{
                        data: [distData.high, distData.medium, distData.low],
                        backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                        borderWidth: 0,
                        spacing: 3,
                        borderRadius: 6,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 16,
                                usePointStyle: true,
                                pointStyleWidth: 10,
                                font: { size: 11 },
                                color: '#667085',
                            },
                        },
                        tooltip: {
                            backgroundColor: '#071833',
                            padding: 12,
                            cornerRadius: 12,
                            callbacks: {
                                label: function(ctx) {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
                                    return `${ctx.label}: ${ctx.parsed} bab (${pct}%)`;
                                },
                            },
                        },
                    },
                },
            });
        },

        destroy() {
            if (this.chart) this.chart.destroy();
        },
    };
}

export function parseComparisonChart(docChars, regChars) {
    return {
        chart: null,

        init() {
            const ctx = this.$refs.parseChart;
            if (!ctx) return;

            this.chart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Dokumen Review', 'Regulasi Acuan'],
                    datasets: [{
                        data: [docChars, regChars],
                        backgroundColor: ['#c99a3e', '#10b981'],
                        borderWidth: 0,
                        spacing: 3,
                        borderRadius: 6,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: '60%',
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#071833',
                            padding: 12,
                            cornerRadius: 12,
                            callbacks: {
                                label: function(ctx) {
                                    const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                    const pct = total > 0 ? Math.round((ctx.parsed / total) * 100) : 0;
                                    return `${ctx.label}: ${ctx.parsed.toLocaleString()} char (${pct}%)`;
                                },
                            },
                        },
                    },
                },
            });
        },

        destroy() {
            if (this.chart) this.chart.destroy();
        },
    };
}
