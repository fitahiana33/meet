<script setup>
import { useAuthStore } from "@/stores/authStore";

const authStore = useAuthStore();
</script>

<template>
  <section class="front-page">
    <div class="welcome-banner">
      <div class="welcome-copy"><p class="eyebrow">Votre espace personnel</p><h2>Bonjour, {{ authStore.user?.first_name || "à vous" }} <span>✦</span></h2><p>Ravi de vous revoir. Retrouvez ici les informations essentielles de votre espace.</p></div>
      <div class="welcome-art" aria-hidden="true"><div class="art-ring ring-one" /><div class="art-ring ring-two" /><div class="art-orb">M</div></div>
    </div>

    <div class="section-heading"><div><p class="eyebrow">Aujourd’hui</p><h3>Votre espace en un coup d’œil</h3></div><span class="date-pill">● Connecté maintenant</span></div>
    <div class="summary-grid">
      <article class="summary-card accent-blue"><div class="card-icon">↗</div><div><strong>Espace actif</strong><p>Votre compte est prêt à l’emploi.</p></div><span class="card-arrow">→</span></article>
      <article class="summary-card accent-green"><div class="card-icon">✓</div><div><strong>Compte sécurisé</strong><p>Vos informations sont protégées.</p></div><span class="card-arrow">→</span></article>
      <article class="summary-card accent-purple"><div class="card-icon">◇</div><div><strong>Profil personnel</strong><p>{{ authStore.displayName || "Votre profil" }}</p></div><span class="card-arrow">→</span></article>
    </div>

    <div class="next-card"><div class="next-icon">✦</div><div><strong>Bienvenue sur Meeting Platform</strong><p>Votre espace frontoffice évoluera avec les prochaines fonctionnalités de la plateforme.</p></div><span class="next-decoration">•••</span></div>
  </section>
</template>

<style scoped>
.front-page { max-width: 1180px; margin: 0 auto; }.welcome-banner { position: relative; display: flex; min-height: 225px; align-items: center; justify-content: space-between; overflow: hidden; padding: 2.4rem 3rem; border-radius: 1.25rem; color: #fff; background: linear-gradient(110deg, #2f5fc7, #4d7de1 68%, #668fe8); box-shadow: 0 15px 35px rgb(56 105 215 / 17%); }.welcome-copy { position: relative; z-index: 1; max-width: 590px; }.eyebrow { margin: 0 0 .55rem; color: #7297e8; font-size: .68rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }.welcome-banner .eyebrow { color: #bfd4ff; }.welcome-copy h2 { margin: 0; color: #fff; font-size: clamp(1.7rem, 3vw, 2.45rem); letter-spacing: -.06em; }.welcome-copy h2 span { color: #fbd478; font-size: 1.25rem; }.welcome-copy p:last-child { max-width: 440px; margin: .85rem 0 0; color: #d2e0ff; font-size: .84rem; line-height: 1.6; }.welcome-art { position: relative; width: 240px; height: 180px; flex: 0 0 auto; }.art-ring { position: absolute; border: 1px solid rgb(255 255 255 / 25%); border-radius: 50%; }.ring-one { inset: 5px -25px 5px 25px; }.ring-two { inset: 27px 6px 27px 45px; border-style: dashed; }.art-orb { position: absolute; top: 50%; left: 50%; display: grid; width: 4.5rem; height: 4.5rem; place-items: center; transform: translate(-50%, -50%); border: 1px solid rgb(255 255 255 / 40%); border-radius: 1.4rem; color: #fff; background: rgb(255 255 255 / 14%); font: 800 2rem "Manrope", sans-serif; box-shadow: 0 15px 35px rgb(15 49 126 / 18%); }.section-heading { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; margin: 2.2rem 0 1rem; }.section-heading h3 { margin: 0; color: #1c2d46; font-size: 1.16rem; letter-spacing: -.04em; }.date-pill { padding: .42rem .7rem; border-radius: 999px; color: #34a276; background: #eaf9f2; font-size: .68rem; font-weight: 700; }.date-pill:first-letter { color: #4ec794; }.summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 1rem; }.summary-card { position: relative; display: flex; min-height: 125px; align-items: flex-start; gap: .8rem; padding: 1.15rem; overflow: hidden; border: 1px solid #e9eef6; border-radius: .9rem; background: #fff; box-shadow: 0 5px 15px rgb(30 52 84 / 3%); }.card-icon { display: grid; width: 2.2rem; height: 2.2rem; flex: 0 0 auto; place-items: center; border-radius: .7rem; font-weight: 800; }.accent-blue .card-icon { color: #4b79df; background: #eaf1ff; }.accent-green .card-icon { color: #32a37a; background: #e8f8f0; }.accent-purple .card-icon { color: #8763cb; background: #f2ecff; }.summary-card strong { display: block; margin-top: .15rem; color: #25364f; font-size: .82rem; }.summary-card p { margin: .35rem 0 0; color: #91a0b2; font-size: .7rem; line-height: 1.45; }.card-arrow { position: absolute; right: 1rem; bottom: .8rem; color: #b5c0cf; }.next-card { display: flex; align-items: center; gap: 1rem; margin-top: 1.3rem; padding: 1.2rem 1.35rem; border: 1px solid #e9eef6; border-radius: .9rem; background: #fff; }.next-icon { display: grid; width: 2.4rem; height: 2.4rem; flex: 0 0 auto; place-items: center; border-radius: .75rem; color: #4d7ee2; background: #eef4ff; }.next-card strong { color: #25364f; font-size: .8rem; }.next-card p { margin: .3rem 0 0; color: #8c9bad; font-size: .72rem; }.next-decoration { margin-left: auto; color: #c6d0df; letter-spacing: .2em; }
@media (max-width: 700px) { .welcome-banner { min-height: 205px; padding: 1.6rem; }.welcome-art { position: absolute; right: -45px; opacity: .65; }.section-heading { display: block; }.date-pill { display: inline-block; margin-top: .8rem; }.summary-grid { grid-template-columns: 1fr; } }
</style>
