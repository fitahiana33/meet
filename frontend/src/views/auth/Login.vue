<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import { useAuthStore } from "@/stores/authStore";

const router = useRouter();
const authStore = useAuthStore();
const email = ref("");
const password = ref("");
const localError = ref("");

async function submit() {
  localError.value = "";

  try {
    const user = await authStore.authenticate({ email: email.value, password: password.value });
    router.replace(user.role?.name === "admin" ? "/admin" : "/home");
  } catch {
    localError.value = authStore.error || "Email ou mot de passe incorrect.";
  }
}
</script>

<template>
  <main class="login-page">
    <section class="login-card" aria-labelledby="login-title">
      <p class="eyebrow">Meeting Platform</p>
      <h1 id="login-title">Connexion</h1>
      <p class="subtitle">Accédez à votre espace personnel.</p>

      <div v-if="localError" class="alert" role="alert">{{ localError }}</div>

      <form @submit.prevent="submit">
        <label for="email">Adresse e-mail</label>
        <input id="email" v-model="email" type="email" autocomplete="email" required />

        <label for="password">Mot de passe</label>
        <input id="password" v-model="password" type="password" autocomplete="current-password" required />

        <button type="submit" :disabled="authStore.loading">
          {{ authStore.loading ? "Connexion…" : "Se connecter" }}
        </button>
      </form>
    </section>
  </main>
</template>

<style scoped>
.login-page {
  display: grid;
  min-height: 100vh;
  place-items: center;
  padding: 1rem;
  box-sizing: border-box;
  background: linear-gradient(135deg, #eff6ff, #f8fafc);
}

.login-card {
  width: min(100%, 400px);
  padding: 2.4rem;
  border: 1px solid #e2e8f0;
  border-radius: 18px;
  background: #fff;
  box-shadow: 0 20px 50px rgb(15 23 42 / 10%);
}

.eyebrow {
  margin: 0 0 0.5rem;
  color: #2563eb;
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.1em;
  text-transform: uppercase;
}

h1 { margin: 0; color: #0f172a; }
.subtitle { margin: 0.5rem 0 1.5rem; color: #64748b; }
form { display: grid; gap: 0.55rem; }
label { margin-top: 0.6rem; color: #334155; font-size: 0.9rem; font-weight: 700; }
input { width: 100%; box-sizing: border-box; padding: 0.8rem 0.9rem; border: 1px solid #cbd5e1; border-radius: 8px; font: inherit; }
input:focus { outline: 2px solid #bfdbfe; border-color: #2563eb; }
button { margin-top: 1rem; padding: 0.85rem; border: 0; border-radius: 8px; color: #fff; background: #2563eb; font: inherit; font-weight: 700; cursor: pointer; }
button:disabled { cursor: wait; opacity: 0.65; }
.alert { padding: 0.8rem 1rem; border-radius: 8px; color: #991b1b; background: #fef2f2; }
</style>
