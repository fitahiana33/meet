<script setup>
import { onMounted } from "vue";
import { useRouter } from "vue-router";
import RoleTable from "@/components/role/RoleTable.vue";
import { useRoleStore } from "@/stores/roleStore";

const router = useRouter();
const roleStore = useRoleStore();

onMounted(() => {
  roleStore.fetchRoles();
});

function editRole(role) {
  router.push({ name: "admin-role-edit", params: { id: role.id } });
}

async function removeRole(role) {
  if (!window.confirm(`Voulez-vous vraiment supprimer le rôle « ${role.name} » ?`)) {
    return;
  }

  try {
    await roleStore.deleteRole(role.id);
  } catch {
    // L’erreur est affichée par roleStore.error.
  }
}
</script>

<template>
  <main class="admin-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">Administration</p>
        <h1>Rôles</h1>
        <p class="intro">Gérez les rôles disponibles pour les utilisateurs.</p>
      </div>

      <div class="header-actions">
        <button class="button button-secondary" type="button" @click="roleStore.fetchRoles()">
          Actualiser
        </button>
        <button class="button button-primary" type="button" @click="router.push({ name: 'admin-role-create' })">
          Ajouter un rôle
        </button>
      </div>
    </header>

    <div v-if="roleStore.error" class="page-alert" role="alert">
      {{ roleStore.error }}
    </div>

    <RoleTable :roles="roleStore.roles" :loading="roleStore.loading" @edit="editRole" @delete="removeRole" />
  </main>
</template>

<style scoped>
.admin-page {
  min-height: 100vh;
  padding: 2.5rem clamp(1rem, 4vw, 4rem);
  box-sizing: border-box;
  color: #0f172a;
  background: #f8fafc;
  text-align: left;
}

.page-header {
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 1.5rem;
  max-width: 1180px;
  margin: 0 auto 2rem;
}

.eyebrow {
  margin: 0 0 0.4rem;
  color: #2563eb;
  font-size: 0.8rem;
  font-weight: 800;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

h1 {
  margin: 0;
  color: #0f172a;
  font-size: clamp(2rem, 4vw, 3rem);
}

.intro {
  margin: 0.5rem 0 0;
  color: #64748b;
}

.header-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.7rem;
}

.admin-page > :not(.page-header) {
  width: 100%;
  max-width: 1180px;
  margin-right: auto;
  margin-left: auto;
}

.page-alert {
  margin-bottom: 1rem;
  padding: 0.85rem 1rem;
  border-radius: 8px;
  color: #991b1b;
  background: #fef2f2;
}

.button {
  padding: 0.7rem 1rem;
  border: 0;
  border-radius: 8px;
  font: inherit;
  font-weight: 700;
  cursor: pointer;
}

.button-primary {
  color: #fff;
  background: #2563eb;
}

.button-secondary {
  color: #334155;
  background: #e2e8f0;
}

@media (max-width: 720px) {
  .page-header {
    align-items: stretch;
    flex-direction: column;
  }

  .header-actions {
    width: 100%;
  }

  .header-actions .button {
    flex: 1;
  }
}
</style>
