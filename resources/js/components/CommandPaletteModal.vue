<template>
  <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.75);" v-if="show" @click.self="$emit('close')">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg text-main overflow-hidden rounded-3" style="background-color: var(--bg-card); border: 1px solid var(--border-color) !important;">
        <!-- Search Header -->
        <div class="p-3 d-flex align-items-center" style="border-bottom: 1px solid var(--border-color);">
          <svg class="text-secondary me-3" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="11" cy="11" r="8"/>
            <line x1="21" y1="21" x2="16.65" y2="16.65"/>
          </svg>
          <input 
            type="text" 
            ref="searchInput"
            v-model="query" 
            class="form-control form-control-lg bg-transparent border-0 text-main shadow-none px-0" 
            placeholder="Type a command or search project name, customer, or engineer... (Esc to close)"
            @keydown.esc="$emit('close')"
          />
          <button type="button" class="btn-close ms-2" @click="$emit('close')"></button>
        </div>

        <!-- Quick Actions List -->
        <div class="p-3" style="max-height: 400px; overflow-y: auto;">
          <div class="label-text text-muted mb-2">QUICK ACTIONS</div>
          <div class="list-group list-group-flush mb-3">
            <button 
              type="button" 
              class="list-group-item list-group-item-action bg-transparent text-main border-0 rounded-2 d-flex align-items-center py-2 px-3 hover-bg-secondary"
              @click="triggerAction('new-project')"
            >
              <svg class="me-3 text-success" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
              </svg>
              <div>
                <div class="fw-semibold">Create New Engineering Project</div>
                <div class="small text-muted">Initialize RF or FSO Point-to-Point link design</div>
              </div>
            </button>

            <button 
              type="button" 
              class="list-group-item list-group-item-action bg-transparent text-main border-0 rounded-2 d-flex align-items-center py-2 px-3 hover-bg-secondary"
              @click="triggerAction('nav-planner')"
            >
              <svg class="me-3 text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 20V10"/>
                <path d="M18 20V4"/>
                <path d="M6 20v-4"/>
              </svg>
              <div>
                <div class="fw-semibold">Open Link Planner</div>
                <div class="small text-muted">Access elevation profile & path budget calculator</div>
              </div>
            </button>
          </div>

          <!-- Matching Projects -->
          <div v-if="filteredProjects.length > 0">
            <div class="label-text text-muted mb-2">PROJECTS ({{ filteredProjects.length }})</div>
            <div class="list-group list-group-flush">
              <button 
                v-for="proj in filteredProjects" 
                :key="proj.id"
                type="button" 
                class="list-group-item list-group-item-action bg-transparent text-main border-0 rounded-2 d-flex align-items-center justify-content-between py-2 px-3 hover-bg-secondary"
                @click="loadProject(proj)"
              >
                <div>
                  <div class="fw-semibold text-primary">{{ proj.name }}</div>
                  <div class="small text-muted">Customer: {{ proj.customer_name }} &bull; Eng: {{ proj.engineer_name }}</div>
                </div>
                <span class="badge" :class="proj.technology === 'rf' ? 'badge-tech-rf' : 'badge-tech-fso'">
                  {{ proj.technology.toUpperCase() }}
                </span>
              </button>
            </div>
          </div>

          <div v-else-if="query.trim().length > 0" class="text-center py-4 text-muted">
            No engineering projects matching "{{ query }}"
          </div>
        </div>

        <div class="px-3 py-2 border-top d-flex align-items-center justify-content-between text-muted fs-7" style="border-color: var(--border-color) !important; background-color: var(--bg-card-header);">
          <span>Press <kbd class="bg-secondary text-white">Esc</kbd> to exit</span>
          <span>NAV LINK PLANNER WGS84 Engine</span>
        </div>
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
    projects: {
      type: Array,
      default: () => []
    }
  },
  data() {
    return {
      query: ''
    };
  },
  emits: ['close', 'open-new-project', 'change-tab', 'load-project'],
  computed: {
    filteredProjects() {
      if (!this.query.trim()) return this.projects.slice(0, 5);
      const q = this.query.toLowerCase();
      return this.projects.filter(p => 
        (p.name && p.name.toLowerCase().includes(q)) ||
        (p.customer_name && p.customer_name.toLowerCase().includes(q)) ||
        (p.engineer_name && p.engineer_name.toLowerCase().includes(q))
      );
    }
  },
  watch: {
    show(val) {
      if (val) {
        this.query = '';
        this.$nextTick(() => {
          if (this.$refs.searchInput) {
            this.$refs.searchInput.focus();
          }
        });
      }
    }
  },
  methods: {
    triggerAction(action) {
      if (action === 'new-project') {
        this.$emit('open-new-project');
      } else if (action === 'nav-planner') {
        this.$emit('change-tab', 'planner');
      }
      this.$emit('close');
    },

    loadProject(proj) {
      this.$emit('load-project', proj);
      this.$emit('close');
    }
  }
}
</script>

<style scoped>
.hover-bg-secondary:hover {
  background-color: var(--border-color-subtle) !important;
}
.fs-7 {
  font-size: 0.75rem;
}
</style>
