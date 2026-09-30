<script setup>
defineProps({
  users: { type: Array, default: () => [] },
  loading: Boolean,
});

defineEmits(["edit", "delete"]);
</script>

<template>
  <div class="table-wrapper">
    <p v-if="loading" class="state">Chargement des utilisateurs…</p>
    <p v-else-if="!users.length" class="state">Aucun utilisateur trouvé.</p>
    <table v-else>
      <thead><tr><th>Nom</th><th>E-mail</th><th>Rôle</th><th>Actions</th></tr></thead>
      <tbody>
        <tr v-for="user in users" :key="user.id">
          <td>{{ user.first_name }} {{ user.last_name }}</td>
          <td>{{ user.email }}</td>
          <td><span class="role">{{ user.role?.name || "—" }}</span></td>
          <td class="actions">
            <button type="button" class="link-button" @click="$emit('edit', user)">Modifier</button>
            <button type="button" class="danger-button" @click="$emit('delete', user)">Supprimer</button>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.table-wrapper { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: .75rem; background: #fff; }
table { width: 100%; border-collapse: collapse; text-align: left; }
th, td { padding: .9rem 1rem; border-bottom: 1px solid #e2e8f0; white-space: nowrap; }
th { color: #475569; font-size: .8rem; text-transform: uppercase; }
tr:last-child td { border-bottom: 0; }
.role { padding: .25rem .5rem; border-radius: 999px; color: #1d4ed8; background: #dbeafe; font-size: .8rem; }
.actions { display: flex; gap: .8rem; }
button { border: 0; background: none; font: inherit; cursor: pointer; }
.link-button { color: #2563eb; }
.danger-button { color: #dc2626; }
.state { padding: 2rem; color: #64748b; text-align: center; }
</style>
