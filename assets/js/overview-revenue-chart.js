(() => {
    if (typeof Chart === 'undefined') {
        return;
    }

    const monthLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const styles = getComputedStyle(document.documentElement);
    const foreground = styles.getPropertyValue('--foreground').trim() || '#18181b';
    const muted = styles.getPropertyValue('--muted-foreground').trim() || '#71717a';
    const border = styles.getPropertyValue('--border').trim() || '#e4e4e7';
    const primary = styles.getPropertyValue('--primary').trim() || '#18181b';
    const chart1 = styles.getPropertyValue('--chart-1').trim() || primary;

    const formatMoney = (value) => 'R ' + Number(value).toLocaleString('en-ZA', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });

    const parseJsonAttr = (element, name) => {
        try {
            return JSON.parse(element.getAttribute(name) || '[]');
        } catch (error) {
            return [];
        }
    };

    const eventCanvas = document.querySelector('[data-overview-revenue-chart]');
    if (eventCanvas) {
        const events = parseJsonAttr(eventCanvas, 'data-chart-events');
        if (Array.isArray(events) && events.length > 0) {
            const monthCounts = Array.from({ length: 12 }, () => 0);
            const monthTotals = Array.from({ length: 12 }, () => 0);
            const points = events.map((event) => {
                const month = Math.max(1, Math.min(12, Number(event.month) || 1));
                const index = month - 1;
                const revenue = Number(event.revenue) || 0;
                const offset = monthCounts[index];
                monthCounts[index] += 1;
                monthTotals[index] += revenue;
                const jitter = offset === 0
                    ? 0
                    : (offset % 2 === 0 ? 1 : -1) * Math.ceil(offset / 2) * 0.18;

                return {
                    x: month + jitter,
                    y: revenue,
                    client: typeof event.client === 'string' && event.client.trim() !== ''
                        ? event.client.trim()
                        : 'No client',
                    title: typeof event.title === 'string' ? event.title : '',
                    monthIndex: index,
                };
            });

            const shadedLine = monthTotals.map((total, index) => ({
                x: index + 1,
                y: total,
            }));

            const clientLabelPlugin = {
                id: 'clientRevenueLabels',
                afterDatasetsDraw(chart) {
                    const { ctx } = chart;
                    const meta = chart.getDatasetMeta(1);
                    if (!meta || !meta.data) {
                        return;
                    }

                    ctx.save();
                    ctx.font = '500 12px Inter, ui-sans-serif, system-ui, sans-serif';
                    ctx.fillStyle = foreground;
                    ctx.textAlign = 'left';
                    ctx.textBaseline = 'middle';

                    meta.data.forEach((element, index) => {
                        const point = points[index];
                        if (!point || !element) {
                            return;
                        }
                        ctx.fillText(point.client + ' · ' + formatMoney(point.y), element.x + 10, element.y);
                    });

                    ctx.restore();
                },
            };

            new Chart(eventCanvas, {
                data: {
                    datasets: [
                        {
                            type: 'line',
                            label: 'Monthly revenue',
                            data: shadedLine,
                            borderColor: primary,
                            backgroundColor: 'oklch(0.21 0.006 285.885 / 0.12)',
                            borderWidth: 2,
                            pointRadius: 0,
                            pointHoverRadius: 0,
                            fill: 'origin',
                            tension: 0.35,
                            order: 2,
                        },
                        {
                            type: 'scatter',
                            label: 'Event revenue',
                            data: points,
                            backgroundColor: chart1,
                            borderColor: primary,
                            borderWidth: 1,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            order: 1,
                        },
                    ],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    layout: {
                        padding: {
                            right: 160,
                            top: 12,
                        },
                    },
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            filter(item) {
                                return item.datasetIndex === 1;
                            },
                            callbacks: {
                                title(items) {
                                    const point = items[0]?.raw;
                                    return point?.title || 'Event';
                                },
                                label(item) {
                                    const point = item.raw;
                                    return [
                                        'Client: ' + (point?.client || 'No client'),
                                        'Revenue: ' + formatMoney(point?.y || 0),
                                        'Month: ' + monthLabels[point?.monthIndex || 0],
                                    ];
                                },
                            },
                        },
                    },
                    scales: {
                        x: {
                            type: 'linear',
                            min: 0.5,
                            max: 12.5,
                            ticks: {
                                stepSize: 1,
                                color: muted,
                                callback(value) {
                                    const month = Math.round(Number(value));
                                    if (month < 1 || month > 12) {
                                        return '';
                                    }
                                    return monthLabels[month - 1];
                                },
                            },
                            grid: {
                                color: border,
                            },
                            title: {
                                display: true,
                                text: 'Month',
                                color: muted,
                            },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: muted,
                                callback(value) {
                                    return formatMoney(value);
                                },
                            },
                            grid: {
                                color: border,
                            },
                            title: {
                                display: true,
                                text: 'Revenue',
                                color: muted,
                            },
                        },
                    },
                },
                plugins: [clientLabelPlugin],
            });
        }
    }

    const totalCanvas = document.querySelector('[data-overview-total-revenue-chart]');
    if (!totalCanvas) {
        return;
    }

    const monthlyTotals = parseJsonAttr(totalCanvas, 'data-monthly-totals');
    if (!Array.isArray(monthlyTotals) || monthlyTotals.length === 0) {
        return;
    }

    new Chart(totalCanvas, {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [{
                label: 'Total revenue',
                data: monthlyTotals.map((value) => Number(value) || 0),
                borderColor: primary,
                backgroundColor: 'transparent',
                borderWidth: 2,
                pointRadius: 3,
                pointHoverRadius: 5,
                pointBackgroundColor: primary,
                tension: 0.25,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    callbacks: {
                        label(item) {
                            return formatMoney(item.parsed.y || 0);
                        },
                    },
                },
            },
            scales: {
                x: {
                    ticks: {
                        color: muted,
                        maxRotation: 0,
                        autoSkip: true,
                        maxTicksLimit: 6,
                    },
                    grid: {
                        display: false,
                    },
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: muted,
                        callback(value) {
                            return formatMoney(value);
                        },
                        maxTicksLimit: 5,
                    },
                    grid: {
                        color: border,
                    },
                },
            },
        },
    });
})();
