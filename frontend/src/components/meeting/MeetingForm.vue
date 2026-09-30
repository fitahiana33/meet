<script setup>
import { reactive } from "vue";

defineProps({ loading: Boolean, error: { type: String, default: "" } });
const emit = defineEmits(["submit", "cancel"]);
const form = reactive({ title: "", description: "", scheduled_at: "" });
function submit() { emit("submit", { title: form.title, description: form.description || null, scheduled_at: form.scheduled_at || null }); }
</script>

<template>
  <form class="meeting-form" @submit.prevent="submit">
    <div class="form-intro"><span class="form-icon">◉</span><div><h3>Informations de la réunion</h3><p>Donnez à votre réunion un cadre clair avant d’inviter les participants.</p></div></div>
    <div v-if="error" class="alert" role="alert"><span>!</span>{{ error }}</div>
    <label>Titre de la réunion<input v-model.trim="form.title" required maxlength="255" placeholder="Ex. Point équipe produit" /></label>
    <label>Description<textarea v-model.trim="form.description" rows="5" maxlength="5000" placeholder="Présentez l’objectif et l’ordre du jour…" /></label>
    <label>Date et heure de début <small>Facultatif</small><input v-model="form.scheduled_at" type="datetime-local" /></label>
    <div class="form-actions"><button type="button" class="secondary" @click="$emit('cancel')">Annuler</button><button type="submit" :disabled="loading">{{ loading ? "Création…" : "Créer la réunion" }} <span>→</span></button></div>
  </form>
</template>

<style scoped>
.meeting-form { display: grid; max-width: 700px; gap: 1.1rem; padding: 1.4rem; border: 1px solid #e8eef6; border-radius: .9rem; background: #fff; box-shadow: 0 5px 15px rgb(30 52 84 / 3%); }.form-intro { display: flex; align-items: center; gap: .75rem; }.form-icon { display: grid; width: 2.35rem; height: 2.35rem; place-items: center; border-radius: .7rem; color: #4d7ddd; background: #eaf1ff; }.form-intro h3 { margin: 0; color: #253650; font-size: .9rem; }.form-intro p { margin: .25rem 0 0; color: #98a5b5; font-size: .7rem; }label { display: grid; gap: .45rem; color: #53647c; font-size: .72rem; font-weight: 700; }small { color: #9aa7b7; font-weight: 400; }input, textarea { width: 100%; padding: .75rem .8rem; border: 1px solid #dce4ef; border-radius: .5rem; outline: none; color: #30415a; background: #fff; font: inherit; font-size: .78rem; resize: vertical; }input:focus, textarea:focus { border-color: #6d95eb; box-shadow: 0 0 0 4px rgb(79 120 232 / 10%); }input::placeholder, textarea::placeholder { color: #b5c0ce; }.form-actions { display: flex; justify-content: flex-end; gap: .65rem; padding-top: .3rem; }.form-actions button { padding: .75rem 1rem; border: 0; border-radius: .5rem; font-size: .72rem; font-weight: 700; cursor: pointer; }.form-actions button[type="submit"] { color: #fff; background: #4b79dc; box-shadow: 0 7px 16px rgb(59 105 218 / 17%); }.form-actions button[type="submit"] span { margin-left: .25rem; font-size: 1rem; }.secondary { color: #68788e; background: #f1f4f8; }.form-actions button:disabled { cursor: wait; opacity: .6; }.alert { display: flex; align-items: center; gap: .5rem; padding: .7rem .8rem; border: 1px solid #ffd7db; border-radius: .5rem; color: #b33e4b; background: #fff3f4; font-size: .75rem; }.alert span { display: grid; width: 1.1rem; height: 1.1rem; place-items: center; border-radius: 50%; color: #fff; background: #e36c76; font-weight: 800; }
@media (max-width: 600px) { .form-actions { flex-direction: column-reverse; }.form-actions button { width: 100%; } }
</style>
