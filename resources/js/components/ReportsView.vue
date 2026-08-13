<template>
  <div class="container-fluid px-4 py-4">
    <div class="d-flex align-items-center justify-content-between pb-3 mb-4 border-bottom">
      <div>
        <h4 class="fw-bold mb-1 brand-blue">Engineering Reports</h4>
        <p class="text-secondary small mb-0">Generate and download official PDF engineering documentation</p>
      </div>
    </div>

    <div class="card border shadow-sm">
      <div class="card-header bg-white py-3">
        <h6 class="fw-bold mb-0">All Saved Project Reports</h6>
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
                <td colspan="6" class="text-center py-4 text-muted">No projects found.</td>
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
                  <a :href="`/api/projects/${proj.id}/pdf`" class="btn btn-sm btn-primary fw-semibold py-1" target="_blank">
                    <svg width="14" height="14" class="me-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
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
  }
}
</script>
