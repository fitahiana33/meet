<script setup>
import { useRouter } from "vue-router";
import ReferenceForm from "@/components/admin/ReferenceForm.vue";
import { useTaxonomyStore } from "@/stores/taxonomyStore";
const router = useRouter(); const store = useTaxonomyStore();
async function submit(payload) { try { await store.save("sector", null, payload); router.push({ name: "admin-sectors" }); } catch {} }
</script>
<template><ReferenceForm type="sector" :loading="store.saving" :error="store.error" @submit="submit" @cancel="router.push({ name: 'admin-sectors' })" /></template>
