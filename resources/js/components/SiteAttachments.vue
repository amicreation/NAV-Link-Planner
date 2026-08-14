<template>
  <div class="card border mb-4">
    <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between">
      <div class="d-flex align-items-center gap-2">
        <svg class="text-primary" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
          <circle cx="8.5" cy="8.5" r="1.5"/>
          <polyline points="21 15 16 10 5 21"/>
        </svg>
        <span class="fw-bold text-main">Field Survey Attachments & Site Photos</span>
      </div>
      <span class="badge bg-secondary font-monospace">{{ attachments.length }} Attached</span>
    </div>

    <div class="card-body p-3">
      <div class="row g-3">
        <!-- Photo Upload Box Dropzone -->
        <div class="col-md-4">
          <div class="border border-2 border-dashed p-4 text-center rounded-3 h-100 d-flex flex-column justify-content-center align-items-center upload-dropzone">
            <svg width="36" height="36" class="text-primary mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
              <polyline points="17 8 12 3 7 8"/>
              <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            <div class="fw-semibold text-main small mb-1">Add Site Photo / Field Diagram</div>
            <p class="text-muted fs-7 mb-3">Upload JPG/PNG field survey images for PDF report</p>
            <input type="file" ref="fileInput" accept="image/*" class="d-none" @change="handleFileUpload" />
            <button class="btn btn-sm btn-outline-primary fw-semibold" @click="$refs.fileInput.click()">
              + Select Image File
            </button>
          </div>
        </div>

        <!-- Attached Photos Grid -->
        <div class="col-md-8">
          <div v-if="attachments.length === 0" class="text-center py-5 text-muted small border border-secondary border-dashed rounded-3 empty-box">
            No field attachments uploaded yet. Click <strong>+ Select Image File</strong> to attach survey photos.
          </div>

          <div v-else class="row g-3">
            <div v-for="(item, idx) in attachments" :key="idx" class="col-md-6">
              <div class="card h-100 border p-2 position-relative attachment-card">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-2 border p-1 rounded-circle remove-btn" @click="removeAttachment(idx)"></button>
                <div class="text-center mb-2 rounded img-container" style="height: 120px; overflow: hidden;">
                  <img :src="item.image" alt="Attachment" style="max-height: 120px; width: auto; object-fit: contain;" />
                </div>
                <input 
                  type="text" 
                  v-model="item.caption" 
                  class="form-control form-control-sm" 
                  placeholder="Enter Photo Caption (e.g. Site A Tower Structure)" 
                />
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    attachments: {
      type: Array,
      default: () => []
    }
  },
  emits: ['update:attachments'],
  methods: {
    handleFileUpload(event) {
      const file = event.target.files[0];
      if (!file) return;

      const reader = new FileReader();
      reader.onload = (e) => {
        const newAttachments = [...this.attachments, {
          image: e.target.result,
          caption: file.name.replace(/\.[^/.]+$/, "")
        }];
        this.$emit('update:attachments', newAttachments);
        this.$refs.fileInput.value = '';
      };
      reader.readAsDataURL(file);
    },

    removeAttachment(index) {
      const updated = [...this.attachments];
      updated.splice(index, 1);
      this.$emit('update:attachments', updated);
    }
  }
}
</script>

<style scoped>
.fs-7 {
  font-size: 0.75rem;
}
.upload-dropzone {
  background-color: var(--bg-input);
  border-color: var(--border-color) !important;
}
.empty-box {
  background-color: var(--bg-input);
  border-color: var(--border-color) !important;
}
.attachment-card {
  background-color: var(--bg-card);
  border-color: var(--border-color) !important;
}
.img-container {
  background-color: var(--bg-input);
}
.remove-btn {
  background-color: var(--bg-card);
}
</style>
