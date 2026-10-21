import { computed, ref } from "vue";
import { defineStore } from "pinia";
import * as taxonomyApi from "@/services/api/taxonomyApi";

function dataOf(response) { return response?.data?.data ?? response?.data ?? []; }
function errorOf(error, fallback) { return error?.response?.data?.message || Object.values(error?.response?.data?.errors || {}).flat()[0] || fallback; }

export const useTaxonomyStore = defineStore("taxonomy", () => {
  const departments = ref([]);
  const sectors = ref([]);
  const entities = ref([]);
  const loading = ref(false);
  const saving = ref(false);
  const error = ref("");

  async function fetchTaxonomies() {
    loading.value = true; error.value = "";
    try {
      const [departmentResponse, sectorResponse, entityResponse] = await Promise.all([
        taxonomyApi.getDepartments(),
        taxonomyApi.getSectors(),
        taxonomyApi.getEntities(),
      ]);
      departments.value = dataOf(departmentResponse);
      sectors.value = dataOf(sectorResponse);
      entities.value = dataOf(entityResponse);
    } catch (requestError) { error.value = errorOf(requestError, "Impossible de charger les référentiels."); throw requestError; }
    finally { loading.value = false; }
  }

  async function save(type, id, payload) {
    saving.value = true; error.value = "";
    try {
      const operations = {
        department: [taxonomyApi.createDepartment, taxonomyApi.updateDepartment],
        sector: [taxonomyApi.createSector, taxonomyApi.updateSector],
        entity: [taxonomyApi.createEntity, taxonomyApi.updateEntity],
      };
      const [create, update] = operations[type];
      const response = id ? await update(id, payload) : await create(payload);
      await fetchTaxonomies();
      return dataOf(response);
    } catch (requestError) { error.value = errorOf(requestError, "Impossible d’enregistrer cet élément."); throw requestError; }
    finally { saving.value = false; }
  }

  async function remove(type, id) {
    saving.value = true; error.value = "";
    try {
      const operations = {
        department: [taxonomyApi.deleteDepartment, departments],
        sector: [taxonomyApi.deleteSector, sectors],
        entity: [taxonomyApi.deleteEntity, entities],
      };
      const [removeItem, collection] = operations[type];
      await removeItem(id);
      collection.value = collection.value.filter((item) => String(item.id) !== String(id));
    } catch (requestError) { error.value = errorOf(requestError, "Impossible de supprimer cet élément."); throw requestError; }
    finally { saving.value = false; }
  }

  return { departments, sectors, entities, loading, saving, error, fetchTaxonomies, save, remove, hasDepartments: computed(() => departments.value.length > 0) };
});
