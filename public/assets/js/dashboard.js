(function () {
    const data = window.dashboardData || { pie: { labels: [], values: [] }, line: { labels: [], income: [], expense: [] } };
    const formatEuro = (value) => `€${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

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
                    tooltip: {
                        callbacks: {
                            label(context) {
                                const label = context.label || '';
                                return `${label}: ${formatEuro(context.raw)}`;
                            },
                        },
                    },
                },
            },
        });
    }

    const lineCtx = document.getElementById('incomeExpenseLine');
    if (lineCtx) {
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: data.line.labels,
                datasets: [
                    {
                        label: 'Expense',
                        data: data.line.expense,
                        borderColor: '#ff8a65',
                        backgroundColor: 'rgba(255, 138, 101, 0.2)',
                        fill: true,
                        tension: 0.35,
                    },
                    {
                        label: 'Income',
                        data: data.line.income,
                        borderColor: '#29c4a9',
                        backgroundColor: 'rgba(41, 196, 169, 0.2)',
                        fill: true,
                        tension: 0.35,
                    }
                ],
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
                    tooltip: {
                        callbacks: {
                            label(context) {
                                return `${context.dataset.label}: ${formatEuro(context.raw)}`;
                            },
                        },
                    },
                },
            },
        });
    }
})();
