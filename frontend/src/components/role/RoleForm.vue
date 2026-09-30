<script setup>
import { computed, reactive, watch } from "vue";

const props = defineProps({
  role: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: null,
  },
});

const emit = defineEmits(["submit", "cancel"]);

const form = reactive({
  name: "",
  description: "",
});

const isEditing = computed(() => Boolean(props.role?.id));
const isBuiltIn = computed(() => ["admin", "client"].includes(form.name));

watch(
  () => props.role,
  (role) => {
    form.name = role?.name ?? "";
    form.description = role?.description ?? "";
  },
  { immediate: true },
);

function submit() {
  emit("submit", {
    name: form.name.trim(),
    description: form.description.trim() || null,
  });
}
</script>

<template>
  <form class="role-form" @submit.prevent="submit">
    <div v-if="error" class="form-alert" role="alert">{{ error }}</div>

    <div class="field-group">
      <label for="role-name">Nom du rôle</label>
      <input
        id="role-name"
        v-model="form.name"
        type="text"
        required
        maxlength="50"
        pattern="[A-Za-z0-9_-]+"
        :readonly="isBuiltIn"
      />
      <small>Utilisez des lettres, chiffres, tirets ou underscores.</small>
    </div>

    <div class="field-group">
      <label for="role-description">Description</label>
      <textarea id="role-description" v-model="form.description" rows="4" maxlength="1000" />
    </div>

    <div class="form-actions">
      <button class="button button-secondary" type="button" :disabled="loading" @click="emit('cancel')">
        Annuler
      </button>
      <button class="button button-primary" type="submit" :disabled="loading">
        {{ loading ? "Enregistrement…" : isEditing ? "Enregistrer" : "Créer le rôle" }}
      </button>
    </div>
  </form>
</template>

<style scoped>
.role-form {
  display: grid;
  gap: 1.1rem;
  max-width: 620px;
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fff;
}

.field-group {
  display: grid;
  gap: 0.4rem;
}

label {
  color: #334155;
  font-size: 0.9rem;
  font-weight: 700;
}

small {
  color: #64748b;
  font-size: 0.78rem;
}

input,
textarea {
  width: 100%;
  box-sizing: border-box;
  padding: 0.75rem 0.85rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  color: #0f172a;
  background: #fff;
  font: inherit;
  resize: vertical;
}

input[readonly] {
  color: #64748b;
  background: #f8fafc;
}

input:focus,
textarea:focus {
  outline: 2px solid #bfdbfe;
  border-color: #2563eb;
}

.form-alert {
  padding: 0.8rem 1rem;
  border-radius: 8px;
  color: #991b1b;
  background: #fef2f2;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.7rem;
  padding-top: 0.5rem;
}

.button {
  padding: 0.7rem 1rem;
  border: 0;
  border-radius: 8px;
  font: inherit;
  font-weight: 700;
  cursor: pointer;
}

.button:disabled {
  cursor: wait;
  opacity: 0.65;
}

.button-secondary {
  color: #334155;
  background: #e2e8f0;
}

.button-primary {
  color: #fff;
  background: #2563eb;
}
</style>
