<script setup>
import { onMounted } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import GrievanceForm from "@/components/grievance/GrievanceFormV2.vue";
import { useTaxonomyStore } from "@/stores/taxonomyStore";
import { useGrievanceStore } from "@/stores/grievanceStore";

const route = useRoute();
const router = useRouter();
const taxonomy = useTaxonomyStore();
const store = useGrievanceStore();

onMounted(async () => {
  await Promise.all([
    store.fetchGrievance(route.params.id).catch(() => null),
    (!taxonomy.departments.length || !taxonomy.sectors.length || !taxonomy.entities.length)
      ? taxonomy.fetchTaxonomies().catch(() => null)
      : Promise.resolve(),
  ]);
});

async function submit(payload) {
  try {
    await store.updateGrievance(route.params.id, payload);
    router.push({ name: "admin-grievance-details", params: { id: route.params.id } });
  } catch {
    // The store exposes the server error beside the form.
  }
}
</script>

<template>
  <section class="form-page">
    <RouterLink :to="{ name: 'admin-grievance-details', params: { id: route.params.id } }" class="back-link">← Retour à la doléance</RouterLink>
    <div v-if="store.loading && !store.current" class="loading">Chargement…</div>
    <template v-else>
      <div class="page-intro"><p class="eyebrow">Administration / Doléances</p><h2>Modifier une doléance</h2><p>Actualisez les informations et conservez l’historique des changements.</p></div>
      <p v-if="taxonomy.error" class="load-error" role="alert">{{ taxonomy.error }}</p>
      <GrievanceForm
        v-if="store.current"
        :grievance="store.current"
        :departments="taxonomy.departments"
        :sectors="taxonomy.sectors"
        :entities="taxonomy.entities"
        :loading="store.saving"
        :error="store.error"
        @submit="submit"
        @cancel="router.push({ name: 'admin-grievance-details', params: { id: route.params.id } })"
      />
      <p v-else-if="store.error" class="load-error" role="alert">{{ store.error }}</p>
    </template>
  </section>
</template>

<style scoped>
.form-page { max-width: 1100px; margin: 0 auto; }
.back-link { display: inline-block; margin-bottom: 1rem; color: #7186ae; font-size: .7rem; font-weight: 700; text-decoration: none; }
.eyebrow { margin: 0 0 .45rem; color: #6d91e4; font-size: .67rem; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; }
.page-intro h2 { margin: 0; color: #1c2d46; font-size: 1.8rem; letter-spacing: -.05em; }
.page-intro p:last-child { margin: .4rem 0 1rem; color: #8b99ab; font-size: .76rem; }
.loading { padding: 1.5rem 0; color: #98a5b5; font-size: .75rem; }
.load-error { padding: .7rem; border: 1px solid #ffd7db; border-radius: .5rem; color: #b33e4b; background: #fff3f4; font-size: .73rem; }
</style>
