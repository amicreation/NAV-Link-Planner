<template>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-primary border-3 py-2">
    <div class="container-fluid px-4">
      <a class="navbar-brand d-flex align-items-center fw-bold" href="#" @click.prevent="navigate('dashboard')">
        <svg class="me-2 text-primary" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
          <circle cx="12" cy="12" r="9"/>
          <path d="M12 3v18M3 12h18"/>
        </svg>
        <span class="text-white">NAV LINK</span>
        <span class="badge bg-primary ms-2 text-uppercase fs-7">PLANNER</span>
      </a>
      
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link px-3" :class="{ active: currentTab === 'dashboard' }" href="#" @click.prevent="navigate('dashboard')">
              Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link px-3" :class="{ active: currentTab === 'planner' }" href="#" @click.prevent="navigate('planner')">
              Link Workbench
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link px-3" :class="{ active: currentTab === 'projects' }" href="#" @click.prevent="navigate('projects')">
              Projects
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link px-3" :class="{ active: currentTab === 'reports' }" href="#" @click.prevent="navigate('reports')">
              Reports
            </a>
          </li>
        </ul>

        <div class="d-flex align-items-center me-3">
          <!-- Dark / Light Theme Toggle Button -->
          <button 
            type="button" 
            class="btn btn-outline-light btn-sm me-3 d-flex align-items-center fw-semibold px-2 py-1" 
            @click="toggleTheme" 
            :title="isDarkMode ? 'Switch to Light Theme' : 'Switch to Dark Theme'"
          >
            <span v-if="isDarkMode" class="d-flex align-items-center text-warning">
              <svg class="me-1" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <circle cx="12" cy="12" r="5"/>
                <path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42" stroke="currentColor" stroke-width="2"/>
              </svg>
              Light
            </span>
            <span v-else class="d-flex align-items-center text-info">
              <svg class="me-1" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
              </svg>
              Dark
            </span>
          </button>

          <span class="badge bg-secondary me-2">WGS84 Engine</span>
          <span class="text-white-50 small">Refraction k=1.33</span>
        </div>

        <button class="btn btn-sm btn-primary fw-semibold" @click="$emit('open-new-project')">
          + New Project
        </button>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  props: {
    currentTab: {
      type: String,
      default: 'dashboard'
    }
  },
  data() {
    return {
      isDarkMode: false
    };
  },
  mounted() {
    const savedTheme = localStorage.getItem('nav_link_planner_theme');
    if (savedTheme === 'dark') {
      this.isDarkMode = true;
      document.body.setAttribute('data-theme', 'dark');
    }
  },
  emits: ['change-tab', 'open-new-project', 'theme-changed'],
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
        document.body.removeAttribute('data-theme');
        localStorage.setItem('nav_link_planner_theme', 'light');
      }
      this.$emit('theme-changed', this.isDarkMode ? 'dark' : 'light');
    }
  }
}
</script>

<style scoped>
.fs-7 {
  font-size: 0.65rem;
}
</style>
