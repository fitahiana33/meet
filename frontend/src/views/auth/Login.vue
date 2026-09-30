```vue
<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { post } from '@/services/api/LoginApi.js'

const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)

const handleLogin = async () => {
  if (loading.value) return

  loading.value = true

  try {
    const response = await post({
      email: email.value,
      password: password.value
    })

    console.log('Réponse backend:', response.data)

    const token = response.data?.token

    if (token) {
      localStorage.setItem('token', token)

      alert('Connexion acceptée !')

      router.push('/dashboard')
    } else {
      alert(
        response.data?.message ||
        'Aucun token reçu. Veuillez réessayer.'
      )
    }

  } catch (error) {
    console.error('Erreur login:', error)

    if (error.response) {
      console.error('Status:', error.response.status)
      console.error('Réponse:', error.response.data)

      if (
        error.response.status === 401 ||
        error.response.status === 422
      ) {
        alert(
          error.response.data?.message ||
          'Email ou mot de passe incorrect !'
        )
      } else {
        alert('Erreur du serveur. Veuillez réessayer plus tard.')
      }
    } else {
      alert(
        'Impossible de contacter le serveur. Vérifiez que le backend Laravel est démarré.'
      )
    }

  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="login-wrapper">

    <!-- Cercle décoratif -->
    <div class="circle circle-one"></div>
    <div class="circle circle-two"></div>

    <div class="login-card">

      <!-- Logo / icône -->
      <div class="login-icon">
        🔐
      </div>

      <h1>CONNEXION</h1>

      <p class="subtitle">
        Connectez-vous à votre espace
      </p>

      <form @submit.prevent="handleLogin">

        <!-- Email -->
        <div class="form-group">
          <label for="email">
            Adresse e-mail
          </label>

          <div class="input-container">
            <span class="input-icon">✉</span>

            <input
              id="email"
              v-model="email"
              type="email"
              placeholder="Entrer votre email"
              required
              autocomplete="email"
            />
          </div>
        </div>

        <!-- Password -->
        <div class="form-group">
          <label for="password">
            Mot de passe
          </label>

          <div class="input-container">
            <span class="input-icon">🔒</span>

            <input
              id="password"
              v-model="password"
              type="password"
              placeholder="Entrer votre mot de passe"
              required
              autocomplete="current-password"
            />
          </div>
        </div>

        <!-- Bouton -->
        <button
          type="submit"
          :disabled="loading"
        >
          <span v-if="!loading">
            Se connecter
          </span>

          <span v-else class="loading-content">
            <span class="spinner"></span>
            Connexion...
          </span>
        </button>

      </form>

      <p class="footer-text">
        Accédez à votre espace personnel en toute sécurité.
      </p>

    </div>
  </div>
</template>

<style scoped>

* {
  box-sizing: border-box;
}

.login-wrapper {
  position: relative;
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  overflow: hidden;
  background: linear-gradient(
    135deg,
    #eef2ff,
    #f8fafc
  );
  font-family: Arial, sans-serif;
}

/* =========================
   CERCLES ANIMÉS
========================= */

.circle {
  position: absolute;
  border-radius: 50%;
  opacity: 0.5;
  animation: floating 6s ease-in-out infinite;
}

.circle-one {
  width: 280px;
  height: 280px;
  background: #dbeafe;
  top: -100px;
  left: -80px;
}

.circle-two {
  width: 350px;
  height: 350px;
  background: #e0e7ff;
  bottom: -150px;
  right: -100px;
  animation-delay: 1.5s;
}

@keyframes floating {
  0% {
    transform: translateY(0) rotate(0deg);
  }

  50% {
    transform: translateY(-25px) rotate(8deg);
  }

  100% {
    transform: translateY(0) rotate(0deg);
  }
}

/* =========================
   CARTE
========================= */

.login-card {
  position: relative;
  z-index: 2;
  width: 100%;
  max-width: 400px;
  padding: 2.5rem 2.2rem;
  background: rgba(255, 255, 255, 0.95);
  border-radius: 18px;
  box-shadow:
    0 20px 50px rgba(0, 0, 0, 0.10);

  animation: cardAppear 0.8s ease-out;
}

@keyframes cardAppear {
  0% {
    opacity: 0;
    transform: translateY(40px) scale(0.95);
  }

  100% {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

/* =========================
   ICÔNE
========================= */

.login-icon {
  width: 70px;
  height: 70px;
  margin: 0 auto 1rem;

  display: flex;
  justify-content: center;
  align-items: center;

  border-radius: 50%;
  background: #2563eb;

  font-size: 2rem;

  box-shadow:
    0 10px 25px rgba(37, 99, 235, 0.25);

  animation: iconPulse 2s ease-in-out infinite;
}

@keyframes iconPulse {
  0% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.06);
  }

  100% {
    transform: scale(1);
  }
}

/* =========================
   TITRE
========================= */

h1 {
  text-align: center;
  font-size: 1.7rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 0.4rem;
}

.subtitle {
  text-align: center;
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 2rem;
}

/* =========================
   FORMULAIRE
========================= */

.form-group {
  margin-bottom: 1.3rem;
}

label {
  display: block;
  font-size: 0.9rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 0.5rem;
}

.input-container {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  font-size: 1rem;
  opacity: 0.6;
  z-index: 1;
}

input {
  width: 100%;
  padding: 0.85rem 1rem 0.85rem 2.8rem;

  border: 1px solid #d1d5db;
  border-radius: 9px;

  background: #f9fafb;

  font-size: 0.95rem;

  transition:
    border-color 0.3s ease,
    box-shadow 0.3s ease,
    background 0.3s ease,
    transform 0.2s ease;
}

input:hover {
  background: #ffffff;
}

input:focus {
  outline: none;
  background: #ffffff;
  border-color: #2563eb;

  box-shadow:
    0 0 0 4px rgba(37, 99, 235, 0.10);

  transform: translateY(-1px);
}

/* =========================
   BOUTON
========================= */

button {
  width: 100%;
  padding: 0.9rem;

  margin-top: 0.5rem;

  background: #2563eb;
  color: #ffffff;

  border: none;
  border-radius: 9px;

  font-size: 1rem;
  font-weight: 600;

  cursor: pointer;

  transition:
    background 0.3s ease,
    transform 0.2s ease,
    box-shadow 0.3s ease;
}

button:hover:not(:disabled) {
  background: #1d4ed8;

  transform: translateY(-2px);

  box-shadow:
    0 8px 20px rgba(37, 99, 235, 0.25);
}

button:active:not(:disabled) {
  transform: translateY(0);
}

button:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

/* =========================
   LOADING
========================= */

.loading-content {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 10px;
}

.spinner {
  width: 18px;
  height: 18px;

  border: 3px solid rgba(255, 255, 255, 0.4);
  border-top-color: #ffffff;

  border-radius: 50%;

  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* =========================
   FOOTER
========================= */

.footer-text {
  margin-top: 1.5rem;
  text-align: center;

  font-size: 0.8rem;
  color: #9ca3af;
}

/* =========================
   RESPONSIVE
========================= */

@media (max-width: 480px) {

  .login-card {
    width: calc(100% - 30px);
    padding: 2rem 1.5rem;
  }

  .login-icon {
    width: 60px;
    height: 60px;
    font-size: 1.7rem;
  }

  h1 {
    font-size: 1.5rem;
  }
}

</style>

