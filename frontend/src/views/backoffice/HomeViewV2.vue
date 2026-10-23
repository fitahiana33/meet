<script setup>
import { computed, onMounted, reactive, ref } from "vue";
import { RouterLink } from "vue-router";
import { useTaxonomyStore } from "@/stores/taxonomyStore";
import { getGrievanceDashboard } from "@/services/api/grievanceApi";
import GrievanceMap from "@/components/grievance/GrievanceMap.vue";

const taxonomy = useTaxonomyStore();
const filters = reactive({ search: "", status: "", sector_id: "", entity_id: "", department_id: "", date_from: "", date_to: "" });
const stats = ref(null);
const loading = ref(false);
const error = ref("");
const activeTab = ref("statistics");
const statuses = [
  { value: "submitted", label: "Soumises", color: "#4c7ce2" },
  { value: "in_progress", label: "En cours", color: "#d5a43d" },
  { value: "processed", label: "Traitées", color: "#32a47b" },
  { value: "rejected", label: "Rejetées", color: "#c66772" },
];
const maxMonth = computed(() => Math.max(1, ...(stats.value?.monthly || []).map((item) => item.count)));
const maxSector = computed(() => Math.max(1, ...(stats.value?.by_sector || []).map((item) => item.count)));

async function loadDashboard() {
  loading.value = true;
  error.value = "";
  try {
    const response = await getGrievanceDashboard({ ...filters });
    stats.value = response.data.data;
  } catch (requestError) {
    error.value = requestError.response?.data?.message
      || Object.values(requestError.response?.data?.errors || {}).flat()[0]
      || "Impossible de charger les statistiques des doléances.";
  } finally {
    loading.value = false;
  }
}

function resetFilters() {
  Object.keys(filters).forEach((key) => { filters[key] = ""; });
  loadDashboard();
}

onMounted(() => {
  loadDashboard();
  if (!taxonomy.sectors.length || !taxonomy.entities.length || !taxonomy.departments.length) {
    taxonomy.fetchTaxonomies().catch(() => {
      error.value = taxonomy.error || "Impossible de charger les filtres du tableau de bord.";
    });
  }
});
</script>

