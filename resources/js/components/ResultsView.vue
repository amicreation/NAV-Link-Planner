<template>
  <div v-if="results" class="mt-4">
    <!-- Top Engineering Banner -->
    <div class="card border-0 bg-dark text-white p-3 mb-4 shadow-sm">
      <div class="d-flex flex-wrap align-items-center justify-content-between">
        <div>
          <span class="badge bg-primary text-uppercase me-2">{{ results.tech_payload?.technology?.toUpperCase() || 'RF' }} LINK</span>
          <h5 class="fw-bold d-inline-block mb-0 text-white me-3">
            {{ format(results.distance_km) }} km Path Overview
          </h5>
          <span class="text-white-50 small">
            Azimuth: {{ format(results.azimuth_a_to_b) }}° &bull; Reverse: {{ format(results.azimuth_b_to_a) }}°
          </span>
        </div>
        <div class="d-flex align-items-center mt-2 mt-md-0">
          <span 
            class="badge fs-6 px-3 py-2 me-3" 
            :class="results.recommendation_status === 'Suitable' ? 'badge-suitable' : (results.recommendation_status === 'Marginal' ? 'badge-marginal' : 'badge-not-recommended')"
          >
            {{ results.recommendation_status?.toUpperCase() }}
          </span>
          <button @click="$emit('export-pdf')" class="btn btn-primary btn-sm fw-semibold">
            <svg width="16" height="16" class="me-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Export PDF Report
          </button>
        </div>
      </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card p-3 border-top border-primary border-4 text-center">
          <div class="text-uppercase text-muted fw-bold small">Aerial Distance</div>
          <div class="fs-4 fw-bold text-dark mt-1 font-monospace">{{ format(results.distance_km) }} km</div>
          <div class="small text-secondary">WGS84 Great Circle</div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card p-3 border-top border-4 text-center" :class="results.los_status === 'PASS' ? 'border-success' : 'border-danger'">
          <div class="text-uppercase text-muted fw-bold small">Line of Sight (LOS)</div>
          <div class="fs-4 fw-bold mt-1" :class="results.los_status === 'PASS' ? 'text-success' : 'text-danger'">
            {{ results.los_status }}
          </div>
          <div class="small text-secondary">Direct Visual Clearance</div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card p-3 border-top border-4 text-center" :class="results.terrain_status === 'PASS' ? 'border-success' : 'border-danger'">
          <div class="text-uppercase text-muted fw-bold small">
            {{ results.tech_payload?.technology === 'fso' ? 'Calculated Fade Margin' : 'Terrain Clearance' }}
          </div>
          <div class="fs-4 fw-bold mt-1" :class="results.terrain_status === 'PASS' ? 'text-success' : 'text-danger'">
            {{ results.tech_payload?.technology === 'fso' ? format(results.tech_payload?.calculated_fade_margin_db) + ' dB' : results.terrain_status }}
          </div>
          <div class="small text-secondary">
            {{ results.tech_payload?.technology === 'fso' ? 'Receiver Margin Above Sensitivity' : '60% Fresnel Zone Margin' }}
          </div>
        </div>
      </div>

      <div class="col-md-3">
        <div class="card p-3 border-top border-info border-4 text-center">
          <div class="text-uppercase text-muted fw-bold small">Feasibility Verdict</div>
          <div class="fs-5 fw-bold mt-1" :class="results.recommendation_status === 'Suitable' ? 'text-success' : (results.recommendation_status === 'Marginal' ? 'text-warning' : 'text-danger')">
            {{ results.recommendation_status }}
          </div>
          <div class="small text-secondary">Field Recommendation</div>
        </div>
      </div>
    </div>

    <!-- Detailed Alignment & Engineering Tables -->
    <div class="row g-4 mb-4">
      <!-- Antenna Alignment Geometry -->
      <div class="col-md-6">
        <div class="card h-100 border">
          <div class="card-header bg-white py-2 px-3 fw-bold text-dark">
            Alignment & Mechanical Tilt Parameters
          </div>
          <div class="card-body p-0">
            <table class="table table-bordered align-middle mb-0">
              <thead>
                <tr>
                  <th>Parameter</th>
                  <th>Site A (Local)</th>
                  <th>Site B (Remote)</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td><strong>Azimuth (Bearing)</strong></td>
                  <td class="font-monospace fw-bold text-primary">{{ format(results.azimuth_a_to_b) }}°</td>
                  <td class="font-monospace fw-bold text-dark">{{ format(results.azimuth_b_to_a) }}°</td>
                </tr>
                <tr>
                  <td><strong>Mechanical Tilt</strong></td>
                  <td class="font-monospace fw-bold">{{ format(results.tilt_a) }}°</td>
                  <td class="font-monospace fw-bold">{{ format(results.tilt_b) }}°</td>
                </tr>
                <tr>
                  <td><strong>Elevation Angle</strong></td>
                  <td class="font-monospace">{{ format(results.elevation_angle_a) }}°</td>
                  <td class="font-monospace">{{ format(results.elevation_angle_b) }}°</td>
                </tr>
                <tr>
                  <td><strong>Recommended Tower Height</strong></td>
                  <td class="font-monospace text-primary fw-bold">{{ format(results.recommended_tower_height_a) }} m</td>
                  <td class="font-monospace text-dark fw-bold">{{ format(results.recommended_tower_height_b) }} m</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Engineering & Wave Metrics -->
      <div class="col-md-6">
        <div class="card h-100 border">
          <div class="card-header bg-white py-2 px-3 fw-bold text-dark">
            {{ results.tech_payload?.technology === 'fso' ? 'FSO Optical Link Budget & Attenuation' : 'RF Wave & Terrain Metrics' }}
          </div>
          <div class="card-body p-0">
            <table class="table table-bordered align-middle mb-0">
              <tbody>
                <template v-if="results.tech_payload?.technology === 'rf'">
                  <tr>
                    <td><strong>Earth Curvature Drop</strong></td>
                    <td class="font-monospace fw-bold">{{ format(results.earth_curvature_max_m) }} m</td>
                  </tr>
                  <tr>
                    <td><strong>Max 1st Fresnel Radius</strong></td>
                    <td class="font-monospace fw-bold text-primary">{{ format(results.fresnel_radius_max_m) }} m</td>
                  </tr>
                  <tr>
                    <td><strong>Minimum Fresnel Clearance</strong></td>
                    <td class="font-monospace fw-bold">{{ format(results.min_clearance_m) }} m</td>
                  </tr>
                  <tr>
                    <td><strong>Recommended Channel Width</strong></td>
                    <td class="fw-bold text-dark">{{ results.tech_payload?.recommended_channel_width_mhz || 40 }} MHz</td>
                  </tr>
                </template>

                <template v-else>
                  <tr>
                    <td><strong>Tx Power / Sensitivity</strong></td>
                    <td class="font-monospace fw-bold">{{ format(results.tech_payload?.tx_power_dbm) }} dBm / {{ format(results.tech_payload?.rx_sensitivity_dbm) }} dBm</td>
                  </tr>
                  <tr>
                    <td><strong>Divergence / Rx Aperture</strong></td>
                    <td class="font-monospace">{{ format(results.tech_payload?.beam_divergence_mrad) }} mrad / {{ format(results.tech_payload?.rx_aperture_m) }} m</td>
                  </tr>
                  <tr>
                    <td><strong>Beam Diameter at Site B</strong></td>
                    <td class="font-monospace fw-bold text-primary">{{ format(results.tech_payload?.beam_diameter_at_receiver_m) }} m</td>
                  </tr>
                  <tr>
                    <td><strong>Geometric Loss</strong></td>
                    <td class="font-monospace">{{ format(results.tech_payload?.geometric_loss_db) }} dB</td>
                  </tr>
                  <tr>
                    <td><strong>Atmospheric Loss (City Ref)</strong></td>
                    <td class="font-monospace">{{ format(results.tech_payload?.atmospheric_loss_db_per_km) }} dB/km ({{ results.tech_payload?.selected_city }}, {{ results.tech_payload?.visibility_km }} km)</td>
                  </tr>
                  <tr>
                    <td><strong>Total Path Optical Loss</strong></td>
                    <td class="font-monospace text-danger fw-bold">{{ format(results.tech_payload?.total_path_loss_db) }} dB</td>
                  </tr>
                  <tr>
                    <td><strong>Received Optical Power (P_rx)</strong></td>
                    <td class="font-monospace fw-bold" :class="results.tech_payload?.rx_power_dbm >= results.tech_payload?.rx_sensitivity_dbm ? 'text-success' : 'text-danger'">
                      {{ format(results.tech_payload?.rx_power_dbm) }} dBm
                    </td>
                  </tr>
                  <tr>
                    <td><strong>Calculated Optical Fade Margin</strong></td>
                    <td class="font-monospace fw-bold" :class="results.tech_payload?.calculated_fade_margin_db >= 0 ? 'text-success' : 'text-danger'">
                      {{ format(results.tech_payload?.calculated_fade_margin_db) }} dB
                    </td>
                  </tr>
                </template>

                <tr>
                  <td><strong>Engineering Recommendation</strong></td>
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

    <!-- Summary Box -->
    <div class="alert alert-light border p-3 mb-4">
      <h6 class="fw-bold mb-1 brand-blue">Field Engineering Verdict</h6>
      <p class="mb-0 text-secondary small">
        {{ results.tech_payload?.engineering_summary }}
      </p>
      <div v-if="results.tech_payload?.atmospheric_recommendation" class="mt-2 text-dark small fst-italic">
        {{ results.tech_payload?.atmospheric_recommendation }}
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
  emits: ['export-pdf'],
  methods: {
    format(val) {
      if (val === null || val === undefined || isNaN(val)) return '0.000';
      return Number(val).toFixed(3);
    }
  }
}
</script>
