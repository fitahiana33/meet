<script setup>
import { onMounted } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import UserForm from "@/components/user/UserForm.vue";
import { useRoleStore } from "@/stores/roleStore";
import { useUserStore } from "@/stores/userStore";

const route = useRoute();
const router = useRouter();
const userStore = useUserStore();
const roleStore = useRoleStore();
onMounted(() => Promise.all([userStore.fetchUser(route.params.id), roleStore.fetchRoles({ per_page: 100 })]));
async function submit(payload) { try { await userStore.updateUser(route.params.id, payload); router.push({ name: "admin-users" }); } catch { /* displayed in the form */ } }
</script>

<template>
  <section class="form-page"><RouterLink :to="{ name: 'admin-users' }" class="back-link">← Retour aux utilisateurs</RouterLink><div class="page-intro"><p class="page-eyebrow">Administration / Utilisateurs</p><h2>Modifier un utilisateur</h2><p>Mettez à jour les informations et le rôle de ce compte.</p></div><p v-if="userStore.loading && !userStore.currentUser" class="loading-state">Chargement du profil…</p><UserForm v-else :user="userStore.currentUser" :roles="roleStore.roles" :loading="userStore.saving" :error="userStore.error || roleStore.error" @submit="submit" @cancel="router.push({ name: 'admin-users' })" /></section>
</template>

<style scoped>
.form-page { max-width: 900px; margin: 0 auto; }.back-link { display: inline-block; margin-bottom: 1.6rem; color: #7186ae; font-size: .72rem; font-weight: 700; text-decoration: none; }.back-link:hover { color: #4777da; }.page-eyebrow { margin: 0 0 .55rem; color: #6d91e4; font-size: .68rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }.page-intro h2 { margin: 0; color: #1c2d46; font-size: 2rem; letter-spacing: -.06em; }.page-intro p:last-child { margin: .6rem 0 1.6rem; color: #8b99ab; font-size: .8rem; }.loading-state { color: #8b99ab; font-size: .8rem; }
</style>
