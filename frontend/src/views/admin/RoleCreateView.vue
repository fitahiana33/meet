<script setup>
import { useRouter } from "vue-router";
import RoleForm from "@/components/role/RoleForm.vue";
import { useRoleStore } from "@/stores/roleStore";

const router = useRouter();
const roleStore = useRoleStore();
async function submit(payload) { try { await roleStore.createRole(payload); router.push({ name: "admin-roles" }); } catch { /* displayed by the form */ } }
</script>

<template><section class="page"><p class="eyebrow">Administration · Rôles</p><h1>Créer un rôle</h1><p class="intro">Ajoutez un rôle personnalisé à la plateforme.</p><RoleForm :loading="roleStore.saving" :error="roleStore.error" @submit="submit" @cancel="router.push({ name: 'admin-roles' })" /></section></template>

<style scoped>.page { max-width: 850px; margin: 0 auto; }.eyebrow { margin: 0; color: #2563eb; font-size: .8rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }h1 { margin: .35rem 0; font-size: 2.2rem; }.intro { margin-bottom: 1.5rem; color: #64748b; }</style>
