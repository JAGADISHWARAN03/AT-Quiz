
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('quizChart').getContext('2d');
    const quizChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: window.quiz_chart_categories,
            datasets: [{
                label: 'Number of Quizzes',
                data: window.quiz_chart_counts,
                borderColor: 'rgba(239, 68, 68, 1)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                fill: true,
                tension: 0.4,
                pointRadius: 0,
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    precision: 0,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)',
                    },
                    ticks: {
                        font: {
                            size: 12,
                        },
                    },
                },
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        font: {
                            size: 12,
                        },
                    },
                }
            },
            plugins: {
                legend: {
                    display: false,
                },
            },
        }
    });
});
