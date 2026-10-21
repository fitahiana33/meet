import axios from "axios";

const apiHost = typeof window === "undefined" ? "localhost" : window.location.hostname;

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || `http://${apiHost}:8000/api`,
  headers: {
    Accept: "application/json",
  },
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem("token");

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  return config;
});

export default api;