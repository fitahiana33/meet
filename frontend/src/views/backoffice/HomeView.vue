<template>
  <section class="page">
    <p class="eyebrow">Backoffice</p>
    <h1>Vue d’ensemble</h1>
    <p class="intro">Gérez les comptes et les rôles de la plateforme depuis cet espace administrateur.</p>

    <div class="cards">
      <RouterLink :to="{ name: 'admin-users' }" class="card">
        <strong>Utilisateurs</strong>
        <span>Créer, modifier et supprimer les comptes.</span>
      </RouterLink>
      <RouterLink :to="{ name: 'admin-roles' }" class="card">
        <strong>Rôles</strong>
        <span>Administrer les rôles disponibles.</span>
      </RouterLink>
    </div>
  </section>
</template>

<style scoped>
.page { max-width: 1000px; margin: 0 auto; }
.eyebrow { margin: 0; color: #2563eb; font-size: .8rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
h1 { margin: .4rem 0; font-size: 2.2rem; }
.intro { color: #64748b; }
.cards { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; margin-top: 2rem; }
.card { display: grid; gap: .5rem; padding: 1.25rem; border: 1px solid #e2e8f0; border-radius: .8rem; color: #0f172a; background: #fff; text-decoration: none; }
.card:hover { border-color: #93c5fd; box-shadow: 0 8px 24px rgb(15 23 42 / 8%); }
.card span { color: #64748b; font-size: .9rem; }
@media (max-width: 640px) { .cards { grid-template-columns: 1fr; } }
</style>
