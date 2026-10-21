<script setup>
import { useRouter, RouterView } from "vue-router";
import WorkspaceShell from "@/components/layout/WorkspaceShell.vue";
import { useAuthStore } from "@/stores/authStore";

const router = useRouter();
const authStore = useAuthStore();
const items = [
  { to: { name: "frontoffice-home" }, label: "Accueil", icon: "⌂" },
  { to: { name: "frontoffice-meetings" }, label: "Réunions", icon: "◉" },
  { to: { name: "admin-grievances" }, label: "Doléances", icon: "!" },
  { to: { name: "admin-departments" }, label: "Départements", icon: "◫" },
  { to: { name: "admin-sectors" }, label: "Secteurs", icon: "◇" },
];

async function signOut() {
  await authStore.signOut();
  router.replace({ name: "login" });
}
</script>

<template>
  <WorkspaceShell workspace="Frontoffice" description="Votre espace personnel" :items="items" fallback-title="Accueil" fallback-subtitle="Retrouvez votre espace de travail." @logout="signOut">
    <RouterView />
  </WorkspaceShell>
</template>
