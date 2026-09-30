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
