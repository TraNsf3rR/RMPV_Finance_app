(function () {
    const data = window.dashboardData || { pie: { labels: [], values: [] }, line: { labels: [], income: [], expense: [] } };
    const formatEuro = (value) => `€${Number(value).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    let pieChart = null;
    let lineChart = null;

    function getThemeColors() {
        const styles = getComputedStyle(document.documentElement);
        const get = (name) => styles.getPropertyValue(name).trim();

        return {
            text: get('--chart-text'),
            muted: get('--chart-muted'),
            grid: get('--chart-grid'),
            surface: get('--chart-surface'),
            income: get('--chart-income'),
            incomeFill: get('--chart-income-fill'),
            expense: get('--chart-expense'),
            expenseFill: get('--chart-expense-fill'),
            categories: [
                get('--chart-category-1'),
                get('--chart-category-2'),
                get('--chart-category-3'),
                get('--chart-category-4'),
                get('--chart-category-5'),
                get('--chart-category-6'),
            ],
        };
    }

    const pieCtx = document.getElementById('expensePie');
    if (pieCtx) {
        const colors = getThemeColors();
        pieChart = new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: data.pie.labels,
                datasets: [{
                    data: data.pie.values,
                    backgroundColor: colors.categories,
                    borderColor: colors.surface,
                    borderWidth: 2,
                }],
            },
            options: {
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: colors.text },
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
        const colors = getThemeColors();
        lineChart = new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: data.line.labels,
                datasets: [
                    {
                        label: 'Expense',
                        data: data.line.expense,
                        borderColor: colors.expense,
                        backgroundColor: colors.expenseFill,
                        fill: true,
                        tension: 0.35,
                    },
                    {
                        label: 'Income',
                        data: data.line.income,
                        borderColor: colors.income,
                        backgroundColor: colors.incomeFill,
                        fill: true,
                        tension: 0.35,
                    }
                ],
            },
            options: {
                maintainAspectRatio: false,
                scales: {
                    x: {
                        ticks: { color: colors.muted },
                        grid: { color: colors.grid },
                    },
                    y: {
                        ticks: { color: colors.muted },
                        grid: { color: colors.grid },
                    },
                },
                plugins: {
                    legend: {
                        labels: { color: colors.text },
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

    document.addEventListener('finance-theme-change', function () {
        const colors = getThemeColors();

        if (pieChart) {
            pieChart.data.datasets[0].backgroundColor = colors.categories;
            pieChart.data.datasets[0].borderColor = colors.surface;
            pieChart.options.plugins.legend.labels.color = colors.text;
            pieChart.update('none');
        }

        if (lineChart) {
            lineChart.data.datasets[0].borderColor = colors.expense;
            lineChart.data.datasets[0].backgroundColor = colors.expenseFill;
            lineChart.data.datasets[1].borderColor = colors.income;
            lineChart.data.datasets[1].backgroundColor = colors.incomeFill;
            lineChart.options.scales.x.ticks.color = colors.muted;
            lineChart.options.scales.x.grid.color = colors.grid;
            lineChart.options.scales.y.ticks.color = colors.muted;
            lineChart.options.scales.y.grid.color = colors.grid;
            lineChart.options.plugins.legend.labels.color = colors.text;
            lineChart.update('none');
        }
    });
})();
