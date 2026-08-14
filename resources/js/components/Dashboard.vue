<template>
  <div class="container-fluid px-4 py-4">
    <!-- Top Engineering Overview Banner -->
    <div class="card p-4 mb-4 border-0" style="background: linear-gradient(135deg, #172554 0%, #0F172A 100%); border: 1px solid var(--border-color) !important;">
      <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
          <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary font-monospace">ENGINEERING PLATFORM</span>
            <span class="text-muted small">&bull; WGS84 Geodetic Engine</span>
          </div>
          <h2 class="page-title text-white mb-1">Field Path Engineering Dashboard</h2>
          <p class="text-secondary small mb-0">Vendor-Independent RF & FSO Point-to-Point Wireless Path Design, Line-of-Sight Clearance & Optical Budget Analysis</p>
        </div>
        <div class="d-flex align-items-center gap-2">
          <button class="btn btn-primary px-4 fw-semibold shadow-sm" @click="$emit('change-tab', 'planner')">
            Quick Link Calculator
          </button>
        </div>
      </div>
    </div>

    <!-- KPI Metric Cards Grid -->
    <div class="row g-3 mb-4">
      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 card-hover h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="label-text">TOTAL PROJECTS</span>
            <div class="p-2 rounded-3 bg-primary-subtle text-primary">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
              </svg>
            </div>
          </div>
          <div class="fs-2 fw-bold text-white mb-1 font-monospace">{{ projects.length }}</div>
          <div class="small text-muted">Stored Engineering Links</div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 card-hover h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="label-text">RF POINT-TO-POINT</span>
            <div class="p-2 rounded-3 bg-info-subtle text-info">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4.9 19.1C1 15.2 1 8.8 4.9 4.9"/>
                <path d="M7.8 16.2c-2.3-2.3-2.3-6.1 0-8.5"/>
                <circle cx="12" cy="12" r="2"/>
                <path d="M16.2 7.8c2.3 2.3 2.3 6.1 0 8.5"/>
                <path d="M19.1 4.9c3.9 3.9 3.9 10.3 0 14.2"/>
              </svg>
            </div>
          </div>
          <div class="fs-2 fw-bold text-info mb-1 font-monospace">{{ rfCount }}</div>
          <div class="small text-muted">Microwave / Millimeter-Wave</div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 card-hover h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="label-text">FREE SPACE OPTICAL</span>
            <div class="p-2 rounded-3 bg-purple-subtle text-purple">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="6"/>
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
              </svg>
            </div>
          </div>
          <div class="fs-2 fw-bold text-purple mb-1 font-monospace">{{ fsoCount }}</div>
          <div class="small text-muted">Laser Telecom (850/1310/1550 nm)</div>
        </div>
      </div>

      <div class="col-12 col-sm-6 col-xl-3">
        <div class="card p-3 card-hover h-100">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="label-text">GEODETIC ENGINE</span>
            <div class="p-2 rounded-3 bg-success-subtle text-success">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="2" y1="12" x2="22" y2="12"/>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
              </svg>
            </div>
          </div>
          <div class="fs-5 fw-bold text-success mb-1">WGS84 Ellipsoid</div>
          <div class="small text-muted">Double Precision (8 Decimals)</div>
        </div>
      </div>
    </div>

    <!-- Technology Module Launchers -->
    <div class="row g-4 mb-4">
      <div class="col-md-6">
        <div class="card h-100 p-4 border card-hover">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div class="d-flex align-items-center gap-3">
              <div class="p-3 bg-primary text-white rounded-3">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4.9 19.1C1 15.2 1 8.8 4.9 4.9"/>
                  <path d="M7.8 16.2c-2.3-2.3-2.3-6.1 0-8.5"/>
                  <circle cx="12" cy="12" r="2"/>
                  <path d="M16.2 7.8c2.3 2.3 2.3 6.1 0 8.5"/>
                  <path d="M19.1 4.9c3.9 3.9 3.9 10.3 0 14.2"/>
                </svg>
              </div>
              <div>
                <h4 class="card-title mb-1">RF Point-to-Point Path Planner</h4>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle">1 to 80 GHz Bands</span>
              </div>
            </div>
          </div>
          <p class="body-text mb-4">
            Compute 1st Fresnel zone 60% clearance margin, earth curvature drop elevation loss, Great-Circle azimuth bearings, mechanical antenna tilt, and channel bandwidth recommendations.
          </p>
          <button class="btn btn-outline-primary fw-semibold mt-auto align-self-start" @click="startTechProject('rf')">
            Launch RF Module &rarr;
          </button>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card h-100 p-4 border card-hover">
          <div class="d-flex align-items-start justify-content-between mb-3">
            <div class="d-flex align-items-center gap-3">
              <div class="p-3 bg-purple text-white rounded-3">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="6"/>
                  <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
                </svg>
              </div>
              <div>
                <h4 class="card-title mb-1">Free Space Optical (FSO) Planner</h4>
                <span class="badge bg-purple-subtle text-purple border border-purple-subtle">850 nm / 1310 nm / 1550 nm</span>
              </div>
            </div>
          </div>
          <p class="body-text mb-4">
            Model optical laser beam divergence, spot diameter at receiver aperture, Kruse atmospheric attenuation, fog visibility risk assessment, and optical fade margins.
          </p>
          <button class="btn btn-outline-purple fw-semibold mt-auto align-self-start" @click="startTechProject('fso')">
            Launch FSO Module &rarr;
          </button>
        </div>
      </div>
    </div>

    <!-- Searchable & Filterable Recent Projects Data Table -->
    <div class="card border">
      <div class="card-header py-3 px-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
          <svg class="text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
          </svg>
          <h5 class="card-title mb-0">Recent Field Engineering Projects</h5>
        </div>

        <!-- Filter Controls -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <input 
            type="text" 
            v-model="searchQuery" 
            class="form-control form-control-sm" 
            placeholder="Search projects..." 
            style="width: 220px;"
          />
          <select v-model="techFilter" class="form-select form-select-sm" style="width: 140px;">
            <option value="all">All Tech</option>
            <option value="rf">RF Only</option>
            <option value="fso">FSO Only</option>
          </select>
          <button class="btn btn-sm btn-link text-decoration-none text-muted" @click="$emit('change-tab', 'projects')">
            View All &rarr;
          </button>
        </div>
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
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredProjects.length === 0">
                <td colspan="6" class="text-center py-5 text-muted">
                  No projects matching filter criteria. Click <strong>+ New Project</strong> to create one.
                </td>
              </tr>
              <tr v-for="proj in filteredProjects" :key="proj.id">
                <td>
                  <div class="fw-semibold text-primary cursor-pointer" @click="$emit('load-project', proj)">
                    {{ proj.name }}
                  </div>
                </td>
                <td class="text-secondary">{{ proj.customer_name }}</td>
                <td class="text-secondary">{{ proj.engineer_name }}</td>
                <td>
                  <span class="badge" :class="proj.technology === 'rf' ? 'badge-tech-rf' : 'badge-tech-fso'">
                    {{ proj.technology.toUpperCase() }}
                  </span>
                </td>
                <td class="small text-muted font-monospace">{{ proj.project_date }}</td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-primary" @click="$emit('load-project', proj)">
                      Load Workbench
                    </button>
                    <a :href="`/api/projects/${proj.id}/pdf`" class="btn btn-outline-secondary" target="_blank" title="Download PDF Report">
                      PDF
                    </a>
                    <button class="btn btn-outline-danger" @click="$emit('delete-project', proj)" title="Delete Project">
                      Delete
                    </button>
                  </div>
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
  data() {
    return {
      searchQuery: '',
      techFilter: 'all'
    };
  },
  emits: ['open-new-project', 'change-tab', 'load-project', 'quick-tech', 'delete-project'],
  computed: {
    rfCount() {
      return this.projects.filter(p => p.technology === 'rf').length;
    },
    fsoCount() {
      return this.projects.filter(p => p.technology === 'fso').length;
    },
    filteredProjects() {
      return this.projects.filter(p => {
        const matchesTech = this.techFilter === 'all' || p.technology === this.techFilter;
        const q = this.searchQuery.toLowerCase();
        const matchesSearch = !q || 
          (p.name && p.name.toLowerCase().includes(q)) ||
          (p.customer_name && p.customer_name.toLowerCase().includes(q)) ||
          (p.engineer_name && p.engineer_name.toLowerCase().includes(q));
        return matchesTech && matchesSearch;
      });
    }
  },
  methods: {
    startTechProject(tech) {
      this.$emit('quick-tech', tech);
    }
  }
}
</script>

<style scoped>
.bg-purple {
  background-color: #8b5cf6 !important;
}
.text-purple {
  color: #c084fc !important;
}
.bg-purple-subtle {
  background-color: rgba(139, 92, 246, 0.15) !important;
}
.border-purple-subtle {
  border-color: rgba(139, 92, 246, 0.3) !important;
}
.btn-outline-purple {
  border-color: #8b5cf6;
  color: #c084fc;
}
.btn-outline-purple:hover {
  background-color: #8b5cf6;
  color: #ffffff;
}
</style>
