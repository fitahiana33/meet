<script setup>
import { onMounted, reactive, ref } from "vue";
import { useTaxonomyStore } from "@/stores/taxonomyStore";

const store = useTaxonomyStore();
const open = ref(false);
const editingId = ref(null);
const localError = ref("");
const form = reactive({ title: "", address: "", latitude: "", longitude: "", description: "" });

function resetForm(entity = null) {
  editingId.value = entity?.id ?? null;
  form.title = entity?.title ?? "";
  form.address = entity?.address ?? "";
  form.latitude = entity?.latitude ?? "";
  form.longitude = entity?.longitude ?? "";
  form.description = entity?.description ?? "";
  localError.value = "";
  open.value = true;
}

async function save() {
  localError.value = "";
  try {
    await store.save("entity", editingId.value, {
      ...form,
      latitude: form.latitude === "" ? null : Number(form.latitude),
      longitude: form.longitude === "" ? null : Number(form.longitude),
    });
    open.value = false;
  } catch {
    localError.value = store.error || "Impossible d’enregistrer cette entité.";
  }
}

async function remove(entity) {
  if (!window.confirm(`Supprimer l’entité « ${entity.title} » ?`)) return;
  localError.value = "";
  try {
    await store.remove("entity", entity.id);
  } catch {
    localError.value = store.error || "Impossible de supprimer cette entité.";
  }
}

onMounted(async () => {
  if (!store.entities.length) {
    try {
      await store.fetchTaxonomies();
    } catch {
      localError.value = store.error || "Impossible de charger les entités.";
    }
  }
});
</script>

<template>
  <section class="entities-page">
    <header class="toolbar">
      <div>
        <p class="eyebrow">Référentiels</p>
        <h2>Entités</h2>
        <p class="description">Gérez les lieux ou organismes liés aux doléances.</p>
      </div>
      <button class="primary-button" type="button" @click="resetForm()">＋ Nouvelle entité</button>
    </header>

    <p v-if="localError || store.error" class="alert" role="alert">{{ localError || store.error }}</p>

    <section class="list-card">
      <p v-if="store.loading && !store.entities.length" class="state">Chargement des entités…</p>
      <p v-else-if="!store.entities.length" class="state">Aucune entité enregistrée.</p>
      <div v-else class="entity-list">
        <article v-for="entity in store.entities" :key="entity.id" class="entity-row">
          <div class="entity-main">
            <strong>{{ entity.title }}</strong>
            <span>{{ entity.address || "Adresse non renseignée" }}</span>
            <small v-if="entity.latitude !== null && entity.longitude !== null">
              {{ entity.latitude }}, {{ entity.longitude }}
            </small>
          </div>
          <span class="count">{{ entity.grievances_count }} doléance{{ entity.grievances_count === 1 ? "" : "s" }}</span>
          <div class="actions">
            <button type="button" @click="resetForm(entity)">Modifier</button>
            <button class="danger" type="button" @click="remove(entity)">Supprimer</button>
          </div>
        </article>
      </div>
    </section>

    <div v-if="open" class="backdrop" @click.self="open = false">
      <section class="dialog" role="dialog" aria-modal="true" :aria-label="editingId ? 'Modifier une entité' : 'Créer une entité'">
        <header class="dialog-heading">
          <div><p class="eyebrow">Référentiel</p><h3>{{ editingId ? "Modifier l’entité" : "Nouvelle entité" }}</h3></div>
          <button class="close" type="button" aria-label="Fermer" @click="open = false">×</button>
        </header>
        <p v-if="localError" class="alert" role="alert">{{ localError }}</p>
        <form class="form-grid" @submit.prevent="save">
          <label class="wide">Titre<input v-model.trim="form.title" maxlength="180" required /></label>
          <label class="wide">Adresse<input v-model.trim="form.address" maxlength="255" /></label>
          <label>Latitude<input v-model="form.latitude" type="number" min="-90" max="90" step="any" /></label>
          <label>Longitude<input v-model="form.longitude" type="number" min="-180" max="180" step="any" /></label>
          <label class="wide">Description<textarea v-model.trim="form.description" rows="3" maxlength="10000" /></label>
          <div class="form-actions wide">
            <button class="secondary-button" type="button" @click="open = false">Annuler</button>
            <button class="primary-button" type="submit" :disabled="store.saving">{{ store.saving ? "Enregistrement…" : "Enregistrer" }}</button>
          </div>
        </form>
      </section>
    </div>
  </section>
