<template>
  <aside class="app-sidebar" :class="{ collapsed: isCollapsed }">
    <!-- Sidebar Toggle Header -->
    <div class="d-flex align-items-center justify-content-between px-3 py-3 border-bottom border-secondary-subtle">
      <div v-if="!isCollapsed" class="d-flex align-items-center">
        <span class="badge bg-primary me-2 font-monospace">v2.5</span>
        <span class="fw-bold text-main small font-monospace">SUITE MODULES</span>
      </div>
      <button 
        type="button" 
        class="btn btn-sm text-secondary p-1 hover-text-white" 
        @click="toggleSidebar"
        :title="isCollapsed ? 'Expand Sidebar' : 'Collapse Sidebar'"
      >
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
          <line x1="9" y1="3" x2="9" y2="21"/>
          <path v-if="isCollapsed" d="M13 15l3-3-3-3"/>
          <path v-else d="M16 15l-3-3 3-3"/>
        </svg>
      </button>
    </div>

    <!-- Navigation Menu -->
    <div class="px-2 py-3 flex-grow-1">
      <div v-if="!isCollapsed" class="px-3 mb-2 label-text text-muted">ENGINEERING</div>
      
      <ul class="nav nav-pills flex-column gap-1">
        <li class="nav-item">
          <a 
            class="nav-link d-flex align-items-center py-2 px-3 text-secondary rounded-3" 
            :class="{ 'active bg-primary text-white': currentTab === 'dashboard' }" 
            href="#" 
            @click.prevent="$emit('change-tab', 'dashboard')"
            :title="isCollapsed ? 'Dashboard' : ''"
          >
            <svg class="me-3" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="7" height="7"/>
              <rect x="14" y="3" width="7" height="7"/>
              <rect x="14" y="14" width="7" height="7"/>
              <rect x="3" y="14" width="7" height="7"/>
            </svg>
            <span v-if="!isCollapsed" class="fw-medium">Dashboard</span>
          </a>
        </li>

        <li class="nav-item">
          <a 
            class="nav-link d-flex align-items-center py-2 px-3 text-secondary rounded-3" 
            :class="{ 'active bg-primary text-white': currentTab === 'planner' }" 
            href="#" 
            @click.prevent="$emit('change-tab', 'planner')"
            :title="isCollapsed ? 'Link Planner' : ''"
          >
            <svg class="me-3" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M12 20V10"/>
              <path d="M18 20V4"/>
              <path d="M6 20v-4"/>
            </svg>
            <span v-if="!isCollapsed" class="fw-medium">Link Planner</span>
          </a>
        </li>

        <li class="nav-item">
          <a 
            class="nav-link d-flex align-items-center py-2 px-3 text-secondary rounded-3" 
            :class="{ 'active bg-primary text-white': currentTab === 'projects' }" 
            href="#" 
            @click.prevent="$emit('change-tab', 'projects')"
            :title="isCollapsed ? 'Projects Directory' : ''"
          >
            <svg class="me-3" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/>
            </svg>
            <span v-if="!isCollapsed" class="fw-medium">Projects</span>
          </a>
        </li>

        <li class="nav-item">
          <a 
            class="nav-link d-flex align-items-center py-2 px-3 text-secondary rounded-3" 
            :class="{ 'active bg-primary text-white': currentTab === 'reports' }" 
            href="#" 
            @click.prevent="$emit('change-tab', 'reports')"
            :title="isCollapsed ? 'Engineering Reports' : ''"
          >
            <svg class="me-3" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M14 2H6a2 2 0 0 1-2 2v16a2 2 0 0 1 2 2h12a2 2 0 0 1 2-2V8z"/>
              <polyline points="14 2 14 8 20 8"/>
              <line x1="16" y1="13" x2="8" y2="13"/>
              <line x1="16" y1="17" x2="8" y2="17"/>
              <polyline points="10 9 9 9 8 9"/>
            </svg>
            <span v-if="!isCollapsed" class="fw-medium">Reports</span>
          </a>
        </li>

        <li class="nav-item">
          <a 
            class="nav-link d-flex align-items-center py-2 px-3 text-secondary rounded-3" 
            :class="{ 'active bg-primary text-white': currentTab === 'equipment' }" 
            href="#" 
            @click.prevent="$emit('change-tab', 'equipment')"
            :title="isCollapsed ? 'Equipment Library' : ''"
          >
            <svg class="me-3" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="2" y="2" width="20" height="8" rx="2" ry="2"/>
              <rect x="2" y="14" width="20" height="8" rx="2" ry="2"/>
              <line x1="6" y1="6" x2="6.01" y2="6"/>
              <line x1="6" y1="18" x2="6.01" y2="18"/>
            </svg>
            <span v-if="!isCollapsed" class="fw-medium">Equipment Library</span>
          </a>
        </li>

        <!-- Admin Module Section -->
        <template v-if="user?.role === 'admin'">
          <li class="nav-item mt-3">
            <div v-if="!isCollapsed" class="px-3 mb-2 label-text text-muted">ADMINISTRATION</div>
          </li>

          <li class="nav-item">
            <a 
              class="nav-link d-flex align-items-center py-2 px-3 text-secondary rounded-3" 
              :class="{ 'active bg-primary text-white': currentTab === 'admin-users' }" 
              href="#" 
              @click.prevent="$emit('change-tab', 'admin-users')"
              :title="isCollapsed ? 'User Management' : ''"
            >
              <svg class="me-3" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
              </svg>
              <span v-if="!isCollapsed" class="fw-medium">User Management</span>
            </a>
          </li>

          <li class="nav-item">
            <a 
              class="nav-link d-flex align-items-center py-2 px-3 text-secondary rounded-3" 
              :class="{ 'active bg-primary text-white': currentTab === 'audit-logs' }" 
              href="#" 
              @click.prevent="$emit('change-tab', 'audit-logs')"
              :title="isCollapsed ? 'Audit Trail' : ''"
            >
              <svg class="me-3" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
              </svg>
              <span v-if="!isCollapsed" class="fw-medium">Audit Trail</span>
            </a>
          </li>
        </template>

        <li class="nav-item mt-3">
          <div v-if="!isCollapsed" class="px-3 mb-2 label-text text-muted">ACTIONS & SHORTCUTS</div>
        </li>

        <li class="nav-item">
          <a 
            class="nav-link d-flex align-items-center py-2 px-3 text-secondary rounded-3" 
            href="#" 
            @click.prevent="$emit('open-command-palette')"
            :title="isCollapsed ? 'Command Palette (Ctrl+K)' : ''"
          >
            <svg class="me-3" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <circle cx="11" cy="11" r="8"/>
              <line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <span v-if="!isCollapsed" class="fw-medium">Quick Command <span class="badge bg-dark border ms-2">Ctrl+K</span></span>
          </a>
        </li>
      </ul>
    </div>

    <!-- Active Project & User Status Footer -->
    <div class="p-3 border-top border-secondary-subtle">
      <div v-if="!isCollapsed">
        <div class="d-flex align-items-center justify-content-between mb-1">
          <span class="label-text">GEODETIC ENGINE</span>
          <span class="badge bg-success-subtle text-success">ONLINE</span>
        </div>
        <div class="small text-secondary font-monospace">WGS84 &bull; k=1.33</div>
        
        <div v-if="activeProject" class="mt-3 p-2 rounded border border-secondary" style="background-color: var(--bg-input);">
          <div class="label-text text-primary mb-1">ACTIVE PROJECT</div>
          <div class="fw-semibold text-truncate small text-main">{{ activeProject.name }}</div>
          <div class="text-muted fs-7 text-uppercase">{{ activeProject.technology }} LINK</div>
        </div>
      </div>
      <div v-else class="text-center" title="WGS84 Engine Active">
        <span class="d-inline-block p-1 bg-success rounded-circle"></span>
      </div>
    </div>
  </aside>
</template>

<script>
export default {
  props: {
    currentTab: {
      type: String,
      default: 'dashboard'
    },
    activeProject: {
      type: Object,
      default: null
    },
    user: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      isCollapsed: false
    };
  },
  emits: ['change-tab', 'open-new-project', 'open-command-palette'],
  methods: {
    toggleSidebar() {
      this.isCollapsed = !this.isCollapsed;
    }
  }
}
</script>

<style scoped>
.hover-text-white:hover {
  color: var(--text-primary) !important;
}
.fs-7 {
  font-size: 0.7rem;
}
.nav-link {
  transition: all 0.15s ease;
  color: var(--text-secondary) !important;
}
.nav-link:hover {
  background-color: var(--border-color-subtle);
  color: var(--text-primary) !important;
}
.nav-link.active {
  color: #ffffff !important;
}
.nav-link.active:hover {
  background-color: var(--bs-primary) !important;
  color: #ffffff !important;
}
</style>
