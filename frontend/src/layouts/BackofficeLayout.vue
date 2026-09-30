<script setup>
import { useRouter, RouterLink, RouterView } from "vue-router";
import { useAuthStore } from "@/stores/authStore";

const router = useRouter();
const authStore = useAuthStore();

async function signOut() {
  await authStore.signOut();
  router.replace({ name: "login" });
}
</script>

<template>
  <div class="backoffice-shell">
    <aside class="sidebar">
      <div>
        <p class="brand">Meeting Platform</p>
        <p class="caption">Backoffice administrateur</p>
      </div>

      <nav class="navigation" aria-label="Navigation backoffice">
        <RouterLink :to="{ name: 'backoffice-home' }">Vue d’ensemble</RouterLink>
        <RouterLink :to="{ name: 'admin-users' }">Utilisateurs</RouterLink>
        <RouterLink :to="{ name: 'admin-roles' }">Rôles</RouterLink>
      </nav>

      <div class="account">
        <strong>{{ authStore.displayName || "Administrateur" }}</strong>
        <span>{{ authStore.user?.email }}</span>
        <button type="button" class="secondary-button" @click="signOut">Se déconnecter</button>
      </div>
    </aside>

    <main class="backoffice-content">
      <RouterView />
    </main>
  </div>
</template>

<style scoped>
.backoffice-shell { min-height: 100vh; display: grid; grid-template-columns: 250px 1fr; color: #0f172a; background: #f8fafc; text-align: left; }
.sidebar { display: flex; flex-direction: column; gap: 2rem; padding: 1.5rem; color: #e2e8f0; background: #0f172a; }
.brand { margin: 0; color: #fff; font-size: 1.1rem; font-weight: 800; }
.caption, .account span { color: #94a3b8; font-size: .85rem; }
.caption { margin: .35rem 0 0; }
.navigation { display: grid; gap: .5rem; }
.navigation a { padding: .75rem .85rem; border-radius: .6rem; color: #cbd5e1; text-decoration: none; }
.navigation a.router-link-active, .navigation a:hover { color: #fff; background: #1e40af; }
.account { display: grid; gap: .35rem; margin-top: auto; }
.account strong { color: #fff; }
.secondary-button { margin-top: .65rem; padding: .65rem .8rem; border: 1px solid #475569; border-radius: .5rem; color: #e2e8f0; background: transparent; cursor: pointer; }
.backoffice-content { min-width: 0; padding: 2rem; }
@media (max-width: 760px) { .backoffice-shell { display: block; } .sidebar { gap: 1rem; } .navigation { display: flex; overflow-x: auto; } .backoffice-content { padding: 1rem; } }
</style>
