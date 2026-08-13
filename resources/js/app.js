import { createApp } from 'vue';
import html2canvas from 'html2canvas';
import Navigation from './components/Navigation.vue';
import Dashboard from './components/Dashboard.vue';
import NewProjectModal from './components/NewProjectModal.vue';
import SiteInputForm from './components/SiteInputForm.vue';
import TechnologyInputs from './components/TechnologyInputs.vue';
import SiteAttachments from './components/SiteAttachments.vue';
import ResultsView from './components/ResultsView.vue';
import ReportsView from './components/ReportsView.vue';

const app = createApp({
    components: {
        Navigation,
        Dashboard,
        NewProjectModal,
        SiteInputForm,
        TechnologyInputs,
        SiteAttachments,
        ResultsView,
        ReportsView,
    },
    data() {
        return {
            currentTab: 'dashboard',
            showNewProjectModal: false,
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
        this.fetchProjects();
    },
    methods: {
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
                    alert('Failed to delete project.');
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
            if (chartCanvas) {
                try {
                    chartImageBase64 = chartCanvas.toDataURL('image/png');
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
        <div>
            <Navigation 
                :currentTab="currentTab" 
                @change-tab="currentTab = $event" 
                @open-new-project="showNewProjectModal = true" 
            />

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

            <!-- Link Workbench View -->
            <div v-else-if="currentTab === 'planner'" class="container-fluid px-4 py-4">
                <div class="d-flex align-items-center justify-content-between pb-3 mb-4 border-bottom">
                    <div>
                        <span class="badge bg-primary me-2">WGS84 Engine</span>
                        <h4 class="fw-bold d-inline-block mb-0 brand-blue">Link Engineering Workbench</h4>
                        <span v-if="activeProject" class="ms-3 badge bg-dark text-white fs-6">
                            Project: {{ activeProject.name }}
                        </span>
                    </div>
                    <div>
                        <button 
                            class="btn btn-primary px-4 fw-bold shadow-sm" 
                            @click="runCalculation" 
                            :disabled="calculating"
                        >
                            <span v-if="calculating" class="spinner-border spinner-border-sm me-2"></span>
                            Calculate Link &rarr;
                        </button>
                    </div>
                </div>

                <!-- Site Input Form -->
                <SiteInputForm :site-a="siteA" :site-b="siteB" class="mb-3" />

                <!-- Tech Specific Form -->
                <TechnologyInputs 
                    v-model:technology="technology"
                    :rf-params="rfParams"
                    :fso-params="fsoParams"
                />

                <!-- Site Photo Attachments Form -->
                <SiteAttachments 
                    v-model:attachments="additionalImages"
                />

                <!-- Action Button -->
                <div class="d-grid mb-4">
                    <button 
                        class="btn btn-primary py-2 fw-bold fs-5 shadow-sm" 
                        @click="runCalculation" 
                        :disabled="calculating"
                    >
                        <span v-if="calculating" class="spinner-border spinner-border-sm me-2"></span>
                        Run Engineering Calculations
                    </button>
                </div>

                <!-- Results Output Screen -->
                <ResultsView 
                    :results="calculationResults" 
                    :site-a="siteA"
                    :site-b="siteB"
                    @export-pdf="exportPdf" 
                />
            </div>

            <!-- Projects Listing View -->
            <div v-else-if="currentTab === 'projects'" class="container-fluid px-4 py-4">
                <div class="d-flex align-items-center justify-content-between pb-3 mb-4 border-bottom">
                    <h4 class="fw-bold mb-0 brand-blue">Projects Directory</h4>
                    <button class="btn btn-primary btn-sm fw-semibold" @click="showNewProjectModal = true">
                        + New Project
                    </button>
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

            <!-- New Project Modal -->
            <NewProjectModal 
                :show="showNewProjectModal"
                :initial-tech="initialTech"
                @close="showNewProjectModal = false"
                @project-created="handleProjectCreated"
            />
        </div>
    `
});

app.mount('#app');
