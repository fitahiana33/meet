<script setup>
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import UserForm from "@/components/user/UserForm.vue";
import { useRoleStore } from "@/stores/roleStore";
import { useUserStore } from "@/stores/userStore";

const route = useRoute();
const router = useRouter();
const userStore = useUserStore();
const roleStore = useRoleStore();
const error = ref(null);

onMounted(async () => {
  try {
    await Promise.all([
      userStore.fetchUser(route.params.id),
      roleStore.fetchRoles(),
    ]);
  } catch {
    error.value = userStore.error;
  }
});

async function updateUser(payload) {
  error.value = null;

  try {
    await userStore.updateUser(route.params.id, payload);
    router.push({ name: "admin-users" });
  } catch {
    error.value = userStore.error;
  }
}
</script>

<template>
  <main class="form-page">
    <div class="page-heading">
      <button class="back-link" type="button" @click="router.push({ name: 'admin-users' })">
        ← Retour aux utilisateurs
      </button>
      <p class="eyebrow">Administration</p>
      <h1>Modifier un utilisateur</h1>
    </div>

    <div v-if="userStore.loading && !userStore.currentUser" class="loading-message">
      Chargement de l’utilisateur…
    </div>
    <div v-else-if="error" class="page-alert" role="alert">{{ error }}</div>
    <UserForm
      v-else
      :user="userStore.currentUser"
      :roles="roleStore.roles"
      :loading="userStore.saving"
      :error="error || roleStore.error"
      @submit="updateUser"
      @cancel="router.push({ name: 'admin-users' })"
    />
  </main>
</template>

<style scoped>
.form-page {
  min-height: 100vh;
  padding: 2.5rem clamp(1rem, 4vw, 4rem);
  box-sizing: border-box;
  color: #0f172a;
  background: #f8fafc;
  text-align: left;
}

.form-page > * {
  width: 100%;
  max-width: 760px;
  margin-right: auto;
  margin-left: auto;
}

.page-heading {
  margin-bottom: 1.5rem;
}

.back-link {
  margin-bottom: 1.5rem;
  padding: 0;
  border: 0;
  color: #2563eb;
  background: transparent;
  font: inherit;
  cursor: pointer;
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
}

.loading-message,
.page-alert {
  padding: 1rem;
  border-radius: 8px;
  background: #fff;
}

.loading-message {
  color: #475569;
}

.page-alert {
  color: #991b1b;
  background: #fef2f2;
}
</style>
