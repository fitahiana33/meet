<script setup>
defineProps({
  roles: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["edit", "delete"]);

function isBuiltIn(role) {
  return ["admin", "client"].includes(role.name);
}
</script>

<template>
  <div class="table-wrapper">
    <table class="roles-table">
      <thead>
        <tr>
          <th>Nom</th>
          <th>Description</th>
          <th class="actions-column">Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr v-if="loading">
          <td colspan="3" class="table-message">Chargement des rôles…</td>
        </tr>
        <tr v-else-if="!roles.length">
          <td colspan="3" class="table-message">Aucun rôle trouvé.</td>
        </tr>
        <template v-else>
          <tr v-for="role in roles" :key="role.id">
            <td>
              <strong>{{ role.name }}</strong>
              <span v-if="isBuiltIn(role)" class="built-in-badge">intégré</span>
            </td>
            <td>{{ role.description || "—" }}</td>
            <td class="actions-column">
              <button class="button button-secondary" type="button" @click="emit('edit', role)">
                Modifier
              </button>
              <button
                class="button button-danger"
                type="button"
                :disabled="isBuiltIn(role)"
                :title="isBuiltIn(role) ? 'Les rôles intégrés ne peuvent pas être supprimés' : ''"
                @click="emit('delete', role)"
              >
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

.roles-table {
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

.built-in-badge {
  display: inline-flex;
  margin-left: 0.5rem;
  padding: 0.2rem 0.5rem;
  border-radius: 999px;
  color: #475569;
  background: #e2e8f0;
  font-size: 0.72rem;
  font-weight: 700;
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

.button:disabled {
  cursor: not-allowed;
  opacity: 0.45;
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
