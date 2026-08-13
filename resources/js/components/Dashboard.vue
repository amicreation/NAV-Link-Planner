<template>
  <div class="container-fluid px-4 py-4">
    <!-- Quick Header Banner -->
    <div class="d-flex align-items-center justify-content-between pb-3 mb-4 border-bottom">
      <div>
        <h4 class="fw-bold mb-1 brand-blue">Field Engineering Dashboard</h4>
        <p class="text-secondary small mb-0">Vendor-Independent RF & FSO Point-to-Point Path Engineering Tool</p>
      </div>
      <div>
        <button class="btn btn-primary px-3 fw-semibold me-2" @click="$emit('open-new-project')">
          + New Project
        </button>
        <button class="btn btn-outline-secondary px-3" @click="$emit('change-tab', 'planner')">
          Quick Calculator
        </button>
      </div>
    </div>

    <!-- Overview Cards -->
    <div class="row g-3 mb-4">
      <div class="col-md-3">
        <div class="card p-3 border-start border-primary border-4 shadow-sm">
          <div class="text-uppercase text-muted fw-bold small">Total Projects</div>
          <div class="fs-3 fw-bold text-dark mt-1">{{ projects.length }}</div>
          <div class="small text-primary mt-1">Stored in Database</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-3 border-start border-info border-4 shadow-sm">
          <div class="text-uppercase text-muted fw-bold small">RF Links</div>
          <div class="fs-3 fw-bold text-dark mt-1">{{ rfCount }}</div>
          <div class="small text-info mt-1">Point-to-Point Wireless</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-3 border-start border-warning border-4 shadow-sm">
          <div class="text-uppercase text-muted fw-bold small">FSO Links</div>
          <div class="fs-3 fw-bold text-dark mt-1">{{ fsoCount }}</div>
          <div class="small text-warning mt-1">Free Space Optical</div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card p-3 border-start border-success border-4 shadow-sm">
          <div class="text-uppercase text-muted fw-bold small">Geodetic Model</div>
          <div class="fs-5 fw-bold text-dark mt-1">WGS84 Ellipsoid</div>
          <div class="small text-success mt-1">Double Precision (8 Decimals)</div>
        </div>
      </div>
    </div>

    <!-- Action Shortcuts Grid -->
    <div class="row g-4 mb-4">
      <div class="col-md-6">
        <div class="card h-100 p-4 border shadow-sm">
          <div class="d-flex align-items-center mb-3">
            <div class="bg-primary text-white p-2 rounded me-3">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M2 12h20"/></svg>
            </div>
            <div>
              <h5 class="fw-bold mb-0">RF Point-to-Point Planning</h5>
              <span class="badge bg-light text-secondary border">5 GHz, 11 GHz, 24 GHz, 60 GHz</span>
            </div>
          </div>
          <p class="text-secondary small">
            Perform 1st Fresnel zone clearance calculation, earth curvature drop compensation, Great-Circle azimuth, antenna tilt, and channel width recommendations.
          </p>
          <button class="btn btn-sm btn-outline-primary align-self-start fw-semibold" @click="startTechProject('rf')">
            Launch RF Module &rarr;
          </button>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100 p-4 border shadow-sm">
          <div class="d-flex align-items-center mb-3">
            <div class="bg-dark text-white p-2 rounded me-3">
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="8"/><line x1="12" y1="2" x2="12" y2="22"/></svg>
            </div>
            <div>
              <h5 class="fw-bold mb-0">Free Space Optical (FSO)</h5>
              <span class="badge bg-light text-secondary border">850 nm, 1310 nm, 1550 nm</span>
            </div>
          </div>
          <p class="text-secondary small">
            Calculate optical laser divergence, spot diameter at receiver, wavelength-dependent atmospheric loss, fog attenuation risk, and practical link distance bounds.
          </p>
          <button class="btn btn-sm btn-outline-dark align-self-start fw-semibold" @click="startTechProject('fso')">
            Launch FSO Module &rarr;
          </button>
        </div>
      </div>
    </div>

    <!-- Recent Projects Table -->
    <div class="card border shadow-sm">
      <div class="card-header d-flex align-items-center justify-content-between py-3">
        <h6 class="fw-bold mb-0">Recent Field Engineering Projects</h6>
        <button class="btn btn-sm btn-link text-decoration-none" @click="$emit('change-tab', 'projects')">View All Projects</button>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Project Name</th>
                <th>Customer</th>
                <th>Engineer</th>
                <th>Technology</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="projects.length === 0">
                <td colspan="6" class="text-center py-4 text-muted">
                  No projects created yet. Click <strong>+ New Project</strong> to begin.
                </td>
              </tr>
              <tr v-for="proj in projects" :key="proj.id">
                <td class="fw-semibold text-primary">{{ proj.name }}</td>
                <td>{{ proj.customer_name }}</td>
                <td>{{ proj.engineer_name }}</td>
                <td>
                  <span class="badge" :class="proj.technology === 'rf' ? 'bg-primary' : 'bg-dark'">
                    {{ proj.technology.toUpperCase() }}
                  </span>
                </td>
                <td class="small text-secondary">{{ proj.project_date }}</td>
                <td>
                  <button class="btn btn-sm btn-outline-primary py-0 me-1" @click="$emit('load-project', proj)">
                    Load Workbench
                  </button>
                  <a :href="`/api/projects/${proj.id}/pdf`" class="btn btn-sm btn-outline-secondary py-0 me-1" target="_blank">
                    PDF Report
                  </a>
                  <button class="btn btn-sm btn-outline-danger py-0" @click="$emit('delete-project', proj)" title="Delete Project">
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    projects: {
      type: Array,
      default: () => []
    }
  },
  emits: ['open-new-project', 'change-tab', 'load-project', 'quick-tech', 'delete-project'],
  computed: {
    rfCount() {
      return this.projects.filter(p => p.technology === 'rf').length;
    },
    fsoCount() {
      return this.projects.filter(p => p.technology === 'fso').length;
    }
  },
  methods: {
    startTechProject(tech) {
      this.$emit('quick-tech', tech);
    }
  }
}
</script>
