<script setup>
import { onMounted } from "vue";
import ReferenceList from "@/components/admin/ReferenceList.vue";
import { useTaxonomyStore } from "@/stores/taxonomyStore";
const store = useTaxonomyStore();
onMounted(() => { if (!store.sectors.length) store.fetchTaxonomies(); });
async function remove(item) { if (window.confirm(`Supprimer le secteur « ${item.name} » ?`)) await store.remove("sector", item.id); }
</script>
<template><ReferenceList type="sector" :items="store.sectors" :loading="store.loading" :error="store.error" create-route="admin-sector-create" edit-route="admin-sector-edit" @delete="remove" /></template>
