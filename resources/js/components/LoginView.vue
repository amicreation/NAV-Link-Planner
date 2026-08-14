<template>
  <div class="min-vh-100 d-flex align-items-center justify-content-center py-5 px-3" style="background: radial-gradient(circle at top right, #172554 0%, #0F172A 70%);">
    <div class="card border-0 shadow-lg p-4 p-md-5 overflow-hidden" style="width: 100%; max-width: 480px; background-color: var(--bg-card); border: 1px solid var(--border-color) !important; border-radius: 16px;">
      <!-- Header Branding -->
      <div class="text-center mb-4">
        <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white p-3 rounded-3 mb-3 shadow">
          <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <circle cx="12" cy="12" r="9"/>
            <path d="M12 3v18M3 12h18"/>
          </svg>
        </div>
        <h3 class="fw-bold text-white mb-1 tracking-wider">NAV LINK <span class="text-primary">PLANNER</span></h3>
        <p class="text-secondary small mb-0">Enterprise RF & FSO Path Engineering Suite</p>
      </div>

      <!-- Error Alert Banner -->
      <div v-if="errorMessage" class="alert alert-danger d-flex align-items-center py-2 px-3 mb-4 rounded-3 small">
        <svg class="me-2 flex-shrink-0" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="12" y1="8" x2="12" y2="12"/>
          <line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <div>{{ errorMessage }}</div>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin">
        <div class="mb-3">
          <label class="form-label">Email Address</label>
          <div class="input-group">
            <span class="input-group-text bg-dark border-secondary">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
              </svg>
            </span>
            <input 
              type="email" 
              v-model="email" 
              class="form-control" 
              placeholder="engineer@navlinkplanner.com" 
              required 
            />
          </div>
        </div>

        <div class="mb-3">
          <div class="d-flex align-items-center justify-content-between mb-1">
            <label class="form-label mb-0">Password</label>
          </div>
          <div class="input-group">
            <span class="input-group-text bg-dark border-secondary">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
              </svg>
            </span>
            <input 
              type="password" 
              v-model="password" 
              class="form-control" 
              placeholder="••••••••" 
              required 
            />
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-4">
          <div class="form-check">
            <input type="checkbox" v-model="remember" class="form-check-input" id="rememberCheck" />
            <label class="form-check-label text-secondary small" for="rememberCheck">Remember me</label>
          </div>
        </div>

        <button type="submit" class="btn btn-primary py-2 w-100 fw-bold d-flex align-items-center justify-content-center gap-2 shadow-sm" :disabled="loading">
          <span v-if="loading" class="spinner-border spinner-border-sm me-1"></span>
          <span>Authenticate & Access Suite &rarr;</span>
        </button>
      </form>

      <!-- Quick Demo Login Shortcuts -->
      <div class="mt-4 pt-3 border-top border-secondary text-center">
        <div class="label-text text-muted mb-2">QUICK DEMO ACCESSS</div>
        <div class="d-flex gap-2">
          <button 
            type="button" 
            class="btn btn-outline-secondary btn-sm flex-fill py-1 small" 
            @click="fillDemo('amitsharma@navwireless.com', 'admin')"
          >
            Admin Demo
          </button>
          <button 
            type="button" 
            class="btn btn-outline-secondary btn-sm flex-fill py-1 small" 
            @click="fillDemo('engineer@navlinkplanner.com', 'password123')"
          >
            Engineer Demo
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      email: '',
      password: '',
      remember: false,
      loading: false,
      errorMessage: ''
    };
  },
  emits: ['login-success'],
  methods: {
    fillDemo(email, password) {
      this.email = email;
      this.password = password;
      this.handleLogin();
    },

    async handleLogin() {
      this.loading = true;
      this.errorMessage = '';
      try {
        const res = await fetch('/api/auth/login', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            email: this.email,
            password: this.password,
            remember: this.remember
          })
        });

        const data = await res.json();
        if (res.ok && data.status === 'success') {
          this.$emit('login-success', data.user);
        } else {
          this.errorMessage = data.message || 'Authentication failed. Check your credentials.';
        }
      } catch (err) {
        this.errorMessage = 'Server connection error: ' + err.message;
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>
.tracking-wider {
  letter-spacing: 0.025em;
}
</style>
