(function () {
    const data = window.dashboardData || { pie: { labels: [], values: [] }, line: { labels: [], values: [] } };

    const pieCtx = document.getElementById('expensePie');
    if (pieCtx) {
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: data.pie.labels,
                datasets: [{
                    data: data.pie.values,
                    backgroundColor: ['#ff8a65', '#ffd166', '#29c4a9', '#56a8ff', '#9b8cff', '#ff6b7a'],
                    borderColor: '#0a0f14',
                    borderWidth: 2,
                }],
            },
            options: {
                plugins: {
                    legend: {
                        labels: { color: '#e7f0ff' },
                    },
                },
            },
        });
    }

    const lineCtx = document.getElementById('expenseLine');
    if (lineCtx) {
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: data.line.labels,
                datasets: [{
                    label: 'Expense',
                    data: data.line.values,
                    borderColor: '#ff8a65',
                    backgroundColor: 'rgba(255, 138, 101, 0.2)',
                    fill: true,
                    tension: 0.35,
                }],
            },
            options: {
                scales: {
                    x: {
                        ticks: { color: '#93a6bf' },
                        grid: { color: 'rgba(255,255,255,0.05)' },
                    },
                    y: {
                        ticks: { color: '#93a6bf' },
                        grid: { color: 'rgba(255,255,255,0.05)' },
                    },
                },
                plugins: {
                    legend: {
                        labels: { color: '#e7f0ff' },
                    },
                },
            },
        });
    }
})();
