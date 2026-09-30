import api from "@/services/api";

export const getRoles = (params = {}) => api.get("/roles", { params });
export const getRole = (id) => api.get(`/roles/${id}`);
export const createRole = (payload) => api.post("/roles", payload);
export const updateRole = (id, payload) => api.put(`/roles/${id}`, payload);
export const deleteRole = (id) => api.delete(`/roles/${id}`);
