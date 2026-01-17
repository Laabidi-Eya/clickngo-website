document.addEventListener('DOMContentLoaded', function () {
  const filter = document.getElementById('filterPeriod');
  const canvas = document.getElementById('salesWave');
  const loadingSpinner = document.getElementById('loading');
  const downloadButton = document.getElementById('downloadChart');
  let chart;
  
  if (!filter || !canvas) {
    console.error("💥 Élément introuvable (canvas ou select)");
    return;
  }
  
  const ctx = canvas.getContext('2d');
  
  const savedPeriod = localStorage.getItem('selectedPeriod');
  if (savedPeriod) {
    filter.value = savedPeriod;
  }
  
  function fetchAndUpdateChart(period) {
    console.log("🔄 Période sélectionnée :", period);

    const colorsByPeriod = {
      '7 DAY': '#ff6b6b',
      '1 MONTH': '#ff8c42',
      '4 MONTH': '#8e44ad',
      '6 MONTH': '#ff7f50',
      '1 YEAR': '#ff6347',
      '3 YEAR': '#ff4500'
    };
  
    const color = colorsByPeriod[period] || '#ff6b6b';
    const bgColor = color.replace(')', ', 0.2)').replace('rgb', 'rgba');
    
    loadingSpinner.style.display = 'block'; // 🔥 Start loading
    if (downloadButton) downloadButton.style.display = 'none'; // hide download button during load

    fetch('load_inscriptions.php?period=' + encodeURIComponent(period))
      .then(response => response.json())
      .then(data => {
        loadingSpinner.style.display = 'none'; // 🔥 Stop loading
        console.log("Labels: ", data.labels);
        console.log("Totaux: ", data.totals);

        if (chart) chart.destroy();

        if (data.labels.length === 0 || data.totals.every(val => val === 0)) {
          ctx.clearRect(0, 0, canvas.width, canvas.height);
          ctx.font = "bold 16px Poppins, Arial";
          ctx.fillStyle = "#999";
          ctx.textAlign = "center";
          ctx.fillText("📉 Aucune inscription trouvée", canvas.width / 2, canvas.height / 2);
          if (downloadButton) downloadButton.style.display = 'none';
          return;
        }

        chart = new Chart(ctx, {
          type: 'line',
          data: {
            labels: data.labels,
            datasets: [{
              label: 'Inscriptions',
              data: data.totals,
              borderColor: color,
              backgroundColor: bgColor,
              fill: true,
              tension: 0.4,
              pointBackgroundColor: '#fff',
              pointBorderColor: color,
              pointBorderWidth: 2,
              pointHoverBackgroundColor: 'orange',
              pointHoverBorderColor: 'red',
              pointHoverRadius: 6,
              pointRadius: 4
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: {
              duration: 800,
              easing: 'easeInOutQuart'
            },
            plugins: {
              legend: { display: false }
            },
            scales: {
              y: {
                beginAtZero: true,
                ticks: {
                  stepSize: 1,
                  callback: function (value) {
                    return Number.isInteger(value) ? value : '';
                  }
                }
              },
              x: {
                grid: { display: false }
              }
            }
          }
        });

        if (downloadButton) downloadButton.style.display = 'inline-block'; // ✅ show download if success
      })
      .catch(error => {
        loadingSpinner.style.display = 'none';
        console.error('Erreur AJAX:', error);
      });
  }

  fetchAndUpdateChart(filter.value);
  
  filter.addEventListener('change', function () {
    const selected = this.value;
    localStorage.setItem('selectedPeriod', selected);
    fetchAndUpdateChart(selected);
  });

  if (downloadButton) {
    downloadButton.addEventListener('click', function () {
      if (!chart) return;
      const link = document.createElement('a');
      link.href = canvas.toDataURL('image/png');
      link.download = 'inscriptions_chart.png';
      link.click();
    });
  }
});
