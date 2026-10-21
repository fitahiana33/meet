<script setup>
import { ref } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import { useAuthStore } from "@/stores/authStore";
import { requestPasswordReset } from "@/services/api/authApi";

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();
const email = ref("");
const password = ref("");
const showPassword = ref(false);
const localError = ref("");
const resetEmail = ref("");
const resetLoading = ref(false);
const resetMessage = ref("");
const resetError = ref("");

async function submit() {
  localError.value = "";

  try {
    const user = await authStore.authenticate({ email: email.value, password: password.value });
    const fallback = authStore.isAdmin ? "/admin" : "/home";
    const redirect = typeof route.query.redirect === "string" ? route.query.redirect : fallback;
    router.replace(redirect);
  } catch {
    localError.value = authStore.error || "L’adresse e-mail ou le mot de passe est incorrect.";
  }
}

async function requestResetLink() {
  resetError.value = "";
  resetMessage.value = "";
  resetLoading.value = true;

  try {
    const response = await requestPasswordReset(resetEmail.value);
    resetMessage.value = response.data.message;
  } catch (requestError) {
    resetError.value = requestError.response?.data?.message
      || "Impossible d’envoyer la demande. Réessayez plus tard.";
  } finally {
    resetLoading.value = false;
  }
}
</script>

<template>
  <main class="login-page">
    <header class="login-header">
      <div class="brand"><span class="brand-symbol">M</span><span>Meeting<span class="brand-accent">Platform</span></span></div>
    </header>

    <section class="login-layout">
      <aside class="intro-panel">
        <div class="intro-content">
          <span class="intro-label">PLATEFORME COLLABORATIVE</span>
          <h1>Tout votre travail.<br /><strong>Un seul espace.</strong></h1>
          <p>Connectez-vous pour retrouver vos équipes, vos échanges et votre espace de travail.</p>
        </div>
        <div class="workspace-preview" aria-hidden="true">
          <div class="preview-top"><span class="preview-logo">M</span><span class="preview-title">Votre espace</span><span class="preview-menu">•••</span></div>
          <div class="preview-body"><div class="preview-sidebar"><i /><i /><i /><i /></div><div class="preview-main"><span class="preview-line short" /><span class="preview-line" /><div class="preview-cards"><i /><i /><i /></div><span class="preview-line small" /></div></div>
        </div>
      </aside>

      <section class="form-panel" aria-labelledby="login-title">
        <div class="form-content">
          <div class="form-heading"><span class="form-kicker">{{ route.meta.passwordReset ? "MOT DE PASSE OUBLIÉ" : "BIENVENUE" }}</span><h2 id="login-title">{{ route.meta.passwordReset ? "Recevoir un lien de réinitialisation" : "Connectez-vous à votre espace" }}</h2><p>{{ route.meta.passwordReset ? "Saisissez l’adresse e-mail associée à votre compte." : "Utilisez vos identifiants pour continuer." }}</p></div>

          <template v-if="route.meta.passwordReset">
            <div v-if="resetMessage" class="success-message" role="status">{{ resetMessage }}</div>
            <div v-if="resetError" class="error-message" role="alert"><span class="error-icon">!</span><span>{{ resetError }}</span></div>
            <form @submit.prevent="requestResetLink">
              <label for="reset-email">Adresse e-mail<span class="input-wrap"><span class="field-icon">@</span><input id="reset-email" v-model.trim="resetEmail" type="email" autocomplete="email" placeholder="nom@entreprise.com" required /></span></label>
              <button type="submit" class="submit-button" :disabled="resetLoading">{{ resetLoading ? "Envoi…" : "Envoyer le lien" }}<span aria-hidden="true">→</span></button>
            </form>
            <RouterLink class="forgot-password-link" :to="{ name: 'login' }">Retour à la connexion</RouterLink>
          </template>
          <template v-else>
            <div v-if="route.query.passwordReset === 'success'" class="success-message" role="status">Votre mot de passe a été modifié. Vous pouvez vous connecter.</div>
            <div v-if="localError" class="error-message" role="alert"><span class="error-icon">!</span><span>{{ localError }}</span></div>

            <form @submit.prevent="submit">
              <label for="email">Adresse e-mail<span class="input-wrap"><span class="field-icon">@</span><input id="email" v-model.trim="email" type="email" autocomplete="email" placeholder="nom@entreprise.com" required /></span></label>
              <label for="password">Mot de passe<span class="input-wrap"><span class="field-icon lock-icon">▣</span><input id="password" v-model="password" :type="showPassword ? 'text' : 'password'" autocomplete="current-password" placeholder="Votre mot de passe" required /><button type="button" class="visibility-button" :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" @click="showPassword = !showPassword">{{ showPassword ? "Masquer" : "Afficher" }}</button></span></label>
              <button type="submit" class="submit-button" :disabled="authStore.loading">{{ authStore.loading ? "Connexion…" : "Se connecter" }}<span aria-hidden="true">→</span></button>
            </form>
            <RouterLink class="forgot-password-link" :to="{ name: 'forgot-password' }">Mot de passe oublié ?</RouterLink>
          </template>
        </div>
      </section>
    </section>

    <footer class="login-footer"><span>© 2026 Meeting Platform. Tous droits réservés.</span><span><a href="#" @click.prevent>Confidentialité</a><a href="#" @click.prevent>Conditions d’utilisation</a></span></footer>
  </main>
