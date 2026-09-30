<script setup>
import { useRouter, RouterView } from "vue-router";
import WorkspaceShell from "@/components/layout/WorkspaceShell.vue";
import { useAuthStore } from "@/stores/authStore";

const router = useRouter();
const authStore = useAuthStore();
const items = [
  { to: { name: "backoffice-home" }, label: "Vue d’ensemble", icon: "⌂" },
  { to: { name: "admin-users" }, label: "Utilisateurs", icon: "♙" },
  { to: { name: "admin-roles" }, label: "Rôles", icon: "◇" },
];

async function signOut() {
  await authStore.signOut();
  router.replace({ name: "login" });
}
</script>

<template>
  <WorkspaceShell workspace="Backoffice" description="Espace administrateur" :items="items" fallback-title="Vue d’ensemble" fallback-subtitle="Pilotez votre plateforme en toute simplicité." @logout="signOut">
    <RouterView />
  </WorkspaceShell>
</template>
