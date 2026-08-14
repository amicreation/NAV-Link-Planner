<template>
  <div class="container-fluid px-4 py-4">
    <!-- Header Banner -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between pb-3 mb-4 border-bottom border-secondary gap-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge bg-primary font-monospace">ADMINISTRATION</span>
          <span class="text-muted small">&bull; Security & Compliance</span>
        </div>
        <h3 class="page-title text-main mb-0">System Audit Trail</h3>
        <p class="text-secondary small mb-0">Immutable record of user authentication, project creation/deletion, and administrative actions</p>
      </div>

      <div class="d-flex align-items-center gap-2">
        <input 
          type="text" 
          v-model="searchQuery" 
          class="form-control form-control-sm" 
          placeholder="Filter logs by user, action, IP..." 
          style="width: 260px;"
          @input="fetchLogs"
        />
        <button class="btn btn-outline-secondary btn-sm" @click="fetchLogs" title="Refresh Audit Logs">
          Refresh
        </button>
      </div>
    </div>

    <!-- Audit Logs Data Table -->
    <div class="card border">
      <div class="card-header py-3 px-4 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
          <svg class="text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
          </svg>
          <h5 class="card-title mb-0">Audit Events Log</h5>
        </div>
        <span class="badge bg-dark border text-muted font-monospace">Real-Time Security Audit</span>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>Timestamp</th>
                <th>User</th>
                <th>Action</th>
                <th>Description</th>
                <th>IP Address</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="5" class="text-center py-5 text-muted">
                  <span class="spinner-border spinner-border-sm me-2"></span> Loading audit logs...
                </td>
              </tr>
              <tr v-else-if="logs.length === 0">
                <td colspan="5" class="text-center py-5 text-muted">No audit log entries recorded.</td>
              </tr>
              <tr v-for="log in logs" :key="log.id">
                <td class="small text-muted font-monospace" style="white-space: nowrap;">
                  {{ new Date(log.created_at).toLocaleString() }}
                </td>
                <td>
                  <div class="fw-semibold text-main">{{ log.user_name || 'System' }}</div>
                </td>
                <td>
                  <span class="badge font-monospace" :class="getActionBadgeClass(log.action)">
                    {{ log.action }}
                  </span>
                </td>
                <td class="small text-secondary">{{ log.description }}</td>
                <td class="small text-muted font-monospace">{{ log.ip_address || '127.0.0.1' }}</td>
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
  data() {
    return {
      logs: [],
      loading: false,
      searchQuery: ''
    };
  },
  mounted() {
    this.fetchLogs();
  },
  methods: {
    async fetchLogs() {
      this.loading = true;
      try {
        const queryParams = new URLSearchParams({
          search: this.searchQuery
        });
        const res = await fetch(`/api/admin/audit-logs?${queryParams}`);
        const data = await res.json();
        if (res.ok && data.status === 'success') {
          this.logs = data.data.data || data.data;
        }
      } catch (err) {
        console.error('Failed to fetch audit logs:', err);
      } finally {
        this.loading = false;
      }
    },

    getActionBadgeClass(action) {
      if (!action) return 'bg-secondary';
      if (action.includes('LOGIN') || action.includes('CREATED')) return 'bg-success-subtle text-success border border-success-subtle';
      if (action.includes('DELETED') || action.includes('FAILED') || action.includes('BLOCKED')) return 'bg-danger-subtle text-danger border border-danger-subtle';
      if (action.includes('UPDATED') || action.includes('RESET')) return 'bg-warning-subtle text-warning border border-warning-subtle';
      return 'bg-primary-subtle text-primary border border-primary-subtle';
    }
  }
}
</script>

<style scoped>
</style>
