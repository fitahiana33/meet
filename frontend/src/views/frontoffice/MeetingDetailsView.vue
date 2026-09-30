<script setup>
import { computed, onBeforeUnmount, onMounted } from "vue";
import { RouterLink, useRoute, useRouter } from "vue-router";
import JoinRequestList from "@/components/meeting/JoinRequestList.vue";
import { useMeetingStore } from "@/stores/meetingStore";

const route = useRoute();
const router = useRouter();
const meetingStore = useMeetingStore();
const meeting = computed(() => meetingStore.currentMeeting);
let refreshTimer;
let hasLoadedMeeting = false;

async function refreshMeeting() {
  const wasJoinable = meeting.value?.can_join ?? false;

  try {
    const loaded = await meetingStore.fetchMeeting(route.params.id);
    if (loaded?.is_creator) await meetingStore.fetchJoinRequests(route.params.id);

    if (hasLoadedMeeting && !wasJoinable && loaded?.can_join) openRoom();
    hasLoadedMeeting = true;
  } catch {
    // The store keeps the API error for the page to display.
  }
}

onMounted(() => {
  void refreshMeeting();
  refreshTimer = window.setInterval(refreshMeeting, 5000);
});

onBeforeUnmount(() => window.clearInterval(refreshTimer));

async function requestJoin() { try { await meetingStore.requestJoin(route.params.id); await refreshMeeting(); } catch {} }
async function decide(request, status) { try { await meetingStore.updateJoinRequest(route.params.id, request.id, status); await refreshMeeting(); } catch {} }
async function startMeeting() { try { await meetingStore.startMeeting(route.params.id); openRoom(); } catch {} }
async function endMeeting() { if (window.confirm("Terminer cette réunion ? Les nouveaux accès seront bloqués.")) await meetingStore.endMeeting(route.params.id); }
function openRoom() { router.push({ name: "frontoffice-meeting-room", params: { id: route.params.id } }); }
function formatDate(value) { return value ? new Intl.DateTimeFormat("fr-FR", { dateStyle: "full", timeStyle: "short" }).format(new Date(value)) : "Date à définir"; }
function statusLabel(status) { return { scheduled: "Programmée", live: "En direct", ended: "Terminée", cancelled: "Annulée" }[status] || status; }
</script>

<template>
  <section v-if="meeting" class="details-page"><RouterLink :to="{ name: 'frontoffice-meetings' }" class="back-link">← Retour aux réunions</RouterLink><div class="details-hero"><div><span class="status" :class="meeting.status"><i /> {{ statusLabel(meeting.status) }}</span><h2>{{ meeting.title }}</h2><p>{{ meeting.description || "Aucune description pour cette réunion." }}</p></div><div class="hero-actions"><button v-if="meeting.can_start" type="button" class="primary-button" :disabled="meetingStore.saving" @click="startMeeting">{{ meetingStore.saving ? "Démarrage…" : "Démarrer la réunion" }} <span>→</span></button><button v-else-if="meeting.can_host || meeting.can_join" type="button" class="primary-button" @click="openRoom">Entrer dans la salle <span>→</span></button><button v-else-if="meeting.my_request?.status === 'pending'" type="button" class="pending-button" disabled>Demande en attente</button><button v-else-if="meeting.my_request?.status === 'accepted'" type="button" class="pending-button" disabled>Acceptée — en attente du démarrage</button><button v-else-if="!meeting.is_creator && meeting.status !== 'ended'" type="button" class="secondary-button" @click="requestJoin">Demander à rejoindre</button><button v-if="meeting.is_creator && meeting.status === 'live'" type="button" class="end-button" @click="endMeeting">Terminer la réunion</button></div></div><div class="info-grid"><div><span class="info-label">Créateur</span><strong>{{ meeting.creator?.name }}</strong></div><div><span class="info-label">Programmée le</span><strong>{{ formatDate(meeting.scheduled_at) }}</strong></div><div><span class="info-label">Participants acceptés</span><strong>{{ meeting.participants_count }} personne{{ meeting.participants_count > 1 ? 's' : '' }}</strong></div></div><JoinRequestList v-if="meeting.is_creator" :requests="meetingStore.joinRequests" :loading="meetingStore.requestsLoading" :processing="meetingStore.saving" @decide="decide" /><div v-if="meetingStore.error" class="error-box">{{ meetingStore.error }}</div></section><section v-else class="loading-state">{{ meetingStore.loading ? "Chargement de la réunion…" : meetingStore.error || "Réunion introuvable." }}</section>
</template>

<style scoped>
.details-page { max-width: 960px; margin: 0 auto; }.back-link { display: inline-block; margin-bottom: 1.6rem; color: #7186ae; font-size: .72rem; font-weight: 700; text-decoration: none; }.details-hero { display: flex; align-items: flex-start; justify-content: space-between; gap: 2rem; padding: 2rem; border-radius: 1rem; color: #fff; background: linear-gradient(115deg, #2f5fc7, #5683e4); box-shadow: 0 15px 35px rgb(56 105 215 / 16%); }.status { display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .5rem; border-radius: .35rem; color: #f6cf79; background: rgb(255 255 255 / 14%); font-size: .62rem; font-weight: 700; }.status.live { color: #9aefca; }.status.ended { color: #d2dbe8; }.status i { width: .4rem; height: .4rem; border-radius: 50%; background: currentColor; }.details-hero h2 { margin: 1rem 0 .5rem; color: #fff; font-size: 2rem; letter-spacing: -.06em; }.details-hero p { max-width: 560px; margin: 0; color: #d6e3ff; font-size: .8rem; line-height: 1.6; }.hero-actions { display: grid; min-width: 165px; gap: .6rem; }.primary-button, .secondary-button, .end-button, .pending-button { padding: .75rem .9rem; border: 0; border-radius: .5rem; font-size: .7rem; font-weight: 700; cursor: pointer; }.primary-button { color: #325dbd; background: #fff; }.primary-button span { margin-left: .35rem; font-size: .95rem; }.secondary-button { color: #4777d6; background: #eff4ff; }.end-button { color: #ffd6db; border: 1px solid rgb(255 255 255 / 25%); background: transparent; }.pending-button { color: #a07b2e; background: #fff7df; cursor: default; }.info-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1rem; }.info-grid > div { display: grid; gap: .35rem; padding: 1rem; border: 1px solid #e8eef6; border-radius: .75rem; background: #fff; }.info-label { color: #9aa7b7; font-size: .64rem; }.info-grid strong { color: #33445d; font-size: .74rem; }.error-box { margin-top: 1rem; padding: .7rem; border-radius: .5rem; color: #b33e4b; background: #fff3f4; font-size: .74rem; }.loading-state { padding: 4rem; color: #93a0b1; font-size: .78rem; text-align: center; }
@media (max-width: 700px) { .details-hero { display: block; padding: 1.4rem; }.details-hero h2 { font-size: 1.65rem; }.hero-actions { margin-top: 1.4rem; }.info-grid { grid-template-columns: 1fr; } }
</style>
