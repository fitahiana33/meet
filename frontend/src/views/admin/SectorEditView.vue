<script setup>
import { computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import ReferenceForm from "@/components/admin/ReferenceForm.vue";
import { useTaxonomyStore } from "@/stores/taxonomyStore";
const route = useRoute(); const router = useRouter(); const store = useTaxonomyStore();
const item = computed(() => store.sectors.find((entry) => String(entry.id) === String(route.params.id)) || null);
onMounted(() => { if (!store.sectors.length) store.fetchTaxonomies(); });
async function submit(payload) { try { await store.save("sector", route.params.id, payload); router.push({ name: "admin-sectors" }); } catch {} }
</script>
<template><ReferenceForm type="sector" :item="item" :loading="store.saving" :error="store.error" @submit="submit" @cancel="router.push({ name: 'admin-sectors' })" /></template>
