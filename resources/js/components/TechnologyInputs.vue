<template>
  <div class="card border mb-3">
    <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between">
      <span class="fw-bold text-dark">Technology Specific Engineering Parameters</span>
      <div class="btn-group btn-group-sm">
        <button 
          type="button" 
          class="btn" 
          :class="technology === 'rf' ? 'btn-primary' : 'btn-outline-secondary'"
          @click="$emit('update:technology', 'rf')"
        >
          RF Point-to-Point
        </button>
        <button 
          type="button" 
          class="btn" 
          :class="technology === 'fso' ? 'btn-dark' : 'btn-outline-secondary'"
          @click="$emit('update:technology', 'fso')"
        >
          Free Space Optical (FSO)
        </button>
      </div>
    </div>

    <div class="card-body p-3">
      <!-- RF Specific Inputs -->
      <div v-if="technology === 'rf'" class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold small mb-1">Frequency (GHz) <span class="text-danger">*</span></label>
          <div class="input-group input-group-sm">
            <input 
              type="number" 
              step="0.1" 
              v-model.number="rfParams.frequency_ghz" 
              class="form-control" 
              placeholder="5.8" 
            />
            <span class="input-group-text">GHz</span>
          </div>
          <div class="form-text small">Common bands: 2.4, 5.8, 11, 18, 24, 60, 80 GHz</div>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold small mb-1">Required Throughput (Mbps) <span class="text-danger">*</span></label>
          <div class="input-group input-group-sm">
            <input 
              type="number" 
              v-model.number="rfParams.required_throughput_mbps" 
              class="form-control" 
              placeholder="100" 
            />
            <span class="input-group-text">Mbps</span>
          </div>
          <div class="form-text small">Target throughput for channel width calculation</div>
        </div>
      </div>

      <!-- FSO Specific Inputs -->
      <div v-else class="row g-3">
        <!-- Row 1: Laser Wavelength & Required Throughput -->
        <div class="col-md-6">
          <label class="form-label fw-semibold small mb-1">Laser Wavelength <span class="text-danger">*</span></label>
          <select v-model="fsoParams.wavelength" class="form-select form-select-sm">
            <option value="850 nm">850 nm (Near Infrared)</option>
            <option value="1310 nm">1310 nm (Telecom Optical)</option>
            <option value="1550 nm">1550 nm (Eye-Safe High Power)</option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold small mb-1">Required Throughput (Mbps) <span class="text-danger">*</span></label>
          <div class="input-group input-group-sm">
            <input 
              type="number" 
              v-model.number="fsoParams.required_throughput_mbps" 
              class="form-control" 
              placeholder="1000" 
            />
            <span class="input-group-text">Mbps</span>
          </div>
        </div>

        <!-- Row 2: Tx Optical Power & Beam Divergence -->
        <div class="col-md-6">
          <label class="form-label fw-semibold small mb-1">Tx Optical Power (dBm) <span class="text-danger">*</span></label>
          <input 
            type="number" 
            step="0.1" 
            v-model.number="fsoParams.tx_power_dbm" 
            class="form-control form-control-sm" 
            placeholder="24" 
          />
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold small mb-1">Beam Divergence (mrad) <span class="text-danger">*</span></label>
          <input 
            type="number" 
            step="0.01" 
            v-model.number="fsoParams.beam_divergence_mrad" 
            class="form-control form-control-sm" 
            placeholder="0.15" 
          />
        </div>

        <!-- Row 3: Receiver Aperture & System Loss -->
        <div class="col-md-6">
          <label class="form-label fw-semibold small mb-1">Receiver Aperture Diameter (m) <span class="text-danger">*</span></label>
          <input 
            type="number" 
            step="0.01" 
            v-model.number="fsoParams.rx_aperture_m" 
            class="form-control form-control-sm" 
            placeholder="0.9" 
          />
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold small mb-1">System / Optical Loss (dB) <span class="text-danger">*</span></label>
          <input 
            type="number" 
            step="0.1" 
            v-model.number="fsoParams.system_loss_db" 
            class="form-control form-control-sm" 
            placeholder="3" 
          />
        </div>

        <!-- Row 4: Receiver Sensitivity & Reference Fade Margin -->
        <div class="col-md-6">
          <label class="form-label fw-semibold small mb-1">Receiver Sensitivity (dBm) <span class="text-danger">*</span></label>
          <input 
            type="number" 
            step="0.1" 
            v-model.number="fsoParams.rx_sensitivity_dbm" 
            class="form-control form-control-sm" 
            placeholder="-18" 
          />
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold small mb-1">Reference Fade Margin (dB @ 1 km) <span class="text-danger">*</span></label>
          <input 
            type="number" 
            step="0.1" 
            v-model.number="fsoParams.ref_fade_margin_db" 
            class="form-control form-control-sm" 
            placeholder="22" 
          />
        </div>

        <!-- Row 5: City & Visibility Reference Selector -->
        <div class="col-md-6">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <label class="form-label fw-semibold small mb-0">City Visibility Location Reference</label>
            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" @click="showVisibilityModal = true">
              View Standard Table &rarr;
            </button>
          </div>
          <select v-model="fsoParams.selected_city" class="form-select form-select-sm" @change="onCityChange">
            <option v-for="(data, city) in cityVisibilityData" :key="city" :value="city">
              {{ city }} (Worst Case: {{ data.worst }} km)
            </option>
          </select>
        </div>

        <div class="col-md-6">
          <label class="form-label fw-semibold small mb-1">Design Visibility Baseline (km) <span class="text-danger">*</span></label>
          <div class="input-group input-group-sm">
            <input 
              type="number" 
              step="0.1" 
              v-model.number="fsoParams.visibility_km" 
              class="form-control" 
              placeholder="2.0" 
            />
            <span class="input-group-text">km</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Standard Visibility Reference Modal -->
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.6);" v-if="showVisibilityModal">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
          <div class="modal-header bg-success text-white py-2 px-3">
            <h6 class="modal-title fw-bold">Standard Visibility Reference (km)</h6>
            <button type="button" class="btn-close btn-close-white" @click="showVisibilityModal = false"></button>
          </div>
          <div class="modal-body p-0" style="max-height: 480px; overflow-y: auto;">
            <table class="table table-striped table-hover table-sm align-middle mb-0" style="font-size: 0.85rem;">
              <thead class="table-success sticky-top">
                <tr>
                  <th>City</th>
                  <th class="text-center">Q1</th>
                  <th class="text-center">Q2</th>
                  <th class="text-center">Q3</th>
                  <th class="text-center">Q4</th>
                  <th class="text-center fw-bold">Worst Case</th>
                  <th class="text-end">Select</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="(data, city) in cityVisibilityData" :key="city">
                  <td class="fw-semibold">{{ city }}</td>
                  <td class="text-center">{{ data.q1 }}</td>
                  <td class="text-center">{{ data.q2 }}</td>
                  <td class="text-center">{{ data.q3 }}</td>
                  <td class="text-center">{{ data.q4 }}</td>
                  <td class="text-center fw-bold text-danger">{{ data.worst }}</td>
                  <td class="text-end">
                    <button class="btn btn-xs btn-outline-success py-0 px-2" @click="selectCity(city, data.worst)">
                      Use {{ data.worst }} km
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="modal-footer py-2 px-3">
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
  emits: ['update:technology'],
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
    onCityChange() {
      const city = this.fsoParams.selected_city;
      if (this.cityVisibilityData[city]) {
        this.fsoParams.visibility_km = this.cityVisibilityData[city].worst;
      }
    },
    selectCity(city, worstVis) {
      this.fsoParams.selected_city = city;
      this.fsoParams.visibility_km = worstVis;
      this.showVisibilityModal = false;
    }
  }
}
</script>
