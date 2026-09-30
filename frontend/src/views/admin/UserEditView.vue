<script setup>
import { onMounted } from "vue";
import { useRoute, useRouter } from "vue-router";
import UserForm from "@/components/user/UserForm.vue";
import { useRoleStore } from "@/stores/roleStore";
import { useUserStore } from "@/stores/userStore";

const route = useRoute();
const router = useRouter();
const userStore = useUserStore();
const roleStore = useRoleStore();
onMounted(() => Promise.all([userStore.fetchUser(route.params.id), roleStore.fetchRoles()]));

async function submit(payload) {
  try { await userStore.updateUser(route.params.id, payload); router.push({ name: "admin-users" }); } catch { /* displayed by the form */ }
}
</script>

<template>
  <section class="page"><p class="eyebrow">Administration · Utilisateurs</p><h1>Modifier un utilisateur</h1><p class="intro">Mettez à jour les informations et le rôle du compte.</p>
    <p v-if="userStore.loading && !userStore.currentUser">Chargement…</p>
    <UserForm v-else :user="userStore.currentUser" :roles="roleStore.roles" :loading="userStore.saving" :error="userStore.error || roleStore.error" @submit="submit" @cancel="router.push({ name: 'admin-users' })" />
  </section>
</template>

<style scoped>
.page { max-width: 850px; margin: 0 auto; }.eyebrow { margin: 0; color: #2563eb; font-size: .8rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }h1 { margin: .35rem 0; font-size: 2.2rem; }.intro { margin-bottom: 1.5rem; color: #64748b; }
</style>
