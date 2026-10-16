<script setup>
import { onMounted } from "vue";
import { RouterLink, useRouter } from "vue-router";
import GrievanceForm from "@/components/grievance/GrievanceFormV2.vue";
import { useTaxonomyStore } from "@/stores/taxonomyStore";
import { useGrievanceStore } from "@/stores/grievanceStore";
const router = useRouter(); const taxonomy = useTaxonomyStore(); const store = useGrievanceStore();
onMounted(() => { if (!taxonomy.departments.length || !taxonomy.sectors.length) taxonomy.fetchTaxonomies(); });
async function submit(payload) { try { await store.createGrievance(payload); router.push({ name: "admin-grievances" }); } catch {} }
</script>
<template><section class="form-page"><RouterLink :to="{ name: 'admin-grievances' }" class="back-link">← Retour aux doléances</RouterLink><div class="page-intro"><p class="eyebrow">Administration / Doléances</p><h2>Créer une doléance</h2><p>Enregistrez une demande et suivez son traitement de bout en bout.</p></div><GrievanceForm :departments="taxonomy.departments" :sectors="taxonomy.sectors" :loading="store.saving" :error="store.error" @submit="submit" @cancel="router.push({ name: 'admin-grievances' })" /></section></template>
<style scoped>.form-page { max-width: 980px; margin: 0 auto; }.back-link { display: inline-block; margin-bottom: 1.6rem; color: #7186ae; font-size: .72rem; font-weight: 700; text-decoration: none; }.eyebrow { margin: 0 0 .55rem; color: #6d91e4; font-size: .68rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }.page-intro h2 { margin: 0; color: #1c2d46; font-size: 2rem; letter-spacing: -.06em; }.page-intro p:last-child { margin: .6rem 0 1.6rem; color: #8b99ab; font-size: .8rem; }</style>
