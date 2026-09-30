<script setup>
import { computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import UserTable from "@/components/user/UserTable.vue";
import { useUserStore } from "@/stores/userStore";

const router = useRouter();
const userStore = useUserStore();

const users = computed(() => userStore.users);

onMounted(() => {
  userStore.fetchUsers();
});

function editUser(user) {
  router.push({ name: "admin-user-edit", params: { id: user.id } });
}

async function removeUser(user) {
  const confirmed = window.confirm(
    `Voulez-vous vraiment supprimer l’utilisateur « ${user.first_name} ${user.last_name} » ?`,
  );

  if (!confirmed) {
    return;
  }

  try {
    await userStore.deleteUser(user.id);
  } catch {
    // L’erreur est affichée par userStore.error.
  }
}
</script>

<template>
  <main class="admin-page">
    <header class="page-header">
      <div>
        <p class="eyebrow">Administration</p>
        <h1>Utilisateurs</h1>
        <p class="intro">Gérez les comptes et leurs rôles.</p>
      </div>

      <div class="header-actions">
        <button class="button button-secondary" type="button" @click="userStore.fetchUsers()">
          Actualiser
        </button>
        <button class="button button-primary" type="button" @click="router.push({ name: 'admin-user-create' })">
          Ajouter un utilisateur
        </button>
      </div>
    </header>

    <div v-if="userStore.error" class="page-alert" role="alert">
      {{ userStore.error }}
    </div>

    <UserTable :users="users" :loading="userStore.loading" @edit="editUser" @delete="removeUser" />
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
  margin: 0 auto 2rem;
  max-width: 1180px;
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
  font-size: clamp(2rem, 4vw, 3rem);
  color: #0f172a;
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
