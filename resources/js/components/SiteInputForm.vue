<template>
  <div class="row g-3">
    <!-- Site A Card -->
    <div class="col-md-6">
      <div class="card h-100 border-top border-primary border-4 shadow-sm">
        <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between">
          <span class="fw-bold brand-blue">Site A (Local Node)</span>
          <span class="badge bg-primary">Transmitter / Node A</span>
        </div>
        <div class="card-body p-3">
          <div class="mb-2">
            <label class="form-label fw-semibold small mb-1">Site Name</label>
            <input type="text" v-model="siteA.name" class="form-control form-control-sm" placeholder="e.g. Tower Alpha" />
          </div>

          <div class="row g-2 mb-2">
            <div class="col-6">
              <label class="form-label fw-semibold small mb-1">Latitude (°)</label>
              <input 
                type="number" 
                step="0.00000001" 
                v-model.number="siteA.latitude" 
                @blur="fetchElevation('A')"
                @change="fetchElevation('A')"
                class="form-control form-control-sm font-monospace" 
                placeholder="12.97159870"
              />
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold small mb-1">Longitude (°)</label>
              <input 
                type="number" 
                step="0.00000001" 
                v-model.number="siteA.longitude" 
                @blur="fetchElevation('A')"
                @change="fetchElevation('A')"
                class="form-control form-control-sm font-monospace" 
                placeholder="77.59456270"
              />
            </div>
          </div>

          <div class="row g-2">
            <div class="col-6">
              <label class="form-label fw-semibold small mb-1">Tower Height (m)</label>
              <input type="number" step="0.1" v-model.number="siteA.tower_height" class="form-control form-control-sm" placeholder="30.0" />
            </div>
            <div class="col-6">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <label class="form-label fw-semibold small mb-0">Ground Elevation (m)</label>
                <button 
                  type="button" 
                  class="btn btn-link p-0 text-decoration-none fs-7 fw-semibold" 
                  @click="fetchElevation('A', true)" 
                  :disabled="loadingA"
                >
                  <span v-if="loadingA" class="spinner-border spinner-border-sm me-1"></span>
                  <span v-else>&circlearrowright; Fetch AMSL</span>
                </button>
              </div>
              <div class="input-group input-group-sm">
                <input 
                  type="number" 
                  step="0.1" 
                  v-model.number="siteA.ground_elevation" 
                  class="form-control font-monospace" 
                  placeholder="920.0" 
                />
                <span class="input-group-text bg-light text-muted small">AMSL</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Site B Card -->
    <div class="col-md-6">
      <div class="card h-100 border-top border-dark border-4 shadow-sm">
        <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between">
          <span class="fw-bold text-dark">Site B (Remote Node)</span>
          <span class="badge bg-dark">Receiver / Node B</span>
        </div>
        <div class="card-body p-3">
          <div class="mb-2">
            <label class="form-label fw-semibold small mb-1">Site Name</label>
            <input type="text" v-model="siteB.name" class="form-control form-control-sm" placeholder="e.g. Tower Bravo" />
          </div>

          <div class="row g-2 mb-2">
            <div class="col-6">
              <label class="form-label fw-semibold small mb-1">Latitude (°)</label>
              <input 
                type="number" 
                step="0.00000001" 
                v-model.number="siteB.latitude" 
                @blur="fetchElevation('B')"
                @change="fetchElevation('B')"
                class="form-control form-control-sm font-monospace" 
                placeholder="13.03582450"
              />
            </div>
            <div class="col-6">
              <label class="form-label fw-semibold small mb-1">Longitude (°)</label>
              <input 
                type="number" 
                step="0.00000001" 
                v-model.number="siteB.longitude" 
                @blur="fetchElevation('B')"
                @change="fetchElevation('B')"
                class="form-control form-control-sm font-monospace" 
                placeholder="77.62514820"
              />
            </div>
          </div>

          <div class="row g-2">
            <div class="col-6">
              <label class="form-label fw-semibold small mb-1">Tower Height (m)</label>
              <input type="number" step="0.1" v-model.number="siteB.tower_height" class="form-control form-control-sm" placeholder="30.0" />
            </div>
            <div class="col-6">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <label class="form-label fw-semibold small mb-0">Ground Elevation (m)</label>
                <button 
                  type="button" 
                  class="btn btn-link p-0 text-decoration-none fs-7 fw-semibold" 
                  @click="fetchElevation('B', true)" 
                  :disabled="loadingB"
                >
                  <span v-if="loadingB" class="spinner-border spinner-border-sm me-1"></span>
                  <span v-else>&circlearrowright; Fetch AMSL</span>
                </button>
              </div>
              <div class="input-group input-group-sm">
                <input 
                  type="number" 
                  step="0.1" 
                  v-model.number="siteB.ground_elevation" 
                  class="form-control font-monospace" 
                  placeholder="915.0" 
                />
                <span class="input-group-text bg-light text-muted small">AMSL</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
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
      loadingA: false,
      loadingB: false,
      lastLatA: null,
      lastLonA: null,
      lastLatB: null,
      lastLonB: null
    };
  },
  methods: {
    async fetchElevation(siteKey, force = false) {
      const site = siteKey === 'A' ? this.siteA : this.siteB;
      const lat = Number(site.latitude);
      const lon = Number(site.longitude);

      if (!lat || !lon) return;

      if (!force) {
        if (siteKey === 'A' && lat === this.lastLatA && lon === this.lastLonA) return;
        if (siteKey === 'B' && lat === this.lastLatB && lon === this.lastLonB) return;
      }

      if (siteKey === 'A') {
        this.loadingA = true;
        this.lastLatA = lat;
        this.lastLonA = lon;
      } else {
        this.loadingB = true;
        this.lastLatB = lat;
        this.lastLonB = lon;
      }

      try {
        const url = `/api/elevation/lookup?latitude=${lat}&longitude=${lon}`;
        const res = await fetch(url);
        const data = await res.json();

        if (res.ok && data.status === 'success' && data.ground_elevation !== undefined) {
          site.ground_elevation = Number(data.ground_elevation);
        }
      } catch (err) {
        console.warn('Elevation lookup error:', err);
      } finally {
        if (siteKey === 'A') this.loadingA = false;
        if (siteKey === 'B') this.loadingB = false;
      }
    }
  }
}
</script>

<style scoped>
.fs-7 {
  font-size: 0.75rem;
}
</style>
