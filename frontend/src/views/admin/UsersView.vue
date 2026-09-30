<script setup>
import { onMounted } from "vue";
import { RouterLink, useRouter } from "vue-router";
import UserTable from "@/components/user/UserTable.vue";
import { useUserStore } from "@/stores/userStore";

const router = useRouter();
const userStore = useUserStore();
onMounted(() => userStore.fetchUsers());
function editUser(user) { router.push({ name: "admin-user-edit", params: { id: user.id } }); }
async function removeUser(user) {
  if (window.confirm(`Supprimer l’utilisateur ${user.first_name} ${user.last_name} ?`)) await userStore.deleteUser(user.id);
}
</script>

<template>
  <section class="content-page">
    <div class="page-toolbar"><div><p class="page-eyebrow">Administration / Comptes</p><h2>Utilisateurs</h2><p class="page-description">Gérez les membres et leurs niveaux d’accès depuis un seul endroit.</p></div><RouterLink :to="{ name: 'admin-user-create' }" class="primary-button"><span>＋</span> Nouvel utilisateur</RouterLink></div>
    <div class="stats-strip"><div><strong>{{ userStore.users.length }}</strong><span>comptes affichés</span></div><div class="status-copy"><span class="status-dot" /> Données synchronisées</div></div>
    <div v-if="userStore.error" class="alert" role="alert"><span>!</span>{{ userStore.error }}</div>
    <div class="table-card"><div class="table-card-head"><div><h3>Répertoire des utilisateurs</h3><p>Liste des comptes enregistrés sur la plateforme.</p></div><span class="table-label">Tous les comptes</span></div><UserTable :users="userStore.users" :loading="userStore.loading" @edit="editUser" @delete="removeUser" /></div>
  </section>
</template>

<style scoped>
.content-page { max-width: 1180px; margin: 0 auto; }.page-toolbar { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; }.page-eyebrow { margin: 0 0 .55rem; color: #6d91e4; font-size: .68rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }.page-toolbar h2 { margin: 0; color: #1c2d46; font-size: 2rem; letter-spacing: -.06em; }.page-description { margin: .6rem 0 0; color: #8b99ab; font-size: .8rem; }.primary-button { display: inline-flex; align-items: center; gap: .4rem; padding: .75rem 1rem; border-radius: .55rem; color: #fff; background: linear-gradient(100deg, #3e70da, #5c82e4); box-shadow: 0 8px 18px rgb(59 105 218 / 17%); font-size: .76rem; font-weight: 700; text-decoration: none; }.primary-button span { font-size: 1.1rem; font-weight: 400; }.stats-strip { display: flex; align-items: center; justify-content: space-between; margin: 1.7rem 0 1rem; padding: 1rem 1.2rem; border: 1px solid #e8eef6; border-radius: .8rem; background: #fff; }.stats-strip strong { margin-right: .5rem; color: #253650; font: 800 1.25rem "Manrope", sans-serif; }.stats-strip span:not(.status-dot) { color: #92a0b1; font-size: .7rem; }.status-copy { display: flex; align-items: center; gap: .4rem; color: #3a9f78; font-size: .7rem; font-weight: 700; }.status-dot { display: inline-block; width: .45rem; height: .45rem; border-radius: 50%; background: #52c796; box-shadow: 0 0 0 3px rgb(82 199 150 / 13%); }.alert { display: flex; align-items: center; gap: .55rem; margin-bottom: 1rem; padding: .75rem .85rem; border: 1px solid #ffd7db; border-radius: .6rem; color: #b33e4b; background: #fff3f4; font-size: .76rem; }.alert span { display: grid; width: 1.1rem; height: 1.1rem; place-items: center; border-radius: 50%; color: #fff; background: #e36c76; font-weight: 800; }.table-card { padding: 1.25rem; border: 1px solid #e8eef6; border-radius: .9rem; background: #fff; box-shadow: 0 5px 15px rgb(30 52 84 / 3%); }.table-card-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1rem; }.table-card-head h3 { margin: 0; color: #253650; font-size: .9rem; }.table-card-head p { margin: .3rem 0 0; color: #9aa7b7; font-size: .7rem; }.table-label { padding: .35rem .55rem; border-radius: .4rem; color: #557bd0; background: #eff4ff; font-size: .65rem; font-weight: 700; white-space: nowrap; }
@media (max-width: 650px) { .page-toolbar { display: block; }.primary-button { margin-top: 1.2rem; }.stats-strip { align-items: flex-start; flex-direction: column; gap: .7rem; }.table-card { padding: .8rem; }.table-card-head { display: block; }.table-label { display: inline-block; margin-top: .7rem; } }
</style>