</template>

<style scoped>
.entities-page { max-width: 1180px; margin: 0 auto; }
.toolbar { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }
.eyebrow { margin: 0 0 .45rem; color: #6d91e4; font-size: .67rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
h2 { margin: 0; color: #1c2d46; font-size: 1.8rem; letter-spacing: -.05em; }
.description { margin: .4rem 0 0; color: #8998ab; font-size: .78rem; }
.primary-button,.secondary-button { display: inline-flex; align-items: center; justify-content: center; padding: .68rem .9rem; border: 0; border-radius: .5rem; font-size: .72rem; font-weight: 700; text-decoration: none; cursor: pointer; }
.primary-button { color: white; background: #4776da; }
.primary-button:disabled { opacity: .6; cursor: wait; }
.secondary-button { color: #68788e; background: #f1f4f8; }
.alert { margin: 1rem 0; padding: .7rem .8rem; border: 1px solid #ffd7db; border-radius: .5rem; color: #b33e4b; background: #fff3f4; font-size: .75rem; }
.list-card { margin-top: 1.2rem; padding: 1rem; border: 1px solid #e8eef6; border-radius: .8rem; background: white; box-shadow: 0 5px 15px rgb(30 52 84 / 3%); }
.entity-list { display: grid; gap: .55rem; }
.entity-row { display: grid; grid-template-columns: minmax(0,1fr) auto auto; align-items: center; gap: 1rem; padding: .8rem; border: 1px solid #edf1f6; border-radius: .6rem; }
.entity-main strong,.entity-main span,.entity-main small { display: block; }
.entity-main strong { color: #2d405d; font-size: .78rem; }
.entity-main span,.entity-main small { margin-top: .2rem; color: #8998ab; font-size: .67rem; }
.count { color: #7183a0; font-size: .67rem; }
.actions { display: flex; gap: .35rem; }
.actions button { padding: .4rem .55rem; border: 0; border-radius: .4rem; color: #4776da; background: #eff4ff; font-size: .65rem; font-weight: 700; cursor: pointer; }
.actions button.danger { color: #bd5a66; background: #fff0f1; }
.state { padding: 2.5rem 1rem; color: #9aa7b7; font-size: .76rem; text-align: center; }
.backdrop { position: fixed; z-index: 100; inset: 0; display: grid; place-items: center; padding: 1rem; background: rgb(22 35 56 / 48%); }
.dialog { width: min(100%, 600px); max-height: calc(100vh - 2rem); overflow: auto; padding: 1.25rem; border: 1px solid #e8eef6; border-radius: .85rem; background: #fff; box-shadow: 0 24px 70px rgb(24 43 72 / 20%); }
.dialog-heading { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem; }
.dialog-heading h3 { margin: 0; color: #253650; font-size: 1rem; }
.close { border: 0; color: #73839a; background: transparent; font-size: 1.4rem; cursor: pointer; }
.form-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: .8rem; }
.form-grid label { display: grid; gap: .35rem; color: #53647c; font-size: .7rem; font-weight: 700; }
.form-grid .wide { grid-column: 1 / -1; }
.form-grid input,.form-grid textarea { width: 100%; padding: .65rem .7rem; border: 1px solid #dce4ef; border-radius: .45rem; color: #30415a; font: inherit; font-size: .75rem; }
.form-grid textarea { resize: vertical; }
.form-actions { display: flex; justify-content: flex-end; gap: .5rem; padding-top: .2rem; }
@media (max-width: 620px) { .toolbar { display: grid; }.entity-row { grid-template-columns: minmax(0,1fr) auto; }.count { grid-column: 1; }.actions { grid-column: 2; grid-row: 1 / span 2; flex-direction: column; }.form-grid { grid-template-columns: 1fr; }.form-grid .wide { grid-column: auto; } }
</style>
