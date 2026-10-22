import { computed, ref } from "vue";
import { defineStore } from "pinia";
import * as roleApi from "@/services/api/roleApi";

function payloadOf(response) {
  return response?.data?.data ?? response?.data ?? response;
}

function resourceOf(response) {
  const payload = payloadOf(response);
  return payload?.data ?? payload;
}

function collectionOf(response) {
  const payload = payloadOf(response);
  return Array.isArray(payload) ? payload : payload?.data ?? payload?.roles ?? [];
}

function errorOf(requestError, fallback) {
  const data = requestError?.response?.data;
  const firstValidationError = data?.errors && Object.values(data.errors).flat()[0];
  return firstValidationError || data?.message || requestError?.message || fallback;
}

export const useRoleStore = defineStore("roles", () => {
  const roles = ref([]);
  const currentRole = ref(null);
  const loading = ref(false);
  const saving = ref(false);
  const error = ref(null);
  const pagination = ref(null);
  const hasRoles = computed(() => roles.value.length > 0);

  async function fetchRoles(params = {}) {
    loading.value = true;
    error.value = null;
    try {
      const response = await roleApi.getRoles(params);
      roles.value = collectionOf(response);
      pagination.value = response?.data?.meta ?? response?.data?.data?.meta ?? null;
      return roles.value;
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de charger les rôles.");
      throw requestError;
    } finally {
      loading.value = false;
    }
  }

  async function fetchRole(id) {
    loading.value = true;
    error.value = null;
    try {
      const response = await roleApi.getRole(id);
      currentRole.value = resourceOf(response);
      return currentRole.value;
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de charger ce rôle.");
      throw requestError;
    } finally {
      loading.value = false;
    }
  }

  async function createRole(payload) {
    saving.value = true;
    error.value = null;
    try {
      const response = await roleApi.createRole(payload);
      return resourceOf(response);
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de créer le rôle.");
      throw requestError;
    } finally {
      saving.value = false;
    }
  }

  async function updateRole(id, payload) {
    saving.value = true;
    error.value = null;
    try {
      const response = await roleApi.updateRole(id, payload);
      return resourceOf(response);
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de modifier le rôle.");
      throw requestError;
    } finally {
      saving.value = false;
    }
  }

  async function deleteRole(id) {
    saving.value = true;
    error.value = null;
    try {
      await roleApi.deleteRole(id);
      roles.value = roles.value.filter((role) => String(role.id) !== String(id));
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de supprimer le rôle.");
      throw requestError;
    } finally {
      saving.value = false;
    }
  }

  return { roles, currentRole, loading, saving, error, pagination, hasRoles, fetchRoles, fetchRole, createRole, updateRole, deleteRole };
});
