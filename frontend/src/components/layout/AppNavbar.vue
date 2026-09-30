<script setup>
import { computed } from "vue";
import { useAuthStore } from "@/stores/authStore";

defineProps({ title: { type: String, default: "Vue d’ensemble" }, subtitle: { type: String, default: "Voici ce qui se passe sur votre espace." } });
defineEmits(["toggle-menu", "logout"]);
const authStore = useAuthStore();
const initials = computed(() => (authStore.displayName || "MP").split(" ").filter(Boolean).slice(0, 2).map((part) => part[0]).join("").toUpperCase());
</script>

<template>
  <header class="topbar">
    <button type="button" class="menu-button" aria-label="Ouvrir le menu" @click="$emit('toggle-menu')">☰</button>
    <div class="heading"><p>{{ subtitle }}</p><h1>{{ title }}</h1></div>
    <div class="topbar-actions">
      <button type="button" class="icon-button" aria-label="Notifications"><span class="notification-dot" />◌</button>
      <div class="profile"><div class="avatar">{{ initials }}</div><div class="profile-copy"><strong>{{ authStore.displayName || "Utilisateur" }}</strong><small>{{ authStore.isAdmin ? "Administrateur" : "Client" }}</small></div></div>
      <button type="button" class="logout-button" title="Se déconnecter" @click="$emit('logout')">↪</button>
    </div>
  </header>
</template>

<style scoped>
.topbar { display: flex; min-height: 76px; align-items: center; justify-content: space-between; gap: 1rem; padding: 1rem 2.6rem; border-bottom: 1px solid #e8edf5; box-sizing: border-box; background: rgb(255 255 255 / 82%); }.heading p { margin: 0 0 .22rem; color: #94a3b8; font-size: .74rem; }.heading h1 { margin: 0; color: #152238; font-size: 1.38rem; font-weight: 800; letter-spacing: -.03em; }.topbar-actions { display: flex; align-items: center; gap: 1rem; }.menu-button, .icon-button, .logout-button { border: 0; background: none; cursor: pointer; }.menu-button { display: none; color: #334155; font-size: 1.25rem; }.icon-button { position: relative; color: #607089; font-size: 1.35rem; }.notification-dot { position: absolute; top: .08rem; right: -.02rem; width: .35rem; height: .35rem; border: 2px solid #fff; border-radius: 50%; background: #f97316; }.profile { display: flex; align-items: center; gap: .6rem; padding-left: 1rem; border-left: 1px solid #e5eaf2; }.avatar { display: grid; width: 2.2rem; height: 2.2rem; place-items: center; border-radius: .7rem; color: #fff; background: linear-gradient(135deg, #4f78e8, #7054d8); font-size: .7rem; font-weight: 800; }.profile-copy strong, .profile-copy small { display: block; }.profile-copy strong { color: #1e293b; font-size: .76rem; }.profile-copy small { margin-top: .12rem; color: #8a98ac; font-size: .66rem; }.logout-button { color: #8a98ac; font-size: 1.2rem; }.logout-button:hover { color: #ef4444; }
@media (max-width: 900px) { .topbar { padding: .9rem 1.2rem; }.menu-button { display: block; }.profile-copy, .icon-button { display: none; }.profile { padding-left: 0; border-left: 0; } }
</style>
