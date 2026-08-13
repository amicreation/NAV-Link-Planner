<template>
  <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.6);" v-if="show">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-dark text-white py-3">
          <h5 class="modal-title fw-bold">New Link Engineering Project</h5>
          <button type="button" class="btn-close btn-close-white" @click="$emit('close')"></button>
        </div>

        <form @submit.prevent="submitProject">
          <div class="modal-body p-4">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold small">Project Name <span class="text-danger">*</span></label>
                <input type="text" v-model="form.name" class="form-control" placeholder="e.g. Backbone Link Site A to Site B" required />
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small">Customer / Organization <span class="text-danger">*</span></label>
                <input type="text" v-model="form.customer_name" class="form-control" placeholder="e.g. Telecom Infra Corp" required />
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small">Field Engineer Name <span class="text-danger">*</span></label>
                <input type="text" v-model="form.engineer_name" class="form-control" placeholder="e.g. Eng. John Doe" required />
              </div>

              <div class="col-md-6">
                <label class="form-label fw-semibold small">Project Date <span class="text-danger">*</span></label>
                <input type="date" v-model="form.project_date" class="form-control" required />
              </div>

              <div class="col-12">
                <label class="form-label fw-semibold small">Link Technology <span class="text-danger">*</span></label>
                <div class="row g-3">
                  <div class="col-md-6">
                    <div 
                      class="card p-3 cursor-pointer text-center border-2" 
                      :class="form.technology === 'rf' ? 'border-primary bg-primary-subtle' : 'border-light-subtle'"
                      @click="form.technology = 'rf'"
                    >
                      <div class="fw-bold fs-6 mb-1 brand-blue">RF Point-to-Point</div>
                      <div class="text-secondary small">Microwave / Millimeter-Wave radio links (1 to 80 GHz)</div>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <div 
                      class="card p-3 cursor-pointer text-center border-2" 
                      :class="form.technology === 'fso' ? 'border-dark bg-dark-subtle' : 'border-light-subtle'"
                      @click="form.technology = 'fso'"
                    >
                      <div class="fw-bold fs-6 mb-1 text-dark">Free Space Optical (FSO)</div>
                      <div class="text-secondary small">High-capacity optical laser links (850, 1310, 1550 nm)</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer bg-light py-2 px-4">
            <button type="button" class="btn btn-secondary px-3" @click="$emit('close')">Cancel</button>
            <button type="submit" class="btn btn-primary px-4 fw-semibold" :disabled="loading">
              <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
              Create Project & Launch Workbench
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
.cursor-pointer {
  cursor: pointer;
}
.bg-primary-subtle {
  background-color: #eff6ff;
}
.bg-dark-subtle {
  background-color: #f1f5f9;
}
</style>
