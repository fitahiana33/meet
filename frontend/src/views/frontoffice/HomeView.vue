<script setup>
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/authStore";

const router = useRouter();
const authStore = useAuthStore();

async function signOut() {
  await authStore.signOut();
  router.replace({ name: "login" });
}
</script>

<template>
  <main class="frontoffice-page">
    <section class="welcome-card">
      <p class="eyebrow">Espace client</p>
      <h1>Bienvenue {{ authStore.displayName }} !</h1>
      <p>Vous êtes connecté à votre espace frontoffice.</p>
      <button type="button" @click="signOut">Se déconnecter</button>
    </section>
  </main>
</template>

<style scoped>
.frontoffice-page { min-height: 100vh; display: grid; place-items: center; padding: 1rem; box-sizing: border-box; color: #0f172a; background: linear-gradient(135deg, #eff6ff, #f8fafc); text-align: center; }
.welcome-card { width: min(100%, 620px); padding: 3rem 2rem; border: 1px solid #dbeafe; border-radius: 1.25rem; background: #fff; box-shadow: 0 20px 50px rgb(15 23 42 / 10%); }
.eyebrow { margin: 0 0 .75rem; color: #2563eb; font-size: .8rem; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
h1 { margin: 0; color: #0f172a; font-size: clamp(2rem, 5vw, 3rem); }
p:not(.eyebrow) { margin: 1rem 0 0; color: #64748b; }
button { margin-top: 2rem; padding: .8rem 1.2rem; border: 0; border-radius: .5rem; color: #fff; background: #2563eb; font: inherit; font-weight: 700; cursor: pointer; }
</style>
