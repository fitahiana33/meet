<script setup>
import { computed, reactive, ref, watch } from "vue";
import SearchableSelect from "@/components/form/SearchableSelect.vue";
import { searchDeclarants } from "@/services/api/grievanceApi";

const props = defineProps({
  grievance: { type: Object, default: null },
  departments: { type: Array, default: () => [] },
  sectors: { type: Array, default: () => [] },
  entities: { type: Array, default: () => [] },
  loading: Boolean,
  error: { type: String, default: "" },
 removingDocumentId: { type: [Number, String], default: null },
});
const emit = defineEmits(["submit", "cancel", "remove-document"]);
const files = ref([]);
const existingDocuments = ref([]);
const picker = ref(null);
const selectedDeclarants = ref([]);
const searchTerm = ref("");
const matches = ref([]);
const searchLoading = ref(false);
const searchError = ref("");
const addOpen = ref(false);
let searchSequence = 0;

const form = reactive({ title: "", sector_id: "", entity_id: "", department_id: "", description: "", status: "submitted", complained_at: "", due_at: "" });
const draft = reactive({ first_name: "", last_name: "", email: "", address: "", phone: "", photo: null });
const statusOptions = [
  { value: "submitted", label: "Soumise" },
  { value: "rejected", label: "Rejetée" },
  { value: "in_progress", label: "En cours" },
  { value: "processed", label: "Traitée" },
];
const isEdit = computed(() => Boolean(props.grievance));
const sectorOptions = computed(() => props.sectors.map((item) => ({ value: item.id, label: item.name })));
const entityOptions = computed(() => props.entities.map((item) => ({ value: item.id, label: item.title })));
const departmentOptions = computed(() => props.departments.map((item) => ({ value: item.id, label: item.name })));