<template>
  <section class="dashboard-page">
    <header class="dashboard-intro">
      <div>
        <p class="eyebrow">Suivi des doléances</p>
        <h2>Tableau de bord</h2>
        <p>Visualisez les demandes et leur avancement selon vos critères.</p>
      </div>
      <RouterLink class="create-link" :to="{ name: 'admin-grievance-create' }">＋ Nouvelle doléance</RouterLink>
    </header>

    <form class="filters" @submit.prevent="loadDashboard">
      <input v-model.trim="filters.search" placeholder="Titre, description ou déclarant" aria-label="Recherche" />
      <select v-model="filters.status" aria-label="Statut">
        <option value="">Tous les statuts</option>
        <option v-for="status in statuses" :key="status.value" :value="status.value">{{ status.label }}</option>
      </select>
      <select v-model="filters.sector_id" aria-label="Secteur">
        <option value="">Tous les secteurs</option>
        <option v-for="item in taxonomy.sectors" :key="item.id" :value="item.id">{{ item.name }}</option>
      </select>
      <select v-model="filters.entity_id" aria-label="Entité">
        <option value="">Toutes les entités</option>
        <option v-for="item in taxonomy.entities" :key="item.id" :value="item.id">{{ item.title }}</option>
      </select>
      <select v-model="filters.department_id" aria-label="Attribution">
        <option value="">Toutes les attributions</option>
        <option v-for="item in taxonomy.departments" :key="item.id" :value="item.id">{{ item.name }}</option>
      </select>
      <label>Du <input v-model="filters.date_from" type="date" /></label>
      <label>Au <input v-model="filters.date_to" type="date" /></label>
      <div class="filter-actions"><button class="apply" type="submit">Appliquer</button><button class="reset" type="button" @click="resetFilters">Réinitialiser</button></div>
    </form>

    <p v-if="error" class="alert" role="alert">{{ error }}</p>
    <p v-if="loading" class="loading">Mise à jour des indicateurs…</p>

    <div class="dashboard-tabs" role="tablist" aria-label="Sections du tableau de bord">
      <button id="statistics-tab" type="button" role="tab" :aria-selected="activeTab === 'statistics'" aria-controls="statistics-panel" :class="{ active: activeTab === 'statistics' }" @click="activeTab = 'statistics'">Statistiques</button>
      <button id="locations-tab" type="button" role="tab" :aria-selected="activeTab === 'locations'" aria-controls="locations-panel" :class="{ active: activeTab === 'locations' }" @click="activeTab = 'locations'">Localisations</button>
    </div>

    <section v-if="stats && activeTab === 'statistics'" id="statistics-panel" role="tabpanel" aria-labelledby="statistics-tab">
      <div class="metric-grid">
        <article class="metric-card blue"><span>Total filtré</span><strong>{{ stats.total }}</strong><small>Doléance{{ stats.total === 1 ? "" : "s" }} trouvée{{ stats.total === 1 ? "" : "s" }}</small></article>
        <article class="metric-card red"><span>En retard</span><strong>{{ stats.overdue }}</strong><small>Échéance dépassée</small></article>
        <article class="metric-card green"><span>Traitées</span><strong>{{ stats.processed }}</strong><small>Demandes clôturées</small></article>
        <article class="metric-card amber"><span>En cours</span><strong>{{ stats.in_progress }}</strong><small>Demandes en traitement</small></article>
      </div>

      <div class="charts-grid">
        <article class="chart-card">
          <div class="chart-heading"><div><h3>Évolution mensuelle</h3><p>Doléances déposées sur les six derniers mois</p></div></div>
          <div class="monthly-chart" role="img" aria-label="Histogramme des doléances par mois">
            <div v-for="item in stats.monthly" :key="item.month" class="month-column">
              <strong>{{ item.count }}</strong>
              <div class="bar-track"><span :style="{ height: `${Math.max(item.count ? 8 : 2, item.count / maxMonth * 100)}%` }" /></div>
              <small>{{ item.label }}</small>
            </div>
          </div>
        </article>

        <article class="chart-card">
          <div class="chart-heading"><div><h3>Répartition par statut</h3><p>Résultats correspondant aux filtres</p></div></div>
          <ul class="status-chart">
            <li v-for="status in statuses" :key="status.value">
              <div class="status-label"><span :style="{ background: status.color }" />{{ status.label }}<strong>{{ stats.by_status[status.value] || 0 }}</strong></div>
              <div class="status-track"><span :style="{ width: `${stats.total ? stats.by_status[status.value] / stats.total * 100 : 0}%`, background: status.color }" /></div>
            </li>
          </ul>
        </article>

        <article class="chart-card sector-card">
          <div class="chart-heading"><div><h3>Principaux secteurs</h3><p>Répartition des doléances filtrées</p></div></div>
          <p v-if="!stats.by_sector.length" class="empty-chart">Aucune donnée à afficher.</p>
          <ul v-else class="sector-chart">
            <li v-for="sector in stats.by_sector" :key="sector.name">
              <div class="status-label"><span class="sector-dot" />{{ sector.name }}<strong>{{ sector.count }}</strong></div>
              <div class="status-track"><span :style="{ width: `${sector.count / maxSector * 100}%` }" /></div>
            </li>
          </ul>
        </article>
      </div>
    </section>

    <section v-if="stats && activeTab === 'locations'" id="locations-panel" role="tabpanel" aria-labelledby="locations-tab">
      <article class="chart-card map-card">
        <div class="chart-heading"><div><h3>Carte des doléances</h3><p>Localisation des demandes selon les coordonnées de leur entité. Les mêmes filtres s’appliquent.</p></div></div>
        <GrievanceMap :markers="stats.map_markers || []" :loading="loading" />
      </article>
    </section>
  </section>
</template>

