<template>
  <div class="card border">
    <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between">
      <span class="fw-bold text-dark">Terrain & Line of Sight Elevation Profile</span>
      <span class="badge bg-light text-dark border">Chart.js Engine</span>
    </div>
    <div class="card-body p-3">
      <div class="chart-container">
        <canvas id="elevationCanvas"></canvas>
      </div>
    </div>
  </div>
</template>

<script>
import { Chart, registerables } from 'chart.js';
Chart.register(...registerables);

export default {
  props: {
    profilePoints: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      chart: null
    };
  },
  mounted() {
    this.renderChart();
  },
  watch: {
    profilePoints: {
      deep: true,
      handler() { this.renderChart(); }
    }
  },
  methods: {
    renderChart() {
      if (!this.profilePoints || this.profilePoints.length === 0) return;

      const isDark = document.body.getAttribute('data-theme') === 'dark';
      const textColor = isDark ? '#94a3b8' : '#475569';
      const gridColor = isDark ? '#233358' : '#f1f5f9';

      const labels = this.profilePoints.map(p => `${p.dist_km} km`);
      const groundElev = this.profilePoints.map(p => p.ground_elevation);
      const effectiveGround = this.profilePoints.map(p => p.effective_ground);
      const losHeight = this.profilePoints.map(p => p.los_height);
      const fresnel60Bottom = this.profilePoints.map(p => p.fresnel_60_bottom);

      const ctx = document.getElementById('elevationCanvas').getContext('2d');

      if (this.chart) {
        this.chart.destroy();
      }

      this.chart = new Chart(ctx, {
        type: 'line',
        data: {
          labels: labels,
          datasets: [
            {
              label: 'Line of Sight (LOS Ray)',
              data: losHeight,
              borderColor: '#1e40af',
              borderWidth: 2.5,
              pointRadius: 0,
              tension: 0.1,
              fill: false
            },
            {
              label: '60% Fresnel Zone Clearance',
              data: fresnel60Bottom,
              borderColor: '#0284c7',
              borderWidth: 1.5,
              borderDash: [4, 4],
              pointRadius: 0,
              tension: 0.1,
              fill: false
            },
            {
              label: 'Effective Ground (with Earth Curvature)',
              data: effectiveGround,
              borderColor: '#dc2626',
              borderWidth: 1.5,
              pointRadius: 0,
              tension: 0.1,
              fill: false
            },
            {
              label: 'Raw Ground Elevation (m)',
              data: groundElev,
              borderColor: '#64748b',
              backgroundColor: isDark ? 'rgba(100, 116, 139, 0.3)' : 'rgba(100, 116, 139, 0.15)',
              borderWidth: 1.5,
              pointRadius: 0,
              tension: 0.2,
              fill: true
            }
          ]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          interaction: {
            mode: 'index',
            intersect: false
          },
          plugins: {
            legend: {
              position: 'top',
              labels: {
                boxWidth: 12,
                color: textColor,
                font: { size: 11, family: 'Inter' }
              }
            },
            tooltip: {
              callbacks: {
                label: (context) => `${context.dataset.label}: ${context.parsed.y.toFixed(2)} m`
              }
            }
          },
          scales: {
            x: {
              title: {
                display: true,
                text: 'Path Distance (km)',
                color: textColor,
                font: { size: 11, weight: 'bold' }
              },
              ticks: { color: textColor },
              grid: { color: gridColor }
            },
            y: {
              title: {
                display: true,
                text: 'Elevation AMSL (Meters)',
                color: textColor,
                font: { size: 11, weight: 'bold' }
              },
              ticks: { color: textColor },
              grid: { color: gridColor }
            }
          }
        }
      });
    }
  }
}
</script>
