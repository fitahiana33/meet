<script setup>
import { onMounted, reactive, ref } from "vue";
import { RouterLink } from "vue-router";
import { useGrievanceStore } from "@/stores/grievanceStore";
import { useTaxonomyStore } from "@/stores/taxonomyStore";
const store = useGrievanceStore(); const taxonomy = useTaxonomyStore(); const filters = reactive({ search: "", status: "", sector_id: "" }); const historyOpen = ref(false); const historyItem = ref(null); const historyLoading = ref(false);
const statuses = [{ value: "submitted", label: "Soumise" }, { value: "rejected", label: "Rejetée" }, { value: "in_progress", label: "En cours" }, { value: "processed", label: "Traitée" }];
const statusLabel = (value) => statuses.find((status) => status.value === value)?.label || value; const date = (value) => value ? new Intl.DateTimeFormat("fr-FR", { dateStyle: "short", timeStyle: "short" }).format(new Date(value)) : "—"; const overdue = (item) => item.status !== "processed" && new Date(item.due_at) < new Date();
async function load() { try { await store.fetchGrievances({ ...filters, per_page: 30 }); } catch { /* The store exposes the request error in the page. */ } }
onMounted(async () => { await Promise.allSettled([load(), (!taxonomy.sectors.length || !taxonomy.departments.length || !taxonomy.entities.length) ? taxonomy.fetchTaxonomies().catch(() => { store.error = taxonomy.error; }) : Promise.resolve()]); });
async function showHistory(item) { historyOpen.value = true; historyLoading.value = true; try { historyItem.value = await store.fetchGrievance(item.id); } catch { historyItem.value = null; } finally { historyLoading.value = false; } }
async function remove(item) { if (window.confirm(`Supprimer la doléance « ${item.title} » ?`)) { try { await store.removeGrievance(item.id); } catch { /* The store exposes the request error in the page. */ } } }
</script>
<template>
  <section class="content-page">
    <div class="page-toolbar">
      <div><p class="eyebrow">Suivi des demandes</p><h2>Doléances</h2><p class="description">Consultez les demandes, leurs fichiers et leur traçabilité.</p></div>
      <RouterLink class="primary-button" :to="{ name: 'admin-grievance-create' }">＋ Nouvelle doléance</RouterLink>
    </div>
    <div class="filters">
      <input v-model="filters.search" placeholder="Rechercher par référence, titre ou description…" @keyup.enter="load" />
      <select v-model="filters.status" @change="load"><option value="">Tous les statuts</option><option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option></select>
      <select v-model="filters.sector_id" @change="load"><option value="">Tous les secteurs</option><option v-for="sector in taxonomy.sectors" :key="sector.id" :value="sector.id">{{ sector.name }}</option></select>
      <button type="button" @click="load">Filtrer</button>
    </div>
    <div v-if="store.error" class="alert" role="alert">{{ store.error }}</div>
    <div class="list-card">
      <div class="list-head"><div><h3>Liste des doléances</h3><p>Cliquez sur « Détails » pour voir les fichiers joints.</p></div><span>{{ store.grievances.length }} résultat{{ store.grievances.length === 1 ? "" : "s" }}</span></div>
      <p v-if="store.loading" class="state">Chargement…</p>
      <p v-else-if="!store.grievances.length" class="state">Aucune doléance trouvée.</p>
      <div v-else class="grievance-list">
        <article v-for="item in store.grievances" :key="item.id" class="grievance-row" :class="{ overdue: overdue(item) }">
          <div class="main"><span class="mark">{{ item.title.slice(0, 1).toUpperCase() }}</span><div><strong>{{ item.title }}</strong><p class="reference">{{ item.code }}</p><p>{{ item.description }}</p></div></div>
          <div class="field"><small>Secteur</small><b>{{ item.sector?.name || "—" }}</b></div>
          <div class="field"><small>Entité</small><b>{{ item.entity?.title || "—" }}</b></div>
          <div class="field"><small>Attribution</small><b>{{ item.department?.name || "Non attribuée" }}</b></div>
          <div class="field"><small>État</small><em :class="item.status">{{ statusLabel(item.status) }}</em></div>
          <div class="field dates"><small>Dates</small><span>Déposée : {{ date(item.complained_at) }}</span><span>Échéance : {{ date(item.due_at) }}</span></div>
          <div class="actions">
            <RouterLink class="detail-link" :to="{ name: 'admin-grievance-details', params: { id: item.id } }">Détails</RouterLink>
            <button type="button" class="history-link" @click="showHistory(item)">Historique</button>
            <RouterLink :to="{ name: 'admin-grievance-edit', params: { id: item.id } }">Modifier</RouterLink>
            <button type="button" class="danger-link" @click="remove(item)">Supprimer</button>
          </div>
        </article>
      </div>
    </div>
    <div v-if="historyOpen" class="backdrop" @click.self="historyOpen = false">
      <section class="modal" role="dialog" aria-modal="true">
        <button class="close" type="button" aria-label="Fermer" @click="historyOpen = false">×</button>
        <p class="eyebrow">Traçabilité</p><h3>Historique</h3><p class="modal-title">{{ historyItem?.code }} · {{ historyItem?.title }}</p>
        <p v-if="historyLoading" class="state">Chargement…</p>
        <div v-else-if="historyItem" class="history-grid">
          <div><h4>Statuts</h4><ul><li v-for="entry in historyItem.status_history" :key="entry.id"><i /><span><b>{{ statusLabel(entry.status) }}</b><small>{{ date(entry.changed_at) }} · {{ entry.changed_by?.name || "Utilisateur supprimé" }}</small></span></li></ul></div>
          <div><h4>Attributions</h4><ul><li v-for="entry in historyItem.assignment_history" :key="entry.id"><i class="purple" /><span><b>{{ entry.department?.name || "Non attribuée" }}</b><small>{{ date(entry.changed_at) }} · {{ entry.changed_by?.name || "Utilisateur supprimé" }}</small></span></li></ul></div>
        </div>
      </section>
    </div>
  </section>
