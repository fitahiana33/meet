<script setup>
import { onBeforeUnmount, onMounted, ref } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { getDeclarantPhoto } from "@/services/api/grievanceApi";
import { useGrievanceStore } from "@/stores/grievanceStore";

const route = useRoute();
const router = useRouter();
const store = useGrievanceStore();
const photoUrls = ref({});
const pageError = ref("");
const statuses = { submitted: "Soumise", rejected: "Rejetée", in_progress: "En cours", processed: "Traitée" };
const statusLabel = (value) => statuses[value] || value;
const date = (value) => value ? new Intl.DateTimeFormat("fr-FR", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value)) : "—";
const photoObjects = new Set();
function exportPdf() { window.print(); }

onMounted(async () => {
  try {
    const grievance = await store.fetchGrievance(route.params.id);
    await Promise.all((grievance.declarants || []).filter((person) => person.has_photo).map(async (person) => {
      try {
        const response = await getDeclarantPhoto(person.id);
        const url = URL.createObjectURL(response.data);
        photoObjects.add(url);
        photoUrls.value[person.id] = url;
      } catch {
        pageError.value = "Une ou plusieurs photos de déclarant n’ont pas pu être chargées.";
      }
    }));
  } catch {
    pageError.value = store.error || "Impossible de charger cette doléance.";
  }
});

onBeforeUnmount(() => photoObjects.forEach((url) => URL.revokeObjectURL(url)));

async function removeDocument(document) {
  if (!window.confirm(`Supprimer le document « ${document.name} » ?`)) return;
  try {
    await store.removeDocument(route.params.id, document.id);
  } catch {
    pageError.value = store.error || "Impossible de supprimer ce document.";
  }
}

async function remove() {
  if (!window.confirm("Supprimer définitivement cette doléance ?")) return;
  try {
    await store.removeGrievance(route.params.id);
    await router.push({ name: "admin-grievances" });
  } catch {
    pageError.value = store.error || "Impossible de supprimer cette doléance.";
  }
}
</script>

