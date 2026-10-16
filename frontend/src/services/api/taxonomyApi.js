import api from "@/services/api";

export const getDepartments = () => api.get("/departments");
export const createDepartment = (payload) => api.post("/departments", payload);
export const updateDepartment = (id, payload) => api.put(`/departments/${id}`, payload);
export const deleteDepartment = (id) => api.delete(`/departments/${id}`);

export const getSectors = () => api.get("/sectors");
export const createSector = (payload) => api.post("/sectors", payload);
export const updateSector = (id, payload) => api.put(`/sectors/${id}`, payload);
export const deleteSector = (id) => api.delete(`/sectors/${id}`);
