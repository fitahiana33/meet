<script setup>
defineProps({ roles: { type: Array, default: () => [] }, loading: Boolean });
defineEmits(["edit", "delete"]);

function isBuiltIn(role) { return ["admin", "client"].includes(role.name); }
</script>

<template>
  <div class="table-wrapper">
    <p v-if="loading" class="state">Chargement des rôles…</p>
    <p v-else-if="!roles.length" class="state">Aucun rôle trouvé.</p>
    <table v-else>
      <thead><tr><th>Nom</th><th>Description</th><th>Actions</th></tr></thead>
      <tbody>
        <tr v-for="role in roles" :key="role.id">
          <td>{{ role.name }} <span v-if="isBuiltIn(role)" class="badge">système</span></td>
          <td>{{ role.description || "—" }}</td>
          <td class="actions">
            <button type="button" class="link-button" @click="$emit('edit', role)">Modifier</button>
            <button v-if="!isBuiltIn(role)" type="button" class="danger-button" @click="$emit('delete', role)">Supprimer</button>
            <span v-else class="muted">Protégé</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.table-wrapper { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: .75rem; background: #fff; }
table { width: 100%; border-collapse: collapse; text-align: left; }
th, td { padding: .9rem 1rem; border-bottom: 1px solid #e2e8f0; }
th { color: #475569; font-size: .8rem; text-transform: uppercase; }
tr:last-child td { border-bottom: 0; }
.badge { margin-left: .35rem; padding: .2rem .45rem; border-radius: 999px; color: #475569; background: #e2e8f0; font-size: .7rem; }
.actions { display: flex; gap: .8rem; white-space: nowrap; }
button { border: 0; background: none; font: inherit; cursor: pointer; }
.link-button { color: #2563eb; }.danger-button { color: #dc2626; }.muted, .state { color: #64748b; }.state { padding: 2rem; text-align: center; }
</style>
