import api from "@/services/api";

export function getRoles(params = {}) {
  return api.get("/roles", { params });
}

export function getRole(id) {
  return api.get(`/roles/${id}`);
}

export function createRole(payload) {
  return api.post("/roles", payload);
}

export function updateRole(id, payload) {
  return api.put(`/roles/${id}`, payload);
}

export function deleteRole(id) {
  return api.delete(`/roles/${id}`);
}
