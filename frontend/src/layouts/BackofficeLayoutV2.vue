<script setup>
import { computed } from "vue";
import { RouterView, useRouter } from "vue-router";
import WorkspaceShell from "@/components/layout/WorkspaceShell.vue";
import { useAuthStore } from "@/stores/authStore";
const router = useRouter(); const authStore = useAuthStore();
const items = computed(() => { const common = [{ to: { name: "backoffice-home" }, label: "Vue d’ensemble", icon: "⌂" }, { to: { name: "frontoffice-meetings" }, label: "Réunions", icon: "◉" }, { to: { name: "admin-grievances" }, label: "Doléances", icon: "!" }, { to: { name: "admin-departments" }, label: "Départements", icon: "◫" }, { to: { name: "admin-sectors" }, label: "Secteurs", icon: "◇" }, { to: { name: "admin-roles" }, label: "Rôles", icon: "◇" }]; return authStore.isAdmin ? [...common, { to: { name: "admin-users" }, label: "Utilisateurs", icon: "♙" }] : common; });
const workspace = computed(() => authStore.isAdmin ? "Administration" : "Espace collaboratif"); const workspaceDescription = computed(() => authStore.isAdmin ? "Gestion de la plateforme" : "Vos demandes et référentiels");
async function signOut() { await authStore.signOut(); router.replace({ name: "login" }); }
</script>
<template><WorkspaceShell :workspace="workspace" :description="workspaceDescription" :items="items" fallback-title="Vue d’ensemble" fallback-subtitle="Retrouvez vos demandes et référentiels." @logout="signOut"><RouterView /></WorkspaceShell></template>
