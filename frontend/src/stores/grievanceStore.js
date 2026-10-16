import { ref } from "vue";
import { defineStore } from "pinia";
import * as grievanceApi from "@/services/api/grievanceApi";

function payloadOf(response) { return response?.data?.data ?? response?.data ?? response; }
function resourceOf(response) { const payload = payloadOf(response); return payload?.data ?? payload; }
function collectionOf(response) { const payload = payloadOf(response); return Array.isArray(payload) ? payload : payload?.data ?? []; }
function errorOf(error, fallback) { return error?.response?.data?.message || Object.values(error?.response?.data?.errors || {}).flat()[0] || fallback; }

export const useGrievanceStore = defineStore("grievances", () => {
  const grievances = ref([]); const current = ref(null); const loading = ref(false); const saving = ref(false); const error = ref(""); const pagination = ref(null);
  async function fetchGrievances(params = {}) {
    loading.value = true; error.value = "";
    try { const response = await grievanceApi.getGrievances(params); grievances.value = collectionOf(response); pagination.value = response?.data; return grievances.value; }
    catch (requestError) { error.value = errorOf(requestError, "Impossible de charger les doléances."); throw requestError; }
    finally { loading.value = false; }
  }
  async function fetchGrievance(id) {
    loading.value = true; error.value = "";
    try { const response = await grievanceApi.getGrievance(id); current.value = resourceOf(response); return current.value; }
    catch (requestError) { error.value = errorOf(requestError, "Impossible de charger cette doléance."); throw requestError; }
    finally { loading.value = false; }
  }
  async function createGrievance(payload) { saving.value = true; error.value = ""; try { return resourceOf(await grievanceApi.createGrievance(payload)); } catch (e) { error.value = errorOf(e, "Impossible de créer la doléance."); throw e; } finally { saving.value = false; } }
  async function updateGrievance(id, payload) { saving.value = true; error.value = ""; try { const item = resourceOf(await grievanceApi.updateGrievance(id, payload)); current.value = item; return item; } catch (e) { error.value = errorOf(e, "Impossible de modifier la doléance."); throw e; } finally { saving.value = false; } }
  async function removeGrievance(id) { saving.value = true; error.value = ""; try { await grievanceApi.deleteGrievance(id); grievances.value = grievances.value.filter((item) => String(item.id) !== String(id)); } catch (e) { error.value = errorOf(e, "Impossible de supprimer la doléance."); throw e; } finally { saving.value = false; } }
  async function removeDocument(grievanceId, documentId) { await grievanceApi.deleteGrievanceDocument(grievanceId, documentId); if (current.value) current.value.documents = current.value.documents.filter((item) => item.id !== documentId); }
  return { grievances, current, loading, saving, error, pagination, fetchGrievances, fetchGrievance, createGrievance, updateGrievance, removeGrievance, removeDocument };
});
