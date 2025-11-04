function loadChartData(labels, data) {
    const ctx = document.getElementById('myChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Order Count',
                    data: data,
                    backgroundColor: 'rgba(247, 160, 114, 0.6)',
                    borderColor: 'rgba(247, 160, 114, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }
}