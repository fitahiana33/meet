<script setup>
defineProps({ users: { type: Array, default: () => [] }, loading: Boolean });
defineEmits(["edit", "delete"]);
</script>

<template>
  <div class="table-wrapper">
    <p v-if="loading" class="state">Chargement des utilisateurs…</p>
    <p v-else-if="!users.length" class="state">Aucun utilisateur trouvé.</p>
    <table v-else><thead><tr><th>Utilisateur</th><th>E-mail</th><th>Rôle</th><th class="actions-heading">Actions</th></tr></thead><tbody>
      <tr v-for="user in users" :key="user.id"><td><div class="user-cell"><span class="avatar">{{ (user.first_name?.[0] || "") + (user.last_name?.[0] || "") }}</span><span><strong>{{ user.first_name }} {{ user.last_name }}</strong><small>ID #{{ user.id }}</small></span></div></td><td class="muted">{{ user.email }}</td><td><span class="role" :class="{ admin: user.role?.name === 'admin' }">{{ user.role?.name || "—" }}</span></td><td class="actions"><button type="button" class="edit-button" @click="$emit('edit', user)">Modifier</button><button type="button" class="delete-button" aria-label="Supprimer" @click="$emit('delete', user)">×</button></td></tr>
    </tbody></table>
  </div>
</template>

<style scoped>
.table-wrapper { overflow-x: auto; }.table-wrapper table { width: 100%; border-collapse: collapse; text-align: left; }.table-wrapper th { padding: .7rem .8rem; border-bottom: 1px solid #edf1f6; color: #a0adbd; font-size: .62rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }.table-wrapper td { padding: .82rem .8rem; border-bottom: 1px solid #f0f3f7; white-space: nowrap; }.table-wrapper tr:last-child td { border-bottom: 0; }.user-cell { display: flex; align-items: center; gap: .65rem; }.avatar { display: grid; width: 2rem; height: 2rem; place-items: center; border-radius: .65rem; color: #5278d0; background: #eaf1ff; font-size: .62rem; font-weight: 800; text-transform: uppercase; }.user-cell strong, .user-cell small { display: block; }.user-cell strong { color: #32425a; font-size: .75rem; }.user-cell small { margin-top: .15rem; color: #a4afbd; font-size: .62rem; }.muted { color: #718097; font-size: .72rem; }.role { padding: .28rem .5rem; border-radius: .35rem; color: #4779d8; background: #edf3ff; font-size: .64rem; font-weight: 700; text-transform: capitalize; }.role.admin { color: #7b61c3; background: #f2edff; }.actions-heading { text-align: right; }.actions { display: flex; align-items: center; justify-content: flex-end; gap: .7rem; text-align: right; }.actions button { border: 0; background: none; font-size: .68rem; cursor: pointer; }.edit-button { color: #4f7bdd; font-weight: 700; }.delete-button { display: grid; width: 1.45rem; height: 1.45rem; place-items: center; border-radius: .4rem !important; color: #d77a83; background: #fff1f2 !important; font-size: 1.05rem !important; font-weight: 400; }.state { padding: 2.5rem 1rem; color: #9aa7b7; font-size: .78rem; text-align: center; }
</style>
