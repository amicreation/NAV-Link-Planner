<template>
  <div class="container-fluid px-4 py-4">
    <!-- Header Banner -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between pb-3 mb-4 border-bottom border-secondary gap-3">
      <div>
        <div class="d-flex align-items-center gap-2 mb-1">
          <span class="badge bg-primary font-monospace">ADMINISTRATION</span>
          <span class="text-muted small">&bull; Role-Based Access Control</span>
        </div>
        <h3 class="page-title text-main mb-0">User Management & Permissions</h3>
        <p class="text-secondary small mb-0">Create, configure, activate, or deactivate engineers and system administrators</p>
      </div>

      <div class="d-flex align-items-center gap-2">
        <button class="btn btn-primary fw-semibold d-flex align-items-center gap-2" @click="openCreateModal">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Create New User
        </button>
      </div>
    </div>

    <!-- Filters & Table Card -->
    <div class="card border">
      <div class="card-header py-3 px-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
          <svg class="text-primary" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
            <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
          </svg>
          <h5 class="card-title mb-0">System Users Directory</h5>
        </div>

        <!-- Filter Inputs -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <input 
            type="text" 
            v-model="searchQuery" 
            class="form-control form-control-sm" 
            placeholder="Search name, email, company..." 
            style="width: 220px;"
            @input="fetchUsers"
          />
          <select v-model="roleFilter" class="form-select form-select-sm" style="width: 150px;" @change="fetchUsers">
            <option value="all">All Roles</option>
            <option value="admin">Full Admin</option>
            <option value="technical">Full Technical</option>
            <option value="engineer">Engineering</option>
          </select>
          <select v-model="statusFilter" class="form-select form-select-sm" style="width: 140px;" @change="fetchUsers">
            <option value="all">All Status</option>
            <option value="active">Active Only</option>
            <option value="inactive">Inactive Only</option>
          </select>
        </div>
      </div>

      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>User</th>
                <th>Role</th>
                <th>Company / Designation</th>
                <th>Department</th>
                <th>Status</th>
                <th>Last Login</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="loading">
                <td colspan="7" class="text-center py-5 text-muted">
                  <span class="spinner-border spinner-border-sm me-2"></span> Loading user records...
                </td>
              </tr>
              <tr v-else-if="users.length === 0">
                <td colspan="7" class="text-center py-5 text-muted">
                  No user records found matching criteria.
                </td>
              </tr>
              <tr v-for="user in users" :key="user.id">
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary-subtle text-primary fw-bold rounded-circle d-flex align-items-center justify-content-center" style="width: 34px; height: 34px; border: 1px solid var(--border-color);">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <div class="fw-semibold text-main">{{ user.name }}</div>
                      <div class="small text-muted font-monospace">{{ user.email }}</div>
                    </div>
                  </div>
                </td>
                <td>
                  <span class="badge text-uppercase" :class="user.role === 'admin' ? 'badge-role-admin' : (user.role === 'technical' ? 'badge-role-technical' : 'badge-role-engineer')">
                    {{ user.role === 'admin' ? 'FULL ADMIN' : (user.role === 'technical' ? 'FULL TECHNICAL' : 'ENGINEERING') }}
                  </span>
                </td>
                <td>
                  <div class="small text-main">{{ user.company || 'N/A' }}</div>
                  <div class="fs-7 text-muted">{{ user.designation || 'N/A' }}</div>
                </td>
                <td class="small text-secondary">{{ user.department || 'N/A' }}</td>
                <td>
                  <span class="badge" :class="user.status === 'active' ? 'badge-suitable' : 'badge-not-recommended'">
                    {{ user.status.toUpperCase() }}
                  </span>
                </td>
                <td class="small text-muted font-monospace">
                  {{ user.last_login_at ? new Date(user.last_login_at).toLocaleString() : 'Never' }}
                </td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary" @click="openEditModal(user)" title="Edit User">
                      Edit
                    </button>
                    <button class="btn btn-outline-warning" @click="openResetModal(user)" title="Reset Password">
                      Reset Password
                    </button>
                    <button 
                      class="btn" 
                      :class="user.status === 'active' ? 'btn-outline-danger' : 'btn-outline-success'" 
                      @click="toggleStatus(user)"
                    >
                      {{ user.status === 'active' ? 'Deactivate' : 'Activate' }}
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Create / Edit User Modal -->
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.65);" v-if="showUserModal" @click.self="showUserModal = false">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg text-main rounded-3" style="background-color: var(--bg-card); border: 1px solid var(--border-color) !important;">
          <div class="modal-header py-3 px-4" style="border-bottom: 1px solid var(--border-color);">
            <h5 class="modal-title fw-bold text-main">{{ editingUser ? 'Edit User Configuration' : 'Create New System User' }}</h5>
            <button type="button" class="btn-close" @click="showUserModal = false"></button>
          </div>
          <form @submit.prevent="saveUser">
            <div class="modal-body p-4">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label">Full Name <span class="text-danger">*</span></label>
                  <input type="text" v-model="userForm.name" class="form-control" placeholder="e.g. Sarah Connor" required />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Email Address <span class="text-danger">*</span></label>
                  <input type="email" v-model="userForm.email" class="form-control" placeholder="sarah@navlinkplanner.com" required />
                </div>

                <div v-if="!editingUser" class="col-md-6">
                  <label class="form-label">Account Password <span class="text-danger">*</span></label>
                  <input type="password" v-model="userForm.password" class="form-control" placeholder="••••••••" required />
                </div>

                <div class="col-md-6">
                  <label class="form-label">System Role <span class="text-danger">*</span></label>
                  <select v-model="userForm.role" class="form-select">
                    <option value="engineer">Engineering (Standard Role)</option>
                    <option value="technical">Full Technical (FSO Optical Budget & Link Analysis)</option>
                    <option value="admin">Full Admin (System Administrator Access)</option>
                  </select>
                </div>

                <div class="col-md-6">
                  <label class="form-label">Mobile Phone Number</label>
                  <input type="text" v-model="userForm.mobile_number" class="form-control" placeholder="+91 9876543210" />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Company / Organization</label>
                  <input type="text" v-model="userForm.company" class="form-control" placeholder="Telecom Systems Ltd" />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Designation</label>
                  <input type="text" v-model="userForm.designation" class="form-control" placeholder="Senior RF Engineer" />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Department</label>
                  <input type="text" v-model="userForm.department" class="form-control" placeholder="Wireless Planning" />
                </div>

                <div class="col-md-6">
                  <label class="form-label">Account Status <span class="text-danger">*</span></label>
                  <select v-model="userForm.status" class="form-select">
                    <option value="active">Active (Login Allowed)</option>
                    <option value="inactive">Inactive (Blocked)</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="modal-footer py-3 px-4" style="border-top: 1px solid var(--border-color); background-color: var(--bg-card-header);">
              <button type="button" class="btn btn-secondary px-3" @click="showUserModal = false">Cancel</button>
              <button type="submit" class="btn btn-primary px-4 fw-semibold" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                {{ editingUser ? 'Update User Record' : 'Save New User' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Password Reset Modal -->
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(15, 23, 42, 0.65);" v-if="showResetModal" @click.self="showResetModal = false">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg text-main rounded-3" style="background-color: var(--bg-card); border: 1px solid var(--border-color) !important;">
          <div class="modal-header py-3 px-4" style="border-bottom: 1px solid var(--border-color);">
            <h5 class="modal-title fw-bold text-main">Reset Password for {{ resetUserTarget?.name }}</h5>
            <button type="button" class="btn-close" @click="showResetModal = false"></button>
          </div>
          <form @submit.prevent="submitPasswordReset">
            <div class="modal-body p-4">
              <div class="mb-3">
                <label class="form-label">New Password <span class="text-danger">*</span></label>
                <input type="password" v-model="newResetPassword" class="form-control" placeholder="Minimum 8 characters" required />
              </div>
            </div>
            <div class="modal-footer py-3 px-4" style="border-top: 1px solid var(--border-color); background-color: var(--bg-card-header);">
              <button type="button" class="btn btn-secondary px-3" @click="showResetModal = false">Cancel</button>
              <button type="submit" class="btn btn-warning px-4 fw-semibold" :disabled="saving">
                <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                Reset Password
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      users: [],
      loading: false,
      saving: false,
      searchQuery: '',
      roleFilter: 'all',
      statusFilter: 'all',
      showUserModal: false,
      editingUser: null,
      userForm: {
        name: '',
        email: '',
        password: '',
        role: 'engineer',
        mobile_number: '',
        company: '',
        designation: '',
        department: '',
        status: 'active'
      },
      showResetModal: false,
      resetUserTarget: null,
      newResetPassword: ''
    };
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    async fetchUsers() {
      this.loading = true;
      try {
        const queryParams = new URLSearchParams({
          search: this.searchQuery,
          role: this.roleFilter,
          status: this.statusFilter
        });
        const res = await fetch(`/api/admin/users?${queryParams}`);
        const data = await res.json();
        if (res.ok && data.status === 'success') {
          this.users = data.data;
        }
      } catch (err) {
        console.error('Failed to fetch users:', err);
      } finally {
        this.loading = false;
      }
    },

    openCreateModal() {
      this.editingUser = null;
      this.userForm = {
        name: '',
        email: '',
        password: '',
        role: 'engineer',
        mobile_number: '',
        company: '',
        designation: '',
        department: '',
        status: 'active'
      };
      this.showUserModal = true;
    },

    openEditModal(user) {
      this.editingUser = user;
      this.userForm = {
        name: user.name,
        email: user.email,
        role: user.role,
        mobile_number: user.mobile_number || '',
        company: user.company || '',
        designation: user.designation || '',
        department: user.department || '',
        status: user.status
      };
      this.showUserModal = true;
    },

    async saveUser() {
      this.saving = true;
      try {
        const url = this.editingUser ? `/api/admin/users/${this.editingUser.id}` : '/api/admin/users';
        const method = this.editingUser ? 'PUT' : 'POST';

        const res = await fetch(url, {
          method: method,
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify(this.userForm)
        });

        const data = await res.json();
        if (res.ok && data.status === 'success') {
          this.showUserModal = false;
          this.fetchUsers();
        } else {
          alert('Failed to save user: ' + (data.message || 'Validation error'));
        }
      } catch (err) {
        alert('Server error: ' + err.message);
      } finally {
        this.saving = false;
      }
    },

    async toggleStatus(user) {
      const newStatus = user.status === 'active' ? 'inactive' : 'active';
      if (!confirm(`Are you sure you want to change user "${user.name}" status to ${newStatus}?`)) return;

      try {
        const res = await fetch(`/api/admin/users/${user.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            name: user.name,
            email: user.email,
            role: user.role,
            status: newStatus
          })
        });

        if (res.ok) {
          this.fetchUsers();
        } else {
          alert('Failed to update user status.');
        }
      } catch (err) {
        alert('Error: ' + err.message);
      }
    },

    openResetModal(user) {
      this.resetUserTarget = user;
      this.newResetPassword = '';
      this.showResetModal = true;
    },

    async submitPasswordReset() {
      this.saving = true;
      try {
        const res = await fetch(`/api/admin/users/${this.resetUserTarget.id}/reset-password`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            password: this.newResetPassword
          })
        });

        const data = await res.json();
        if (res.ok && data.status === 'success') {
          alert(`Password reset successfully for ${this.resetUserTarget.name}.`);
          this.showResetModal = false;
        } else {
          alert('Password reset failed: ' + (data.message || 'Validation error'));
        }
      } catch (err) {
        alert('Error: ' + err.message);
      } finally {
        this.saving = false;
      }
    }
  }
}
</script>

<style scoped>
.fs-7 {
  font-size: 0.75rem;
}
</style>
