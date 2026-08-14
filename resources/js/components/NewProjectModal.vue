<template>
  <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.65);" v-if="show" @click.self="$emit('close')">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg text-main rounded-3" style="background-color: var(--bg-card); border: 1px solid var(--border-color) !important;">
        <div class="modal-header py-3 px-4" style="border-bottom: 1px solid var(--border-color);">
          <div class="d-flex align-items-center gap-2">
            <svg class="text-primary" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="12" cy="12" r="10"/>
              <line x1="12" y1="8" x2="12" y2="16"/>
              <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            <h5 class="modal-title fw-bold text-main">New Link Engineering Project</h5>
          </div>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>

        <form @submit.prevent="submitProject">
          <div class="modal-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Project Name <span class="text-danger">*</span></label>
                <input type="text" v-model="form.name" class="form-control" placeholder="e.g. Backbone Link Site A to Site B" required />
              </div>

              <div class="col-md-6">
                <label class="form-label">Customer / Organization <span class="text-danger">*</span></label>
                <input type="text" v-model="form.customer_name" class="form-control" placeholder="e.g. Telecom Infra Corp" required />
              </div>

              <div class="col-md-6">
                <label class="form-label">Field Engineer Name <span class="text-danger">*</span></label>
                <input type="text" v-model="form.engineer_name" class="form-control" placeholder="e.g. Eng. John Doe" required />
              </div>

              <div class="col-md-6">
                <label class="form-label">Project Date <span class="text-danger">*</span></label>
                <input type="date" v-model="form.project_date" class="form-control" required />
              </div>

              <div class="col-12">
                <label class="form-label mb-2">Link Technology Module <span class="text-danger">*</span></label>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div 
                      class="card p-3 cursor-pointer text-center border-2 card-hover h-100" 
                      :class="form.technology === 'rf' ? 'border-primary bg-primary-subtle' : 'border-secondary'"
                      style="background-color: var(--bg-input);"
                      @click="form.technology = 'rf'"
                    >
                      <div class="fw-bold fs-6 mb-1 text-primary">RF Point-to-Point</div>
                      <div class="text-secondary small">Microwave & Millimeter-Wave Radio Links (1 to 80 GHz)</div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div 
                      class="card p-3 cursor-pointer text-center border-2 card-hover h-100" 
                      :class="form.technology === 'fso' ? 'border-purple bg-purple-subtle' : 'border-secondary'"
                      style="background-color: var(--bg-input);"
                      @click="form.technology = 'fso'"
                    >
                      <div class="fw-bold fs-6 mb-1 text-purple">Free Space Optical (FSO)</div>
                      <div class="text-secondary small">High-Capacity Optical Laser Links (850, 1310, 1550 nm)</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer py-3 px-4" style="border-top: 1px solid var(--border-color); background-color: var(--bg-card-header);">
            <button type="button" class="btn btn-secondary px-3" @click="$emit('close')">Cancel</button>
            <button type="submit" class="btn btn-primary px-4 fw-semibold d-flex align-items-center gap-2" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
              Create Project & Launch Planner
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    show: {
      type: Boolean,
      default: false
    },
    initialTech: {
      type: String,
      default: 'rf'
    }
  },
  emits: ['close', 'project-created'],
  data() {
    return {
      loading: false,
      form: {
        name: '',
        customer_name: '',
        engineer_name: '',
        project_date: new Date().toISOString().slice(0, 10),
        technology: this.initialTech || 'rf'
      }
    };
  },
  watch: {
    initialTech(val) {
      if (val) this.form.technology = val;
    }
  },
  methods: {
    async submitProject() {
      this.loading = true;
      try {
        const payload = {
          ...this.form,
          site_a: {
            name: 'Site A',
            latitude: 12.97159870,
            longitude: 77.59456270,
            tower_height: 30.000,
            ground_elevation: 920.000
          },
          site_b: {
            name: 'Site B',
            latitude: 13.03582450,
            longitude: 77.62514820,
            tower_height: 30.000,
            ground_elevation: 915.000
          }
        };

        const res = await fetch('/api/projects', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(payload)
        });

        const data = await res.json();
        if (res.ok && data.status === 'success') {
          this.$emit('project-created', data.data);
          this.$emit('close');
        } else {
          alert('Error creating project: ' + (data.message || 'Validation failed'));
        }
      } catch (err) {
        alert('Server request failed: ' + err.message);
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>
.bg-primary-subtle {
  background-color: rgba(37, 99, 235, 0.15) !important;
}
.bg-purple-subtle {
  background-color: rgba(139, 92, 246, 0.15) !important;
}
.border-purple {
  border-color: #8b5cf6 !important;
}
.text-purple {
  color: #c084fc !important;
}
</style>