function localDate(value) {
  if (!value) return "";
  const date = new Date(value);
  const pad = (number) => String(number).padStart(2, "0");
  return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}T${pad(date.getHours())}:${pad(date.getMinutes())}`;
}
function nowLocal() { return localDate(new Date()); }
function plus48Hours(value) {
  const date = value ? new Date(value) : new Date();
  date.setHours(date.getHours() + 48);
  return localDate(date);
}

watch(() => props.grievance, (item) => {
  form.title = item?.title || "";
  form.sector_id = item?.sector?.id || "";
  form.entity_id = item?.entity?.id || "";
  form.department_id = item?.department?.id || "";
  form.description = item?.description || "";
  form.status = item?.status || "submitted";
  form.complained_at = localDate(item?.complained_at) || nowLocal();
  form.due_at = localDate(item?.due_at) || plus48Hours(form.complained_at);
  selectedDeclarants.value = (item?.declarants || []).map((declarant) => ({ ...declarant }));
  files.value = [];
}, { immediate: true });

watch(() => props.grievance?.documents, (documents) => {
 existingDocuments.value = documents || [];
}, { deep: true, immediate: true });

watch(searchTerm, async (term) => {
  const sequence = ++searchSequence;
  searchError.value = "";
  if (term.trim().length < 2) {
    matches.value = [];
    searchLoading.value = false;
    return;
  }
  searchLoading.value = true;
  try {
    const response = await searchDeclarants(term.trim());
    if (sequence === searchSequence) matches.value = response.data.data || [];
  } catch (requestError) {
    if (sequence === searchSequence) {
      searchError.value = requestError.response?.data?.message || "La recherche des déclarants a échoué.";
      matches.value = [];
    }
  } finally {
    if (sequence === searchSequence) searchLoading.value = false;
  }
});

function validateDueDate() {
  if (!form.due_at || form.due_at <= form.complained_at) form.due_at = plus48Hours(form.complained_at);
}
function addFiles(event) {
  const incoming = [...event.target.files];
  files.value = [...files.value, ...incoming.filter((file) => !files.value.some((oldFile) => oldFile.name === file.name && oldFile.size === file.size))];
  event.target.value = "";
}
function removeFile(index) { files.value.splice(index, 1); }
function removeExistingDocument(document) { emit("remove-document", document); }
function addExisting(declarant) {
  if (selectedDeclarants.value.some((item) => String(item.id) === String(declarant.id))) return;
  selectedDeclarants.value.push({ ...declarant });
  searchTerm.value = "";
  matches.value = [];
}
function removeDeclarant(index) { selectedDeclarants.value.splice(index, 1); }
function clearDraft() {
  Object.keys(draft).forEach((key) => { draft[key] = key === "photo" ? null : ""; });
  addOpen.value = false;
}
function addNewDeclarant() {
  selectedDeclarants.value.push({ ...draft, id: null });
  clearDraft();
}
function submit() {
  const declarants = selectedDeclarants.value.map((item) => item.id
    ? { id: item.id }
    : {
      first_name: item.first_name,
      last_name: item.last_name,
      email: item.email,
      address: item.address,
      phone: item.phone,
      photo: item.photo,
    });
  emit("submit", {
    ...form,
    sector_id: Number(form.sector_id),
    entity_id: form.entity_id ? Number(form.entity_id) : null,
    department_id: form.department_id ? Number(form.department_id) : null,
    documents: files.value,
    declarants,
  });
}
</script>

<template>
  <form class="form-card" @submit.prevent="submit">
    <div class="form-intro">
      <span class="form-icon">!</span>
      <div><h3>{{ isEdit ? "Modifier la doléance" : "Nouvelle doléance" }}</h3><p>Renseignez la demande et les personnes concernées.</p></div>
    </div>
    <div v-if="error" class="alert" role="alert">{{ error }}</div>

    <div class="form-grid">
      <label class="wide">Titre<input v-model.trim="form.title" required maxlength="255" placeholder="Ex. Éclairage public défectueux" /></label>
      <label>Secteur<SearchableSelect v-model="form.sector_id" :options="sectorOptions" required placeholder="Rechercher un secteur" /></label>
      <label>Entité (facultatif)<SearchableSelect v-model="form.entity_id" :options="entityOptions" placeholder="Rechercher une entité" /></label>
      <label>Attribution<SearchableSelect v-model="form.department_id" :options="departmentOptions" placeholder="Rechercher un département" /></label>
      <label class="wide">Description<textarea v-model.trim="form.description" required rows="3" placeholder="Décrivez la doléance…" /></label>
      <label>Statut<select v-model="form.status"><option v-for="status in statusOptions" :key="status.value" :value="status.value">{{ status.label }}</option></select></label>
      <label>Date de la doléance<input v-model="form.complained_at" type="datetime-local" required @change="validateDueDate" /></label>
      <label>Date d’échéance<input v-model="form.due_at" type="datetime-local" :min="form.complained_at" /><small>Par défaut : 48 h après le dépôt</small></label>

      <fieldset class="wide declarant-section">
        <legend>Déclarants <span>{{ selectedDeclarants.length }}</span></legend>
        <div v-if="selectedDeclarants.length" class="selected-list">
          <article v-for="(declarant, index) in selectedDeclarants" :key="declarant.id || `new-${index}`" class="selected-person">
            <span class="person-avatar">{{ (declarant.first_name || declarant.name || "?").slice(0, 1).toUpperCase() }}</span>
            <div class="person-info"><strong>{{ declarant.name || `${declarant.first_name} ${declarant.last_name}` }}</strong><small>{{ declarant.email || declarant.phone || (declarant.id ? "Déjà enregistré" : "Nouveau déclarant") }}</small></div>
            <button type="button" class="remove-person" :aria-label="`Retirer ${declarant.name || declarant.first_name}`" @click="removeDeclarant(index)">Retirer</button>
          </article>
        </div>
        <div class="declarant-tools">
          <div class="search-wrap"><input v-model.trim="searchTerm" placeholder="Rechercher un déclarant par nom, prénom ou e-mail…" autocomplete="off" /><span v-if="searchLoading" class="search-status">Recherche…</span>
            <ul v-if="matches.length" class="search-results"><li v-for="match in matches" :key="match.id"><button type="button" @click="addExisting(match)"><strong>{{ match.name }}</strong><small>{{ match.email || match.phone || "Coordonnées non renseignées" }}</small></button></li></ul>
          </div>
          <button type="button" class="add-person-button" @click="addOpen = !addOpen">{{ addOpen ? "Annuler" : "＋ Nouveau déclarant" }}</button>
        </div>
        <p v-if="searchError" class="inline-error" role="alert">{{ searchError }}</p>
        <p v-else-if="searchTerm.trim().length >= 2 && !searchLoading && !matches.length" class="helper">Aucun déclarant correspondant. Vous pouvez en créer un nouveau.</p>

        <div v-if="addOpen" class="new-person-form">
          <label>Prénom<input v-model.trim="draft.first_name" maxlength="120" /></label>
          <label>Nom<input v-model.trim="draft.last_name" maxlength="120" /></label>
          <label>E-mail<input v-model.trim="draft.email" type="email" maxlength="255" /></label>
          <label>Téléphone<input v-model.trim="draft.phone" type="tel" maxlength="40" /></label>
          <label class="wide">Adresse<input v-model.trim="draft.address" maxlength="255" /></label>
          <label class="photo-input">Photo (facultative)<input type="file" accept="image/jpeg,image/png,image/webp" @change="draft.photo = $event.target.files?.[0] || null" /><small>{{ draft.photo?.name || "JPG, PNG ou WebP · 5 Mo maximum" }}</small></label>
          <button class="add-person-button confirm-person" type="button" :disabled="!draft.first_name || !draft.last_name" @click="addNewDeclarant">Ajouter à la doléance</button>
        </div>
      </fieldset>

      <label class="wide file-label">Documents
        <input ref="picker" type="file" multiple @change="addFiles" />
        <button type="button" class="add-file" @click="picker?.click()">＋ Ajouter des fichiers</button>
        <small>{{ files.length ? `${files.length} fichier(s) sélectionné(s)` : "50 Mo maximum par fichier" }}</small>
        <div v-if="existingDocuments.length" class="existing-documents"><strong>Fichiers déjà joints ({{ existingDocuments.length }})</strong><ul class="file-list"><li v-for="document in existingDocuments" :key="document.id"><a :href="document.url" target="_blank" rel="noreferrer">{{ document.name }}</a><button type="button" class="remove-file" :disabled="removingDocumentId === document.id" @click="removeExistingDocument(document)">{{ removingDocumentId === document.id ? "Suppression…" : "Retirer" }}</button></li></ul></div>
 <ul v-if="files.length" class="file-list"><li v-for="(file, index) in files" :key="`${file.name}-${file.size}-${index}`"><span>{{ file.name }}</span><button type="button" @click="removeFile(index)">Retirer</button></li></ul>
      </label>
    </div>

    <div class="form-actions">
      <button type="button" class="secondary-button" @click="$emit('cancel')">Annuler</button>
      <button type="submit" class="submit-button" :disabled="loading">{{ loading ? "Enregistrement…" : isEdit ? "Enregistrer les changements" : "Créer la doléance" }} <span>→</span></button>
    </div>
  </form>
</template>

<style scoped>
.form-card { display: grid; max-width: 1050px; gap: .85rem; padding: 1.15rem; border: 1px solid #e8eef6; border-radius: .85rem; background: #fff; box-shadow: 0 5px 15px rgb(30 52 84 / 3%); }
.form-intro { display: flex; align-items: center; gap: .7rem; }.form-icon { display: grid; width: 2.15rem; height: 2.15rem; place-items: center; border-radius: .65rem; color: #fff; background: linear-gradient(135deg,#5b8cff,#7c3aed); font-weight: 800; }
.form-intro h3 { margin: 0; color: #253650; font-size: .86rem; }.form-intro p { margin: .2rem 0 0; color: #98a5b5; font-size: .67rem; }
.alert,.inline-error { padding: .65rem .75rem; border: 1px solid #ffd7db; border-radius: .5rem; color: #b33e4b; background: #fff3f4; font-size: .72rem; }
.form-grid { display: grid; grid-template-columns: repeat(3,minmax(0,1fr)); gap: .75rem; }.form-grid label { display: grid; align-content: start; gap: .35rem; color: #53647c; font-size: .68rem; font-weight: 700; }.form-grid .wide { grid-column: 1 / -1; }
.form-grid input,.form-grid select,.form-grid textarea { width: 100%; min-width: 0; padding: .62rem .7rem; border: 1px solid #dce4ef; border-radius: .48rem; outline: 0; color: #30415a; background: #fff; font-size: .74rem; }.form-grid input:focus,.form-grid select:focus,.form-grid textarea:focus { border-color: #7a9dea; box-shadow: 0 0 0 3px rgb(79 120 232 / 9%); }.form-grid textarea { resize: vertical; }.form-grid small,.helper { color: #9aa7b7; font-size: .62rem; font-weight: 400; }
.declarant-section { min-width: 0; margin: 0; padding: .7rem; border: 1px solid #e5ebf3; border-radius: .6rem; }.declarant-section legend { padding: 0 .35rem; color: #40516a; font-size: .7rem; font-weight: 800; }.declarant-section legend span { margin-left: .2rem; color: #557bd0; }
.selected-list { display: flex; flex-wrap: wrap; gap: .4rem; margin-bottom: .55rem; }.selected-person { display: flex; align-items: center; gap: .45rem; max-width: 100%; padding: .35rem .45rem; border: 1px solid #e8eef6; border-radius: .5rem; background: #fbfcfe; }.person-avatar { display: grid; width: 1.6rem; height: 1.6rem; flex: 0 0 auto; place-items: center; border-radius: 50%; color: #4776da; background: #eaf1ff; font-size: .65rem; font-weight: 800; }.person-info { min-width: 0; }.person-info strong,.person-info small { display: block; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }.person-info strong { color: #43556e; font-size: .66rem; }.person-info small { margin-top: .1rem; color: #98a5b5; font-size: .59rem; }.remove-person { padding: .25rem; border: 0; color: #bd5a66; background: none; font-size: .61rem; font-weight: 700; cursor: pointer; }
.declarant-tools { display: flex; gap: .5rem; }.search-wrap { position: relative; flex: 1; }.search-wrap>input { padding-right: 4rem; }.search-status { position: absolute; top: 50%; right: .6rem; color: #99a5b5; font-size: .6rem; transform: translateY(-50%); }.search-results { position: absolute; z-index: 40; top: calc(100% + .2rem); right: 0; left: 0; display: grid; max-height: 12rem; overflow: auto; margin: 0; padding: .25rem; border: 1px solid #dce4ef; border-radius: .5rem; background: #fff; box-shadow: 0 12px 24px rgb(30 52 84 / 13%); list-style: none; }.search-results button { display: grid; width: 100%; gap: .15rem; padding: .5rem; border: 0; border-radius: .35rem; background: #fff; text-align: left; cursor: pointer; }.search-results button:hover { background: #eff4ff; }.search-results strong { color: #43556e; font-size: .68rem; }.search-results small { color: #98a5b5; font-size: .61rem; }
.add-person-button { flex: 0 0 auto; padding: .58rem .68rem; border: 1px solid #dce4ef; border-radius: .45rem; color: #4776da; background: #f8faff; font-size: .65rem; font-weight: 700; cursor: pointer; }.add-person-button:disabled { opacity: .55; cursor: not-allowed; }.helper { margin: .35rem 0 0; }.inline-error { margin: .4rem 0 0; }
.new-person-form { display: grid; grid-template-columns: repeat(4,minmax(0,1fr)); align-items: end; gap: .55rem; margin-top: .6rem; padding: .65rem; border-radius: .5rem; background: #f8faff; }.new-person-form .wide { grid-column: 1 / -1; }.new-person-form .photo-input { grid-column: span 2; }.new-person-form input[type=file] { padding: .38rem; }.confirm-person { align-self: end; }
.file-label input[type=file] { padding: .45rem; background: #f8faff; }.add-file { width: max-content; padding: 0; border: 0; color: #4776da; background: none; font-size: .67rem; font-weight: 700; cursor: pointer; }.file-list { display: grid; gap: .3rem; margin: 0; padding: 0; list-style: none; }.file-list li { display: flex; align-items: center; justify-content: space-between; gap: .5rem; padding: .42rem .55rem; border-radius: .4rem; background: #f7f9fc; font-size: .66rem; }.file-list span { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }.file-list button { border: 0; color: #bd5a66; background: none; font-size: .63rem; font-weight: 700; cursor: pointer; } .existing-documents { display: grid; gap: .35rem; }.existing-documents>strong { color: #53647c; font-size: .65rem; }.existing-documents a { overflow: hidden; color: #4776da; text-overflow: ellipsis; white-space: nowrap; } .remove-file { flex: 0 0 auto; border: 0; color: #bd5a66; background: none; font-size: .62rem; font-weight: 700; cursor: pointer; }
.form-actions { display: flex; justify-content: flex-end; gap: .55rem; }.submit-button,.secondary-button { padding: .68rem .9rem; border: 0; border-radius: .48rem; font-size: .7rem; font-weight: 700; cursor: pointer; }.submit-button { color: #fff; background: linear-gradient(100deg,#3e70da,#5c82e4); }.secondary-button { color: #68788e; background: #f1f4f8; }.submit-button:disabled { opacity: .6; cursor: wait; }
@media(max-width:760px){.form-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.new-person-form{grid-template-columns:repeat(2,minmax(0,1fr))}.new-person-form .photo-input{grid-column:auto}}
@media(max-width:520px){.form-grid,.new-person-form{grid-template-columns:1fr}.form-grid .wide,.new-person-form .wide,.new-person-form .photo-input{grid-column:auto}.declarant-tools{display:grid}.form-actions{flex-direction:column-reverse}.form-actions button{width:100%}}
</style>