</template>
<style scoped>.content-page{max-width:1280px;margin:auto}.page-toolbar{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start}.eyebrow{margin:0 0 .55rem;color:#6d91e4;font-size:.68rem;font-weight:800;letter-spacing:.15em;text-transform:uppercase}.page-toolbar h2{margin:0;color:#1c2d46;font-size:2rem;letter-spacing:-.06em}.description{margin:.6rem 0 0;color:#8b99ab;font-size:.8rem}.primary-button{display:inline-flex;padding:.75rem 1rem;border-radius:.55rem;color:#fff;background:#4776da;font-size:.76rem;font-weight:700;text-decoration:none;white-space:nowrap}.filters{display:grid;grid-template-columns:1.6fr .8fr .8fr auto;gap:.6rem;margin:1.7rem 0 1rem}.filters input,.filters select,.filters button{min-width:0;padding:.7rem;border:1px solid #dce4ef;border-radius:.5rem;background:#fff;color:#52637a;font-size:.72rem}.filters button{border:0;color:#fff;background:#294fbb;font-weight:700;cursor:pointer}.alert{margin-bottom:1rem;padding:.75rem;border:1px solid #ffd7db;border-radius:.6rem;color:#b33e4b;background:#fff3f4;font-size:.76rem}.list-card{padding:1.25rem;border:1px solid #e8eef6;border-radius:.9rem;background:#fff;box-shadow:0 5px 15px rgb(30 52 84 / 3%)}.list-head{display:flex;justify-content:space-between;gap:1rem;margin-bottom:1rem}.list-head h3{margin:0;color:#253650;font-size:.9rem}.list-head p{margin:.3rem 0 0;color:#9aa7b7;font-size:.7rem}.list-head>span{padding:.35rem .55rem;border-radius:.4rem;color:#557bd0;background:#eff4ff;font-size:.65rem;white-space:nowrap}.grievance-list{display:grid;gap:.6rem}.grievance-row{display:grid;grid-template-columns:minmax(220px,1.5fr) repeat(3,minmax(100px,.7fr)) minmax(145px,1fr) auto;gap:.75rem;align-items:center;padding:.85rem .65rem;border:1px solid #edf1f6;border-radius:.65rem;background:#fcfdff}.grievance-row.overdue{border-color:#f6d5d8}.main{display:flex;gap:.6rem;min-width:0}.mark{display:grid;width:2rem;height:2rem;flex:0 0 auto;place-items:center;border-radius:.6rem;color:#4e7fe0;background:#eaf1ff;font-weight:800}.main strong{display:block;overflow:hidden;color:#2d405d;font-size:.76rem;text-overflow:ellipsis;white-space:nowrap}.main p{display:-webkit-box;overflow:hidden;margin:.25rem 0 0;color:#8c9aac;font-size:.67rem;line-height:1.35;-webkit-box-orient:vertical;-webkit-line-clamp:2}.field{min-width:0}.field small,.field b,.field span{display:block}.field small{margin-bottom:.25rem;color:#a4afbd;font-size:.59rem;text-transform:uppercase}.field b,.field span{overflow:hidden;color:#62738a;font-size:.68rem;text-overflow:ellipsis;white-space:nowrap}.field em{display:inline-block;padding:.3rem .45rem;border-radius:999px;font-size:.61rem;font-style:normal;font-weight:800}.submitted{color:#4778cf;background:#eaf1ff}.rejected{color:#bd5a66;background:#fff0f1}.in_progress{color:#ac7a1b;background:#fff7df}.processed{color:#2f9b75;background:#e7f8ef}.dates span{margin-top:.15rem;font-size:.62rem}.actions{display:grid;gap:.25rem;text-align:right}.actions a,.actions button{padding:0;border:0;color:#4f7bdd;background:none;font-size:.64rem;font-weight:700;text-decoration:none;cursor:pointer}.actions .detail-link{color:#315fc5}.actions .history-link{color:#6d55bd}.actions .danger-link{color:#c66b74}.state{padding:2.5rem;color:#9aa7b7;font-size:.78rem;text-align:center}.backdrop{position:fixed;z-index:100;inset:0;display:grid;place-items:center;padding:1rem;background:rgb(15 23 42 / 48%)}.modal{position:relative;width:min(720px,100%);max-height:85vh;overflow:auto;padding:1.5rem;border-radius:1rem;background:#fff;box-shadow:0 24px 70px rgb(15 23 42 / 24%)}.close{position:absolute;top:.8rem;right:.9rem;border:0;background:none;color:#7c8aa0;font-size:1.5rem;cursor:pointer}.modal h3{margin:0;color:#253650;font-size:1.1rem}.modal-title{margin:.35rem 0 1.2rem;color:#8b99ab;font-size:.75rem}.history-grid{display:grid;grid-template-columns:1fr 1fr;gap:1rem}.history-grid>div{padding:1rem;border:1px solid #edf1f6;border-radius:.7rem}.history-grid h4{margin:0;color:#53647c;font-size:.78rem}.history-grid ul{display:grid;gap:.7rem;margin:1rem 0 0;padding:0;list-style:none}.history-grid li{display:flex;gap:.6rem}.history-grid i{width:.5rem;height:.5rem;flex:0 0 auto;margin-top:.25rem;border-radius:50%;background:#4776da;box-shadow:0 0 0 4px #eaf1ff}.history-grid i.purple{background:#8a68cc;box-shadow:0 0 0 4px #f1ebff}.history-grid b,.history-grid small{display:block}.history-grid b{color:#53647c;font-size:.72rem}.history-grid small{margin-top:.18rem;color:#9aa7b7;font-size:.64rem}@media(max-width:1050px){.grievance-row{grid-template-columns:minmax(210px,1.4fr) repeat(2,minmax(100px,.7fr)) minmax(90px,.6fr) auto}.dates{grid-column:2/4}.actions{grid-column:4/-1;grid-row:2;display:flex;justify-content:flex-end;gap:.7rem}}@media(max-width:700px){.page-toolbar{display:block}.page-toolbar .primary-button{display:inline-flex;margin-top:1rem}.filters{grid-template-columns:1fr}.list-card{padding:.8rem}.list-head{display:block}.list-head>span{display:inline-block;margin-top:.6rem}.grievance-row{display:block;padding:.9rem}.field{display:flex;justify-content:space-between;gap:1rem;margin-top:.7rem}.dates{display:block}.actions{display:flex;justify-content:flex-start;gap:.75rem;margin-top:.9rem;padding-top:.7rem;border-top:1px solid #edf1f6}.history-grid{grid-template-columns:1fr}}
</style>
<style scoped>
.grievance-row{grid-template-columns:minmax(180px,1.5fr) repeat(4,minmax(75px,.65fr)) minmax(145px,1fr) auto}
.main .reference{display:block;margin:.15rem 0 0;color:#4776da;font-size:.66rem;font-weight:800;letter-spacing:.02em;white-space:nowrap}
@media(max-width:1050px){.grievance-row{grid-template-columns:minmax(190px,1.4fr) repeat(3,minmax(75px,.7fr)) minmax(90px,.6fr) auto}.dates{grid-column:2/5}}
@media(max-width:700px){.grievance-row{display:block}.dates{display:block}}
</style>
