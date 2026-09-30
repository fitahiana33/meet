<script setup>
import { onMounted, ref } from "vue";
import { useRoute, useRouter } from "vue-router";
import RoleForm from "@/components/role/RoleForm.vue";
import { useRoleStore } from "@/stores/roleStore";

const route = useRoute();
const router = useRouter();
const roleStore = useRoleStore();
const error = ref(null);

onMounted(async () => {
  try {
    await roleStore.fetchRole(route.params.id);
  } catch {
    error.value = roleStore.error;
  }
});

async function updateRole(payload) {
  error.value = null;

  try {
    await roleStore.updateRole(route.params.id, payload);
    router.push({ name: "admin-roles" });
  } catch {
    error.value = roleStore.error;
  }
}
</script>

<template>
  <main class="form-page">
    <div class="page-heading">
      <button class="back-link" type="button" @click="router.push({ name: 'admin-roles' })">
        ← Retour aux rôles
      </button>
      <p class="eyebrow">Administration</p>
      <h1>Modifier un rôle</h1>
    </div>

    <div v-if="roleStore.loading && !roleStore.currentRole" class="loading-message">
      Chargement du rôle…
    </div>
    <div v-else-if="error" class="page-alert" role="alert">{{ error }}</div>
    <RoleForm
      v-else
      :role="roleStore.currentRole"
      :loading="roleStore.saving"
      :error="error"
      @submit="updateRole"
      @cancel="router.push({ name: 'admin-roles' })"
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
