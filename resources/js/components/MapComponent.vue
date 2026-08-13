<template>
  <div class="card border">
    <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between">
      <span class="fw-bold text-dark">OpenStreetMap Path Route</span>
      <span class="badge bg-light text-dark border">Leaflet.js &bull; OpenStreetMap</span>
    </div>
    <div class="card-body p-0">
      <div id="leaflet-map" class="map-container"></div>
    </div>
  </div>
</template>

<script>
import L from 'leaflet';

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
      markerA: null,
      markerB: null,
      polyline: null
    };
  },
  mounted() {
    this.initMap();
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
        preferCanvas: true // Render markers and polylines on canvas for easy image export
      }).setView([latA, lonA], 12);

      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        crossOrigin: 'anonymous',
        attribution: '&copy; OpenStreetMap contributors'
      }).addTo(this.map);

      this.updateMap();
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

      // Create Custom Canvas Circle Markers or standard Leaflet markers
      this.markerA = L.circleMarker([latA, lonA], {
        radius: 8,
        fillColor: '#1e40af',
        color: '#ffffff',
        weight: 2,
        fillOpacity: 1
      }).addTo(this.map)
        .bindPopup(`<b>Site A: ${this.siteA.name || 'Node A'}</b><br>Lat: ${latA.toFixed(8)}°<br>Lon: ${lonA.toFixed(8)}°<br>Tower: ${this.siteA.tower_height}m`);

      this.markerB = L.circleMarker([latB, lonB], {
        radius: 8,
        fillColor: '#0f172a',
        color: '#ffffff',
        weight: 2,
        fillOpacity: 1
      }).addTo(this.map)
        .bindPopup(`<b>Site B: ${this.siteB.name || 'Node B'}</b><br>Lat: ${latB.toFixed(8)}°<br>Lon: ${lonB.toFixed(8)}°<br>Tower: ${this.siteB.tower_height}m`);

      const latlngs = [[latA, lonA], [latB, lonB]];
      this.polyline = L.polyline(latlngs, { color: '#1e40af', weight: 4, opacity: 0.9, dashArray: '6, 6' }).addTo(this.map);

      const bounds = L.latLngBounds(latlngs);
      this.map.fitBounds(bounds, { padding: [40, 40] });
    }
  }
}
</script>
