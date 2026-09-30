<script setup>
import { computed, reactive, watch } from "vue";

const props = defineProps({ user: { type: Object, default: null }, roles: { type: Array, default: () => [] }, loading: Boolean, error: { type: String, default: "" } });
const emit = defineEmits(["submit", "cancel"]);
const form = reactive({ first_name: "", last_name: "", email: "", password: "", password_confirmation: "", role_id: "" });
const isEdit = computed(() => Boolean(props.user));
watch(() => props.user, (user) => { form.first_name = user?.first_name || ""; form.last_name = user?.last_name || ""; form.email = user?.email || ""; form.password = ""; form.password_confirmation = ""; form.role_id = user?.role_id ?? user?.role?.id ?? ""; }, { immediate: true });
function submit() { const payload = { first_name: form.first_name, last_name: form.last_name, email: form.email, role_id: Number(form.role_id) }; if (form.password) { payload.password = form.password; payload.password_confirmation = form.password_confirmation; } emit("submit", payload); }
</script>

<template>
  <form class="form-card" @submit.prevent="submit">
    <div class="form-intro"><span class="form-icon">♙</span><div><h3>Informations du compte</h3><p>Renseignez les informations principales de cet utilisateur.</p></div></div>
    <div v-if="error" class="alert" role="alert"><span>!</span>{{ error }}</div>
    <div class="form-grid"><label>Prénom<input v-model.trim="form.first_name" required autocomplete="given-name" placeholder="Ex. Jean" /></label><label>Nom<input v-model.trim="form.last_name" required autocomplete="family-name" placeholder="Ex. Dupont" /></label></div>
    <label>Adresse e-mail<input v-model.trim="form.email" type="email" required autocomplete="email" placeholder="jean@exemple.com" /></label>
    <label>Rôle<select v-model="form.role_id" required><option disabled value="">Sélectionner un rôle</option><option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name }}</option></select></label>
    <div class="form-divider"><span>Sécurité du compte</span></div>
    <div class="form-grid"><label>Mot de passe <small v-if="isEdit">(facultatif en modification)</small><input v-model="form.password" type="password" :required="!isEdit" autocomplete="new-password" minlength="8" placeholder="8 caractères minimum" /></label><label>Confirmation<input v-model="form.password_confirmation" type="password" :required="Boolean(form.password)" autocomplete="new-password" minlength="8" placeholder="Répétez le mot de passe" /></label></div>
    <div class="form-actions"><button type="button" class="secondary-button" @click="$emit('cancel')">Annuler</button><button type="submit" class="submit-button" :disabled="loading">{{ loading ? "Enregistrement…" : isEdit ? "Enregistrer les changements" : "Créer l’utilisateur" }} <span>→</span></button></div>
  </form>
</template>

<style scoped>
.form-card { display: grid; max-width: 760px; gap: 1.15rem; padding: 1.4rem; border: 1px solid #e8eef6; border-radius: .9rem; background: #fff; box-shadow: 0 5px 15px rgb(30 52 84 / 3%); }.form-intro { display: flex; align-items: center; gap: .75rem; padding-bottom: .2rem; }.form-icon { display: grid; width: 2.35rem; height: 2.35rem; place-items: center; border-radius: .7rem; color: #4d7ddd; background: #eaf1ff; }.form-intro h3 { margin: 0; color: #253650; font-size: .9rem; }.form-intro p { margin: .25rem 0 0; color: #98a5b5; font-size: .7rem; }.form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }label { display: grid; gap: .45rem; color: #53647c; font-size: .72rem; font-weight: 700; }small { color: #9aa7b7; font-weight: 400; }input, select { width: 100%; padding: .75rem .8rem; border: 1px solid #dce4ef; border-radius: .5rem; outline: none; color: #30415a; background: #fff; font-size: .78rem; }input::placeholder { color: #b5c0ce; }input:focus, select:focus { border-color: #6d95eb; box-shadow: 0 0 0 4px rgb(79 120 232 / 10%); }.form-divider { display: flex; align-items: center; gap: .7rem; color: #9aa7b7; font-size: .66rem; }.form-divider::after { content: ""; height: 1px; flex: 1; background: #edf1f6; }.form-actions { display: flex; justify-content: flex-end; gap: .65rem; padding-top: .3rem; }.submit-button, .secondary-button { padding: .75rem 1rem; border: 0; border-radius: .5rem; font-size: .72rem; font-weight: 700; cursor: pointer; }.submit-button { color: #fff; background: linear-gradient(100deg, #3e70da, #5c82e4); box-shadow: 0 7px 16px rgb(59 105 218 / 17%); }.submit-button span { margin-left: .3rem; font-size: 1rem; font-weight: 400; }.secondary-button { color: #68788e; background: #f1f4f8; }.submit-button:disabled { cursor: wait; opacity: .6; }.alert { display: flex; align-items: center; gap: .5rem; padding: .7rem .8rem; border: 1px solid #ffd7db; border-radius: .5rem; color: #b33e4b; background: #fff3f4; font-size: .75rem; }.alert span { display: grid; width: 1.1rem; height: 1.1rem; place-items: center; border-radius: 50%; color: #fff; background: #e36c76; font-weight: 800; }
@media (max-width: 600px) { .form-grid { grid-template-columns: 1fr; }.form-actions { flex-direction: column-reverse; }.form-actions button { width: 100%; } }
</style>
