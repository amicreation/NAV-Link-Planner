<template>
  <div class="container-fluid px-4 py-4">
    <!-- Header Banner -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between pb-3 mb-4 border-bottom border-secondary gap-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge bg-primary font-monospace">HARDWARE REFERENCE</span>
          <span class="text-muted small">&bull; Custom Hardware Specifications</span>
        </div>
        <h3 class="page-title text-main mb-0">Equipment Library</h3>
        <p class="text-secondary small mb-0">Add or manage equipment specifications for Microwave Antennas, Radios, and FSO Laser Transceivers</p>
      </div>

      <div class="d-flex align-items-center gap-2">
        <select v-model="category" class="form-select form-select-sm" style="width: 160px;">
          <option value="all">All Types</option>
          <option value="antennas">RF Antennas</option>
          <option value="radios">RF Radios</option>
          <option value="fso">FSO Transceivers</option>
        </select>

        <button class="btn btn-primary btn-sm fw-semibold d-flex align-items-center gap-2 px-3" @click="showAddModal = true">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Add Equipment
        </button>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="filteredItems.length === 0" class="card border p-5 text-center">
      <div class="py-4">
        <svg class="text-secondary mb-3" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
          <rect x="2" y="2" width="20" height="8" rx="2" ry="2"/>
          <rect x="2" y="14" width="20" height="8" rx="2" ry="2"/>
          <line x1="6" y1="6" x2="6.01" y2="6"/>
          <line x1="6" y1="18" x2="6.01" y2="18"/>
        </svg>
        <h5 class="text-main fw-bold">No Equipment Added</h5>
        <p class="text-muted small">Click the "Add Equipment" button above to add custom antenna or transceiver specifications.</p>
        <button class="btn btn-primary btn-sm mt-2" @click="showAddModal = true">+ Add Equipment</button>
      </div>
    </div>

    <!-- Equipment Grid -->
    <div v-else class="row g-4">
      <div v-for="item in filteredItems" :key="item.id" class="col-md-6 col-lg-4">
        <div class="card h-100 border card-hover p-4">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div>
              <span class="badge mb-2 font-monospace text-uppercase" :class="item.type === 'fso' ? 'badge-tech-fso' : 'badge-tech-rf'">
                {{ item.category }}
              </span>
              <h5 class="card-title text-main mb-1">{{ item.name }}</h5>
              <div class="small text-muted font-monospace">{{ item.vendor }} &bull; {{ item.model }}</div>
            </div>

            <!-- Delete Action Button -->
            <button class="btn btn-outline-danger btn-sm p-1" @click="deleteItem(item.id)" title="Delete Equipment">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
              </svg>
            </button>
          </div>
          
          <p class="body-text small text-secondary mb-3">{{ item.description }}</p>

          <table class="table table-sm table-borderless fs-7 mb-0">
            <tbody>
              <tr v-for="(val, key) in item.specs" :key="key">
                <td class="text-muted px-0 py-1">{{ key }}</td>
                <td class="text-main fw-bold font-monospace text-end px-0 py-1">{{ val }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Add Equipment Modal -->
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.65);" v-if="showAddModal" @click.self="showAddModal = false">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg text-main rounded-3" style="background-color: var(--bg-card); border: 1px solid var(--border-color) !important;">
          <div class="modal-header py-3 px-4" style="border-bottom: 1px solid var(--border-color);">
            <h5 class="modal-title fw-bold text-main">Add Equipment Specification</h5>
            <button type="button" class="btn-close" @click="showAddModal = false"></button>
          </div>
          <form @submit.prevent="saveEquipment">
            <div class="modal-body p-4">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Equipment Name <span class="text-danger">*</span></label>
                  <input type="text" v-model="newItem.name" class="form-control" placeholder="e.g. Parabolic Dish Antenna" required />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Vendor / Manufacturer</label>
                  <input type="text" v-model="newItem.vendor" class="form-control" placeholder="e.g. NAV Wireless" />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Model Number</label>
                  <input type="text" v-model="newItem.model" class="form-control" placeholder="e.g. NAV-ANT-30" />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Equipment Category <span class="text-danger">*</span></label>
                  <select v-model="newItem.category" class="form-select">
                    <option value="antennas">RF Antennas</option>
                    <option value="radios">RF Radios</option>
                    <option value="fso">FSO Transceivers</option>
                  </select>
                </div>

                <div class="col-12">
                  <label class="form-label">Description</label>
                  <textarea v-model="newItem.description" class="form-control" rows="2" placeholder="Brief technical summary..."></textarea>
                </div>

                <div class="col-12">
                  <label class="form-label font-monospace text-primary">Technical Specifications (Key: Value)</label>
                  <div class="row g-2">
                    <div class="col-md-6">
                      <input type="text" v-model="specKey1" class="form-control form-control-sm" placeholder="Key 1 (e.g. Frequency Range)" />
                    </div>
                    <div class="col-md-6">
                      <input type="text" v-model="specVal1" class="form-control form-control-sm" placeholder="Value 1 (e.g. 5.1 - 5.8 GHz)" />
                    </div>

                    <div class="col-md-6">
                      <input type="text" v-model="specKey2" class="form-control form-control-sm" placeholder="Key 2 (e.g. Antenna Gain)" />
                    </div>
                    <div class="col-md-6">
                      <input type="text" v-model="specVal2" class="form-control form-control-sm" placeholder="Value 2 (e.g. 30 dBi)" />
                    </div>

                    <div class="col-md-6">
                      <input type="text" v-model="specKey3" class="form-control form-control-sm" placeholder="Key 3 (e.g. Beamwidth)" />
                    </div>
                    <div class="col-md-6">
                      <input type="text" v-model="specVal3" class="form-control form-control-sm" placeholder="Value 3 (e.g. 5.0 deg)" />
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="modal-footer py-3 px-4" style="border-top: 1px solid var(--border-color); background-color: var(--bg-card-header);">
              <button type="button" class="btn btn-secondary px-3" @click="showAddModal = false">Cancel</button>
              <button type="submit" class="btn btn-primary px-4 fw-semibold">Save Equipment</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      category: 'all',
      showAddModal: false,
      specKey1: '', specVal1: '',
      specKey2: '', specVal2: '',
      specKey3: '', specVal3: '',
      newItem: {
        name: '',
        vendor: '',
        model: '',
        category: 'antennas',
        description: ''
      },
      items: [
        {
          id: 1,
          name: 'High-Gain Parabolic Dish Antenna',
          vendor: 'NAV Wireless',
          model: 'ANT-5G-30dBi',
          type: 'rf',
          category: 'antennas',
          description: 'High-performance 5 GHz dual-polarized dish antenna for long-range point-to-point backhaul.',
          specs: {
            'Frequency Range': '4.9 - 5.85 GHz',
            'Antenna Gain': '30.0 dBi',
            'Beamwidth (3dB)': '5.0°'
          }
        }
      ]
    };
  },
  computed: {
    filteredItems() {
      if (this.category === 'all') return this.items;
      return this.items.filter(i => i.category === this.category);
    }
  },
  methods: {
    saveEquipment() {
      const specs = {};
      if (this.specKey1.trim() && this.specVal1.trim()) specs[this.specKey1.trim()] = this.specVal1.trim();
      if (this.specKey2.trim() && this.specVal2.trim()) specs[this.specKey2.trim()] = this.specVal2.trim();
      if (this.specKey3.trim() && this.specVal3.trim()) specs[this.specKey3.trim()] = this.specVal3.trim();

      const item = {
        id: Date.now(),
        name: this.newItem.name,
        vendor: this.newItem.vendor || 'Generic',
        model: this.newItem.model || 'N/A',
        type: this.newItem.category === 'fso' ? 'fso' : 'rf',
        category: this.newItem.category,
        description: this.newItem.description || 'Custom equipment specification',
        specs: specs
      };

      this.items.unshift(item);
      this.showAddModal = false;
      this.newItem = { name: '', vendor: '', model: '', category: 'antennas', description: '' };
      this.specKey1 = ''; this.specVal1 = '';
      this.specKey2 = ''; this.specVal2 = '';
      this.specKey3 = ''; this.specVal3 = '';
    },

    deleteItem(id) {
      if (confirm('Delete this equipment item from library?')) {
        this.items = this.items.filter(i => i.id !== id);
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