<style scoped>
.dashboard-page { max-width: 1220px; margin: 0 auto; }
.dashboard-intro { display: flex; justify-content: space-between; align-items: flex-end; gap: 1rem; }
.eyebrow { margin: 0 0 .45rem; color: #6d91e4; font-size: .67rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
.dashboard-intro h2 { margin: 0; color: #1c2d46; font-size: clamp(1.55rem,3vw,2rem); letter-spacing: -.05em; }
.dashboard-intro p:last-child { margin: .45rem 0 0; color: #8998ab; font-size: .76rem; }
.create-link { padding: .68rem .85rem; border-radius: .5rem; color: #fff; background: #4776da; font-size: .7rem; font-weight: 700; text-decoration: none; white-space: nowrap; }
.dashboard-tabs { display: flex; gap: .3rem; margin-top: 1.25rem; padding: .25rem; border: 1px solid #e8eef6; border-radius: .65rem; background: #f5f7fb; }
.dashboard-tabs button { padding: .58rem .85rem; border: 0; border-radius: .45rem; color: #78879b; background: transparent; font-size: .7rem; font-weight: 700; cursor: pointer; }
.dashboard-tabs button.active { color: #315fc5; background: #fff; box-shadow: 0 2px 7px rgb(30 52 84 / 9%); }
.dashboard-tabs button:focus-visible { outline: 2px solid #4776da; outline-offset: 2px; }
.filters { display: grid; grid-template-columns: minmax(180px,1.5fr) repeat(4,minmax(120px,1fr)); gap: .55rem; margin: 1.25rem 0 .85rem; padding: .75rem; border: 1px solid #e8eef6; border-radius: .7rem; background: #fff; }
.filters input,.filters select { width: 100%; min-width: 0; padding: .58rem .62rem; border: 1px solid #dce4ef; border-radius: .45rem; color: #52637a; background: #fff; font-size: .68rem; }
.filters label { display: flex; align-items: center; gap: .35rem; color: #7d8da3; font-size: .65rem; }
.filter-actions { display: flex; align-items: center; gap: .35rem; }
.filter-actions button { padding: .58rem .65rem; border: 0; border-radius: .45rem; font-size: .66rem; font-weight: 700; cursor: pointer; }
.apply { color: #fff; background: #294fbb; }.reset { color: #66768c; background: #f1f4f8; }
.alert { margin: .7rem 0; padding: .7rem; border: 1px solid #ffd7db; border-radius: .5rem; color: #b33e4b; background: #fff3f4; font-size: .73rem; }
.loading { margin: .45rem 0; color: #7d8da3; font-size: .7rem; }
.metric-grid { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); gap: .7rem; margin-top: .85rem; }
.metric-card { display: grid; gap: .22rem; padding: .9rem 1rem; border: 1px solid #e8eef6; border-top: 3px solid #4c7ce2; border-radius: .7rem; background: #fff; box-shadow: 0 5px 15px rgb(30 52 84 / 3%); }
.metric-card span { color: #8998ab; font-size: .65rem; font-weight: 700; }.metric-card strong { color: #263650; font-size: 1.55rem; letter-spacing: -.04em; }.metric-card small { color: #9aa7b7; font-size: .62rem; }
.metric-card.red { border-top-color: #c66772; }.metric-card.green { border-top-color: #32a47b; }.metric-card.amber { border-top-color: #d5a43d; }
.charts-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: .8rem; margin-top: .8rem; }
.chart-card { min-width: 0; padding: .95rem 1rem; border: 1px solid #e8eef6; border-radius: .75rem; background: #fff; box-shadow: 0 5px 15px rgb(30 52 84 / 3%); }
.map-card { grid-column: 1 / -1; }.map-card :deep(.map-frame) { margin-top: .75rem; }
.chart-heading h3 { margin: 0; color: #253650; font-size: .82rem; }.chart-heading p { margin: .25rem 0 0; color: #9aa7b7; font-size: .64rem; }
.monthly-chart { display: grid; grid-template-columns: repeat(6,minmax(0,1fr)); align-items: end; gap: .65rem; height: 160px; margin-top: .7rem; padding-top: .4rem; }
.month-column { display: grid; grid-template-rows: 1rem 1fr 1rem; justify-items: center; align-items: end; height: 100%; }.month-column strong { color: #63758f; font-size: .62rem; }
.bar-track { display: flex; align-items: flex-end; width: 100%; height: 100%; border-bottom: 1px solid #e7edf5; background: linear-gradient(to bottom,transparent 49%,#f0f3f8 50%,transparent 51%); }.bar-track span { width: min(36px,70%); min-height: 2px; margin: 0 auto; border-radius: .3rem .3rem 0 0; background: linear-gradient(#6a93ed,#4776da); transition: height .2s; }
.month-column small { align-self: end; color: #8998ab; font-size: .62rem; text-transform: capitalize; }
.status-chart,.sector-chart { display: grid; gap: .75rem; margin: 1rem 0 0; padding: 0; list-style: none; }
.status-label { display: flex; align-items: center; gap: .45rem; color: #68788e; font-size: .68rem; }.status-label span { width: .48rem; height: .48rem; flex: 0 0 auto; border-radius: 50%; }.status-label strong { margin-left: auto; color: #2d405d; }
.status-track { height: .38rem; margin-top: .32rem; overflow: hidden; border-radius: 99px; background: #f0f3f8; }.status-track span { display: block; height: 100%; border-radius: inherit; transition: width .2s; }
.sector-card { grid-column: 1 / -1; }.sector-chart { grid-template-columns: repeat(2,minmax(0,1fr)); }.sector-dot { background: #4776da; }.empty-chart { color: #9aa7b7; font-size: .7rem; }
@media(max-width:900px){.filters{grid-template-columns:repeat(3,minmax(0,1fr))}.charts-grid{grid-template-columns:1fr}.sector-card,.map-card{grid-column:auto}}
@media(max-width:620px){.dashboard-intro{display:grid}.filters{grid-template-columns:repeat(2,minmax(0,1fr))}.filters>input:first-child{grid-column:1/-1}.metric-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.sector-chart{grid-template-columns:1fr}}
</style>
