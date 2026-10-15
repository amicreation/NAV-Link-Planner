<template>
  <div>
    <!-- Top Action Bar for Coordinate Helpers & Format Selector -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 bg-card p-2 px-3 rounded-3 border border-secondary-subtle">
      <div class="d-flex align-items-center gap-2">
        <svg class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="10" r="3"/>
          <path d="M12 21s-7-5.3-7-11a7 7 0 0 1 14 0c0 5.7-7 11-7 11z"/>
        </svg>
        <span class="fw-semibold text-white small">Site Geodetic Coordinates (WGS84)</span>
      </div>

      <!-- Format Toggle & Action Buttons -->
      <div class="d-flex align-items-center gap-2">
        <!-- Coordinate Format Mode Selector (DD vs DMS) -->
        <div class="btn-group btn-group-sm" role="group" aria-label="Coordinate Format">
          <button 
            type="button" 
            class="btn py-1 px-2 fw-semibold"
            :class="coordFormat === 'DD' ? 'btn-primary' : 'btn-outline-secondary'"
            @click="setCoordFormat('DD')"
            title="Decimal Degrees (e.g. 19.21338889°)"
          >
            Decimal Degrees (DD)
          </button>
          <button 
            type="button" 
            class="btn py-1 px-2 fw-semibold"
            :class="coordFormat === 'DMS' ? 'btn-primary' : 'btn-outline-secondary'"
            @click="setCoordFormat('DMS')"
            title="Degrees, Minutes, Seconds (e.g. 19° 12' 48.20&quot; N)"
          >
            Deg, Min, Sec (DMS)
          </button>
        </div>

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
          title="Copy Site A & B Coordinates in both DD and DMS formats"
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

            <!-- MODE 1: DECIMAL DEGREES (DD) -->
            <div v-if="coordFormat === 'DD'" class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label d-flex justify-content-between align-items-center mb-1">
                  <span>Latitude (°N/°S)</span>
                </label>
                <input 
                  type="text" 
                  v-model="rawA_lat" 
                  @paste="onPasteCoordinate($event, 'A', 'lat')"
                  @input="onInputCoordinate('A', 'lat', $event.target.value)"
                  @blur="onBlurCoordinate('A', 'lat')"
                  @change="onBlurCoordinate('A', 'lat')"
                  class="form-control form-control-sm font-monospace" 
                  placeholder="e.g. 19.21338889 or 19°12'48.20&quot;N" 
                />
                <div class="fs-7 text-muted font-monospace mt-1 text-truncate" :title="dmsDisplay(siteA.latitude, false)">
                  <span class="text-primary-subtle">DMS:</span> {{ dmsDisplay(siteA.latitude, false) }}
                </div>
              </div>

              <div class="col-6">
                <label class="form-label d-flex justify-content-between align-items-center mb-1">
                  <span>Longitude (°E/°W)</span>
                </label>
                <input 
                  type="text" 
                  v-model="rawA_lon" 
                  @paste="onPasteCoordinate($event, 'A', 'lon')"
                  @input="onInputCoordinate('A', 'lon', $event.target.value)"
                  @blur="onBlurCoordinate('A', 'lon')"
                  @change="onBlurCoordinate('A', 'lon')"
                  class="form-control form-control-sm font-monospace" 
                  placeholder="e.g. 77.59456270 or 77°35'40.43&quot;E" 
                />
                <div class="fs-7 text-muted font-monospace mt-1 text-truncate" :title="dmsDisplay(siteA.longitude, true)">
                  <span class="text-primary-subtle">DMS:</span> {{ dmsDisplay(siteA.longitude, true) }}
                </div>
              </div>
            </div>

            <!-- MODE 2: DEGREES, MINUTES, SECONDS (DMS) -->
            <div v-else class="mb-3">
              <!-- Site A Latitude (DMS) -->
              <div class="mb-2 p-2 rounded border border-secondary-subtle bg-dark-subtle">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="small fw-semibold text-white">Latitude (DMS)</span>
                  <span class="fs-7 font-monospace text-primary fw-semibold">
                    DD: {{ Number(siteA.latitude || 0).toFixed(8) }}°
                  </span>
                </div>
                <div class="row g-1 align-items-center">
                  <div class="col-4">
                    <div class="input-group input-group-sm">
                      <input 
                        type="number" 
                        min="0" 
                        max="90" 
                        step="1"
                        v-model.number="dmsA.lat.deg" 
                        @paste="onPasteCoordinate($event, 'A', 'lat')"
                        @input="syncDmsToDd('A')"
                        @blur="fetchElevation('A')"
                        class="form-control font-monospace px-1 text-center" 
                        placeholder="Deg" 
                      />
                      <span class="input-group-text px-1">°</span>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="input-group input-group-sm">
                      <input 
                        type="number" 
                        min="0" 
                        max="59" 
                        step="1"
                        v-model.number="dmsA.lat.min" 
                        @paste="onPasteCoordinate($event, 'A', 'lat')"
                        @input="syncDmsToDd('A')"
                        @blur="fetchElevation('A')"
                        class="form-control font-monospace px-1 text-center" 
                        placeholder="Min" 
                      />
                      <span class="input-group-text px-1">'</span>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="input-group input-group-sm">
                      <input 
                        type="number" 
                        min="0" 
                        max="59.99" 
                        step="0.01"
                        v-model.number="dmsA.lat.sec" 
                        @paste="onPasteCoordinate($event, 'A', 'lat')"
                        @input="syncDmsToDd('A')"
                        @blur="fetchElevation('A')"
                        class="form-control font-monospace px-1 text-center" 
                        placeholder="Sec" 
                      />
                      <span class="input-group-text px-1">"</span>
                    </div>
                  </div>
                  <div class="col-2">
                    <select 
                      v-model="dmsA.lat.dir" 
                      @change="syncDmsToDd('A'); fetchElevation('A');"
                      class="form-select form-select-sm font-monospace px-1 text-center fw-bold text-primary"
                    >
                      <option value="N">N</option>
                      <option value="S">S</option>
                    </select>
                  </div>
                </div>
                <!-- Quick DMS String Paste Parser -->
                <div class="mt-1 d-flex align-items-center gap-1">
                  <input 
                    type="text" 
                    v-model="quickDmsA_lat" 
                    @paste="onPasteCoordinate($event, 'A', 'lat')"
                    @change="applyQuickDms('A', 'lat')" 
                    @keyup.enter="applyQuickDms('A', 'lat')"
                    class="form-control form-control-sm font-monospace text-muted py-0 px-2" 
                    style="font-size: 0.72rem; height: 24px;"
                    placeholder='Paste e.g. 19°12&apos;48.20"N' 
                  />
                  <button 
                    type="button" 
                    class="btn btn-outline-secondary py-0 px-2 text-nowrap" 
                    style="font-size: 0.7rem; height: 24px;"
                    @click="applyQuickDms('A', 'lat')"
                  >
                    Parse
                  </button>
                </div>
              </div>

              <!-- Site A Longitude (DMS) -->
              <div class="p-2 rounded border border-secondary-subtle bg-dark-subtle">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="small fw-semibold text-white">Longitude (DMS)</span>
                  <span class="fs-7 font-monospace text-primary fw-semibold">
                    DD: {{ Number(siteA.longitude || 0).toFixed(8) }}°
                  </span>
                </div>
                <div class="row g-1 align-items-center">
                  <div class="col-4">
                    <div class="input-group input-group-sm">
                      <input 
                        type="number" 
                        min="0" 
                        max="180" 
                        step="1"
                        v-model.number="dmsA.lon.deg" 
                        @paste="onPasteCoordinate($event, 'A', 'lon')"
                        @input="syncDmsToDd('A')"
                        @blur="fetchElevation('A')"
                        class="form-control font-monospace px-1 text-center" 
                        placeholder="Deg" 
                      />
                      <span class="input-group-text px-1">°</span>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="input-group input-group-sm">
                      <input 
                        type="number" 
                        min="0" 
                        max="59" 
                        step="1"
                        v-model.number="dmsA.lon.min" 
                        @paste="onPasteCoordinate($event, 'A', 'lon')"
                        @input="syncDmsToDd('A')"
                        @blur="fetchElevation('A')"
                        class="form-control font-monospace px-1 text-center" 
                        placeholder="Min" 
                      />
                      <span class="input-group-text px-1">'</span>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="input-group input-group-sm">
                      <input 
                        type="number" 
                        min="0" 
                        max="59.99" 
                        step="0.01"
                        v-model.number="dmsA.lon.sec" 
                        @paste="onPasteCoordinate($event, 'A', 'lon')"
                        @input="syncDmsToDd('A')"
                        @blur="fetchElevation('A')"
                        class="form-control font-monospace px-1 text-center" 
                        placeholder="Sec" 
                      />
                      <span class="input-group-text px-1">"</span>
                    </div>
                  </div>
                  <div class="col-2">
                    <select 
                      v-model="dmsA.lon.dir" 
                      @change="syncDmsToDd('A'); fetchElevation('A');"
                      class="form-select form-select-sm font-monospace px-1 text-center fw-bold text-primary"
                    >
                      <option value="E">E</option>
                      <option value="W">W</option>
                    </select>
                  </div>
                </div>
                <!-- Quick DMS String Paste Parser -->
                <div class="mt-1 d-flex align-items-center gap-1">
                  <input 
                    type="text" 
                    v-model="quickDmsA_lon" 
                    @paste="onPasteCoordinate($event, 'A', 'lon')"
                    @change="applyQuickDms('A', 'lon')" 
                    @keyup.enter="applyQuickDms('A', 'lon')"
                    class="form-control form-control-sm font-monospace text-muted py-0 px-2" 
                    style="font-size: 0.72rem; height: 24px;"
                    placeholder='Paste e.g. 77°35&apos;40.43"E' 
                  />
                  <button 
                    type="button" 
                    class="btn btn-outline-secondary py-0 px-2 text-nowrap" 
                    style="font-size: 0.7rem; height: 24px;"
                    @click="applyQuickDms('A', 'lon')"
                  >
                    Parse
                  </button>
                </div>
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

            <!-- MODE 1: DECIMAL DEGREES (DD) -->
            <div v-if="coordFormat === 'DD'" class="row g-2 mb-3">
              <div class="col-6">
                <label class="form-label d-flex justify-content-between align-items-center mb-1">
                  <span>Latitude (°N/°S)</span>
                </label>
                <input 
                  type="text" 
                  v-model="rawB_lat" 
                  @paste="onPasteCoordinate($event, 'B', 'lat')"
                  @input="onInputCoordinate('B', 'lat', $event.target.value)"
                  @blur="onBlurCoordinate('B', 'lat')"
                  @change="onBlurCoordinate('B', 'lat')"
                  class="form-control form-control-sm font-monospace" 
                  placeholder="e.g. 13.03582450 or 13°02'08.97&quot;N" 
                />
                <div class="fs-7 text-muted font-monospace mt-1 text-truncate" :title="dmsDisplay(siteB.latitude, false)">
                  <span class="text-primary-subtle">DMS:</span> {{ dmsDisplay(siteB.latitude, false) }}
                </div>
              </div>

              <div class="col-6">
                <label class="form-label d-flex justify-content-between align-items-center mb-1">
                  <span>Longitude (°E/°W)</span>
                </label>
                <input 
                  type="text" 
                  v-model="rawB_lon" 
                  @paste="onPasteCoordinate($event, 'B', 'lon')"
                  @input="onInputCoordinate('B', 'lon', $event.target.value)"
                  @blur="onBlurCoordinate('B', 'lon')"
                  @change="onBlurCoordinate('B', 'lon')"
                  class="form-control form-control-sm font-monospace" 
                  placeholder="e.g. 77.62514820 or 77°37'30.53&quot;E" 
                />
                <div class="fs-7 text-muted font-monospace mt-1 text-truncate" :title="dmsDisplay(siteB.longitude, true)">
                  <span class="text-primary-subtle">DMS:</span> {{ dmsDisplay(siteB.longitude, true) }}
                </div>
              </div>
            </div>

            <!-- MODE 2: DEGREES, MINUTES, SECONDS (DMS) -->
            <div v-else class="mb-3">
              <!-- Site B Latitude (DMS) -->
              <div class="mb-2 p-2 rounded border border-secondary-subtle bg-dark-subtle">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="small fw-semibold text-white">Latitude (DMS)</span>
                  <span class="fs-7 font-monospace text-primary fw-semibold">
                    DD: {{ Number(siteB.latitude || 0).toFixed(8) }}°
                  </span>
                </div>
                <div class="row g-1 align-items-center">
                  <div class="col-4">
                    <div class="input-group input-group-sm">
                      <input 
                        type="number" 
                        min="0" 
                        max="90" 
                        step="1"
                        v-model.number="dmsB.lat.deg" 
                        @paste="onPasteCoordinate($event, 'B', 'lat')"
                        @input="syncDmsToDd('B')"
                        @blur="fetchElevation('B')"
                        class="form-control font-monospace px-1 text-center" 
                        placeholder="Deg" 
                      />
                      <span class="input-group-text px-1">°</span>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="input-group input-group-sm">
                      <input 
                        type="number" 
                        min="0" 
                        max="59" 
                        step="1"
                        v-model.number="dmsB.lat.min" 
                        @paste="onPasteCoordinate($event, 'B', 'lat')"
                        @input="syncDmsToDd('B')"
                        @blur="fetchElevation('B')"
                        class="form-control font-monospace px-1 text-center" 
                        placeholder="Min" 
                      />
                      <span class="input-group-text px-1">'</span>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="input-group input-group-sm">
                      <input 
                        type="number" 
                        min="0" 
                        max="59.99" 
                        step="0.01"
                        v-model.number="dmsB.lat.sec" 
                        @paste="onPasteCoordinate($event, 'B', 'lat')"
                        @input="syncDmsToDd('B')"
                        @blur="fetchElevation('B')"
                        class="form-control font-monospace px-1 text-center" 
                        placeholder="Sec" 
                      />
                      <span class="input-group-text px-1">"</span>
                    </div>
                  </div>
                  <div class="col-2">
                    <select 
                      v-model="dmsB.lat.dir" 
                      @change="syncDmsToDd('B'); fetchElevation('B');"
                      class="form-select form-select-sm font-monospace px-1 text-center fw-bold text-primary"
                    >
                      <option value="N">N</option>
                      <option value="S">S</option>
                    </select>
                  </div>
                </div>
                <!-- Quick DMS String Paste Parser -->
                <div class="mt-1 d-flex align-items-center gap-1">
                  <input 
                    type="text" 
                    v-model="quickDmsB_lat" 
                    @paste="onPasteCoordinate($event, 'B', 'lat')"
                    @change="applyQuickDms('B', 'lat')" 
                    @keyup.enter="applyQuickDms('B', 'lat')"
                    class="form-control form-control-sm font-monospace text-muted py-0 px-2" 
                    style="font-size: 0.72rem; height: 24px;"
                    placeholder='Paste e.g. 13°02&apos;08.97"N' 
                  />
                  <button 
                    type="button" 
                    class="btn btn-outline-secondary py-0 px-2 text-nowrap" 
                    style="font-size: 0.7rem; height: 24px;"
                    @click="applyQuickDms('B', 'lat')"
                  >
                    Parse
                  </button>
                </div>
              </div>

              <!-- Site B Longitude (DMS) -->
              <div class="p-2 rounded border border-secondary-subtle bg-dark-subtle">
                <div class="d-flex align-items-center justify-content-between mb-1">
                  <span class="small fw-semibold text-white">Longitude (DMS)</span>
                  <span class="fs-7 font-monospace text-primary fw-semibold">
                    DD: {{ Number(siteB.longitude || 0).toFixed(8) }}°
                  </span>
                </div>
                <div class="row g-1 align-items-center">
                  <div class="col-4">
                    <div class="input-group input-group-sm">
                      <input 
                        type="number" 
                        min="0" 
                        max="180" 
                        step="1"
                        v-model.number="dmsB.lon.deg" 
                        @paste="onPasteCoordinate($event, 'B', 'lon')"
                        @input="syncDmsToDd('B')"
                        @blur="fetchElevation('B')"
                        class="form-control font-monospace px-1 text-center" 
                        placeholder="Deg" 
                      />
                      <span class="input-group-text px-1">°</span>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="input-group input-group-sm">
                      <input 
                        type="number" 
                        min="0" 
                        max="59" 
                        step="1"
                        v-model.number="dmsB.lon.min" 
                        @paste="onPasteCoordinate($event, 'B', 'lon')"
                        @input="syncDmsToDd('B')"
                        @blur="fetchElevation('B')"
                        class="form-control font-monospace px-1 text-center" 
                        placeholder="Min" 
                      />
                      <span class="input-group-text px-1">'</span>
                    </div>
                  </div>
                  <div class="col-3">
                    <div class="input-group input-group-sm">
                      <input 
                        type="number" 
                        min="0" 
                        max="59.99" 
                        step="0.01"
                        v-model.number="dmsB.lon.sec" 
                        @paste="onPasteCoordinate($event, 'B', 'lon')"
                        @input="syncDmsToDd('B')"
                        @blur="fetchElevation('B')"
                        class="form-control font-monospace px-1 text-center" 
                        placeholder="Sec" 
                      />
                      <span class="input-group-text px-1">"</span>
                    </div>
                  </div>
                  <div class="col-2">
                    <select 
                      v-model="dmsB.lon.dir" 
                      @change="syncDmsToDd('B'); fetchElevation('B');"
                      class="form-select form-select-sm font-monospace px-1 text-center fw-bold text-primary"
                    >
                      <option value="E">E</option>
                      <option value="W">W</option>
                    </select>
                  </div>
                </div>
                <!-- Quick DMS String Paste Parser -->
                <div class="mt-1 d-flex align-items-center gap-1">
                  <input 
                    type="text" 
                    v-model="quickDmsB_lon" 
                    @paste="onPasteCoordinate($event, 'B', 'lon')"
                    @change="applyQuickDms('B', 'lon')" 
                    @keyup.enter="applyQuickDms('B', 'lon')"
                    class="form-control form-control-sm font-monospace text-muted py-0 px-2" 
                    style="font-size: 0.72rem; height: 24px;"
                    placeholder='Paste e.g. 77°37&apos;30.53"E' 
                  />
                  <button 
                    type="button" 
                    class="btn btn-outline-secondary py-0 px-2 text-nowrap" 
                    style="font-size: 0.7rem; height: 24px;"
                    @click="applyQuickDms('B', 'lon')"
                  >
                    Parse
                  </button>
                </div>
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
import { decimalToDms, dmsToDecimal, parseCoordinateInput, parseCombinedCoordinates } from '../utils/coordinates.js';

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
      coordFormat: 'DD', // 'DD' or 'DMS'
      loadingA: false,
      loadingB: false,
      lastLatA: null,
      lastLonA: null,
      lastLatB: null,
      lastLonB: null,
      copySuccess: false,

      // Text input bindings for clean typing & pasting without browser number stripping
      rawA_lat: '',
      rawA_lon: '',
      rawB_lat: '',
      rawB_lon: '',

      // DMS local structured state for Site A
      dmsA: {
        lat: { deg: 0, min: 0, sec: 0, dir: 'N' },
        lon: { deg: 0, min: 0, sec: 0, dir: 'E' }
      },
      // DMS local structured state for Site B
      dmsB: {
        lat: { deg: 0, min: 0, sec: 0, dir: 'N' },
        lon: { deg: 0, min: 0, sec: 0, dir: 'E' }
      },

      // Quick Paste DMS string models
      quickDmsA_lat: '',
      quickDmsA_lon: '',
      quickDmsB_lat: '',
      quickDmsB_lon: ''
    };
  },
  mounted() {
    this.refreshRawInputs();
    this.syncAllDdToDms();
  },
  watch: {
    'siteA.latitude'(val) {
      this.rawA_lat = val !== undefined && val !== null ? String(val) : '';
      this.syncDdToDms('A', 'lat', val);
    },
    'siteA.longitude'(val) {
      this.rawA_lon = val !== undefined && val !== null ? String(val) : '';
      this.syncDdToDms('A', 'lon', val);
    },
    'siteB.latitude'(val) {
      this.rawB_lat = val !== undefined && val !== null ? String(val) : '';
      this.syncDdToDms('B', 'lat', val);
    },
    'siteB.longitude'(val) {
      this.rawB_lon = val !== undefined && val !== null ? String(val) : '';
      this.syncDdToDms('B', 'lon', val);
    }
  },
  methods: {
    setCoordFormat(fmt) {
      this.coordFormat = fmt;
      this.syncAllDdToDms();
      this.refreshRawInputs();
    },

    refreshRawInputs() {
      this.rawA_lat = this.siteA.latitude !== undefined && this.siteA.latitude !== null ? String(this.siteA.latitude) : '';
      this.rawA_lon = this.siteA.longitude !== undefined && this.siteA.longitude !== null ? String(this.siteA.longitude) : '';
      this.rawB_lat = this.siteB.latitude !== undefined && this.siteB.latitude !== null ? String(this.siteB.latitude) : '';
      this.rawB_lon = this.siteB.longitude !== undefined && this.siteB.longitude !== null ? String(this.siteB.longitude) : '';
    },

    dmsDisplay(val, isLongitude) {
      return decimalToDms(val, isLongitude).formatted;
    },

    syncAllDdToDms() {
      this.syncDdToDms('A', 'lat', this.siteA.latitude);
      this.syncDdToDms('A', 'lon', this.siteA.longitude);
      this.syncDdToDms('B', 'lat', this.siteB.latitude);
      this.syncDdToDms('B', 'lon', this.siteB.longitude);
    },

    syncDdToDms(siteKey, coordType, decimalVal) {
      const isLon = coordType === 'lon';
      const dmsObj = decimalToDms(decimalVal, isLon);
      const target = siteKey === 'A' ? this.dmsA : this.dmsB;

      target[coordType] = {
        deg: dmsObj.degrees,
        min: dmsObj.minutes,
        sec: dmsObj.seconds,
        dir: dmsObj.direction
      };
    },

    syncDmsToDd(siteKey) {
      const dmsObj = siteKey === 'A' ? this.dmsA : this.dmsB;
      const site = siteKey === 'A' ? this.siteA : this.siteB;

      const newLat = dmsToDecimal(dmsObj.lat.deg, dmsObj.lat.min, dmsObj.lat.sec, dmsObj.lat.dir);
      const newLon = dmsToDecimal(dmsObj.lon.deg, dmsObj.lon.min, dmsObj.lon.sec, dmsObj.lon.dir);

      site.latitude = newLat;
      site.longitude = newLon;
      this.refreshRawInputs();
    },

    onPasteCoordinate(event, siteKey, coordType) {
      const pasteText = (event.clipboardData || window.clipboardData)?.getData('text');
      if (!pasteText || !pasteText.trim()) return;

      const text = pasteText.trim();
      const site = siteKey === 'A' ? this.siteA : this.siteB;

      // 1. Check if user pasted a combined pair e.g. "19°12'48.20"N, 77°35'40.43"E" or "19.213388, 77.594562"
      const combined = parseCombinedCoordinates(text);
      if (combined) {
        event.preventDefault();
        site.latitude = combined.latitude;
        site.longitude = combined.longitude;
        this.syncAllDdToDms();
        this.refreshRawInputs();
        this.fetchElevation(siteKey);
        return;
      }

      // 2. Check if user pasted a single DMS or decimal coordinate (e.g. 19°12'48.20"N)
      const isLon = coordType === 'lon';
      const parsed = parseCoordinateInput(text, isLon);
      if (parsed.isValid) {
        event.preventDefault();
        if (isLon) {
          site.longitude = parsed.decimal;
        } else {
          site.latitude = parsed.decimal;
        }
        this.syncAllDdToDms();
        this.refreshRawInputs();
        this.fetchElevation(siteKey);
      }
    },

    onInputCoordinate(siteKey, coordType, val) {
      if (!val) return;
      const isLon = coordType === 'lon';
      const site = siteKey === 'A' ? this.siteA : this.siteB;

      // Check if user typed or pasted DMS characters or compact DDMMSS
      if (val.match(/[°\'"”’dmsDMSNSEWnsew:]/) || val.length >= 6) {
        const parsed = parseCoordinateInput(val, isLon);
        if (parsed.isValid) {
          if (isLon) {
            site.longitude = parsed.decimal;
          } else {
            site.latitude = parsed.decimal;
          }
          this.syncDdToDms(siteKey, coordType, parsed.decimal);
        }
      } else {
        const num = Number(val);
        if (!isNaN(num)) {
          if (isLon) site.longitude = num; else site.latitude = num;
          this.syncDdToDms(siteKey, coordType, num);
        }
      }
    },

    onBlurCoordinate(siteKey, coordType) {
      const isLon = coordType === 'lon';
      const site = siteKey === 'A' ? this.siteA : this.siteB;
      const currentVal = siteKey === 'A' 
        ? (isLon ? this.rawA_lon : this.rawA_lat)
        : (isLon ? this.rawB_lon : this.rawB_lat);

      const parsed = parseCoordinateInput(currentVal, isLon);
      if (parsed.isValid) {
        if (isLon) {
          site.longitude = parsed.decimal;
        } else {
          site.latitude = parsed.decimal;
        }
      }

      this.syncAllDdToDms();
      this.refreshRawInputs();
      this.fetchElevation(siteKey);
    },

    applyQuickDms(siteKey, coordType) {
      const isLon = coordType === 'lon';
      let rawStr = '';

      if (siteKey === 'A') {
        rawStr = isLon ? this.quickDmsA_lon : this.quickDmsA_lat;
      } else {
        rawStr = isLon ? this.quickDmsB_lon : this.quickDmsB_lat;
      }

      if (!rawStr || !rawStr.trim()) return;

      const parsed = parseCoordinateInput(rawStr, isLon);
      if (parsed.isValid) {
        const site = siteKey === 'A' ? this.siteA : this.siteB;
        const dmsObj = siteKey === 'A' ? this.dmsA : this.dmsB;

        dmsObj[coordType] = {
          deg: parsed.degrees,
          min: parsed.minutes,
          sec: parsed.seconds,
          dir: parsed.direction
        };

        if (isLon) {
          site.longitude = parsed.decimal;
        } else {
          site.latitude = parsed.decimal;
        }

        if (siteKey === 'A') {
          if (isLon) this.quickDmsA_lon = ''; else this.quickDmsA_lat = '';
        } else {
          if (isLon) this.quickDmsB_lon = ''; else this.quickDmsB_lat = '';
        }

        this.syncAllDdToDms();
        this.refreshRawInputs();
        this.fetchElevation(siteKey);
      } else {
        alert('Could not parse coordinate. Example formats:\n- 19°12\'48.20"N\n- 19 12 48.20 N\n- 19.21338889');
      }
    },

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

      this.syncAllDdToDms();
      this.refreshRawInputs();
    },

    copyCoordinates() {
      const dmsA_lat = decimalToDms(this.siteA.latitude, false).formatted;
      const dmsA_lon = decimalToDms(this.siteA.longitude, true).formatted;
      const dmsB_lat = decimalToDms(this.siteB.latitude, false).formatted;
      const dmsB_lon = decimalToDms(this.siteB.longitude, true).formatted;

      const text = [
        `Site A (${this.siteA.name}):`,
        `  Decimal Degrees: ${Number(this.siteA.latitude || 0).toFixed(8)}°, ${Number(this.siteA.longitude || 0).toFixed(8)}°`,
        `  DMS Format:      ${dmsA_lat}, ${dmsA_lon}`,
        `Site B (${this.siteB.name}):`,
        `  Decimal Degrees: ${Number(this.siteB.latitude || 0).toFixed(8)}°, ${Number(this.siteB.longitude || 0).toFixed(8)}°`,
        `  DMS Format:      ${dmsB_lat}, ${dmsB_lon}`
      ].join('\n');

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
.bg-dark-subtle {
  background-color: rgba(30, 41, 59, 0.4) !important;
}
.text-primary-subtle {
  color: #60a5fa !important;
}
</style>
