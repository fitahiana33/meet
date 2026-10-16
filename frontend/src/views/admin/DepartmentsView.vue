<script setup>
import { onMounted } from "vue";
import ReferenceList from "@/components/admin/ReferenceList.vue";
import { useTaxonomyStore } from "@/stores/taxonomyStore";
const store = useTaxonomyStore();
onMounted(() => { if (!store.departments.length) store.fetchTaxonomies(); });
async function remove(item) { if (window.confirm(`Supprimer le département « ${item.name} » ?`)) await store.remove("department", item.id); }
</script>
<template><ReferenceList type="department" :items="store.departments" :loading="store.loading" :error="store.error" create-route="admin-department-create" edit-route="admin-department-edit" @delete="remove" /></template>
