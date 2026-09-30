<script setup>
defineProps({
  users: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["edit", "delete"]);
</script>

<template>
  <div class="table-wrapper">
    <table class="users-table">
      <thead>
        <tr>
          <th>Nom</th>
          <th>Email</th>
          <th>Rôle</th>
          <th class="actions-column">Actions</th>
        </tr>
      </thead>

      <tbody>
        <tr v-if="loading">
          <td colspan="4" class="table-message">Chargement des utilisateurs…</td>
        </tr>
        <tr v-else-if="!users.length">
          <td colspan="4" class="table-message">Aucun utilisateur trouvé.</td>
        </tr>
        <template v-else>
          <tr v-for="user in users" :key="user.id">
            <td>{{ user.first_name }} {{ user.last_name }}</td>
            <td>{{ user.email }}</td>
            <td>
              <span class="role-badge" :class="`role-${user.role}`">
                {{ user.role?.name === "admin" ? "Administrateur" : "Client" }}
              </span>
            </td>
            <td class="actions-column">
              <button class="button button-secondary" type="button" @click="emit('edit', user)">
                Modifier
              </button>
              <button class="button button-danger" type="button" @click="emit('delete', user)">
                Supprimer
              </button>
            </td>
          </tr>
        </template>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.table-wrapper {
  overflow-x: auto;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fff;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
  min-width: 680px;
}

th,
td {
  padding: 1rem 1.1rem;
  text-align: left;
  border-bottom: 1px solid #f1f5f9;
}

th {
  color: #475569;
  font-size: 0.8rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  background: #f8fafc;
}

tbody tr:last-child td {
  border-bottom: 0;
}

.actions-column {
  text-align: right;
  white-space: nowrap;
}

.table-message {
  padding: 2rem;
  color: #64748b;
  text-align: center;
}

.role-badge {
  display: inline-flex;
  padding: 0.25rem 0.65rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 700;
}

.role-admin {
  color: #7c2d12;
  background: #ffedd5;
}

.role-client {
  color: #1e3a8a;
  background: #dbeafe;
}

.button {
  padding: 0.45rem 0.7rem;
  border: 0;
  border-radius: 7px;
  font: inherit;
  font-size: 0.82rem;
  font-weight: 700;
  cursor: pointer;
}

.button + .button {
  margin-left: 0.45rem;
}

.button-secondary {
  color: #1d4ed8;
  background: #eff6ff;
}

.button-danger {
  color: #b91c1c;
  background: #fef2f2;
}
</style>
