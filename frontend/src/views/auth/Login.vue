<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { post } from '@/services/api/LoginApi.js'

// Router mba hahafahana rediriger rehefa connecter
const router = useRouter()

// Variables reactive
const email = ref('')
const password = ref('')

// Fonction handleLogin ampiharana 'async'
const handleLogin = async () => {
  try {
    // 1. Alefa amin'ny API ny credentials
    const response = await post({
      email: email.value,
      password: password.value
    })

    console.log('Reponse backend:', response.data)

    // 2. Raha misy token avy amin'ny API dia tehirizina (optionnel)
    if (response.data.token) {
      localStorage.setItem('token', response.data.token)
    }

    alert('Connexion acceptée!')

    // 3. Redirection mankany amin'ny dashboard na accueil
    router.push('/dashboard') // Soloina araka ny route-nao
    
  } catch (error) {
    console.error('Erreur login:', error)
    alert(error.response?.data?.message || 'Email ou mot de passe incorrect !')
  }
}
</script>

<template>
  <div class="login-wrapper">
    <div class="login-card">
      <h1>CONNEXION</h1>

      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label for="email">Adresse e-mail</label>
          <input 
            id="email"
            v-model="email" 
            type="email" 
            placeholder="Entrer votre email" 
            required 
          />
        </div>

        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input 
            id="password"
            v-model="password" 
            type="password" 
            placeholder="Entrer votre password" 
            required 
          />
        </div>

        <button type="submit">Login</button>
      </form>
    </div>
  </div>
</template>

<style scoped>
.login-wrapper {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background-color: #f3f4f6;
  font-family: Arial, sans-serif;
}

.login-card {
  width: 100%;
  max-width: 380px;
  padding: 2.5rem 2rem;
  background-color: #ffffff;
  border-radius: 10px;
  box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
}

h1 {
  text-align: center;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1f2937;
  margin-bottom: 1.5rem;
}

.form-group {
  margin-bottom: 1.25rem;
}

label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  color: #4b5563;
  margin-bottom: 0.4rem;
}

input {
  width: 100%;
  padding: 0.75rem 1rem;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  font-size: 0.95rem;
  box-sizing: border-box;
}

input:focus {
  outline: none;
  border-color: #2563eb;
}

button {
  width: 100%;
  padding: 0.75rem;
  margin-top: 0.5rem;
  background-color: #2563eb;
  color: #ffffff;
  border: none;
  border-radius: 6px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
}

button:hover {
  background-color: #1d4ed8;
}
</style>