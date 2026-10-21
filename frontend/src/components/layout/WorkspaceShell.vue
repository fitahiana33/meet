<script setup>
import { computed, ref } from "vue";
import { useRoute } from "vue-router";
import AppFooter from "@/components/layout/AppFooter.vue";
import AppNavbar from "@/components/layout/AppNavbar.vue";
import AppSidebar from "@/components/layout/AppSidebar.vue";

const props = defineProps({
  workspace: { type: String, required: true },
  description: { type: String, default: "" },
  items: { type: Array, default: () => [] },
  fallbackTitle: { type: String, default: "Vue d’ensemble" },
  fallbackSubtitle: { type: String, default: "Voici ce qui se passe sur votre espace." },
});
const emit = defineEmits(["logout"]);
const route = useRoute();
const sidebarOpen = ref(false);
const title = computed(() => route.meta.title || props.fallbackTitle);
const subtitle = computed(() => route.meta.subtitle || props.fallbackSubtitle);
</script>

<template>
  <div class="workspace-shell">
    <AppSidebar :items="items" :workspace="workspace" :description="description" :open="sidebarOpen" @close="sidebarOpen = false" />
    <div class="workspace-main">
      <AppNavbar :title="title" :subtitle="subtitle" @toggle-menu="sidebarOpen = true" @logout="emit('logout')" />
      <main class="workspace-content"><slot /></main>
      <AppFooter />
    </div>
    <div v-if="sidebarOpen" class="sidebar-overlay" @click="sidebarOpen = false" />
  </div>
</template>

<style scoped>
.workspace-shell { min-height: 100vh; color: #27364d; background: #f7f9fc; text-align: left; }.workspace-main { min-height: 100vh; margin-left: 248px; display: flex; flex-direction: column; }.workspace-content { flex: 1; padding: 1.15rem 1.5rem 2rem; }.sidebar-overlay { display: none; }
.workspace-content :deep(.page-intro),
.workspace-content :deep(.page-toolbar > div),
.workspace-content :deep(.entities-page .toolbar > div),
.workspace-content :deep(.dashboard-intro > div) { display: none; }
.workspace-content :deep(.back-link) { margin-top: 0; margin-bottom: .65rem; }
.workspace-content :deep(.page-toolbar),
.workspace-content :deep(.entities-page .toolbar),
.workspace-content :deep(.dashboard-intro) { margin-bottom: .75rem; }
@media (max-width: 900px) { .workspace-main { margin-left: 0; }.workspace-content { padding: .9rem 1rem 1.5rem; }.sidebar-overlay { position: fixed; z-index: 10; inset: 0; display: block; background: rgb(15 23 42 / 42%); } }
@media (max-width: 520px) { .workspace-content { padding: .75rem .7rem 1.2rem; } }
</style>
