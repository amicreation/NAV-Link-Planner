<template>
  <header class="navbar navbar-expand-lg sticky-top border-bottom" style="height: 64px; background-color: var(--bg-card); border-color: var(--border-color) !important;">
    <div class="container-fluid px-3">
      <!-- Left: Logo & Brand -->
      <div class="d-flex align-items-center">
        <a class="navbar-brand d-flex align-items-center me-3" href="#" @click.prevent="navigate('dashboard')">
          <div class="bg-primary text-white p-1 rounded-2 me-2 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
              <circle cx="12" cy="12" r="9"/>
              <path d="M12 3v18M3 12h18"/>
            </svg>
          </div>
          <div class="d-flex flex-column">
            <span class="fw-extrabold text-main tracking-wider" style="font-size: 1.05rem; letter-spacing: -0.01em;">NAV LINK <span class="text-primary">PLANNER</span></span>
            <span class="fs-7 text-muted text-uppercase font-monospace" style="font-size: 0.65rem;">Enterprise RF & FSO Suite</span>
          </div>
        </a>

        <!-- System Engine Badge -->
        <div class="d-none d-md-flex align-items-center ms-3 ps-3 border-start border-secondary">
          <span class="badge bg-primary-subtle text-primary border border-primary-subtle me-2 font-monospace">WGS84 Engine</span>
          <span class="text-muted small">Refraction k=1.33</span>
        </div>
      </div>
      
      <!-- Right: Search, Theme Toggle, User Profile & Logout -->
      <div class="d-flex align-items-center gap-2">
        <!-- Quick Command Search Box Trigger -->
        <button 
          type="button" 
          class="btn btn-outline-secondary btn-sm d-none d-sm-flex align-items-center px-3 py-1 text-muted" 
          @click="$emit('open-command-palette')"
          style="background-color: var(--bg-input); border-color: var(--border-color);"
        >
          <svg class="me-2" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <span class="me-3 small">Quick Command / Search...</span>
          <kbd class="bg-dark text-white border border-secondary rounded px-1 fs-7">Ctrl+K</kbd>
        </button>

        <!-- Dark / Light Theme Toggle -->
        <button 
          type="button" 
          class="btn btn-outline-secondary btn-sm d-flex align-items-center px-2 py-1" 
          @click="toggleTheme" 
          :title="isDarkMode ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
        >
          <span v-if="isDarkMode" class="d-flex align-items-center text-warning small fw-semibold">
            <svg class="me-1" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <circle cx="12" cy="12" r="5"/>
              <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" stroke="currentColor" stroke-width="2"/>
            </svg>
            Light
          </span>
          <span v-else class="d-flex align-items-center text-info small fw-semibold">
            <svg class="me-1" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
              <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
            </svg>
            Dark
          </span>
        </button>

        <!-- Primary Action Button -->
        <button class="btn btn-primary btn-sm fw-semibold d-flex align-items-center px-3" @click="$emit('open-new-project')">
          <svg class="me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          New Project
        </button>

        <!-- User Profile & Account Dropdown -->
        <div v-if="user" class="ms-2 d-flex align-items-center gap-2">
          <button 
            type="button" 
            class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2 px-2 py-1"
            @click="$emit('open-profile')"
            :title="'User: ' + user.name"
          >
            <div class="bg-primary text-white fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 26px; height: 26px; font-size: 0.8rem;">
              {{ user.name.charAt(0).toUpperCase() }}
            </div>
            <span class="d-none d-md-inline small fw-semibold text-main">{{ user.name }}</span>
            <span class="badge text-uppercase ms-1" :class="user.role === 'admin' ? 'badge-role-admin' : (user.role === 'technical' ? 'badge-role-technical' : 'badge-role-engineer')">
              {{ user.role === 'admin' ? 'FULL ADMIN' : (user.role === 'technical' ? 'FULL TECHNICAL' : 'ENGINEERING') }}
            </span>
          </button>

          <!-- Logout Action Button -->
          <button 
            type="button" 
            class="btn btn-outline-danger btn-sm p-1 px-2"
            @click="$emit('logout')"
            title="Sign Out"
          >
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
              <polyline points="16 17 21 12 16 7"/>
              <line x1="21" y1="12" x2="9" y2="12"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </header>
</template>

<script>
export default {
  props: {
    currentTab: {
      type: String,
      default: 'dashboard'
    },
    user: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      isDarkMode: true
    };
  },
  mounted() {
    const savedTheme = localStorage.getItem('nav_link_planner_theme');
    if (savedTheme === 'light') {
      this.isDarkMode = false;
      document.body.setAttribute('data-theme', 'light');
    } else {
      this.isDarkMode = true;
      document.body.setAttribute('data-theme', 'dark');
    }
  },
  emits: ['change-tab', 'open-new-project', 'open-command-palette', 'open-profile', 'logout', 'theme-changed'],
  methods: {
    navigate(tab) {
      this.$emit('change-tab', tab);
    },

    toggleTheme() {
      this.isDarkMode = !this.isDarkMode;
      if (this.isDarkMode) {
        document.body.setAttribute('data-theme', 'dark');
        localStorage.setItem('nav_link_planner_theme', 'dark');
      } else {
        document.body.setAttribute('data-theme', 'light');
        localStorage.setItem('nav_link_planner_theme', 'light');
      }
      this.$emit('theme-changed', this.isDarkMode ? 'dark' : 'light');
    }
  }
}
</script>

<style scoped>
.fs-7 {
  font-size: 0.7rem;
}
.tracking-wider {
  letter-spacing: 0.025em;
}
.fw-extrabold {
  font-weight: 800;
}
</style>
