import { computed, ref } from "vue";
import { defineStore } from "pinia";
import * as taxonomyApi from "@/services/api/taxonomyApi";

function dataOf(response) { return response?.data?.data ?? response?.data ?? []; }
function errorOf(error, fallback) { return error?.response?.data?.message || Object.values(error?.response?.data?.errors || {}).flat()[0] || fallback; }

export const useTaxonomyStore = defineStore("taxonomy", () => {
  const departments = ref([]);
  const sectors = ref([]);
  const loading = ref(false);
  const saving = ref(false);
  const error = ref("");

  async function fetchTaxonomies() {
    loading.value = true; error.value = "";
    try {
      const [departmentResponse, sectorResponse] = await Promise.all([taxonomyApi.getDepartments(), taxonomyApi.getSectors()]);
      departments.value = dataOf(departmentResponse);
      sectors.value = dataOf(sectorResponse);
    } catch (requestError) { error.value = errorOf(requestError, "Impossible de charger les référentiels."); throw requestError; }
    finally { loading.value = false; }
  }

  async function save(type, id, payload) {
    saving.value = true; error.value = "";
    try {
      const response = type === "department"
        ? (id ? await taxonomyApi.updateDepartment(id, payload) : await taxonomyApi.createDepartment(payload))
        : (id ? await taxonomyApi.updateSector(id, payload) : await taxonomyApi.createSector(payload));
      await fetchTaxonomies();
      return dataOf(response);
    } catch (requestError) { error.value = errorOf(requestError, "Impossible d’enregistrer cet élément."); throw requestError; }
    finally { saving.value = false; }
  }

  async function remove(type, id) {
    saving.value = true; error.value = "";
    try {
      if (type === "department") await taxonomyApi.deleteDepartment(id); else await taxonomyApi.deleteSector(id);
      if (type === "department") departments.value = departments.value.filter((item) => item.id !== id);
      else sectors.value = sectors.value.filter((item) => item.id !== id);
    } catch (requestError) { error.value = errorOf(requestError, "Impossible de supprimer cet élément."); throw requestError; }
    finally { saving.value = false; }
  }

  return { departments, sectors, loading, saving, error, fetchTaxonomies, save, remove, hasDepartments: computed(() => departments.value.length > 0) };
});
