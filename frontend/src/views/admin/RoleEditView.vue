<script setup>
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import RoleForm from "@/components/role/RoleForm.vue";
import { useRoleStore } from "@/stores/roleStore";

const route = useRoute();
const router = useRouter();
const roleStore = useRoleStore();
onMounted(() => roleStore.fetchRole(route.params.id));
async function submit(payload) { try { await roleStore.updateRole(route.params.id, payload); router.push({ name: "admin-roles" }); } catch { /* displayed by the form */ } }
</script>

<template><section class="page"><p class="eyebrow">Administration · Rôles</p><h1>Modifier un rôle</h1><p class="intro">Mettez à jour la description de ce rôle.</p><p v-if="roleStore.loading && !roleStore.currentRole">Chargement…</p><RoleForm v-else :role="roleStore.currentRole" :loading="roleStore.saving" :error="roleStore.error" @submit="submit" @cancel="router.push({ name: 'admin-roles' })" /></section></template>

<style scoped>.page { max-width: 850px; margin: 0 auto; }.eyebrow { margin: 0; color: #2563eb; font-size: .8rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }h1 { margin: .35rem 0; font-size: 2.2rem; }.intro { margin-bottom: 1.5rem; color: #64748b; }</style>
