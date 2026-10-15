<template>
  <div v-if="results" class="mt-4">
    <!-- Top Feasibility Banner -->
    <div class="card border-0 p-4 mb-4 shadow-sm" style="background: linear-gradient(135deg, #172554 0%, #0F172A 100%); border: 1px solid var(--border-color) !important;">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge" :class="results.tech_payload?.technology === 'rf' ? 'badge-tech-rf' : 'badge-tech-fso'">
              {{ (results.tech_payload?.technology || 'RF').toUpperCase() }} LINK ANALYSIS
            </span>
            <span v-if="results.tech_payload?.technology === 'fso'" class="badge" :class="results.tech_payload?.fso_calc_mode === 'los_only' ? 'bg-warning text-dark' : 'bg-primary'">
              {{ results.tech_payload?.fso_calc_mode === 'los_only' ? 'LOS SURVEY ONLY' : 'FULL TECHNICAL MODE' }}
            </span>
            <span class="text-white-50 small">&bull; WGS84 Ellipsoid</span>
          </div>
          <h3 class="page-title text-white mb-1">
            {{ format(results.distance_km) }} km Aerial Path Engineering
          </h3>
          <div class="text-white-50 small font-monospace">
            Site A &rarr; Site B: {{ format(results.azimuth_a_to_b) }}° &bull; Site B &rarr; Site A: {{ format(results.azimuth_b_to_a) }}°
          </div>
        </div>

        <div class="d-flex align-items-center gap-3">
          <span 
            class="badge fs-6 px-3 py-2" 
            :class="results.recommendation_status === 'Suitable' ? 'badge-suitable' : (results.recommendation_status === 'Marginal' ? 'badge-marginal' : 'badge-not-recommended')"
          >
            {{ (results.recommendation_status || 'UNVALUATED').toUpperCase() }}
          </span>
          <button @click="triggerExportPdf" class="btn btn-primary fw-semibold d-flex align-items-center gap-2">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="7 10 12 15 17 10"/>
              <line x1="12" y1="15" x2="12" y2="3"/>
            </svg>
            Export PDF Report
          </button>
        </div>
      </div>
    </div>

    <!-- Summary Metrics Cards Grid -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 text-center border-top border-primary border-4">
          <div class="label-text">AERIAL DISTANCE</div>
          <div class="fs-3 fw-bold text-main mt-1 font-monospace">{{ format(results.distance_km) }} km</div>
          <div class="small text-muted">WGS84 Great Circle</div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 text-center border-top border-4" :class="results.los_status === 'PASS' ? 'border-success' : 'border-danger'">
          <div class="label-text">LINE OF SIGHT (LOS)</div>
          <div class="fs-3 fw-bold mt-1" :class="results.los_status === 'PASS' ? 'text-success' : 'text-danger'">
            {{ results.los_status }}
          </div>
          <div class="small text-muted">Direct Ray Clearance</div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 text-center border-top border-4" :class="results.terrain_status === 'PASS' ? 'border-success' : 'border-danger'">
          <div class="label-text">
            {{ results.tech_payload?.fso_calc_mode === 'los_only' ? 'TERRAIN CLEARANCE' : (results.tech_payload?.technology === 'fso' ? 'OPTICAL FADE MARGIN' : 'FRESNEL CLEARANCE') }}
          </div>
          <div class="fs-3 fw-bold mt-1" :class="results.terrain_status === 'PASS' ? 'text-success' : 'text-danger'">
            <template v-if="results.tech_payload?.fso_calc_mode === 'los_only'">
              {{ results.terrain_status }}
            </template>
            <template v-else-if="results.tech_payload?.technology === 'fso'">
              {{ format(results.tech_payload?.calculated_fade_margin_db) }} dB
            </template>
            <template v-else>
              {{ results.terrain_status }}
            </template>
          </div>
          <div class="small text-muted">
            {{ results.tech_payload?.fso_calc_mode === 'los_only' ? 'Elevation Profile Status' : (results.tech_payload?.technology === 'fso' ? 'Receiver Margin Above Sensitivity' : '60% 1st Fresnel Clearance') }}
          </div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 text-center border-top border-info border-4">
          <div class="label-text">FEASIBILITY VERDICT</div>
          <div class="fs-4 fw-bold mt-1" :class="results.recommendation_status === 'Suitable' ? 'text-success' : (results.recommendation_status === 'Marginal' ? 'text-warning' : 'text-danger')">
            {{ results.recommendation_status }}
          </div>
          <div class="small text-muted">Engineering Recommendation</div>
        </div>
      </div>
    </div>

    <!-- Detailed Alignment & Wave/Optical Link Budget Tables -->
    <div class="row g-4 mb-4">
      <!-- Alignment & Mechanical Tilt Parameters -->
      <div class="col-md-6">
        <div class="card h-100 border">
          <div class="card-header py-2 px-3 fw-bold text-main d-flex align-items-center gap-2">
            <svg class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="12"/>
              <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            Antenna Alignment & Tilt Parameters
          </div>
          <div class="card-body p-0">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th>Parameter</th>
                  <th>Site A (Local)</th>
                  <th>Site B (Remote)</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="fw-semibold">Azimuth Bearing</td>
                  <td class="font-monospace fw-bold text-primary">{{ format(results.azimuth_a_to_b) }}°</td>
                  <td class="font-monospace fw-bold text-main">{{ format(results.azimuth_b_to_a) }}°</td>
                </tr>
                <tr>
                  <td class="fw-semibold">Mechanical Tilt</td>
                  <td class="font-monospace fw-bold text-main">{{ format(results.tilt_a) }}°</td>
                  <td class="font-monospace fw-bold text-main">{{ format(results.tilt_b) }}°</td>
                </tr>
                <tr>
                  <td class="fw-semibold">Elevation Angle</td>
                  <td class="font-monospace text-muted">{{ format(results.elevation_angle_a) }}°</td>
                  <td class="font-monospace text-muted">{{ format(results.elevation_angle_b) }}°</td>
                </tr>
                <tr>
                  <td class="fw-semibold">Recommended Tower Height</td>
                  <td class="font-monospace text-primary fw-bold">{{ format(results.recommended_tower_height_a) }} m</td>
                  <td class="font-monospace text-main fw-bold">{{ format(results.recommended_tower_height_b) }} m</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Wave / Optical / LOS Budget Metrics -->
      <div class="col-md-6">
        <div class="card h-100 border">
          <div class="card-header py-2 px-3 fw-bold text-main d-flex align-items-center gap-2">
            <svg class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 12h-4l-3 9L9 3l-3 9H2"/>
            </svg>
            <template v-if="results.tech_payload?.fso_calc_mode === 'los_only'">
              LOS Field Survey & Height Analysis
            </template>
            <template v-else-if="results.tech_payload?.technology === 'fso'">
              FSO Optical Link Budget & Attenuation
            </template>
            <template v-else>
              RF Wave & Terrain Metrics
            </template>
          </div>
          <div class="card-body p-0">
            <table class="table align-middle mb-0">
              <tbody>
                <!-- LOS Only Mode View -->
                <template v-if="results.tech_payload?.fso_calc_mode === 'los_only'">
                  <tr>
                    <td class="fw-semibold">Direct Ray Line-of-Sight Status</td>
                    <td>
                      <span class="badge" :class="results.los_status === 'PASS' ? 'badge-pass' : 'badge-fail'">
                        {{ results.los_status }}
                      </span>
                    </td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Site A Ground Elevation AMSL</td>
                    <td class="font-monospace fw-bold text-main">{{ format(siteA.ground_elevation) }} m</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Site B Ground Elevation AMSL</td>
                    <td class="font-monospace fw-bold text-main">{{ format(siteB.ground_elevation) }} m</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Earth Curvature Max Drop</td>
                    <td class="font-monospace text-main">{{ format(results.earth_curvature_max_m) }} m</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Minimum Terrain Clearance</td>
                    <td class="font-monospace fw-bold text-primary">{{ format(results.min_clearance_m) }} m</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Height Suggestion (Site A)</td>
                    <td class="font-monospace text-primary fw-bold">
                      {{ siteA.tower_height >= results.recommended_tower_height_a ? 'Height OK (' + format(siteA.tower_height) + 'm)' : 'Increase to ' + format(results.recommended_tower_height_a) + 'm' }}
                    </td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Height Suggestion (Site B)</td>
                    <td class="font-monospace text-main fw-bold">
                      {{ siteB.tower_height >= results.recommended_tower_height_b ? 'Height OK (' + format(siteB.tower_height) + 'm)' : 'Increase to ' + format(results.recommended_tower_height_b) + 'm' }}
                    </td>
                  </tr>
                </template>

                <!-- RF Mode View -->
                <template v-else-if="results.tech_payload?.technology === 'rf'">
                  <tr>
                    <td class="fw-semibold">Earth Curvature Max Drop</td>
                    <td class="font-monospace fw-bold text-main">{{ format(results.earth_curvature_max_m) }} m</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Max 1st Fresnel Radius</td>
                    <td class="font-monospace fw-bold text-primary">{{ format(results.fresnel_radius_max_m) }} m</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Minimum Fresnel Clearance</td>
                    <td class="font-monospace fw-bold text-main">{{ format(results.min_clearance_m) }} m</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Recommended Channel Width</td>
                    <td class="fw-bold text-main">{{ results.tech_payload?.recommended_channel_width_mhz || 40 }} MHz</td>
                  </tr>
                </template>

                <!-- Full Technical FSO Mode View -->
                <template v-else>
                  <tr>
                    <td class="fw-semibold">Tx Power / Rx Sensitivity</td>
                    <td class="font-monospace fw-bold text-main">{{ format(results.tech_payload?.tx_power_dbm) }} dBm / {{ format(results.tech_payload?.rx_sensitivity_dbm) }} dBm</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Divergence / Rx Aperture</td>
                    <td class="font-monospace text-main">{{ format(results.tech_payload?.beam_divergence_mrad) }} mrad / {{ format(results.tech_payload?.rx_aperture_m) }} m</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Beam Diameter at Site B</td>
                    <td class="font-monospace fw-bold text-primary">{{ format(results.tech_payload?.beam_diameter_at_receiver_m) }} m</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Geometric Divergence Loss</td>
                    <td class="font-monospace text-main">{{ format(results.tech_payload?.geometric_loss_db) }} dB</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Atmospheric Loss (City Ref)</td>
                    <td class="font-monospace text-main">{{ format(results.tech_payload?.atmospheric_loss_db_per_km) }} dB/km ({{ results.tech_payload?.selected_city }}, {{ results.tech_payload?.visibility_km }} km)</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Total Path Optical Loss</td>
                    <td class="font-monospace text-danger fw-bold">{{ format(results.tech_payload?.total_path_loss_db) }} dB</td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Received Optical Power (P_rx)</td>
                    <td class="font-monospace fw-bold" :class="results.tech_payload?.rx_power_dbm >= results.tech_payload?.rx_sensitivity_dbm ? 'text-success' : 'text-danger'">
                      {{ format(results.tech_payload?.rx_power_dbm) }} dBm
                    </td>
                  </tr>
                  <tr>
                    <td class="fw-semibold">Calculated Optical Fade Margin</td>
                    <td class="font-monospace fw-bold" :class="results.tech_payload?.calculated_fade_margin_db >= 0 ? 'text-success' : 'text-danger'">
                      {{ format(results.tech_payload?.calculated_fade_margin_db) }} dB
                    </td>
                  </tr>
                </template>

                <tr>
                  <td class="fw-semibold">Engineering Recommendation</td>
                  <td>
                    <span 
                      class="badge" 
                      :class="results.recommendation_status === 'Suitable' ? 'badge-suitable' : (results.recommendation_status === 'Marginal' ? 'badge-marginal' : 'badge-not-recommended')"
                    >
                      {{ results.recommendation_status }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <!-- Field Engineering Summary & Editable Custom Remarks Section -->
    <div class="card p-3 mb-4 border border-secondary" style="background-color: var(--bg-card-header);">
      <div class="d-flex align-items-center justify-content-between mb-2">
        <h6 class="fw-bold text-primary mb-0">Field Engineering Summary & Remarks</h6>
        <span class="badge bg-secondary-subtle text-main font-monospace fs-7">
          {{ results.tech_payload?.fso_calc_mode === 'los_only' ? 'LOS SURVEY MODE' : 'FULL TECHNICAL MODE' }}
        </span>
      </div>

      <p class="mb-1 text-main small fw-semibold">
        {{ results.tech_payload?.engineering_summary }}
      </p>
      <div v-if="results.tech_payload?.atmospheric_recommendation" class="mb-3 text-warning small fst-italic">
        {{ results.tech_payload?.atmospheric_recommendation }}
      </div>

      <!-- User Custom Remarks Input Field -->
      <div class="mt-3 pt-3 border-top border-secondary">
        <label class="form-label fw-bold text-main small mb-1">
          Engineer Custom Survey Remarks & Field Notes (Embedded in PDF Report)
        </label>
        <textarea 
          v-model="customRemarks" 
          class="form-control form-control-sm text-main" 
          rows="3" 
          placeholder="Type custom field survey notes, tower height recommendations, vegetation clearance notes, or site observations here..."
          @input="onRemarksChange"
        ></textarea>
      </div>
    </div>

    <!-- Map & Elevation Profile Visualizers -->
    <div class="row g-4 mb-4">
      <div class="col-md-6">
        <map-component :site-a="siteA" :site-b="siteB" />
      </div>
      <div class="col-md-6">
        <elevation-chart :profile-points="results.profile_points || []" />
      </div>
    </div>
  </div>
</template>

<script>
import MapComponent from './MapComponent.vue';
import ElevationChart from './ElevationChart.vue';

export default {
  components: { MapComponent, ElevationChart },
  props: {
    results: {
      type: Object,
      default: null
    },
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
      customRemarks: ''
    };
  },
  watch: {
    results: {
      immediate: true,
      handler(val) {
        const remarks = val?.tech_payload?.user_custom_remarks || val?.user_custom_remarks;
        if (remarks !== undefined && remarks !== null && remarks !== '') {
          this.customRemarks = remarks;
        }
      }
    }
  },
  emits: ['export-pdf', 'update-remarks'],
  methods: {
    format(val) {
      if (val === null || val === undefined || isNaN(val)) return '0.000';
      return Number(val).toFixed(3);
    },

    triggerExportPdf() {
      if (this.results?.tech_payload) {
        this.results.tech_payload.user_custom_remarks = this.customRemarks;
      }
      if (this.results) {
        this.results.user_custom_remarks = this.customRemarks;
      }
      this.$emit('update-remarks', this.customRemarks);
      this.$emit('export-pdf', this.customRemarks);
    },

    onRemarksChange() {
      if (this.results?.tech_payload) {
        this.results.tech_payload.user_custom_remarks = this.customRemarks;
      }
      if (this.results) {
        this.results.user_custom_remarks = this.customRemarks;
      }
      this.$emit('update-remarks', this.customRemarks);
    }
  }
}
</script>

<style scoped>
.fs-7 {
  font-size: 0.75rem;
}
</style>
