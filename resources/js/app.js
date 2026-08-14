import { createApp } from 'vue';
import html2canvas from 'html2canvas';
import Navigation from './components/Navigation.vue';
import Sidebar from './components/Sidebar.vue';
import CommandPaletteModal from './components/CommandPaletteModal.vue';
import Dashboard from './components/Dashboard.vue';
import NewProjectModal from './components/NewProjectModal.vue';
import SiteInputForm from './components/SiteInputForm.vue';
import TechnologyInputs from './components/TechnologyInputs.vue';
import SiteAttachments from './components/SiteAttachments.vue';
import ResultsView from './components/ResultsView.vue';
import ReportsView from './components/ReportsView.vue';
import LoginView from './components/LoginView.vue';
import AdminUsersView from './components/AdminUsersView.vue';
import AuditLogsView from './components/AuditLogsView.vue';
import EquipmentLibraryView from './components/EquipmentLibraryView.vue';
import UserProfileModal from './components/UserProfileModal.vue';

const app = createApp({
    components: {
        Navigation,
        Sidebar,
        CommandPaletteModal,
        Dashboard,
        NewProjectModal,
        SiteInputForm,
        TechnologyInputs,
        SiteAttachments,
        ResultsView,
        ReportsView,
        LoginView,
        AdminUsersView,
        AuditLogsView,
        EquipmentLibraryView,
        UserProfileModal,
    },
    data() {
        return {
            currentUser: null,
            checkingAuth: true,
            currentTab: 'dashboard',
            showNewProjectModal: false,
            showCommandPaletteModal: false,
            showProfileModal: false,
            initialTech: 'rf',
            projects: [],
            activeProject: null,
            calculating: false,

            // Workbench State
            technology: 'rf',
            siteA: {
                name: 'Site A (Node A)',
                latitude: 12.97159870,
                longitude: 77.59456270,
                tower_height: 30.000,
                ground_elevation: 0
            },
            siteB: {
                name: 'Site B (Node B)',
                latitude: 13.03582450,
                longitude: 77.62514820,
                tower_height: 30.000,
                ground_elevation: 0
            },
            rfParams: {
                frequency_ghz: 5.8,
                required_throughput_mbps: 100
            },
            fsoParams: {
                fso_calc_mode: 'full_tech',
                user_custom_remarks: '',
                wavelength: '1550 nm',
                required_throughput_mbps: 1000,
                tx_power_dbm: 24,
                beam_divergence_mrad: 0.15,
                rx_aperture_m: 0.9,
                system_loss_db: 3,
                rx_sensitivity_dbm: -18,
                ref_fade_margin_db: 22,
                selected_city: 'Bengaluru, Karnataka',
                visibility_km: 2.0
            },
            additionalImages: [],
            calculationResults: null
        };
    },
    mounted() {
        this.checkAuth();
        window.addEventListener('keydown', this.handleGlobalKeydown);
    },
    beforeUnmount() {
        window.removeEventListener('keydown', this.handleGlobalKeydown);
    },
    methods: {
        async checkAuth() {
            this.checkingAuth = true;
            try {
                const res = await fetch('/api/auth/me');
                const data = await res.json();
                if (res.ok && data.status === 'success' && data.user) {
                    this.currentUser = data.user;
                    this.fetchProjects();
                } else {
                    this.currentUser = null;
                }
            } catch (err) {
                console.warn('Auth check error:', err);
                this.currentUser = null;
            } finally {
                this.checkingAuth = false;
            }
        },

        handleLoginSuccess(user) {
            this.currentUser = user;
            this.currentTab = 'dashboard';
            this.fetchProjects();
        },

        async handleLogout() {
            try {
                await fetch('/api/auth/logout', { method: 'POST' });
            } catch (err) {
                console.warn('Logout error:', err);
            } finally {
                this.currentUser = null;
                this.projects = [];
                this.activeProject = null;
                this.calculationResults = null;
            }
        },

        handleGlobalKeydown(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                if (this.currentUser) {
                    this.showCommandPaletteModal = !this.showCommandPaletteModal;
                }
            }
        },

        async fetchProjects() {
            try {
                const res = await fetch('/api/projects');
                const data = await res.json();
                if (data.status === 'success') {
                    this.projects = data.data;
                }
            } catch (err) {
                console.error('Failed to fetch projects:', err);
            }
        },

        handleQuickTech(tech) {
            this.initialTech = tech;
            this.technology = tech;
            this.showNewProjectModal = true;
        },

        handleProjectCreated(project) {
            this.projects.unshift(project);
            this.loadProject(project);
        },

        async handleDeleteProject(project) {
            if (!confirm(`Are you sure you want to delete project "${project.name}"? This action cannot be undone.`)) {
                return;
            }

            try {
                const res = await fetch(`/api/projects/${project.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json'
                    }
                });

                if (res.ok) {
                    this.projects = this.projects.filter(p => p.id !== project.id);
                    if (this.activeProject && this.activeProject.id === project.id) {
                        this.activeProject = null;
                        this.calculationResults = null;
                    }
                } else {
                    const data = await res.json();
                    alert('Failed to delete project: ' + (data.message || 'Permission denied'));
                }
            } catch (err) {
                alert('Error deleting project: ' + err.message);
            }
        },

        loadProject(project) {
            this.activeProject = project;
            this.technology = project.technology;

            const sA = project.sites?.find(s => s.site_label === 'A');
            const sB = project.sites?.find(s => s.site_label === 'B');

            if (sA) {
                this.siteA = {
                    name: sA.name,
                    latitude: Number(sA.latitude),
                    longitude: Number(sA.longitude),
                    tower_height: Number(sA.tower_height),
                    ground_elevation: Number(sA.ground_elevation || 0)
                };
            }
            if (sB) {
                this.siteB = {
                    name: sB.name,
                    latitude: Number(sB.latitude),
                    longitude: Number(sB.longitude),
                    tower_height: Number(sB.tower_height),
                    ground_elevation: Number(sB.ground_elevation || 0)
                };
            }

            this.currentTab = 'planner';
            this.runCalculation();
        },

        async runCalculation() {
            this.calculating = true;
            try {
                const payload = {
                    project_id: this.activeProject?.id || null,
                    technology: this.technology,
                    site_a: this.siteA,
                    site_b: this.siteB,
                    rf_frequency_ghz: this.rfParams.frequency_ghz,
                    rf_required_throughput_mbps: this.rfParams.required_throughput_mbps,
                    fso_calc_mode: this.fsoParams.fso_calc_mode,
                    user_custom_remarks: this.fsoParams.user_custom_remarks,
                    fso_wavelength: this.fsoParams.wavelength,
                    fso_required_throughput_mbps: this.fsoParams.required_throughput_mbps,
                    fso_tx_power_dbm: this.fsoParams.tx_power_dbm,
                    fso_beam_divergence_mrad: this.fsoParams.beam_divergence_mrad,
                    fso_rx_aperture_m: this.fsoParams.rx_aperture_m,
                    fso_system_loss_db: this.fsoParams.system_loss_db,
                    fso_rx_sensitivity_dbm: this.fsoParams.rx_sensitivity_dbm,
                    fso_ref_fade_margin_db: this.fsoParams.ref_fade_margin_db,
                    fso_selected_city: this.fsoParams.selected_city,
                    fso_visibility_km: this.fsoParams.visibility_km
                };

                const res = await fetch('/api/calculate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();
                if (res.ok && data.status === 'success') {
                    this.calculationResults = data.data;
                    if (data.data.effective_elev_a) {
                        this.siteA.ground_elevation = data.data.effective_elev_a;
                    }
                    if (data.data.effective_elev_b) {
                        this.siteB.ground_elevation = data.data.effective_elev_b;
                    }
                } else {
                    alert('Calculation error: ' + (data.message || 'Check site parameters'));
                }
            } catch (err) {
                alert('Server request error: ' + err.message);
            } finally {
                this.calculating = false;
            }
        },

        async exportPdf() {
            if (!this.activeProject) {
                alert('Please create or load a project first to export PDF report.');
                return;
            }

            let mapImageBase64 = null;
            let chartImageBase64 = null;

            const mapElem = document.getElementById('leaflet-map');
            if (mapElem) {
                try {
                    const canvas = await html2canvas(mapElem, {
                        useCORS: true,
                        allowTaint: true,
                        scale: 2,
                        logging: false
                    });
                    mapImageBase64 = canvas.toDataURL('image/png');
                } catch (e) {
                    console.warn('Map html2canvas capture fallback:', e);
                }
            }

            const chartCanvas = document.getElementById('elevationCanvas');
            if (chartCanvas && chartCanvas.height > 0) {
                try {
                    const dataUrl = chartCanvas.toDataURL('image/png');
                    if (dataUrl && dataUrl.length > 500) {
                        chartImageBase64 = dataUrl;
                    }
                } catch (e) {
                    console.warn('Chart canvas export fallback:', e);
                }
            }

            const payload = {
                map_image: mapImageBase64 || this.calculationResults?.map_image,
                chart_image: chartImageBase64 || this.calculationResults?.chart_image,
                additional_images: this.additionalImages
            };

            try {
                const res = await fetch(`/api/projects/${this.activeProject.id}/pdf`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/pdf'
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    const blob = await res.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `NAV_Link_Planner_${this.activeProject.name.replace(/\s+/g, '_')}.pdf`;
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                } else {
                    alert('Failed to generate PDF report.');
                }
            } catch (err) {
                alert('PDF generation error: ' + err.message);
            }
        }
    },
    template: `
        <div class="d-flex flex-column min-vh-100">
            <!-- Loading Auth State -->
            <div v-if="checkingAuth" class="min-vh-100 d-flex align-items-center justify-content-center bg-dark text-white">
                <div class="text-center">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
                    <div class="fw-bold tracking-wider">NAV LINK PLANNER</div>
                    <div class="small text-muted">Authenticating Session...</div>
                </div>
            </div>

            <!-- Login View if Not Authenticated -->
            <LoginView 
                v-else-if="!currentUser" 
                @login-success="handleLoginSuccess" 
            />

            <!-- Main Authenticated Application Layout -->
            <template v-else>
                <!-- Top Sticky Navigation Bar -->
                <Navigation 
                    :currentTab="currentTab" 
                    :user="currentUser"
                    @change-tab="currentTab = $event" 
                    @open-new-project="showNewProjectModal = true" 
                    @open-command-palette="showCommandPaletteModal = true"
                    @open-profile="showProfileModal = true"
                    @logout="handleLogout"
                />

                <!-- App Layout: Sidebar + Main Content -->
                <div class="app-wrapper">
                    <!-- Collapsible Sidebar -->
                    <Sidebar 
                        :currentTab="currentTab"
                        :activeProject="activeProject"
                        :user="currentUser"
                        @change-tab="currentTab = $event"
                        @open-new-project="showNewProjectModal = true"
                        @open-command-palette="showCommandPaletteModal = true"
                    />

                    <!-- Main Application Content View -->
                    <main class="app-content">
                        <!-- Dashboard View -->
                        <Dashboard 
                            v-if="currentTab === 'dashboard'" 
                            :projects="projects"
                            @open-new-project="showNewProjectModal = true"
                            @change-tab="currentTab = $event"
                            @load-project="loadProject"
                            @quick-tech="handleQuickTech"
                            @delete-project="handleDeleteProject"
                        />

                        <!-- Link Engineering Workbench Flagship Screen -->
                        <div v-else-if="currentTab === 'planner'" class="container-fluid px-4 py-4">
                            <!-- Workbench Top Header Toolbar -->
                            <div class="card p-3 mb-4 border border-secondary" style="background-color: var(--bg-card-header);">
                                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge bg-primary font-monospace">WGS84 ENGINE</span>
                                            <span v-if="activeProject" class="badge bg-dark border text-white">
                                                Project: {{ activeProject.name }}
                                            </span>
                                        </div>
                                        <h3 class="page-title text-main mb-0" style="font-size: 1.5rem;">Link Planner</h3>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button 
                                            class="btn btn-primary px-4 fw-semibold d-flex align-items-center gap-2 shadow-sm" 
                                            @click="runCalculation" 
                                            :disabled="calculating"
                                        >
                                            <span v-if="calculating" class="spinner-border spinner-border-sm"></span>
                                            <svg v-else width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <polygon points="5 3 19 12 5 21 5 3"/>
                                            </svg>
                                            Calculate Link Analysis
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Site Inputs Form -->
                            <SiteInputForm :site-a="siteA" :site-b="siteB" class="mb-4" />

                            <!-- Technology Specific Parameters -->
                            <TechnologyInputs 
                                v-model:technology="technology"
                                :rf-params="rfParams"
                                :fso-params="fsoParams"
                                @recalculate="runCalculation"
                            />

                            <!-- Site Survey Photo Attachments -->
                            <SiteAttachments 
                                v-model:attachments="additionalImages"
                            />

                            <!-- Results Output Screen -->
                            <ResultsView 
                                :results="calculationResults" 
                                :site-a="siteA"
                                :site-b="siteB"
                                @export-pdf="exportPdf" 
                            />
                        </div>

                        <!-- Floating Calculate Link Analysis Button (Bottom Right) -->
                        <button 
                            v-if="currentTab === 'planner'"
                            @click="runCalculation" 
                            :disabled="calculating"
                            class="btn btn-primary btn-lg rounded-pill shadow-lg d-flex align-items-center gap-2 px-4 py-3 fw-bold border-0"
                            style="position: fixed; bottom: 32px; right: 32px; z-index: 1050; box-shadow: 0 10px 30px -5px rgba(37, 99, 235, 0.65) !important; cursor: pointer;"
                        >
                            <span v-if="calculating" class="spinner-border spinner-border-sm" role="status"></span>
                            <svg v-else width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                <polygon points="5 3 19 12 5 21 5 3"/>
                            </svg>
                            {{ calculating ? 'Calculating Path...' : 'Calculate Link Analysis' }}
                        </button>

                        <!-- Projects Directory View -->
                        <div v-else-if="currentTab === 'projects'" class="container-fluid px-4 py-4">
                            <div class="pb-3 mb-4 border-bottom border-secondary">
                                <div>
                                    <h3 class="page-title text-main mb-1">Projects Directory</h3>
                                    <p class="text-secondary small mb-0">Browse, filter and manage stored engineering link projects</p>
                                </div>
                            </div>
                            <Dashboard 
                                :projects="projects"
                                @open-new-project="showNewProjectModal = true"
                                @change-tab="currentTab = $event"
                                @load-project="loadProject"
                                @quick-tech="handleQuickTech"
                                @delete-project="handleDeleteProject"
                            />
                        </div>

                        <!-- Reports View -->
                        <ReportsView 
                            v-else-if="currentTab === 'reports'"
                            :projects="projects"
                        />

                        <!-- Equipment Reference Library View -->
                        <EquipmentLibraryView 
                            v-else-if="currentTab === 'equipment'"
                        />

                        <!-- Admin Module: User Management -->
                        <AdminUsersView 
                            v-else-if="currentTab === 'admin-users' && currentUser?.role === 'admin'"
                        />

                        <!-- Admin Module: Audit Trail -->
                        <AuditLogsView 
                            v-else-if="currentTab === 'audit-logs' && currentUser?.role === 'admin'"
                        />
                    </main>
                </div>

                <!-- New Project Modal -->
                <NewProjectModal 
                    :show="showNewProjectModal"
                    :initial-tech="initialTech"
                    @close="showNewProjectModal = false"
                    @project-created="handleProjectCreated"
                />

                <!-- Command Palette Quick Search Modal -->
                <CommandPaletteModal 
                    :show="showCommandPaletteModal"
                    :projects="projects"
                    @close="showCommandPaletteModal = false"
                    @open-new-project="showNewProjectModal = true"
                    @change-tab="currentTab = $event"
                    @load-project="loadProject"
                />

                <!-- User Profile Modal -->
                <UserProfileModal 
                    :show="showProfileModal"
                    :user="currentUser"
                    @close="showProfileModal = false"
                    @user-updated="currentUser = $event"
                />
            </template>
        </div>
    `
});

app.mount('#app');
