<script setup>
import { onMounted } from "vue";
import { RouterLink, useRouter } from "vue-router";
import UserForm from "@/components/user/UserForm.vue";
import { useRoleStore } from "@/stores/roleStore";
import { useUserStore } from "@/stores/userStore";

const router = useRouter();
const userStore = useUserStore();
const roleStore = useRoleStore();
onMounted(() => roleStore.fetchRoles());
async function submit(payload) { try { await userStore.createUser(payload); router.push({ name: "admin-users" }); } catch { /* displayed in the form */ } }
</script>

<template>
  <section class="form-page"><RouterLink :to="{ name: 'admin-users' }" class="back-link">← Retour aux utilisateurs</RouterLink><div class="page-intro"><p class="page-eyebrow">Administration / Utilisateurs</p><h2>Créer un utilisateur</h2><p>Ajoutez un nouveau membre et définissez son niveau d’accès.</p></div><UserForm :roles="roleStore.roles" :loading="userStore.saving" :error="userStore.error || roleStore.error" @submit="submit" @cancel="router.push({ name: 'admin-users' })" /></section>
</template>

<style scoped>
.form-page { max-width: 900px; margin: 0 auto; }.back-link { display: inline-block; margin-bottom: 1.6rem; color: #7186ae; font-size: .72rem; font-weight: 700; text-decoration: none; }.back-link:hover { color: #4777da; }.page-eyebrow { margin: 0 0 .55rem; color: #6d91e4; font-size: .68rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }.page-intro h2 { margin: 0; color: #1c2d46; font-size: 2rem; letter-spacing: -.06em; }.page-intro p:last-child { margin: .6rem 0 1.6rem; color: #8b99ab; font-size: .8rem; }
</style>
