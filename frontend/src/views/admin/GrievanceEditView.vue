<script setup>
import { onMounted } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import GrievanceForm from "@/components/grievance/GrievanceFormV2.vue";
import { useTaxonomyStore } from "@/stores/taxonomyStore";
import { useGrievanceStore } from "@/stores/grievanceStore";
const route = useRoute(); const router = useRouter(); const taxonomy = useTaxonomyStore(); const store = useGrievanceStore();
onMounted(async () => { await Promise.all([store.fetchGrievance(route.params.id), (!taxonomy.departments.length || !taxonomy.sectors.length) ? taxonomy.fetchTaxonomies() : Promise.resolve()]); });
async function submit(payload) { try { await store.updateGrievance(route.params.id, payload); router.push({ name: "admin-grievance-details", params: { id: route.params.id } }); } catch {} }
</script>
<template><section class="form-page"><RouterLink :to="{ name: 'admin-grievance-details', params: { id: route.params.id } }" class="back-link">← Retour à la doléance</RouterLink><div v-if="store.loading && !store.current" class="loading">Chargement…</div><template v-else><div class="page-intro"><p class="eyebrow">Administration / Doléances</p><h2>Modifier une doléance</h2><p>Actualisez les informations et conservez l’historique des changements.</p></div><GrievanceForm :grievance="store.current" :departments="taxonomy.departments" :sectors="taxonomy.sectors" :loading="store.saving" :error="store.error" @submit="submit" @cancel="router.push({ name: 'admin-grievance-details', params: { id: route.params.id } })" /></template></section></template>
<style scoped>.form-page { max-width: 980px; margin: 0 auto; }.back-link { display: inline-block; margin-bottom: 1.6rem; color: #7186ae; font-size: .72rem; font-weight: 700; text-decoration: none; }.eyebrow { margin: 0 0 .55rem; color: #6d91e4; font-size: .68rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }.page-intro h2 { margin: 0; color: #1c2d46; font-size: 2rem; letter-spacing: -.06em; }.page-intro p:last-child { margin: .6rem 0 1.6rem; color: #8b99ab; font-size: .8rem; }.loading { color: #98a5b5; padding: 2rem 0; }</style>
