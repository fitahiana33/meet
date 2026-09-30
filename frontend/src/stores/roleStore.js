import { defineStore } from "pinia";
import {
  createRole as createRoleRequest,
  deleteRole as deleteRoleRequest,
  getRole,
  getRoles,
  updateRole as updateRoleRequest,
} from "@/services/api/roleApi";

function unwrap(payload) {
  return payload?.data ?? payload;
}

function unwrapResource(payload) {
  const data = unwrap(payload);
  return data?.data ?? data;
}

function unwrapCollection(payload) {
  const data = unwrap(payload);

  if (Array.isArray(data)) {
    return data;
  }

  return data?.data ?? data?.roles ?? [];
}

function getErrorMessage(error, fallback) {
  const response = error?.response?.data;
  const validationErrors = response?.errors;
  const firstValidationError = validationErrors
    ? Object.values(validationErrors).flat()[0]
    : null;

  return firstValidationError || response?.message || fallback;
}

export const useRoleStore = defineStore("roles", {
  state: () => ({
    roles: [],
    currentRole: null,
    loading: false,
    saving: false,
    error: null,
  }),

  actions: {
    async fetchRoles(params = {}) {
      this.loading = true;
      this.error = null;

      try {
        const response = await getRoles(params);
        this.roles = unwrapCollection(response);
        return this.roles;
      } catch (error) {
        this.error = getErrorMessage(error, "Impossible de charger les rôles.");
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchRole(id) {
      this.loading = true;
      this.error = null;
      this.currentRole = null;

      try {
        const response = await getRole(id);
        this.currentRole = unwrapResource(response);
        return this.currentRole;
      } catch (error) {
        this.error = getErrorMessage(error, "Impossible de charger ce rôle.");
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createRole(payload) {
      this.saving = true;
      this.error = null;

      try {
        const response = await createRoleRequest(payload);
        const createdRole = unwrapResource(response);
        this.roles = [...this.roles, createdRole].sort((a, b) =>
          a.name.localeCompare(b.name),
        );
        return createdRole;
      } catch (error) {
        this.error = getErrorMessage(error, "Impossible de créer ce rôle.");
        throw error;
      } finally {
        this.saving = false;
      }
    },

    async updateRole(id, payload) {
      this.saving = true;
      this.error = null;

      try {
        const response = await updateRoleRequest(id, payload);
        const updatedRole = unwrapResource(response);
        const index = this.roles.findIndex(
          (role) => String(role.id) === String(id),
        );

        if (index !== -1) {
          this.roles.splice(index, 1, updatedRole);
        }

        this.roles.sort((a, b) => a.name.localeCompare(b.name));
        this.currentRole = updatedRole;
        return updatedRole;
      } catch (error) {
        this.error = getErrorMessage(error, "Impossible de modifier ce rôle.");
        throw error;
      } finally {
        this.saving = false;
      }
    },

    async deleteRole(id) {
      this.saving = true;
      this.error = null;

      try {
        await deleteRoleRequest(id);
        this.roles = this.roles.filter(
          (role) => String(role.id) !== String(id),
        );
      } catch (error) {
        this.error = getErrorMessage(error, "Impossible de supprimer ce rôle.");
        throw error;
      } finally {
        this.saving = false;
      }
    },
  },
});
