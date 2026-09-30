<script setup>
import { RouterLink } from "vue-router";
import { useAuthStore } from "@/stores/authStore";

defineProps({
  items: { type: Array, default: () => [] },
  workspace: { type: String, default: "Workspace" },
  description: { type: String, default: "" },
  open: Boolean,
});

defineEmits(["close"]);
const authStore = useAuthStore();

function initials() {
  return (authStore.displayName || "MP")
    .split(" ")
    .filter(Boolean)
    .slice(0, 2)
    .map((part) => part[0])
    .join("")
    .toUpperCase();
}
</script>

<template>
  <aside class="sidebar" :class="{ 'is-open': open }">
    <div class="sidebar-top">
      <div class="brand-lockup">
        <div class="brand-mark">M</div>
        <div>
          <strong>Meet<span>ing</span></strong>
          <small>Platform</small>
        </div>
      </div>
      <button type="button" class="close-sidebar" aria-label="Fermer le menu" @click="$emit('close')">×</button>
    </div>

    <div class="workspace-label">
      <span class="workspace-dot" />
      <div><strong>{{ workspace }}</strong><small>{{ description }}</small></div>
    </div>

    <nav class="navigation" aria-label="Navigation principale">
      <p class="nav-heading">Menu principal</p>
      <RouterLink v-for="item in items" :key="item.to" :to="item.to" @click="$emit('close')">
        <span class="nav-icon" aria-hidden="true">{{ item.icon }}</span>
        <span>{{ item.label }}</span>
        <span v-if="item.badge" class="nav-badge">{{ item.badge }}</span>
      </RouterLink>
    </nav>

    <div class="sidebar-help">
      <div class="help-icon">?</div>
      <strong>Besoin d’aide ?</strong>
      <p>Consultez le guide de la plateforme.</p>
    </div>

    <div class="sidebar-account">
      <div class="avatar">{{ initials() }}</div>
      <div class="account-copy"><strong>{{ authStore.displayName || "Utilisateur" }}</strong><small>{{ authStore.user?.email }}</small></div>
      <span class="account-more">•••</span>
    </div>
  </aside>
</template>

<style scoped>
.sidebar { position: fixed; z-index: 20; inset: 0 auto 0 0; display: flex; width: 264px; flex-direction: column; padding: 1.4rem 1rem 1rem; box-sizing: border-box; color: #cbd5e1; background: #101827; box-shadow: 10px 0 35px rgb(15 23 42 / 8%); }
.sidebar-top { display: flex; align-items: center; justify-content: space-between; padding: 0 .55rem; }
.brand-lockup { display: flex; align-items: center; gap: .7rem; color: #fff; }.brand-mark { display: grid; width: 2.2rem; height: 2.2rem; place-items: center; border-radius: .7rem; color: #fff; background: linear-gradient(135deg, #5b8cff, #7c3aed); font-size: 1.1rem; font-weight: 900; box-shadow: 0 8px 18px rgb(91 140 255 / 25%); }.brand-lockup strong { display: block; font-size: 1rem; letter-spacing: -.02em; }.brand-lockup strong span { color: #82a7ff; }.brand-lockup small { display: block; margin-top: -.15rem; color: #64748b; font-size: .63rem; letter-spacing: .18em; text-transform: uppercase; }.close-sidebar { display: none; border: 0; color: #94a3b8; background: none; font-size: 1.7rem; cursor: pointer; }
.workspace-label { display: flex; align-items: center; gap: .7rem; margin: 2rem .3rem 1.5rem; padding: .75rem; border: 1px solid #233149; border-radius: .75rem; background: #172236; }.workspace-dot { width: .55rem; height: .55rem; border-radius: 50%; background: #59d6a2; box-shadow: 0 0 0 4px rgb(89 214 162 / 12%); }.workspace-label strong, .workspace-label small { display: block; }.workspace-label strong { color: #f8fafc; font-size: .78rem; }.workspace-label small { margin-top: .18rem; color: #8190a7; font-size: .68rem; }
.navigation { display: grid; gap: .28rem; }.nav-heading { margin: 0 .7rem .55rem; color: #64748b; font-size: .65rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }.navigation a { display: flex; align-items: center; gap: .7rem; padding: .75rem .7rem; border: 1px solid transparent; border-radius: .65rem; color: #9aa8bd; font-size: .84rem; font-weight: 600; text-decoration: none; transition: .2s ease; }.navigation a:hover { color: #fff; background: #1a2940; }.navigation a.router-link-active { color: #fff; border-color: #294a9a; background: linear-gradient(90deg, #2451b8, #2e68dc); box-shadow: 0 7px 20px rgb(37 81 184 / 22%); }.nav-icon { display: grid; width: 1.3rem; place-items: center; color: inherit; font-size: 1rem; font-weight: 500; }.nav-badge { margin-left: auto; padding: .12rem .4rem; border-radius: 999px; color: #bcd0ff; background: rgb(255 255 255 / 14%); font-size: .65rem; }
.sidebar-help { margin-top: auto; padding: .95rem; border: 1px solid #273955; border-radius: .85rem; background: linear-gradient(145deg, #172640, #172033); }.help-icon { display: grid; width: 1.45rem; height: 1.45rem; place-items: center; margin-bottom: .6rem; border-radius: 50%; color: #a9c3ff; background: #25478d; font-size: .75rem; font-weight: 800; }.sidebar-help strong { color: #eef4ff; font-size: .78rem; }.sidebar-help p { margin: .3rem 0 0; color: #8190a7; font-size: .7rem; line-height: 1.45; }
.sidebar-account { display: flex; align-items: center; gap: .65rem; margin-top: 1rem; padding: .85rem .3rem .1rem; border-top: 1px solid #253149; }.avatar { display: grid; width: 2rem; height: 2rem; flex: 0 0 auto; place-items: center; border-radius: .65rem; color: #dbeafe; background: #264a89; font-size: .68rem; font-weight: 800; }.account-copy { min-width: 0; flex: 1; }.account-copy strong, .account-copy small { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }.account-copy strong { color: #e2e8f0; font-size: .75rem; }.account-copy small { margin-top: .18rem; color: #718096; font-size: .65rem; }.account-more { color: #718096; font-size: .8rem; letter-spacing: .08em; }
@media (max-width: 900px) { .sidebar { transform: translateX(-100%); transition: transform .25s ease; }.sidebar.is-open { transform: translateX(0); }.close-sidebar { display: block; } }
</style>