</template>

<style>
.login-page { min-height: 100vh; padding: 0 4.5vw; color: #26364e; background: #f4f7fb; }.login-header, .login-footer { display: flex; align-items: center; justify-content: space-between; max-width: 1240px; margin: 0 auto; }.login-header { min-height: 86px; }.brand { display: flex; align-items: center; gap: .65rem; color: #182840; font: 800 1rem "Manrope", sans-serif; letter-spacing: -.035em; }.brand-symbol { display: grid; width: 2rem; height: 2rem; place-items: center; border-radius: .55rem; color: #fff; background: #366bd7; font-size: .9rem; box-shadow: 0 6px 14px rgb(54 107 215 / 20%); }.brand-accent { color: #4779dc; }.help-link { color: #8b99ac; font-size: .72rem; text-decoration: none; }.help-link span { margin-left: .3rem; color: #4778d5; font-weight: 700; }.login-layout { display: grid; min-height: min(680px, calc(100vh - 170px)); max-width: 1240px; margin: 0 auto; grid-template-columns: .92fr 1.08fr; overflow: hidden; border: 1px solid #e4eaf2; border-radius: 1.15rem; background: #fff; box-shadow: 0 24px 70px rgb(34 57 91 / 10%); }.intro-panel { position: relative; display: flex; flex-direction: column; overflow: hidden; padding: 3.2rem 3.3rem 2rem; color: #fff; background: linear-gradient(145deg, #12213a, #1c3867 68%, #2555a3); }.intro-panel::after { position: absolute; right: -9rem; bottom: -11rem; width: 28rem; height: 28rem; border: 1px solid rgb(144 181 255 / 16%); border-radius: 50%; box-shadow: 0 0 0 42px rgb(144 181 255 / 4%), 0 0 0 84px rgb(144 181 255 / 2%); content: ""; }.intro-content { position: relative; z-index: 1; }.intro-label { color: #9dbcf6; font-size: .62rem; font-weight: 800; letter-spacing: .18em; }.intro-content h1 { margin: 1.2rem 0 0; color: #fff; font-size: clamp(2rem, 3.4vw, 3.3rem); line-height: 1.07; letter-spacing: -.07em; }.intro-content h1 strong { color: #84acff; font-weight: 800; }.intro-content p { max-width: 340px; margin: 1.2rem 0 0; color: #afc0dd; font-size: .82rem; line-height: 1.7; }.workspace-preview { position: relative; z-index: 1; width: min(100%, 390px); margin: auto auto 2rem; padding: .8rem; border: 1px solid rgb(173 201 255 / 23%); border-radius: .85rem; background: rgb(255 255 255 / 9%); box-shadow: 0 20px 35px rgb(7 24 55 / 17%); transform: rotate(-2deg); }.preview-top { display: flex; align-items: center; gap: .45rem; padding: .15rem .2rem .7rem; }.preview-logo { display: grid; width: 1.3rem; height: 1.3rem; place-items: center; border-radius: .35rem; color: #fff; background: #5f8ff0; font-size: .6rem; font-weight: 800; }.preview-title { color: #d7e4ff; font-size: .6rem; font-weight: 700; }.preview-menu { margin-left: auto; color: #8ea9dc; font-size: .65rem; }.preview-body { display: flex; min-height: 125px; gap: .65rem; padding: .7rem; border-radius: .55rem; background: rgb(12 27 54 / 74%); }.preview-sidebar { display: grid; width: 1.3rem; align-content: start; gap: .55rem; padding-top: .2rem; }.preview-sidebar i { display: block; width: .9rem; height: .35rem; border-radius: .15rem; background: #4c75c3; }.preview-sidebar i:first-child { background: #86b0ff; }.preview-main { flex: 1; padding: .1rem; }.preview-line { display: block; width: 58%; height: .4rem; margin-bottom: .45rem; border-radius: .2rem; background: #4167ad; }.preview-line.short { width: 34%; height: .28rem; background: #84aaff; }.preview-line.small { width: 42%; height: .25rem; margin-top: 1rem; background: #375b9e; }.preview-cards { display: grid; grid-template-columns: repeat(3, 1fr); gap: .4rem; margin-top: .9rem; }.preview-cards i { display: block; height: 3.4rem; border: 1px solid rgb(126 165 240 / 22%); border-radius: .35rem; background: linear-gradient(150deg, #254b8c, #1a376c); }.preview-cards i:nth-child(2) { background: linear-gradient(150deg, #36588f, #253f76); }.preview-cards i:nth-child(3) { background: linear-gradient(150deg, #285f81, #1c456f); }.intro-footer { position: relative; z-index: 1; display: flex; justify-content: space-between; color: #83a0cf; font-size: .62rem; }.intro-footer span:first-child { color: #72d2af; }.intro-footer i { display: inline-block; width: .4rem; height: .4rem; margin-right: .3rem; border-radius: 50%; background: #60d0a4; }
.form-panel { display: grid; place-items: center; padding: 3rem 4rem; background: #fff; }.form-content { width: min(100%, 390px); }.form-heading { margin-bottom: 2rem; }.form-kicker { color: #4b7bdc; font-size: .62rem; font-weight: 800; letter-spacing: .18em; }.form-heading h2 { margin: .75rem 0 .55rem; color: #1c2d46; font-size: clamp(1.55rem, 2.6vw, 2.05rem); line-height: 1.15; letter-spacing: -.06em; }.form-heading p { margin: 0; color: #8c9aab; font-size: .78rem; }.error-message { display: flex; align-items: center; gap: .55rem; margin-bottom: 1.1rem; padding: .7rem .8rem; border: 1px solid #f6d4d8; border-radius: .55rem; color: #ad4853; background: #fff5f5; font-size: .72rem; line-height: 1.4; }.error-icon { display: grid; width: 1.05rem; height: 1.05rem; flex: 0 0 auto; place-items: center; border-radius: 50%; color: #fff; background: #db6974; font-size: .65rem; font-weight: 800; }form { display: grid; gap: 1.1rem; }form > label { display: grid; gap: .45rem; color: #4a5b72; font-size: .7rem; font-weight: 700; }.input-wrap { position: relative; display: block; }.field-icon { position: absolute; top: 50%; left: .85rem; transform: translateY(-50%); color: #9aabc1; font-size: .78rem; font-weight: 700; }.lock-icon { font-size: .67rem; }.input-wrap input { width: 100%; height: 2.8rem; padding: 0 2.6rem; border: 1px solid #dce4ef; border-radius: .52rem; outline: none; color: #2f4058; background: #fff; font-size: .77rem; transition: .2s; }.input-wrap input::placeholder { color: #b0bccb; }.input-wrap input:focus { border-color: #6d95eb; box-shadow: 0 0 0 4px rgb(79 120 232 / 10%); }.visibility-button { position: absolute; top: 50%; right: .75rem; padding: 0; transform: translateY(-50%); border: 0; color: #5c82d4; background: none; font-size: .62rem; font-weight: 700; cursor: pointer; }.form-options { display: flex; align-items: center; justify-content: space-between; gap: .5rem; margin-top: -.3rem; font-size: .68rem; }.form-options a { color: #557ed5; font-weight: 700; text-decoration: none; }.remember { display: flex; align-items: center; gap: .35rem; color: #8d9bad; }.remember input { width: .85rem; height: .85rem; accent-color: #4e7de0; }.submit-button { display: flex; align-items: center; justify-content: space-between; height: 2.85rem; margin-top: .35rem; padding: 0 1rem 0 1.1rem; border: 0; border-radius: .52rem; color: #fff; background: #3f72dc; box-shadow: 0 9px 18px rgb(63 114 220 / 18%); font-size: .76rem; font-weight: 700; cursor: pointer; transition: .2s; }.submit-button span { font-size: 1.1rem; font-weight: 400; }.submit-button:hover { background: #3265d0; box-shadow: 0 11px 22px rgb(63 114 220 / 25%); transform: translateY(-1px); }.submit-button:disabled { cursor: wait; opacity: .6; transform: none; }.security-note { display: flex; align-items: center; justify-content: center; gap: .35rem; margin: 2rem 0 0; color: #a1adbd; font-size: .65rem; }.security-note span { display: grid; width: .95rem; height: .95rem; place-items: center; border-radius: 50%; color: #fff; background: #5cbd96; font-size: .58rem; font-weight: 800; }.login-footer { min-height: 64px; color: #9aa7b7; font-size: .64rem; }.login-footer span:last-child { display: flex; gap: 1.2rem; }.login-footer a { color: #7587a2; text-decoration: none; }.login-footer a:hover, .help-link:hover { color: #4778d5; }
.success-message { margin-bottom: 1rem; padding: .75rem .8rem; border: 1px solid #ccebdc; border-radius: .55rem; color: #26754e; background: #effaf4; font-size: .72rem; line-height: 1.5; }.forgot-password-link { display: inline-block; margin-top: 1rem; color: #557ed5; font-size: .7rem; font-weight: 700; text-decoration: none; }.forgot-password-link:hover { color: #3265d0; text-decoration: underline; }
@media (max-width: 900px) { .login-page { padding: 0 1.2rem; }.login-layout { display: block; min-height: auto; margin-bottom: 2rem; }.intro-panel { min-height: 260px; padding: 2rem; }.intro-content h1 { margin-top: .75rem; font-size: 2rem; }.intro-content p { margin-top: .8rem; }.workspace-preview { display: none; }.intro-footer { margin-top: 2rem; }.form-panel { padding: 2.5rem 1.5rem 3rem; }.login-footer { display: block; padding: 1rem 0; line-height: 2; }.login-footer span:last-child { gap: 1rem; } }
@media (max-width: 520px) { .login-header { min-height: 72px; }.help-link { display: none; }.intro-panel { min-height: 245px; padding: 1.8rem 1.5rem; }.form-panel { padding: 2.2rem 1.1rem 2.7rem; }.login-footer span:last-child { display: flex; flex-wrap: wrap; } }
</style>
