<script setup>
import { computed, reactive, watch } from "vue";

const props = defineProps({
  user: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: null,
  },
  roles: {
    type: Array,
    default: () => [],
  },
});

const emit = defineEmits(["submit", "cancel"]);

const form = reactive({
  first_name: "",
  last_name: "",
  email: "",
  password: "",
  password_confirmation: "",
  role_id: "",
});

const isEditing = computed(() => Boolean(props.user?.id));

function fillForm(user) {
  form.first_name = user?.first_name ?? "";
  form.last_name = user?.last_name ?? "";
  form.email = user?.email ?? "";
  form.password = "";
  form.password_confirmation = "";
  form.role_id = user?.role?.id ?? "";
}

watch(() => props.user, fillForm, { immediate: true });

function submit() {
  const payload = {
    first_name: form.first_name.trim(),
    last_name: form.last_name.trim(),
    email: form.email.trim(),
    role_id: Number(form.role_id),
  };

  if (form.password) {
    payload.password = form.password;
    payload.password_confirmation = form.password_confirmation;
  }

  emit("submit", payload);
}
</script>

<template>
  <form class="user-form" @submit.prevent="submit">
    <div v-if="error" class="form-alert" role="alert">{{ error }}</div>

    <div class="field-group">
      <label for="user-first-name">Prénom</label>
      <input id="user-first-name" v-model="form.first_name" type="text" required autocomplete="given-name" />
    </div>

    <div class="field-group">
      <label for="user-last-name">Nom</label>
      <input id="user-last-name" v-model="form.last_name" type="text" required autocomplete="family-name" />
    </div>

    <div class="field-group">
      <label for="user-email">Email</label>
      <input id="user-email" v-model="form.email" type="email" required autocomplete="email" />
    </div>

    <div class="field-group">
      <label for="user-password">
        Mot de passe
        <span v-if="isEditing" class="field-hint">(laisser vide pour conserver l’actuel)</span>
      </label>
      <input
        id="user-password"
        v-model="form.password"
        :required="!isEditing"
        type="password"
        :autocomplete="isEditing ? 'new-password' : 'new-password'"
      />
    </div>

    <div v-if="form.password" class="field-group">
      <label for="user-password-confirmation">Confirmation du mot de passe</label>
      <input
        id="user-password-confirmation"
        v-model="form.password_confirmation"
        type="password"
        required
        autocomplete="new-password"
      />
    </div>

    <div class="field-group">
      <label for="user-role">Rôle</label>
      <select id="user-role" v-model="form.role_id" required>
        <option disabled value="">Sélectionner un rôle</option>
        <option v-for="role in roles" :key="role.id" :value="role.id">
          {{ role.name }}
        </option>
      </select>
    </div>

    <div class="form-actions">
      <button class="button button-secondary" type="button" :disabled="loading" @click="emit('cancel')">
        Annuler
      </button>
      <button class="button button-primary" type="submit" :disabled="loading">
        {{ loading ? "Enregistrement…" : isEditing ? "Enregistrer" : "Créer l’utilisateur" }}
      </button>
    </div>
  </form>
</template>

<style scoped>
.user-form {
  display: grid;
  gap: 1.1rem;
  max-width: 620px;
  padding: 1.5rem;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
  background: #fff;
}

.field-group {
  display: grid;
  gap: 0.4rem;
}

label {
  color: #334155;
  font-size: 0.9rem;
  font-weight: 700;
}

.field-hint {
  color: #64748b;
  font-size: 0.78rem;
  font-weight: 400;
}

input,
select {
  width: 100%;
  box-sizing: border-box;
  padding: 0.75rem 0.85rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  color: #0f172a;
  background: #fff;
  font: inherit;
}

input:focus,
select:focus {
  outline: 2px solid #bfdbfe;
  border-color: #2563eb;
}

.form-alert {
  padding: 0.8rem 1rem;
  border-radius: 8px;
  color: #991b1b;
  background: #fef2f2;
}

.form-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.7rem;
  padding-top: 0.5rem;
}

.button {
  padding: 0.7rem 1rem;
  border: 0;
  border-radius: 8px;
  font: inherit;
  font-weight: 700;
  cursor: pointer;
}

.button:disabled {
  cursor: wait;
  opacity: 0.65;
}

.button-secondary {
  color: #334155;
  background: #e2e8f0;
}

.button-primary {
  color: #fff;
  background: #2563eb;
}
</style>
