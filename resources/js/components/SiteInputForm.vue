<template>
  <div>
    <!-- Top Action Bar for Coordinate Helpers -->
    <div class="d-flex align-items-center justify-content-between mb-3 bg-card p-2 px-3 rounded-3 border border-secondary-subtle">
      <div class="d-flex align-items-center gap-2">
        <svg class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="10" r="3"/>
          <path d="M12 21s-7-5.3-7-11a7 7 0 0 1 14 0c0 5.7-7 11-7 11z"/>
        </svg>
        <span class="fw-semibold text-white small">Site Geodetic Coordinates (WGS84)</span>
      </div>

      <div class="d-flex align-items-center gap-2">
        <!-- Swap Sites Button -->
        <button 
          type="button" 
          class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
          @click="swapSites"
          title="Swap Site A & Site B Coordinates"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M7 16V4M7 4L3 8M7 4l4 4M17 8v12M17 20l4-4M17 20l-4-4"/>
          </svg>
          <span class="d-none d-sm-inline small">Swap Sites A & B</span>
        </button>

        <!-- Copy Coordinates Button -->
        <button 
          type="button" 
          class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1"
          @click="copyCoordinates"
          title="Copy Site A & B Coordinates to Clipboard"
        >
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
          </svg>
          <span class="d-none d-sm-inline small">{{ copySuccess ? 'Copied!' : 'Copy Coords' }}</span>
        </button>
      </div>
    </div>

    <!-- Site Input Cards Grid -->
    <div class="row g-3">
      <!-- Site A Card -->
      <div class="col-md-6">
        <div class="card h-100 border-0 shadow-sm" style="border-top: 4px solid var(--color-primary) !important;">
          <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
              <span class="badge bg-primary">Node A</span>
              <span class="fw-bold text-white">Site A (Local Node)</span>
            </div>
            <span class="fs-7 text-muted font-monospace">Transmitter / Node A</span>
          </div>

          <div class="card-body p-3">
            <!-- Site Name -->
            <div class="mb-3">
              <label class="form-label">Site Name</label>
              <input 
                type="text" 
                v-model="siteA.name" 
                class="form-control form-control-sm" 
                placeholder="e.g. Tower Alpha" 
              />
            </div>

            <!-- Latitude & Longitude -->
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label">Latitude (°N)</label>
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
                <label class="form-label">Longitude (°E)</label>
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

            <!-- Tower Height & Ground Elevation -->
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label">Tower Height (m)</label>
                <div class="input-group input-group-sm">
                  <input 
                    type="number" 
                    step="0.1" 
                    v-model.number="siteA.tower_height" 
                    class="form-control font-monospace" 
                    placeholder="30.0" 
                  />
                  <span class="input-group-text">m</span>
                </div>
              </div>
              <div class="col-6">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <label class="form-label mb-0">Ground Elevation</label>
                  <button 
                    type="button" 
                    class="btn btn-link p-0 text-decoration-none fs-7 text-primary fw-semibold" 
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
                  <span class="input-group-text">AMSL</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Site B Card -->
      <div class="col-md-6">
        <div class="card h-100 border-0 shadow-sm" style="border-top: 4px solid #8B5CF6 !important;">
          <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
              <span class="badge" style="background-color: #8B5CF6; color: #fff;">Node B</span>
              <span class="fw-bold text-white">Site B (Remote Node)</span>
            </div>
            <span class="fs-7 text-muted font-monospace">Receiver / Node B</span>
          </div>

          <div class="card-body p-3">
            <!-- Site Name -->
            <div class="mb-3">
              <label class="form-label">Site Name</label>
              <input 
                type="text" 
                v-model="siteB.name" 
                class="form-control form-control-sm" 
                placeholder="e.g. Tower Bravo" 
              />
            </div>

            <!-- Latitude & Longitude -->
            <div class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label">Latitude (°N)</label>
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
                <label class="form-label">Longitude (°E)</label>
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

            <!-- Tower Height & Ground Elevation -->
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label">Tower Height (m)</label>
                <div class="input-group input-group-sm">
                  <input 
                    type="number" 
                    step="0.1" 
                    v-model.number="siteB.tower_height" 
                    class="form-control font-monospace" 
                    placeholder="30.0" 
                  />
                  <span class="input-group-text">m</span>
                </div>
              </div>
              <div class="col-6">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <label class="form-label mb-0">Ground Elevation</label>
                  <button 
                    type="button" 
                    class="btn btn-link p-0 text-decoration-none fs-7 text-primary fw-semibold" 
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
                  <span class="input-group-text">AMSL</span>
                </div>
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
      lastLonB: null,
      copySuccess: false
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
    },

    swapSites() {
      const temp = { ...this.siteA };
      this.siteA.name = this.siteB.name;
      this.siteA.latitude = this.siteB.latitude;
      this.siteA.longitude = this.siteB.longitude;
      this.siteA.tower_height = this.siteB.tower_height;
      this.siteA.ground_elevation = this.siteB.ground_elevation;

      this.siteB.name = temp.name;
      this.siteB.latitude = temp.latitude;
      this.siteB.longitude = temp.longitude;
      this.siteB.tower_height = temp.tower_height;
      this.siteB.ground_elevation = temp.ground_elevation;
    },

    copyCoordinates() {
      const text = `Site A (${this.siteA.name}): ${this.siteA.latitude}, ${this.siteA.longitude}\nSite B (${this.siteB.name}): ${this.siteB.latitude}, ${this.siteB.longitude}`;
      navigator.clipboard.writeText(text).then(() => {
        this.copySuccess = true;
        setTimeout(() => { this.copySuccess = false; }, 2000);
      }).catch(err => {
        console.error('Copy failed:', err);
      });
    }
  }
}
</script>

<style scoped>
.fs-7 {
  font-size: 0.75rem;
}
.bg-card {
  background-color: var(--bg-card);
}
</style>
