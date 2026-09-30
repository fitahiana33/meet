import { computed, ref } from "vue";
import { defineStore } from "pinia";
import { getCurrentUser, login, logout } from "@/services/api/authApi";

function unwrapUser(response) {
  return response?.data?.user ?? response?.data?.data?.user ?? null;
}

export const useAuthStore = defineStore("auth", () => {
  const token = ref(localStorage.getItem("token"));
  const user = ref(readStoredUser());
  const loading = ref(false);
  const error = ref(null);

  const isAuthenticated = computed(() => Boolean(token.value && user.value));
  const roleName = computed(() =>
    typeof user.value?.role === "string" ? user.value.role : user.value?.role?.name,
  );
  const isAdmin = computed(() => roleName.value === "admin");
  const isClient = computed(() => roleName.value === "client");
  const displayName = computed(() =>
    [user.value?.first_name, user.value?.last_name].filter(Boolean).join(" "),
  );

  async function authenticate(credentials) {
    loading.value = true;
    error.value = null;

    try {
      const response = await login(credentials);
      const payload = response.data?.data ?? response.data;

      token.value = payload?.token ?? null;
      user.value = payload?.user ?? null;

      if (!token.value || !user.value) {
        throw new Error("La réponse de connexion est incomplète.");
      }

      localStorage.setItem("token", token.value);
      localStorage.setItem("user", JSON.stringify(user.value));
      return user.value;
    } catch (requestError) {
      error.value = getApiError(requestError, "Connexion impossible.");
      clearSession();
      throw requestError;
    } finally {
      loading.value = false;
    }
  }

  async function hydrate() {
    if (!localStorage.getItem("token")) return null;

    try {
      const response = await getCurrentUser();
      user.value = unwrapUser(response);
      token.value = localStorage.getItem("token");

      if (user.value) localStorage.setItem("user", JSON.stringify(user.value));
      return user.value;
    } catch (requestError) {
      clearSession();
      throw requestError;
    }
  }

  async function signOut() {
    try {
      if (token.value) await logout();
    } finally {
      clearSession();
    }
  }

  function clearSession() {
    token.value = null;
    user.value = null;
    localStorage.removeItem("token");
    localStorage.removeItem("user");
  }

  function getApiError(requestError, fallback) {
    return requestError?.response?.data?.message || requestError?.message || fallback;
  }

  return {
    token,
    user,
    loading,
    error,
    isAuthenticated,
    roleName,
    isAdmin,
    isClient,
    displayName,
    authenticate,
    hydrate,
    signOut,
    clearSession,
  };
});

function readStoredUser() {
  const storedUser = localStorage.getItem("user");
  if (!storedUser) return null;

  try {
    return JSON.parse(storedUser);
  } catch {
    localStorage.removeItem("user");
    return null;
  }
}
