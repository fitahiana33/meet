<script setup>
import { ref } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { resetPassword } from "@/services/api/authApi";

const route = useRoute();
const router = useRouter();
const password = ref("");
const passwordConfirmation = ref("");
const loading = ref(false);
const error = ref("");
const token = typeof route.query.token === "string" ? route.query.token : "";
const email = typeof route.query.email === "string" ? route.query.email : "";

async function submit() {
  error.value = "";
  loading.value = true;

  try {
    await resetPassword({
      token,
      email,
      password: password.value,
      password_confirmation: passwordConfirmation.value,
    });
    await router.replace({ name: "login", query: { passwordReset: "success" } });
  } catch (requestError) {
    error.value = requestError.response?.data?.errors?.email?.[0]
      || requestError.response?.data?.errors?.password?.[0]
      || requestError.response?.data?.message
      || "Impossible de modifier le mot de passe. Demandez un nouveau lien.";
  } finally {
    loading.value = false;
  }
}
</script>

<template>
  <main class="reset-page">
    <section class="reset-card">
      <div class="brand"><span class="brand-symbol">M</span><span>Meeting<span class="brand-accent">Platform</span></span></div>
      <p class="form-kicker">RÉINITIALISATION</p>
      <h1>Choisir un nouveau mot de passe</h1>
      <p class="description">Saisissez votre nouveau mot de passe pour le compte <strong>{{ email || "associé au lien" }}</strong>.</p>

      <div v-if="!token || !email" class="error-message" role="alert">
        Ce lien est incomplet. Demandez un nouveau lien de réinitialisation.
      </div>
      <div v-else>
        <div v-if="error" class="error-message" role="alert">{{ error }}</div>
        <form @submit.prevent="submit">
          <label for="new-password">Nouveau mot de passe
            <input id="new-password" v-model="password" type="password" autocomplete="new-password" minlength="8" required />
          </label>
          <label for="password-confirmation">Confirmer le mot de passe
            <input id="password-confirmation" v-model="passwordConfirmation" type="password" autocomplete="new-password" minlength="8" required />
          </label>
          <button type="submit" :disabled="loading">{{ loading ? "Enregistrement…" : "Modifier le mot de passe" }}</button>
        </form>
      </div>

      <RouterLink class="back-link" :to="{ name: 'login' }">Retour à la connexion</RouterLink>
    </section>
  </main>
</template>

<style scoped>
.reset-page { display: grid; min-height: 100vh; place-items: center; padding: 1.5rem; color: #26364e; background: #f4f7fb; }
.reset-card { width: min(100%, 440px); padding: 2.5rem; border: 1px solid #e4eaf2; border-radius: 1rem; background: #fff; box-shadow: 0 24px 70px rgb(34 57 91 / 10%); }
.brand { display: flex; align-items: center; gap: .65rem; margin-bottom: 2.5rem; color: #182840; font: 800 1rem "Manrope", sans-serif; }
.brand-symbol { display: grid; width: 2rem; height: 2rem; place-items: center; border-radius: .55rem; color: #fff; background: #366bd7; }
.brand-accent { color: #4779dc; }
.form-kicker { color: #4b7bdc; font-size: .65rem; font-weight: 800; letter-spacing: .16em; }
h1 { margin: .6rem 0; font-size: 1.7rem; letter-spacing: -.05em; }
.description { margin-bottom: 1.5rem; color: #8491a3; font-size: .85rem; line-height: 1.6; overflow-wrap: anywhere; }
form { display: grid; gap: 1rem; }
label { display: grid; gap: .45rem; color: #40516a; font-size: .75rem; font-weight: 700; }
input { min-height: 46px; padding: 0 .8rem; border: 1px solid #e3e9f1; border-radius: .55rem; color: #293a53; font: inherit; }
input:focus { border-color: #79a0f1; outline: 3px solid rgb(74 121 220 / 10%); }
button { min-height: 48px; margin-top: .3rem; border: 0; border-radius: .55rem; color: #fff; background: linear-gradient(100deg, #366bd7, #5a82e4); font: inherit; font-size: .78rem; font-weight: 800; cursor: pointer; }
button:disabled { opacity: .65; cursor: wait; }
.error-message { margin-bottom: 1rem; padding: .75rem; border: 1px solid #ffd7db; border-radius: .55rem; color: #aa3e4a; background: #fff3f4; font-size: .75rem; line-height: 1.5; }
.back-link { display: inline-block; margin-top: 1.5rem; color: #557bd0; font-size: .78rem; font-weight: 700; text-decoration: none; }
@media (max-width: 480px) { .reset-card { padding: 1.5rem; } }
</style>
