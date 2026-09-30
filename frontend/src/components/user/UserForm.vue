<script setup>
import { computed, reactive, watch } from "vue";

const props = defineProps({
  user: { type: Object, default: null },
  roles: { type: Array, default: () => [] },
  loading: Boolean,
  error: { type: String, default: "" },
});

const emit = defineEmits(["submit", "cancel"]);
const form = reactive({ first_name: "", last_name: "", email: "", password: "", password_confirmation: "", role_id: "" });
const isEdit = computed(() => Boolean(props.user));

watch(() => props.user, (user) => {
  form.first_name = user?.first_name || "";
  form.last_name = user?.last_name || "";
  form.email = user?.email || "";
  form.password = "";
  form.password_confirmation = "";
  form.role_id = user?.role_id ?? user?.role?.id ?? "";
}, { immediate: true });

function submit() {
  const payload = { first_name: form.first_name, last_name: form.last_name, email: form.email, role_id: Number(form.role_id) };
  if (form.password) {
    payload.password = form.password;
    payload.password_confirmation = form.password_confirmation;
  }
  emit("submit", payload);
}
</script>

<template>
  <form class="form-card" @submit.prevent="submit">
    <div v-if="error" class="alert" role="alert">{{ error }}</div>
    <div class="grid">
      <label>Prénom<input v-model.trim="form.first_name" required autocomplete="given-name" /></label>
      <label>Nom<input v-model.trim="form.last_name" required autocomplete="family-name" /></label>
    </div>
    <label>E-mail<input v-model.trim="form.email" type="email" required autocomplete="email" /></label>
    <label>Rôle
      <select v-model="form.role_id" required>
        <option disabled value="">Sélectionner un rôle</option>
        <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option>
      </select>
    </label>
    <div class="grid">
      <label>Mot de passe <small v-if="isEdit">(laisser vide pour ne pas changer)</small><input v-model="form.password" type="password" :required="!isEdit" autocomplete="new-password" minlength="8" /></label>
      <label>Confirmation<input v-model="form.password_confirmation" type="password" :required="Boolean(form.password)" autocomplete="new-password" minlength="8" /></label>
    </div>
    <div class="actions">
      <button type="button" class="secondary" @click="$emit('cancel')">Annuler</button>
      <button type="submit" :disabled="loading">{{ loading ? "Enregistrement…" : isEdit ? "Enregistrer" : "Créer" }}</button>
    </div>
  </form>
</template>

<style scoped>
.form-card { max-width: 720px; display: grid; gap: 1rem; padding: 1.5rem; border: 1px solid #e2e8f0; border-radius: .75rem; background: #fff; }
.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
label { display: grid; gap: .4rem; color: #334155; font-weight: 700; }
small { color: #64748b; font-weight: 400; }
input, select { width: 100%; box-sizing: border-box; padding: .7rem .8rem; border: 1px solid #cbd5e1; border-radius: .45rem; font: inherit; }
input:focus, select:focus { outline: 2px solid #bfdbfe; border-color: #2563eb; }
.actions { display: flex; justify-content: flex-end; gap: .75rem; }
button { padding: .75rem 1rem; border: 0; border-radius: .45rem; color: #fff; background: #2563eb; font: inherit; font-weight: 700; cursor: pointer; }
button:disabled { opacity: .6; cursor: wait; }
.secondary { color: #334155; background: #e2e8f0; }
.alert { padding: .75rem 1rem; border-radius: .45rem; color: #991b1b; background: #fef2f2; }
@media (max-width: 600px) { .grid { grid-template-columns: 1fr; } }
</style>
