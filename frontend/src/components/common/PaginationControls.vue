<script setup>
import { ref, watch } from "vue";

const props = defineProps({
  currentPage: { type: Number, default: 1 },
  lastPage: { type: Number, default: 1 },
  total: { type: Number, default: 0 },
});
const emit = defineEmits(["change"]);
const pageInput = ref(String(props.currentPage));

watch(() => props.currentPage, (page) => {
  pageInput.value = String(page);
});

function goToPage() {
  const page = Number(pageInput.value);
  if (!Number.isInteger(page) || page < 1 || page > props.lastPage) {
    pageInput.value = String(props.currentPage);
    return;
  }
  emit("change", page);
}
</script>

<template>
  <nav v-if="lastPage > 1" class="pagination" aria-label="Pagination">
    <span>{{ total }} résultat{{ total === 1 ? "" : "s" }} · Page {{ currentPage }} sur {{ lastPage }}</span>
    <form class="pagination-actions" @submit.prevent="goToPage">
      <button type="button" :disabled="currentPage <= 1" @click="$emit('change', currentPage - 1)">Précédent</button>
      <label>Aller à
        <input v-model="pageInput" type="number" min="1" :max="lastPage" inputmode="numeric" :aria-label="`Numéro de page, de 1 à ${lastPage}`" />
      </label>
      <button type="submit" :disabled="Number(pageInput) === currentPage">Afficher</button>
      <button type="button" :disabled="currentPage >= lastPage" @click="$emit('change', currentPage + 1)">Suivant</button>
    </form>
  </nav>
</template>

<style scoped>
.pagination { display: flex; align-items: center; justify-content: space-between; gap: .75rem; margin-top: 1rem; color: #8b99ab; font-size: .7rem; }
.pagination-actions { display: flex; align-items: center; gap: .45rem; }
.pagination-actions label { display: flex; align-items: center; gap: .35rem; }
.pagination-actions input { width: 3.5rem; padding: .4rem; border: 1px solid #dce4ef; border-radius: .4rem; color: #4f6fae; font-size: .68rem; text-align: center; }
.pagination button { padding: .45rem .65rem; border: 1px solid #dce4ef; border-radius: .4rem; color: #4f6fae; background: #fff; font-size: .68rem; font-weight: 700; cursor: pointer; }
.pagination button:disabled { cursor: not-allowed; opacity: .45; }
@media (max-width: 600px) { .pagination { align-items: flex-start; flex-direction: column; }.pagination-actions { flex-wrap: wrap; } }
</style>
