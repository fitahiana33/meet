<script setup>
import { onMounted } from "vue";
import { RouterLink, useRouter } from "vue-router";
import UserTable from "@/components/user/UserTable.vue";
import { useUserStore } from "@/stores/userStore";

const router = useRouter();
const userStore = useUserStore();

onMounted(() => userStore.fetchUsers());

function editUser(user) { router.push({ name: "admin-user-edit", params: { id: user.id } }); }
async function removeUser(user) {
  if (window.confirm(`Supprimer l’utilisateur ${user.first_name} ${user.last_name} ?`)) await userStore.deleteUser(user.id);
}
</script>

<template>
  <section class="page">
    <div class="page-header">
      <div><p class="eyebrow">Administration</p><h1>Utilisateurs</h1><p class="intro">Gérez les comptes et leur rôle d’accès.</p></div>
      <RouterLink :to="{ name: 'admin-user-create' }" class="primary-button">Nouvel utilisateur</RouterLink>
    </div>
    <div v-if="userStore.error" class="alert" role="alert">{{ userStore.error }}</div>
    <UserTable :users="userStore.users" :loading="userStore.loading" @edit="editUser" @delete="removeUser" />
  </section>
</template>

<style scoped>
.page { max-width: 1100px; margin: 0 auto; }.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }.eyebrow { margin: 0; color: #2563eb; font-size: .8rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }h1 { margin: .35rem 0; font-size: 2.2rem; }.intro { color: #64748b; }.primary-button { padding: .75rem 1rem; border-radius: .45rem; color: #fff; background: #2563eb; font-weight: 700; text-decoration: none; white-space: nowrap; }.alert { margin-bottom: 1rem; padding: .75rem 1rem; border-radius: .45rem; color: #991b1b; background: #fef2f2; }@media (max-width: 600px) { .page-header { display: block; }.primary-button { display: inline-block; margin-top: 1rem; } }
</style>
