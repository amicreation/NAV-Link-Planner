<template>
  <div class="card border">
    <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <svg class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <polygon points="1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6"/>
          <line x1="8" y1="2" x2="8" y2="18"/>
          <line x1="16" y1="6" x2="16" y2="22"/>
        </svg>
        <span class="fw-bold text-main">Path GIS Route Map</span>
      </div>
      <span class="badge bg-secondary font-monospace">Leaflet.js &bull; OpenStreetMap</span>
    </div>
    <div class="card-body p-0">
      <div id="leaflet-map" class="map-container"></div>
    </div>
  </div>
</template>

<script>
import L from 'leaflet';
import { decimalToDms } from '../utils/coordinates.js';

export default {
  props: {
    siteA: {
      type: Object,
      required: true
    },
    siteB: {
      type: Object,
      required: true
    }
  },
  data() {
    return {
      map: null,
      tileLayer: null,
      markerA: null,
      markerB: null,
      polyline: null,
      observer: null
    };
  },
  mounted() {
    this.initMap();
    this.setupThemeObserver();
  },
  beforeUnmount() {
    if (this.observer) {
      this.observer.disconnect();
    }
  },
  watch: {
    siteA: {
      deep: true,
      handler() { this.updateMap(); }
    },
    siteB: {
      deep: true,
      handler() { this.updateMap(); }
    }
  },
  methods: {
    initMap() {
      const latA = this.siteA.latitude || 12.9715987;
      const lonA = this.siteA.longitude || 77.5945627;

      this.map = L.map('leaflet-map', {
        preferCanvas: true
      }).setView([latA, lonA], 12);

      this.applyTileLayer();
      this.updateMap();
    },

    applyTileLayer() {
      if (!this.map) return;
      if (this.tileLayer) {
        this.map.removeLayer(this.tileLayer);
      }

      const isDark = document.body.getAttribute('data-theme') !== 'light';
      const tileUrl = isDark 
        ? 'https://{s}.basemaps.cartocdn.com/dark_all/{z}/{x}/{y}{r}.png'
        : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

      this.tileLayer = L.tileLayer(tileUrl, {
        maxZoom: 19,
        crossOrigin: 'anonymous',
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(this.map);
    },

    setupThemeObserver() {
      this.observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.type === 'attributes' && mutation.attributeName === 'data-theme') {
            this.applyTileLayer();
          }
        });
      });
      this.observer.observe(document.body, { attributes: true });
    },

    updateMap() {
      if (!this.map) return;

      const latA = Number(this.siteA.latitude) || 0;
      const lonA = Number(this.siteA.longitude) || 0;
      const latB = Number(this.siteB.latitude) || 0;
      const lonB = Number(this.siteB.longitude) || 0;

      if (this.markerA) this.map.removeLayer(this.markerA);
      if (this.markerB) this.map.removeLayer(this.markerB);
      if (this.polyline) this.map.removeLayer(this.polyline);

      const dmsA_lat = decimalToDms(latA, false).formatted;
      const dmsA_lon = decimalToDms(lonA, true).formatted;
      const dmsB_lat = decimalToDms(latB, false).formatted;
      const dmsB_lon = decimalToDms(lonB, true).formatted;

      // Create Custom Canvas Circle Markers
      this.markerA = L.circleMarker([latA, lonA], {
        radius: 9,
        fillColor: '#2563eb',
        color: '#ffffff',
        weight: 2,
        fillOpacity: 1
      }).addTo(this.map)
        .bindPopup(`<b>Site A: ${this.siteA.name || 'Node A'}</b><br>Lat: ${latA.toFixed(8)}° <span style="font-size:0.85em;color:#94a3b8;">(${dmsA_lat})</span><br>Lon: ${lonA.toFixed(8)}° <span style="font-size:0.85em;color:#94a3b8;">(${dmsA_lon})</span><br>Tower Height: ${this.siteA.tower_height}m`);

      this.markerB = L.circleMarker([latB, lonB], {
        radius: 9,
        fillColor: '#8b5cf6',
        color: '#ffffff',
        weight: 2,
        fillOpacity: 1
      }).addTo(this.map)
        .bindPopup(`<b>Site B: ${this.siteB.name || 'Node B'}</b><br>Lat: ${latB.toFixed(8)}° <span style="font-size:0.85em;color:#94a3b8;">(${dmsB_lat})</span><br>Lon: ${lonB.toFixed(8)}° <span style="font-size:0.85em;color:#94a3b8;">(${dmsB_lon})</span><br>Tower Height: ${this.siteB.tower_height}m`);

      const latlngs = [[latA, lonA], [latB, lonB]];
      this.polyline = L.polyline(latlngs, { color: '#0284c7', weight: 4, opacity: 0.9, dashArray: '6, 6' }).addTo(this.map);

      const bounds = L.latLngBounds(latlngs);
      this.map.fitBounds(bounds, { padding: [40, 40] });
    }
  }
}
</script>

<style scoped>
</style>
