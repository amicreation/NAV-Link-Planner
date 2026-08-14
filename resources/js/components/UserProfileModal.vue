<template>
  <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.65);" v-if="show" @click.self="$emit('close')">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content border-0 shadow-lg text-main rounded-3" style="background-color: var(--bg-card); border: 1px solid var(--border-color) !important;">
        <div class="modal-header py-3 px-4" style="border-bottom: 1px solid var(--border-color);">
          <div class="d-flex align-items-center gap-2">
            <svg class="text-primary" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
              <circle cx="12" cy="7" r="4"/>
            </svg>
            <h5 class="modal-title fw-bold text-main">User Account Profile</h5>
          </div>
          <button type="button" class="btn-close" @click="$emit('close')"></button>
        </div>

        <div class="modal-body p-4">
          <!-- Profile Overview Card -->
          <div class="d-flex align-items-center gap-3 p-3 border rounded-3 mb-4" style="background-color: var(--bg-input); border-color: var(--border-color) !important;">
            <div class="bg-primary text-white fw-bold fs-4 rounded-circle d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
              {{ user?.name?.charAt(0).toUpperCase() }}
            </div>
            <div>
              <div class="fw-bold fs-5 text-main">{{ user?.name }}</div>
              <div class="text-secondary small font-monospace">{{ user?.email }}</div>
              <span class="badge bg-primary-subtle text-primary border border-primary-subtle text-uppercase mt-1">
                {{ user?.role }}
              </span>
            </div>
          </div>

          <!-- Update Profile Details -->
          <form @submit.prevent="updateProfile">
            <h6 class="fw-bold text-primary mb-3">Profile Information</h6>
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" v-model="profileForm.name" class="form-control" required />
              </div>

              <div class="col-md-6">
                <label class="form-label">Mobile Number</label>
                <input type="text" v-model="profileForm.mobile_number" class="form-control" placeholder="+91 9876543210" />
              </div>

              <div class="col-md-6">
                <label class="form-label">Company / Organization</label>
                <input type="text" v-model="profileForm.company" class="form-control" placeholder="NAV Infra Group" />
              </div>

              <div class="col-md-6">
                <label class="form-label">Designation</label>
                <input type="text" v-model="profileForm.designation" class="form-control" placeholder="Senior RF Engineer" />
              </div>

              <div class="col-md-6">
                <label class="form-label">Department</label>
                <input type="text" v-model="profileForm.department" class="form-control" placeholder="Wireless Planning" />
              </div>
            </div>

            <div class="d-flex justify-content-end mb-4">
              <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold" :disabled="loadingProfile">
                <span v-if="loadingProfile" class="spinner-border spinner-border-sm me-1"></span>
                Save Profile Updates
              </button>
            </div>
          </form>

          <hr style="border-color: var(--border-color) !important;" class="my-4" />

          <!-- Change Password Section -->
          <form @submit.prevent="changePassword">
            <h6 class="fw-bold text-warning mb-3">Change Password</h6>
            <div class="row g-3 mb-3">
              <div class="col-md-6">
                <label class="form-label">Current Password</label>
                <input type="password" v-model="passwordForm.current_password" class="form-control" required />
              </div>
              <div class="col-md-6">
                <label class="form-label">New Password</label>
                <input type="password" v-model="passwordForm.new_password" class="form-control" placeholder="Minimum 8 characters" required />
              </div>
            </div>

            <div class="d-flex justify-content-end">
              <button type="submit" class="btn btn-warning btn-sm px-3 fw-semibold" :disabled="loadingPassword">
                <span v-if="loadingPassword" class="spinner-border spinner-border-sm me-1"></span>
                Update Password
              </button>
            </div>
          </form>
        </div>

        <div class="modal-footer py-2 px-4" style="border-top: 1px solid var(--border-color); background-color: var(--bg-card-header);">
          <button type="button" class="btn btn-secondary btn-sm" @click="$emit('close')">Close</button>
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
    user: {
      type: Object,
      default: null
    }
  },
  data() {
    return {
      profileForm: {
        name: this.user?.name || '',
        mobile_number: this.user?.mobile_number || '',
        company: this.user?.company || '',
        designation: this.user?.designation || '',
        department: this.user?.department || ''
      },
      passwordForm: {
        current_password: '',
        new_password: ''
      },
      loadingProfile: false,
      loadingPassword: false
    };
  },
  watch: {
    user: {
      deep: true,
      immediate: true,
      handler(val) {
        if (val) {
          this.profileForm = {
            name: val.name || '',
            mobile_number: val.mobile_number || '',
            company: val.company || '',
            designation: val.designation || '',
            department: val.department || ''
          };
        }
      }
    }
  },
  emits: ['close', 'user-updated'],
  methods: {
    async updateProfile() {
      this.loadingProfile = true;
      try {
        const res = await fetch('/api/auth/profile', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(this.profileForm)
        });
        const data = await res.json();
        if (res.ok && data.status === 'success') {
          alert('Profile information updated successfully.');
          this.$emit('user-updated', data.user);
        } else {
          alert('Profile update failed: ' + (data.message || 'Validation error'));
        }
      } catch (err) {
        alert('Error: ' + err.message);
      } finally {
        this.loadingProfile = false;
      }
    },

    async changePassword() {
      this.loadingPassword = true;
      try {
        const res = await fetch('/api/auth/password', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(this.passwordForm)
        });
        const data = await res.json();
        if (res.ok && data.status === 'success') {
          alert('Password updated successfully.');
          this.passwordForm = { current_password: '', new_password: '' };
        } else {
          alert('Password update failed: ' + (data.message || 'Validation error'));
        }
      } catch (err) {
        alert('Error: ' + err.message);
      } finally {
        this.loadingPassword = false;
      }
    }
  }
}
</script>

<style scoped>
</style>
