<script setup>
import { computed, onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import ReferenceForm from "@/components/admin/ReferenceForm.vue";
import { useTaxonomyStore } from "@/stores/taxonomyStore";
const route = useRoute(); const router = useRouter(); const store = useTaxonomyStore();
const item = computed(() => store.departments.find((entry) => String(entry.id) === String(route.params.id)) || null);
onMounted(() => { if (!store.departments.length) store.fetchTaxonomies(); });
async function submit(payload) { try { await store.save("department", route.params.id, payload); router.push({ name: "admin-departments" }); } catch {} }
</script>
<template><ReferenceForm type="department" :item="item" :loading="store.saving" :error="store.error" @submit="submit" @cancel="router.push({ name: 'admin-departments' })" /></template>
