import { defineStore } from "pinia";
import {
  createUser as createUserRequest,
  deleteUser as deleteUserRequest,
  getUser,
  getUsers,
  updateUser as updateUserRequest,
} from "@/services/api/userApi";

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

  return data?.users ?? data?.data ?? [];
}

function getErrorMessage(error, fallback) {
  const response = error?.response?.data;
  const validationErrors = response?.errors;
  const firstValidationError = validationErrors
    ? Object.values(validationErrors).flat()[0]
    : null;

  return firstValidationError || response?.message || fallback;
}

export const useUserStore = defineStore("users", {
  state: () => ({
    users: [],
    currentUser: null,
    loading: false,
    saving: false,
    error: null,
  }),

  actions: {
    async fetchUsers(params = {}) {
      this.loading = true;
      this.error = null;

      try {
        const response = await getUsers(params);
        this.users = unwrapCollection(response);
        return this.users;
      } catch (error) {
        this.error = getErrorMessage(
          error,
          "Impossible de charger les utilisateurs.",
        );
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchUser(id) {
      this.loading = true;
      this.error = null;
      this.currentUser = null;

      try {
        const response = await getUser(id);
        this.currentUser = unwrapResource(response);
        return this.currentUser;
      } catch (error) {
        this.error = getErrorMessage(
          error,
          "Impossible de charger cet utilisateur.",
        );
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createUser(payload) {
      this.saving = true;
      this.error = null;

      try {
        const response = await createUserRequest(payload);
        const createdUser = unwrapResource(response);
        this.users = [createdUser, ...this.users];
        return createdUser;
      } catch (error) {
        this.error = getErrorMessage(
          error,
          "Impossible de créer cet utilisateur.",
        );
        throw error;
      } finally {
        this.saving = false;
      }
    },

    async updateUser(id, payload) {
      this.saving = true;
      this.error = null;

      try {
        const response = await updateUserRequest(id, payload);
        const updatedUser = unwrapResource(response);
        const index = this.users.findIndex(
          (user) => String(user.id) === String(id),
        );

        if (index !== -1) {
          this.users.splice(index, 1, updatedUser);
        }

        this.currentUser = updatedUser;
        return updatedUser;
      } catch (error) {
        this.error = getErrorMessage(
          error,
          "Impossible de modifier cet utilisateur.",
        );
        throw error;
      } finally {
        this.saving = false;
      }
    },

    async deleteUser(id) {
      this.saving = true;
      this.error = null;

      try {
        await deleteUserRequest(id);
        this.users = this.users.filter(
          (user) => String(user.id) !== String(id),
        );
      } catch (error) {
        this.error = getErrorMessage(
          error,
          "Impossible de supprimer cet utilisateur.",
        );
        throw error;
      } finally {
        this.saving = false;
      }
    },

    clearError() {
      this.error = null;
    },
  },
});
