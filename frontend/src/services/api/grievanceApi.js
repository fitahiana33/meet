import api from "@/services/api";

export const getGrievances = (params = {}) => api.get("/grievances", { params });
export const getGrievance = (id) => api.get(`/grievances/${id}`);

function asFormData(payload, method = null) {
  const formData = new FormData();
  if (method) formData.append("_method", method);
  Object.entries(payload).forEach(([key, value]) => {
    if (key === "documents") {
      (value || []).forEach((file) => formData.append("documents[]", file));
    } else if (value !== null && value !== undefined && value !== "") {
      formData.append(key, value);
    } else if (["department_id", "due_at"].includes(key)) {
      formData.append(key, "");
    }
  });
  return formData;
}

export const createGrievance = (payload) => api.post("/grievances", asFormData(payload), { headers: { "Content-Type": "multipart/form-data" } });
export const updateGrievance = (id, payload) => api.post(`/grievances/${id}`, asFormData(payload, "PUT"), { headers: { "Content-Type": "multipart/form-data" } });
export const deleteGrievance = (id) => api.delete(`/grievances/${id}`);
export const deleteGrievanceDocument = (grievanceId, documentId) => api.delete(`/grievances/${grievanceId}/documents/${documentId}`);
