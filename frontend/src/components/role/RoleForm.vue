<script setup>
import { computed, reactive, watch } from "vue";

const props = defineProps({ role: { type: Object, default: null }, loading: Boolean, error: { type: String, default: "" } });
const emit = defineEmits(["submit", "cancel"]);
const form = reactive({ name: "", description: "" });
const isEdit = computed(() => Boolean(props.role));
const isBuiltIn = computed(() => ["admin", "client"].includes(props.role?.name));

watch(() => props.role, (role) => { form.name = role?.name || ""; form.description = role?.description || ""; }, { immediate: true });
function submit() { emit("submit", { name: form.name, description: form.description || null }); }
</script>

<template>
  <form class="form-card" @submit.prevent="submit">
    <div v-if="error" class="alert" role="alert">{{ error }}</div>
    <label>Nom du rôle<input v-model.trim="form.name" required pattern="[A-Za-z0-9_-]+" :readonly="isBuiltIn" /></label>
    <p v-if="isBuiltIn" class="hint">Le nom d’un rôle système ne peut pas être modifié.</p>
    <label>Description<textarea v-model.trim="form.description" rows="4" /></label>
    <div class="actions">
      <button type="button" class="secondary" @click="$emit('cancel')">Annuler</button>
      <button type="submit" :disabled="loading">{{ loading ? "Enregistrement…" : isEdit ? "Enregistrer" : "Créer" }}</button>
    </div>
  </form>
</template>

<style scoped>
.form-card { max-width: 620px; display: grid; gap: 1rem; padding: 1.5rem; border: 1px solid #e2e8f0; border-radius: .75rem; background: #fff; }
label { display: grid; gap: .4rem; color: #334155; font-weight: 700; }
input, textarea { width: 100%; box-sizing: border-box; padding: .7rem .8rem; border: 1px solid #cbd5e1; border-radius: .45rem; font: inherit; resize: vertical; }
input[readonly] { color: #64748b; background: #f1f5f9; }.hint { margin: -.5rem 0 0; color: #64748b; font-size: .85rem; }
.actions { display: flex; justify-content: flex-end; gap: .75rem; } button { padding: .75rem 1rem; border: 0; border-radius: .45rem; color: #fff; background: #2563eb; font: inherit; font-weight: 700; cursor: pointer; }.secondary { color: #334155; background: #e2e8f0; }.alert { padding: .75rem 1rem; border-radius: .45rem; color: #991b1b; background: #fef2f2; }
</style>
