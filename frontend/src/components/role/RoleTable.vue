<script setup>
defineProps({ roles: { type: Array, default: () => [] }, loading: Boolean });
defineEmits(["edit", "delete"]);
function isBuiltIn(role) { return ["admin", "client"].includes(role.name); }
</script>

<template>
  <div class="table-wrapper">
    <p v-if="loading" class="state">Chargement des rôles…</p>
    <p v-else-if="!roles.length" class="state">Aucun rôle trouvé.</p>
    <table v-else><thead><tr><th>Rôle</th><th>Description</th><th class="actions-heading">Actions</th></tr></thead><tbody>
      <tr v-for="role in roles" :key="role.id"><td><div class="role-cell"><span class="role-icon" :class="{ system: isBuiltIn(role) }">◇</span><span><strong>{{ role.name }}</strong><small>ID #{{ role.id }} <em v-if="isBuiltIn(role)">Système</em></small></span></div></td><td class="muted">{{ role.description || "Aucune description" }}</td><td class="actions"><button type="button" class="edit-button" @click="$emit('edit', role)">Modifier</button><button v-if="!isBuiltIn(role)" type="button" class="delete-button" aria-label="Supprimer" @click="$emit('delete', role)">×</button><span v-else class="protected">Protégé</span></td></tr>
    </tbody></table>
  </div>
</template>

<style scoped>
.table-wrapper { overflow-x: auto; }.table-wrapper table { width: 100%; border-collapse: collapse; text-align: left; }.table-wrapper th { padding: .7rem .8rem; border-bottom: 1px solid #edf1f6; color: #a0adbd; font-size: .62rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }.table-wrapper td { padding: .82rem .8rem; border-bottom: 1px solid #f0f3f7; white-space: nowrap; }.table-wrapper tr:last-child td { border-bottom: 0; }.role-cell { display: flex; align-items: center; gap: .65rem; }.role-icon { display: grid; width: 2rem; height: 2rem; place-items: center; border-radius: .65rem; color: #9a72d8; background: #f2edff; }.role-icon.system { color: #4e7fe0; background: #eaf1ff; }.role-cell strong, .role-cell small { display: block; }.role-cell strong { color: #32425a; font-size: .75rem; text-transform: capitalize; }.role-cell small { margin-top: .15rem; color: #a4afbd; font-size: .62rem; }.role-cell em { margin-left: .25rem; color: #6f90d8; font-style: normal; }.muted { color: #718097; font-size: .72rem; }.actions-heading { text-align: right; }.actions { display: flex; align-items: center; justify-content: flex-end; gap: .7rem; text-align: right; }.actions button { border: 0; background: none; font-size: .68rem; cursor: pointer; }.edit-button { color: #4f7bdd; font-weight: 700; }.delete-button { display: grid; width: 1.45rem; height: 1.45rem; place-items: center; border-radius: .4rem !important; color: #d77a83; background: #fff1f2 !important; font-size: 1.05rem !important; }.protected { color: #a0adbd; font-size: .65rem; }.state { padding: 2.5rem 1rem; color: #9aa7b7; font-size: .78rem; text-align: center; }
</style>
