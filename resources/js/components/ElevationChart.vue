<template>
  <div class="card border">
    <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <svg class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="20" x2="18" y2="10"/>
          <line x1="12" y1="20" x2="12" y2="4"/>
          <line x1="6" y1="20" x2="6" y2="14"/>
        </svg>
        <span class="fw-bold text-main">Terrain Elevation & Fresnel Profile</span>
      </div>
      <span class="badge bg-secondary font-monospace">High-Precision GIS Vector</span>
    </div>

    <div class="card-body p-3">
      <!-- Fallback message if no points -->
      <div v-if="!profilePoints || profilePoints.length === 0" class="text-center py-5 text-muted">
        <p class="mb-0">No terrain elevation profile data available. Click <strong>Calculate Link Analysis</strong> to generate profile.</p>
      </div>

      <!-- SVG Profile Visualization -->
      <div v-else class="elevation-svg-container position-relative">
        <svg 
          viewBox="0 0 700 320" 
          class="w-100 h-auto" 
          preserveAspectRatio="xMidYMid meet"
          @mousemove="onSvgMouseMove"
          @mouseleave="hoverPoint = null"
        >
          <!-- Background Grid & Axis Lines -->
          <g class="grid-lines">
            <line v-for="tick in yTicks" :key="'grid-y-' + tick.val" :x1="padL" :y1="tick.y" :x2="width - padR" :y2="tick.y" stroke="var(--border-color)" stroke-dasharray="3 3" opacity="0.6" />
            <line v-for="tick in xTicks" :key="'grid-x-' + tick.dist" :x1="tick.x" :y1="padT" :x2="tick.x" :y2="height - padB" stroke="var(--border-color)" stroke-dasharray="3 3" opacity="0.6" />
          </g>

          <!-- Y Axis Labels (Elevation AMSL m) -->
          <text v-for="tick in yTicks" :key="'lbl-y-' + tick.val" :x="padL - 8" :y="tick.y + 4" text-anchor="end" fill="var(--text-muted)" font-size="10" font-family="monospace">
            {{ tick.val }}m
          </text>

          <!-- X Axis Labels (Distance km) -->
          <text v-for="tick in xTicks" :key="'lbl-x-' + tick.dist" :x="tick.x" :y="height - padB + 16" text-anchor="middle" fill="var(--text-muted)" font-size="10" font-family="monospace">
            {{ tick.dist }}km
          </text>

          <!-- Axis Titles -->
          <text :x="width / 2" :y="height - 6" text-anchor="middle" fill="var(--text-primary)" font-size="11" font-weight="bold">Path Distance (km)</text>
          <text :x="14" :y="height / 2" text-anchor="middle" fill="var(--text-primary)" font-size="10" font-weight="bold" transform="rotate(-90 14 160)">Elevation AMSL (m)</text>

          <!-- Ground Elevation Filled Area Polygon -->
          <path :d="groundAreaPath" fill="rgba(100, 116, 139, 0.25)" stroke="none" />

          <!-- Raw Ground Elevation Line -->
          <path :d="groundLinePath" fill="none" stroke="#64748b" stroke-width="2" stroke-linejoin="round" />

          <!-- Effective Ground (Earth Curvature Drop) Line -->
          <path :d="effectiveGroundLinePath" fill="none" stroke="#ef4444" stroke-width="2" stroke-linejoin="round" />

          <!-- 60% Fresnel Zone Clearance Envelope Line -->
          <path :d="fresnelLinePath" fill="none" stroke="#0284c7" stroke-width="1.5" stroke-dasharray="5 4" />

          <!-- Direct Line of Sight (LOS Ray) Line -->
          <path :d="losLinePath" fill="none" stroke="#2563eb" stroke-width="3" />

          <!-- Site A Marker -->
          <circle :cx="mapX(0)" :cy="mapY(profilePoints[0].los_height)" r="5" fill="#2563eb" stroke="#ffffff" stroke-width="2" />

          <!-- Site B Marker -->
          <circle :cx="mapX(totalDistanceKm)" :cy="mapY(profilePoints[profilePoints.length - 1].los_height)" r="5" fill="#2563eb" stroke="#ffffff" stroke-width="2" />

          <!-- Interactive Hover Marker -->
          <g v-if="hoverPoint">
            <line :x1="mapX(hoverPoint.dist_km)" :y1="padT" :x2="mapX(hoverPoint.dist_km)" :y2="height - padB" stroke="var(--text-primary)" stroke-dasharray="2 2" stroke-width="1" />
            <circle :cx="mapX(hoverPoint.dist_km)" :cy="mapY(hoverPoint.ground_elevation)" r="5" fill="#64748b" stroke="#ffffff" stroke-width="1.5" />
            <circle :cx="mapX(hoverPoint.dist_km)" :cy="mapY(hoverPoint.los_height)" r="5" fill="#2563eb" stroke="#ffffff" stroke-width="1.5" />
          </g>
        </svg>

        <!-- SVG Legend Top Bar -->
        <div class="d-flex align-items-center justify-content-center flex-wrap gap-3 mt-2 pt-2 border-top fs-7">
          <div class="d-flex align-items-center gap-1">
            <span class="legend-box" style="background-color: #2563eb; height: 3px; width: 16px;"></span>
            <span class="text-main">LOS Ray</span>
          </div>
          <div class="d-flex align-items-center gap-1">
            <span class="legend-box" style="border-top: 2px dashed #0284c7; width: 16px;"></span>
            <span class="text-main">60% Fresnel Zone</span>
          </div>
          <div class="d-flex align-items-center gap-1">
            <span class="legend-box" style="background-color: #ef4444; height: 2px; width: 16px;"></span>
            <span class="text-main">Earth Drop Ground</span>
          </div>
          <div class="d-flex align-items-center gap-1">
            <span class="legend-box" style="background-color: #64748b; height: 2px; width: 16px;"></span>
            <span class="text-main">Ground AMSL</span>
          </div>
        </div>

        <!-- Hover Tooltip Box -->
        <div 
          v-if="hoverPoint" 
          class="position-absolute bg-dark text-white rounded p-2 shadow-lg fs-7 font-monospace"
          :style="{ left: hoverTooltipLeft + 'px', top: '10px', pointerEvents: 'none', zIndex: 10, border: '1px solid var(--border-color)' }"
        >
          <div><strong>Dist:</strong> {{ hoverPoint.dist_km }} km</div>
          <div><strong>Ground AMSL:</strong> {{ hoverPoint.ground_elevation }} m</div>
          <div><strong>Effective Ground:</strong> {{ hoverPoint.effective_ground }} m</div>
          <div><strong>LOS Ray Height:</strong> {{ hoverPoint.los_height }} m</div>
          <div><strong>Clearance Margin:</strong> {{ hoverPoint.clearance_margin }} m</div>
        </div>
      </div>

      <!-- Hidden Canvas for PDF export toDataURL fallback -->
      <canvas ref="chartCanvas" id="elevationCanvas" width="700" height="320" style="display: none;"></canvas>
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
      width: 700,
      height: 320,
      padL: 55,
      padR: 20,
      padT: 30,
      padB: 45,
      hoverPoint: null,
      hoverTooltipLeft: 60,
      chart: null
    };
  },
  computed: {
    totalDistanceKm() {
      if (!this.profilePoints || this.profilePoints.length === 0) return 1.0;
      return Math.max(0.001, this.profilePoints[this.profilePoints.length - 1].dist_km || 1.0);
    },

    allYValues() {
      if (!this.profilePoints || this.profilePoints.length === 0) return [0, 100];
      const arr = [];
      this.profilePoints.forEach(p => {
        arr.push(Number(p.ground_elevation || 0));
        arr.push(Number(p.effective_ground || 0));
        arr.push(Number(p.los_height || 0));
        arr.push(Number(p.fresnel_60_bottom || 0));
      });
      return arr;
    },

    minY() {
      return Math.floor(Math.min(...this.allYValues) - 5);
    },

    maxY() {
      return Math.ceil(Math.max(...this.allYValues) + 5);
    },

    rangeY() {
      return Math.max(10, this.maxY - this.minY);
    },

    plotW() {
      return this.width - this.padL - this.padR;
    },

    plotH() {
      return this.height - this.padT - this.padB;
    },

    yTicks() {
      const ticks = [];
      const count = 5;
      for (let i = 0; i <= count; i++) {
        const val = Math.round(this.minY + (this.rangeY / count) * i);
        const y = this.mapY(val);
        ticks.push({ val, y });
      }
      return ticks;
    },

    xTicks() {
      const ticks = [];
      const count = 5;
      for (let i = 0; i <= count; i++) {
        const dist = Number((this.totalDistanceKm / count * i).toFixed(1));
        const x = this.mapX(dist);
        ticks.push({ dist, x });
      }
      return ticks;
    },

    groundAreaPath() {
      if (!this.profilePoints || this.profilePoints.length === 0) return '';
      const bottomY = this.height - this.padB;
      const pts = this.profilePoints.map(p => `${this.mapX(p.dist_km)},${this.mapY(p.ground_elevation)}`).join(' L ');
      return `M ${this.mapX(0)},${bottomY} L ${pts} L ${this.mapX(this.totalDistanceKm)},${bottomY} Z`;
    },

    groundLinePath() {
      if (!this.profilePoints || this.profilePoints.length === 0) return '';
      return 'M ' + this.profilePoints.map(p => `${this.mapX(p.dist_km)},${this.mapY(p.ground_elevation)}`).join(' L ');
    },

    effectiveGroundLinePath() {
      if (!this.profilePoints || this.profilePoints.length === 0) return '';
      return 'M ' + this.profilePoints.map(p => `${this.mapX(p.dist_km)},${this.mapY(p.effective_ground)}`).join(' L ');
    },

    fresnelLinePath() {
      if (!this.profilePoints || this.profilePoints.length === 0) return '';
      return 'M ' + this.profilePoints.map(p => `${this.mapX(p.dist_km)},${this.mapY(p.fresnel_60_bottom)}`).join(' L ');
    },

    losLinePath() {
      if (!this.profilePoints || this.profilePoints.length === 0) return '';
      return 'M ' + this.profilePoints.map(p => `${this.mapX(p.dist_km)},${this.mapY(p.los_height)}`).join(' L ');
    }
  },
  mounted() {
    this.renderChartToHiddenCanvas();
  },
  watch: {
    profilePoints: {
      deep: true,
      immediate: true,
      handler() {
        this.$nextTick(() => {
          this.renderChartToHiddenCanvas();
        });
      }
    }
  },
  methods: {
    mapX(distKm) {
      return this.padL + (distKm / this.totalDistanceKm) * this.plotW;
    },

    mapY(elevM) {
      return this.padT + this.plotH - ((elevM - this.minY) / this.rangeY) * this.plotH;
    },

    onSvgMouseMove(e) {
      if (!this.profilePoints || this.profilePoints.length === 0) return;
      const rect = e.currentTarget.getBoundingClientRect();
      const clickX = e.clientX - rect.left;
      const scaleX = 700 / rect.width;
      const svgX = clickX * scaleX;

      const dist = ((svgX - this.padL) / this.plotW) * this.totalDistanceKm;
      let closest = this.profilePoints[0];
      let minDiff = 99999;

      this.profilePoints.forEach(p => {
        const diff = Math.abs(p.dist_km - dist);
        if (diff < minDiff) {
          minDiff = diff;
          closest = p;
        }
      });

      this.hoverPoint = closest;
      this.hoverTooltipLeft = Math.min(rect.width - 180, Math.max(10, clickX - 80));
    },

    renderChartToHiddenCanvas() {
      if (!this.profilePoints || this.profilePoints.length === 0) return;
      const canvas = this.$refs.chartCanvas || document.getElementById('elevationCanvas');
      if (!canvas) return;

      const existing = Chart.getChart(canvas);
      if (existing) {
        existing.destroy();
      }

      const labels = this.profilePoints.map(p => `${p.dist_km} km`);
      const groundElev = this.profilePoints.map(p => Number(p.ground_elevation || 0));
      const effectiveGround = this.profilePoints.map(p => Number(p.effective_ground || 0));
      const losHeight = this.profilePoints.map(p => Number(p.los_height || 0));
      const fresnel60Bottom = this.profilePoints.map(p => Number(p.fresnel_60_bottom || 0));

      const ctx = canvas.getContext('2d');
      try {
        this.chart = new Chart(ctx, {
          type: 'line',
          data: {
            labels,
            datasets: [
              { label: 'LOS Ray', data: losHeight, borderColor: '#2563eb', borderWidth: 3, fill: false },
              { label: '60% Fresnel Zone', data: fresnel60Bottom, borderColor: '#0284c7', borderWidth: 2, borderDash: [4, 4], fill: false },
              { label: 'Effective Ground', data: effectiveGround, borderColor: '#ef4444', borderWidth: 2, fill: false },
              { label: 'Ground AMSL', data: groundElev, borderColor: '#64748b', backgroundColor: 'rgba(100, 116, 139, 0.25)', borderWidth: 1.5, fill: true }
            ]
          },
          options: {
            responsive: false,
            animation: false,
            plugins: { legend: { display: true } }
          }
        });
      } catch (err) {
        console.warn('Canvas render fallback warning:', err);
      }
    }
  }
}
</script>

<style scoped>
.fs-7 {
  font-size: 0.75rem;
}
.legend-box {
  display: inline-block;
  vertical-align: middle;
}
</style>
