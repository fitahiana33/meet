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
    <div class="form-intro"><span class="form-icon">◇</span><div><h3>Détails du rôle</h3><p>Créez un niveau d’accès clair pour votre organisation.</p></div></div>
    <div v-if="error" class="alert" role="alert"><span>!</span>{{ error }}</div>
    <label>Nom du rôle<input v-model.trim="form.name" required pattern="[A-Za-z0-9_-]+" :readonly="isBuiltIn" placeholder="Ex. manager" /></label>
    <p v-if="isBuiltIn" class="hint">Le nom d’un rôle système ne peut pas être modifié.</p>
    <label>Description<textarea v-model.trim="form.description" rows="5" placeholder="Décrivez les responsabilités de ce rôle…" /></label>
    <div class="form-actions"><button type="button" class="secondary-button" @click="$emit('cancel')">Annuler</button><button type="submit" class="submit-button" :disabled="loading">{{ loading ? "Enregistrement…" : isEdit ? "Enregistrer les changements" : "Créer le rôle" }} <span>→</span></button></div>
  </form>
</template>

<style scoped>
.form-card { display: grid; max-width: 680px; gap: 1.15rem; padding: 1.4rem; border: 1px solid #e8eef6; border-radius: .9rem; background: #fff; box-shadow: 0 5px 15px rgb(30 52 84 / 3%); }.form-intro { display: flex; align-items: center; gap: .75rem; }.form-icon { display: grid; width: 2.35rem; height: 2.35rem; place-items: center; border-radius: .7rem; color: #8864ca; background: #f1ebff; }.form-intro h3 { margin: 0; color: #253650; font-size: .9rem; }.form-intro p { margin: .25rem 0 0; color: #98a5b5; font-size: .7rem; }label { display: grid; gap: .45rem; color: #53647c; font-size: .72rem; font-weight: 700; }input, textarea { width: 100%; padding: .75rem .8rem; border: 1px solid #dce4ef; border-radius: .5rem; outline: none; color: #30415a; background: #fff; font-size: .78rem; font-family: inherit; resize: vertical; }input::placeholder, textarea::placeholder { color: #b5c0ce; }input:focus, textarea:focus { border-color: #6d95eb; box-shadow: 0 0 0 4px rgb(79 120 232 / 10%); }input[readonly] { color: #8c99ab; background: #f5f7fa; }.hint { margin: -.65rem 0 0; color: #9aa7b7; font-size: .68rem; }.form-actions { display: flex; justify-content: flex-end; gap: .65rem; padding-top: .3rem; }.submit-button, .secondary-button { padding: .75rem 1rem; border: 0; border-radius: .5rem; font-size: .72rem; font-weight: 700; cursor: pointer; }.submit-button { color: #fff; background: linear-gradient(100deg, #3e70da, #5c82e4); box-shadow: 0 7px 16px rgb(59 105 218 / 17%); }.submit-button span { margin-left: .3rem; font-size: 1rem; font-weight: 400; }.secondary-button { color: #68788e; background: #f1f4f8; }.submit-button:disabled { cursor: wait; opacity: .6; }.alert { display: flex; align-items: center; gap: .5rem; padding: .7rem .8rem; border: 1px solid #ffd7db; border-radius: .5rem; color: #b33e4b; background: #fff3f4; font-size: .75rem; }.alert span { display: grid; width: 1.1rem; height: 1.1rem; place-items: center; border-radius: 50%; color: #fff; background: #e36c76; font-weight: 800; }
@media (max-width: 600px) { .form-actions { flex-direction: column-reverse; }.form-actions button { width: 100%; } }
</style>
