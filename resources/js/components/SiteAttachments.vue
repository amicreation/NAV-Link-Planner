<template>
  <div class="card border mb-4">
    <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between">
      <span class="fw-bold text-dark">Field Survey Attachments & Site Photos (Optional Report Additions)</span>
      <span class="badge bg-light text-dark border">{{ attachments.length }} Attached</span>
    </div>

    <div class="card-body p-3">
      <div class="row g-3">
        <!-- Photo Upload Box -->
        <div class="col-md-4">
          <div class="border border-2 border-dashed p-3 text-center rounded bg-light h-100 d-flex flex-column justify-content-center align-items-center">
            <svg width="32" height="32" class="text-secondary mb-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
              <circle cx="8.5" cy="8.5" r="1.5"/>
              <polyline points="21 15 16 10 5 21"/>
            </svg>
            <div class="fw-semibold small text-dark mb-1">Add Site Photo / Field Diagram</div>
            <p class="text-muted fs-7 mb-2">Upload JPG/PNG field images for PDF report</p>
            <input type="file" ref="fileInput" accept="image/*" class="d-none" @change="handleFileUpload" />
            <button class="btn btn-sm btn-outline-primary fw-semibold" @click="$refs.fileInput.click()">
              + Select Image File
            </button>
          </div>
        </div>

        <!-- Attached Photos Grid -->
        <div class="col-md-8">
          <div v-if="attachments.length === 0" class="text-center py-4 text-muted small border rounded">
            No field attachments uploaded yet. Click <strong>+ Select Image File</strong> to attach site survey photos to the engineering report.
          </div>

          <div v-else class="row g-2">
            <div v-for="(item, idx) in attachments" :key="idx" class="col-md-6">
              <div class="card h-100 border p-2 position-relative">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-1 bg-white p-1" @click="removeAttachment(idx)"></button>
                <div class="text-center mb-2 bg-dark rounded" style="height: 120px; overflow: hidden;">
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
.border-dashed {
  border-style: dashed !important;
}
</style>