<template>
  <section class="detail-page print-sheet">
    <RouterLink :to="{ name: 'admin-grievances' }" class="back-link no-print">← Retour aux doléances</RouterLink>
    <p v-if="pageError" class="alert no-print" role="alert">{{ pageError }}</p>
    <div v-if="store.loading && !store.current" class="state">Chargement…</div>
    <template v-else-if="store.current">
      <header class="detail-heading">
        <div>
          <p class="eyebrow">Doléance / #{{ store.current.id }}</p>
          <h2>{{ store.current.title }}</h2>
          <p class="description">Suivi complet de la demande et de ses changements.</p>
        </div>
        <div class="heading-actions no-print">
          <button class="print-button" type="button" @click="exportPdf">Exporter en PDF</button>
          <RouterLink :to="{ name: 'admin-grievance-edit', params: { id: store.current.id } }" class="edit-button">Modifier</RouterLink>
          <button class="delete-button" type="button" @click="remove">Supprimer</button>
        </div>
      </header>

      <div class="summary-grid">
        <article><small>État actuel</small><strong class="status-pill" :class="store.current.status">{{ statusLabel(store.current.status) }}</strong></article>
        <article><small>Secteur</small><strong>{{ store.current.sector?.name || "—" }}</strong></article>
        <article><small>Entité</small><strong>{{ store.current.entity?.title || "—" }}</strong></article>
        <article><small>Attribution</small><strong>{{ store.current.department?.name || "Non attribuée" }}</strong></article>
        <article><small>Échéance</small><strong>{{ date(store.current.due_at) }}</strong></article>
      </div>

      <div class="detail-grid">
        <article class="detail-card description-card">
          <h3>Description</h3><p>{{ store.current.description }}</p>
          <h3 class="subheading">Dates</h3>
          <dl><div><dt>Date de la doléance</dt><dd>{{ date(store.current.complained_at) }}</dd></div><div><dt>Date d’échéance</dt><dd>{{ date(store.current.due_at) }}</dd></div></dl>
          <template v-if="store.current.entity">
            <h3 class="subheading">Adresse de l’entité</h3>
            <p>{{ store.current.entity.address || "Adresse non renseignée" }}</p>
            <p v-if="store.current.entity.latitude && store.current.entity.longitude" class="coordinates">Coordonnées : {{ store.current.entity.latitude }}, {{ store.current.entity.longitude }}</p>
          </template>
        </article>

        <article class="detail-card">
          <h3>Déclarants</h3>
          <p v-if="!store.current.declarants?.length" class="muted">Aucun déclarant associé.</p>
          <ul v-else class="declarants">
            <li v-for="person in store.current.declarants" :key="person.id">
              <img v-if="photoUrls[person.id]" :src="photoUrls[person.id]" alt="" class="person-photo" />
              <span v-else class="person-photo fallback">{{ person.first_name.slice(0, 1).toUpperCase() }}</span>
              <div><strong>{{ person.name }}</strong><small>{{ person.email || "E-mail non renseigné" }}</small><small>{{ person.phone || "Téléphone non renseigné" }} · {{ person.address || "Adresse non renseignée" }}</small></div>
            </li>
          </ul>
        </article>

        <article class="detail-card">
          <h3>Documents</h3>
          <p v-if="!store.current.documents?.length" class="muted">Aucun document joint.</p>
          <ul v-else class="documents"><li v-for="document in store.current.documents" :key="document.id"><a :href="document.url" target="_blank" rel="noreferrer">{{ document.name }}</a><button class="no-print" type="button" @click="removeDocument(document)">×</button></li></ul>
        </article>
        <article class="detail-card">
          <h3>Historique des statuts</h3>
          <ul class="timeline"><li v-for="entry in store.current.status_history" :key="entry.id"><span class="timeline-dot" /><div><strong>{{ statusLabel(entry.status) }}</strong><small>{{ date(entry.changed_at) }} · {{ entry.changed_by?.name || "Utilisateur supprimé" }}</small></div></li></ul>
        </article>
        <article class="detail-card">
          <h3>Historique des attributions</h3>
          <ul class="timeline"><li v-for="entry in store.current.assignment_history" :key="entry.id"><span class="timeline-dot purple" /><div><strong>{{ entry.department?.name || "Non attribuée" }}</strong><small>{{ date(entry.changed_at) }} · {{ entry.changed_by?.name || "Utilisateur supprimé" }}</small></div></li></ul>
        </article>
      </div>
    </template>
    <p v-else class="state">Cette doléance est introuvable.</p>
  </section>
</template>

