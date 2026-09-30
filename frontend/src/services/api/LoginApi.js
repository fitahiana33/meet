import api from "@/services/api";


export function post(credentials) {
  return api.post("/login", credentials);
}