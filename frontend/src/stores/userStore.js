import { computed, ref } from "vue";
import { defineStore } from "pinia";
import * as userApi from "@/services/api/userApi";

function payloadOf(response) {
  return response?.data?.data ?? response?.data ?? response;
}

function resourceOf(response) {
  const payload = payloadOf(response);
  return payload?.data ?? payload;
}

function collectionOf(response) {
  const payload = payloadOf(response);
  return Array.isArray(payload) ? payload : payload?.data ?? payload?.users ?? [];
}

function errorOf(requestError, fallback) {
  const data = requestError?.response?.data;
  const firstValidationError = data?.errors && Object.values(data.errors).flat()[0];
  return firstValidationError || data?.message || requestError?.message || fallback;
}

export const useUserStore = defineStore("users", () => {
  const users = ref([]);
  const currentUser = ref(null);
  const loading = ref(false);
  const saving = ref(false);
  const error = ref(null);
  const hasUsers = computed(() => users.value.length > 0);

  async function fetchUsers(params = {}) {
    loading.value = true;
    error.value = null;
    try {
      const response = await userApi.getUsers(params);
      users.value = collectionOf(response);
      return users.value;
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de charger les utilisateurs.");
      throw requestError;
    } finally {
      loading.value = false;
    }
  }

  async function fetchUser(id) {
    loading.value = true;
    error.value = null;
    try {
      const response = await userApi.getUser(id);
      currentUser.value = resourceOf(response);
      return currentUser.value;
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de charger cet utilisateur.");
      throw requestError;
    } finally {
      loading.value = false;
    }
  }

  async function createUser(payload) {
    saving.value = true;
    error.value = null;
    try {
      const response = await userApi.createUser(payload);
      return resourceOf(response);
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de créer l’utilisateur.");
      throw requestError;
    } finally {
      saving.value = false;
    }
  }

  async function updateUser(id, payload) {
    saving.value = true;
    error.value = null;
    try {
      const response = await userApi.updateUser(id, payload);
      return resourceOf(response);
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de modifier l’utilisateur.");
      throw requestError;
    } finally {
      saving.value = false;
    }
  }

  async function deleteUser(id) {
    saving.value = true;
    error.value = null;
    try {
      await userApi.deleteUser(id);
      users.value = users.value.filter((user) => String(user.id) !== String(id));
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de supprimer l’utilisateur.");
      throw requestError;
    } finally {
      saving.value = false;
    }
  }

  return { users, currentUser, loading, saving, error, hasUsers, fetchUsers, fetchUser, createUser, updateUser, deleteUser };
});