<style scoped>
.detail-page { max-width: 1180px; margin: 0 auto; }
.back-link { display: inline-block; margin-bottom: 1rem; color: #7186ae; font-size: .7rem; font-weight: 700; text-decoration: none; }
.alert { margin-bottom: .8rem; padding: .7rem; border: 1px solid #ffd7db; border-radius: .5rem; color: #b33e4b; background: #fff3f4; font-size: .72rem; }
.detail-heading { display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; }
.eyebrow { margin: 0 0 .45rem; color: #6d91e4; font-size: .66rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
.detail-heading h2 { margin: 0; color: #1c2d46; font-size: clamp(1.45rem,3vw,1.9rem); letter-spacing: -.05em; }
.description { margin: .4rem 0 0; color: #8b99ab; font-size: .76rem; }
.heading-actions { display: flex; gap: .45rem; }
.print-button,.edit-button,.delete-button { padding: .62rem .75rem; border: 0; border-radius: .48rem; font-size: .67rem; font-weight: 700; text-decoration: none; cursor: pointer; white-space: nowrap; }
.print-button,.edit-button { color: #fff; background: #4776da; }.delete-button { color: #bd5a66; background: #fff0f1; }
.summary-grid { display: grid; grid-template-columns: repeat(5,minmax(0,1fr)); gap: .6rem; margin: 1.2rem 0 .8rem; }
.summary-grid article,.detail-card { min-width: 0; padding: .85rem; border: 1px solid #e8eef6; border-radius: .7rem; background: #fff; box-shadow: 0 5px 15px rgb(30 52 84 / 3%); }
.summary-grid small { display: block; margin-bottom: .35rem; color: #a1adbc; font-size: .59rem; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; }
.summary-grid strong:not(.status-pill) { display: block; overflow: hidden; color: #43556e; font-size: .7rem; text-overflow: ellipsis; white-space: nowrap; }
.status-pill { display: inline-block; padding: .28rem .4rem; border-radius: 999px; font-size: .59rem; }.status-pill.submitted { color: #4778cf; background: #eaf1ff; }.status-pill.rejected { color: #bd5a66; background: #fff0f1; }.status-pill.in_progress { color: #ac7a1b; background: #fff7df; }.status-pill.processed { color: #2f9b75; background: #e7f8ef; }
.detail-grid { display: grid; grid-template-columns: repeat(2,minmax(0,1fr)); gap: .75rem; }.detail-card h3 { margin: 0; color: #253650; font-size: .82rem; }
.description-card p { margin: .7rem 0 0; color: #68788e; font-size: .73rem; line-height: 1.6; white-space: pre-line; }.subheading { margin-top: 1.1rem !important; }.detail-card dl { display: grid; gap: .55rem; margin: .7rem 0 0; }.detail-card dl div { display: flex; justify-content: space-between; gap: .8rem; }.detail-card dt,.detail-card dd { margin: 0; font-size: .67rem; }.detail-card dt { color: #9aa7b7; }.detail-card dd { color: #53647c; font-weight: 700; text-align: right; }.coordinates { font-size: .67rem !important; }
.muted { color: #9aa7b7; font-size: .7rem; }.documents,.timeline,.declarants { display: grid; gap: .5rem; margin: .75rem 0 0; padding: 0; list-style: none; }.documents li { display: flex; justify-content: space-between; gap: .5rem; align-items: center; padding: .5rem .6rem; border-radius: .45rem; background: #f7f9fc; }.documents a { overflow: hidden; color: #4776da; font-size: .68rem; font-weight: 700; text-overflow: ellipsis; white-space: nowrap; }.documents button { border: 0; color: #bd5a66; background: none; font-size: 1rem; cursor: pointer; }
.declarants li { display: flex; align-items: center; gap: .55rem; padding: .45rem; border-radius: .5rem; background: #f8faff; }.person-photo { width: 2rem; height: 2rem; flex: 0 0 auto; border-radius: 50%; object-fit: cover; }.person-photo.fallback { display: grid; place-items: center; color: #4776da; background: #eaf1ff; font-size: .75rem; font-weight: 800; }.declarants strong,.declarants small { display: block; }.declarants strong { color: #43556e; font-size: .68rem; }.declarants small { margin-top: .1rem; color: #98a5b5; font-size: .6rem; }
.timeline li { display: flex; gap: .6rem; align-items: flex-start; }.timeline-dot { width: .48rem; height: .48rem; flex: 0 0 auto; margin-top: .22rem; border-radius: 50%; background: #4776da; box-shadow: 0 0 0 4px #eaf1ff; }.timeline-dot.purple { background: #8a68cc; box-shadow: 0 0 0 4px #f1ebff; }.timeline strong,.timeline small { display: block; }.timeline strong { color: #53647c; font-size: .69rem; }.timeline small { margin-top: .15rem; color: #9aa7b7; font-size: .61rem; }
.state { padding: 2rem 1rem; color: #9aa7b7; font-size: .76rem; text-align: center; }
@media(max-width:800px){.summary-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.detail-grid{grid-template-columns:1fr}}
@media(max-width:560px){.detail-heading{display:block}.heading-actions{flex-wrap:wrap;margin-top:.8rem}.summary-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media print {
  @page { size: A4; margin: 14mm; }
  :global(body *),:global(body *::before),:global(body *::after) { visibility: hidden !important; }
  .print-sheet,.print-sheet * { visibility: visible !important; }
  .print-sheet { position: absolute !important; inset: 0 !important; width: 100% !important; max-width: none !important; margin: 0 !important; padding: 0 !important; background: #fff !important; }
  .no-print { display: none !important; }
  .summary-grid article,.detail-card { break-inside: avoid; box-shadow: none !important; }
}
</style>
