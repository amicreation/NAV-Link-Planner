<template>
  <div class="container-fluid px-4 py-4">
    <!-- Header Banner -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between pb-3 mb-4 border-bottom border-secondary">
      <div>
        <h3 class="page-title text-main mb-1">Official Engineering Reports</h3>
        <p class="text-secondary small mb-0">Generate, preview, and download official PDF path engineering documentations</p>
      </div>

      <div class="d-flex align-items-center gap-2 mt-2 mt-md-0">
        <input 
          type="text" 
          v-model="searchQuery" 
          class="form-control form-control-sm" 
          placeholder="Search reports..." 
          style="width: 220px;"
        />
        <select v-model="techFilter" class="form-select form-select-sm" style="width: 140px;">
          <option value="all">All Tech</option>
          <option value="rf">RF Only</option>
          <option value="fso">FSO Only</option>
        </select>
      </div>
    </div>

    <!-- Reports Data Table -->
    <div class="card border">
      <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <svg class="text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
            <polyline points="10 9 9 9 8 9"/>
          </svg>
          <h5 class="card-title mb-0">All Generated PDF Reports</h5>
        </div>
        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace">{{ filteredProjects.length }} Records</span>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Project Name</th>
                <th>Customer / Organization</th>
                <th>Field Engineer</th>
                <th>Technology</th>
                <th>Date</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="filteredProjects.length === 0">
                <td colspan="6" class="text-center py-5 text-muted">No engineering reports found.</td>
              </tr>
              <tr v-for="proj in filteredProjects" :key="proj.id">
                <td>
                  <div class="fw-semibold text-primary font-monospace">{{ proj.name }}</div>
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
                  <a :href="`/api/projects/${proj.id}/pdf`" class="btn btn-sm btn-primary fw-semibold d-inline-flex align-items-center gap-1 py-1 px-3" target="_blank">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                      <polyline points="7 10 12 15 17 10"/>
                      <line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Download PDF Report
                  </a>
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
  computed: {
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
  }
}
</script>

<style scoped>
.bg-primary-subtle {
  background-color: rgba(37, 99, 235, 0.15) !important;
}
</style>
