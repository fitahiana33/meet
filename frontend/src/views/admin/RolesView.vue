<script setup>
import { onMounted } from "vue";
import { RouterLink, useRouter } from "vue-router";
import RoleTable from "@/components/role/RoleTable.vue";
import { useRoleStore } from "@/stores/roleStore";

const router = useRouter();
const roleStore = useRoleStore();
onMounted(() => roleStore.fetchRoles());
function editRole(role) { router.push({ name: "admin-role-edit", params: { id: role.id } }); }
async function removeRole(role) {
  if (window.confirm(`Supprimer le rôle ${role.name} ?`)) await roleStore.deleteRole(role.id);
}
</script>

<template>
  <section class="page"><div class="page-header"><div><p class="eyebrow">Administration</p><h1>Rôles</h1><p class="intro">Gérez les rôles attribuables aux utilisateurs.</p></div><RouterLink :to="{ name: 'admin-role-create' }" class="primary-button">Nouveau rôle</RouterLink></div>
    <div v-if="roleStore.error" class="alert" role="alert">{{ roleStore.error }}</div>
    <RoleTable :roles="roleStore.roles" :loading="roleStore.loading" @edit="editRole" @delete="removeRole" />
  </section>
</template>

<style scoped>
.page { max-width: 1100px; margin: 0 auto; }.page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }.eyebrow { margin: 0; color: #2563eb; font-size: .8rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }h1 { margin: .35rem 0; font-size: 2.2rem; }.intro { color: #64748b; }.primary-button { padding: .75rem 1rem; border-radius: .45rem; color: #fff; background: #2563eb; font-weight: 700; text-decoration: none; white-space: nowrap; }.alert { margin-bottom: 1rem; padding: .75rem 1rem; border-radius: .45rem; color: #991b1b; background: #fef2f2; }@media (max-width: 600px) { .page-header { display: block; }.primary-button { display: inline-block; margin-top: 1rem; } }
</style>
