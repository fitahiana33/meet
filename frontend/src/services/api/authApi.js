import api from "@/services/api";

export function login(payload) {
  return api.post("/login", payload);
}

export function logout() {
  return api.post("/logout");
}

export function getCurrentUser() {
  return api.get("/me");
}

export function requestPasswordReset(email) {
  return api.post("/forgot-password", { email });
}

export function resetPassword(payload) {
  return api.post("/reset-password", payload);
}
