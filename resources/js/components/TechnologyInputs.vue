<template>
  <div class="card p-4 mb-4 border" style="background-color: var(--bg-card); border-color: var(--border-color) !important;">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h5 class="card-title text-main mb-0 d-flex align-items-center gap-2">
        <svg class="text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
        </svg>
        Link Technology & Path Parameters
      </h5>

      <!-- Segmented Technology Switcher -->
      <div class="btn-group btn-group-sm" role="group">
        <button 
          type="button" 
          class="btn py-1 px-3 fw-semibold" 
          :class="technology === 'rf' ? 'btn-primary' : 'btn-outline-secondary'"
          @click="$emit('update:technology', 'rf')"
        >
          RF Point-to-Point (1 to 80 GHz)
        </button>
        <button 
          type="button" 
          class="btn py-1 px-3 fw-semibold" 
          :class="technology === 'fso' ? 'btn-primary' : 'btn-outline-secondary'"
          @click="$emit('update:technology', 'fso')"
        >
          Free Space Optical (FSO)
        </button>
      </div>
    </div>

    <!-- RF Specific Inputs -->
    <div v-if="technology === 'rf'" class="row g-3">
      <div class="col-md-6">
        <label class="form-label">Operating Frequency Band (GHz) <span class="text-danger">*</span></label>
        <select v-model.number="rfParams.frequency_ghz" class="form-select form-select-sm">
          <option :value="2.4">2.4 GHz (Sub-6 ISM Band)</option>
          <option :value="5.8">5.8 GHz (Unlicensed ISM Backhaul)</option>
          <option :value="11">11 GHz (Licensed Microwave)</option>
          <option :value="18">18 GHz (Licensed High Capacity)</option>
          <option :value="24">24 GHz (ISM Millimeter Wave)</option>
          <option :value="60">60 GHz (V-Band Millimeter Wave)</option>
          <option :value="80">80 GHz (E-Band Millimeter Wave)</option>
        </select>
        <div class="form-text fs-7 text-muted">Select frequency band for 1st Fresnel zone clearance calculation</div>
      </div>

      <div class="col-md-6">
        <label class="form-label">Required Target Throughput (Mbps) <span class="text-danger">*</span></label>
        <div class="input-group input-group-sm">
          <input 
            type="number" 
            v-model.number="rfParams.required_throughput_mbps" 
            class="form-control font-monospace" 
            placeholder="100" 
          />
          <span class="input-group-text">Mbps</span>
        </div>
        <div class="form-text fs-7 text-muted">Determines recommended RF channel bandwidth clearance (20/40/80 MHz)</div>
      </div>
    </div>

    <!-- FSO Specific Inputs -->
    <div v-else class="row g-3">
      <!-- FSO Evaluation Mode Selector -->
      <div class="col-12 mb-2">
        <label class="form-label fw-bold text-primary mb-2">FSO Calculation Mode <span class="text-danger">*</span></label>
        <div class="btn-group w-100" role="group">
          <button 
            type="button" 
            class="btn btn-sm py-2 px-3 fw-semibold" 
            :class="fsoParams.fso_calc_mode === 'full_tech' ? 'btn-primary' : 'btn-outline-secondary'"
            @click="setFsoMode('full_tech')"
          >
            Full Technical Link Budget Analysis
          </button>
          <button 
            type="button" 
            class="btn btn-sm py-2 px-3 fw-semibold" 
            :class="fsoParams.fso_calc_mode === 'los_only' ? 'btn-warning text-dark' : 'btn-outline-secondary'"
            @click="setFsoMode('los_only')"
          >
            Line of Sight (LOS) Survey Only
          </button>
        </div>
      </div>

      <div v-if="fsoParams.fso_calc_mode === 'los_only'" class="col-12">
        <div class="alert alert-warning py-2 px-3 mb-0 small rounded-3 d-flex align-items-center">
          <svg class="me-2 flex-shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="16" x2="12" y2="12"/>
            <line x1="12" y1="8" x2="12.01" y2="8"/>
          </svg>
          <div>
            <strong>LOS Survey Mode Active:</strong> Link budget calculations disabled. Analysis will evaluate direct optical Line of Sight clearance, site elevations, and tower height recommendations.
          </div>
        </div>
      </div>

      <!-- Technical Parameters (Shown only when in Full Tech Mode) -->
      <template v-if="fsoParams.fso_calc_mode === 'full_tech'">
        <div class="col-md-6">
          <label class="form-label">Laser Wavelength Band <span class="text-danger">*</span></label>
          <select v-model="fsoParams.wavelength" class="form-select form-select-sm">
            <option value="850 nm">850 nm (Near Infrared)</option>
            <option value="1310 nm">1310 nm (Telecom Optical)</option>
            <option value="1550 nm">1550 nm (Eye-Safe High Power)</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Required Throughput (Mbps) <span class="text-danger">*</span></label>
          <div class="input-group input-group-sm">
            <input 
              type="number" 
              v-model.number="fsoParams.required_throughput_mbps" 
              class="form-control font-monospace" 
              placeholder="1000" 
            />
            <span class="input-group-text">Mbps</span>
          </div>
        </div>

        <div class="col-md-6">
          <label class="form-label">Tx Optical Power (dBm) <span class="text-danger">*</span></label>
          <input 
            type="number" 
            step="0.1" 
            v-model.number="fsoParams.tx_power_dbm" 
            class="form-control form-control-sm font-monospace" 
            placeholder="24" 
          />
        </div>

        <div class="col-md-6">
          <label class="form-label">Beam Divergence (mrad) <span class="text-danger">*</span></label>
          <input 
            type="number" 
            step="0.01" 
            v-model.number="fsoParams.beam_divergence_mrad" 
            class="form-control form-control-sm font-monospace" 
            placeholder="0.15" 
          />
        </div>

        <div class="col-md-6">
          <label class="form-label">Receiver Aperture Diameter (m) <span class="text-danger">*</span></label>
          <input 
            type="number" 
            step="0.01" 
            v-model.number="fsoParams.rx_aperture_m" 
            class="form-control form-control-sm font-monospace" 
            placeholder="0.9" 
          />
        </div>

        <div class="col-md-6">
          <label class="form-label">System / Optical Internal Loss (dB) <span class="text-danger">*</span></label>
          <input 
            type="number" 
            step="0.1" 
            v-model.number="fsoParams.system_loss_db" 
            class="form-control form-control-sm font-monospace" 
            placeholder="3" 
          />
        </div>

        <div class="col-md-6">
          <label class="form-label">Receiver Sensitivity (dBm) <span class="text-danger">*</span></label>
          <input 
            type="number" 
            step="0.1" 
            v-model.number="fsoParams.rx_sensitivity_dbm" 
            class="form-control form-control-sm font-monospace" 
            placeholder="-18" 
          />
        </div>

        <div class="col-md-6">
          <label class="form-label">Reference Fade Margin (dB @ 1 km) <span class="text-danger">*</span></label>
          <input 
            type="number" 
            step="0.1" 
            v-model.number="fsoParams.ref_fade_margin_db" 
            class="form-control form-control-sm font-monospace" 
            placeholder="22" 
          />
        </div>

        <div class="col-md-6">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <label class="form-label mb-0">City Visibility Reference</label>
            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fs-7 text-primary" @click="showVisibilityModal = true">
              View Standard Table &rarr;
            </button>
          </div>
          <select v-model="fsoParams.selected_city" class="form-select form-select-sm" @change="onCityChange">
            <option v-for="(data, city) in cityVisibilityData" :key="city" :value="city">
              {{ city }} (Worst: {{ data.worst }} km)
            </option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label">Design Visibility Baseline (km) <span class="text-danger">*</span></label>
          <div class="input-group input-group-sm">
            <input 
              type="number" 
              step="0.1" 
              v-model.number="fsoParams.visibility_km" 
              class="form-control font-monospace" 
              placeholder="2.0" 
            />
            <span class="input-group-text">km</span>
          </div>
        </div>
      </template>
    </div>

    <!-- Standard Visibility Reference Modal -->
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.65);" v-if="showVisibilityModal" @click.self="showVisibilityModal = false">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg text-main rounded-3" style="background-color: var(--bg-card); border: 1px solid var(--border-color) !important;">
          <div class="modal-header py-3 px-4" style="border-bottom: 1px solid var(--border-color);">
            <h6 class="modal-title fw-bold text-main">Standard Indian Cities Visibility Reference (km)</h6>
            <button type="button" class="btn-close" @click="showVisibilityModal = false"></button>
          </div>
          <div class="modal-body p-0" style="max-height: 480px; overflow-y: auto;">
            <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.85rem;">
              <thead class="sticky-top" style="background-color: var(--bg-card-header);">
                <tr>
                  <th>City</th>
                  <th class="text-center">Q1</th>
                  <th class="text-center">Q2</th>
                  <th class="text-center">Q3</th>
                  <th class="text-center">Q4</th>
                  <th class="text-center fw-bold text-danger">Worst Case</th>
                  <th class="text-end">Select</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(data, city) in cityVisibilityData" :key="city">
                  <td class="fw-semibold text-main">{{ city }}</td>
                  <td class="text-center text-secondary">{{ data.q1 }}</td>
                  <td class="text-center text-secondary">{{ data.q2 }}</td>
                  <td class="text-center text-secondary">{{ data.q3 }}</td>
                  <td class="text-center text-secondary">{{ data.q4 }}</td>
                  <td class="text-center fw-bold text-danger font-monospace">{{ data.worst }}</td>
                  <td class="text-end">
                    <button class="btn btn-xs btn-outline-primary py-0 px-2" @click="selectCity(city, data.worst)">
                      Use {{ data.worst }} km
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer py-2 px-3" style="border-top: 1px solid var(--border-color); background-color: var(--bg-card-header);">
            <button type="button" class="btn btn-secondary btn-sm" @click="showVisibilityModal = false">Close</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    technology: {
      type: String,
      default: 'rf'
    },
    rfParams: {
      type: Object,
      required: true
    },
    fsoParams: {
      type: Object,
      required: true
    }
  },
  emits: ['update:technology', 'recalculate'],
  data() {
    return {
      showVisibilityModal: false,
      cityVisibilityData: {
        'Agartala, Tripura': { q1: 1.2, q2: 3, q3: 4, q4: 1.8, worst: 1.2 },
        'Ahmedabad, Gujarat': { q1: 1.2, q2: 4, q3: 5, q4: 2.5, worst: 1.2 },
        'Aizawl, Mizoram': { q1: 2, q2: 3, q3: 4, q4: 2, worst: 2 },
        'Amritsar, Punjab': { q1: 0.3, q2: 3, q3: 5, q4: 1.5, worst: 0.3 },
        'Bengaluru, Karnataka': { q1: 2, q2: 4, q3: 4, q4: 2.5, worst: 2 },
        'Bhopal, Madhya Pradesh': { q1: 1.5, q2: 4, q3: 5, q4: 2.5, worst: 1.5 },
        'Bhubaneswar, Odisha': { q1: 2, q2: 4, q3: 4, q4: 2.5, worst: 2 },
        'Chennai, Tamil Nadu': { q1: 3, q2: 4, q3: 4, q4: 2.5, worst: 2.5 },
        'Dehradun, Uttarakhand': { q1: 1, q2: 4, q3: 5, q4: 2, worst: 1 },
        'Gangtok, Sikkim': { q1: 2, q2: 3, q3: 3, q4: 2, worst: 2 },
        'Gurugram, Haryana': { q1: 0.3, q2: 3, q3: 5, q4: 1.5, worst: 0.3 },
        'Guwahati, Assam': { q1: 1, q2: 3, q3: 4, q4: 1.5, worst: 1 },
        'Hyderabad, Telangana': { q1: 2.5, q2: 4, q3: 4, q4: 2.5, worst: 2.5 },
        'Imphal, Manipur': { q1: 1.2, q2: 3, q3: 4, q4: 1.8, worst: 1.2 },
        'Itanagar, Arunachal Pradesh': { q1: 1.5, q2: 3, q3: 4, q4: 2, worst: 1.5 },
        'Jaipur, Rajasthan': { q1: 0.8, q2: 4, q3: 6, q4: 2, worst: 0.8 },
        'Jammu, Jammu & Kashmir': { q1: 0.8, q2: 4, q3: 6, q4: 2, worst: 0.8 },
        'Kochi, Kerala': { q1: 3, q2: 4, q3: 4, q4: 3, worst: 3 },
        'Kohima, Nagaland': { q1: 1.8, q2: 3, q3: 4, q4: 2, worst: 1.8 },
        'Kolkata, West Bengal': { q1: 1, q2: 3, q3: 5, q4: 1.5, worst: 1 },
        'Lucknow, Uttar Pradesh': { q1: 0.4, q2: 3, q3: 5, q4: 1.5, worst: 0.4 },
        'Nagpur, Maharashtra': { q1: 1.8, q2: 4, q3: 5, q4: 2.5, worst: 1.8 },
        'New Delhi, Delhi': { q1: 0.2, q2: 3, q3: 5, q4: 1.2, worst: 0.2 },
        'Panaji, Goa': { q1: 3, q2: 5, q3: 4, q4: 4, worst: 3 },
        'Patna, Bihar': { q1: 0.5, q2: 3, q3: 5, q4: 1.5, worst: 0.5 },
        'Raipur, Chhattisgarh': { q1: 2, q2: 4, q3: 5, q4: 2.5, worst: 2 },
        'Ranchi, Jharkhand': { q1: 1.5, q2: 4, q3: 5, q4: 2, worst: 1.5 },
        'Shillong, Meghalaya': { q1: 1.5, q2: 3, q3: 3, q4: 2, worst: 1.5 },
        'Shimla, Himachal Pradesh': { q1: 2, q2: 4, q3: 4, q4: 3, worst: 2 },
        'Visakhapatnam, Andhra Pradesh': { q1: 3, q2: 4, q3: 4, q4: 3, worst: 3 }
      }
    };
  },
  methods: {
    setFsoMode(mode) {
      this.fsoParams.fso_calc_mode = mode;
      this.$emit('recalculate');
    },

    onCityChange() {
      const city = this.fsoParams.selected_city;
      if (this.cityVisibilityData[city]) {
        this.fsoParams.visibility_km = this.cityVisibilityData[city].worst;
      }
    },

    selectCity(cityName, worstVal) {
      this.fsoParams.selected_city = cityName;
      this.fsoParams.visibility_km = worstVal;
      this.showVisibilityModal = false;
    }
  }
}
</script>

<style scoped>
.fs-7 {
  font-size: 0.75rem;
}
</style>
