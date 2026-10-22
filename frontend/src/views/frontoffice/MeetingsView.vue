<script setup>
import { onBeforeUnmount, onMounted, ref } from "vue";
import { RouterLink, useRouter } from "vue-router";
import MeetingCard from "@/components/meeting/MeetingCard.vue";
import PaginationControls from "@/components/common/PaginationControls.vue";
import { useMeetingStore } from "@/stores/meetingStore";

const router = useRouter();
const meetingStore = useMeetingStore();
let refreshTimer;
let hasLoadedMeetings = false;
let openingRoom = false;
const perPage = 10;
const currentPage = ref(1);

async function refreshMeetings(silent = false) {
  const previouslyJoinable = new Set(meetingStore.meetings.filter((meeting) => meeting.can_join).map((meeting) => meeting.id));

  try {
    const meetings = await meetingStore.fetchMeetings({ page: currentPage.value, per_page: perPage }, { silent });
    const newlyJoinable = hasLoadedMeetings
      ? meetings.find((meeting) => meeting.can_join && !previouslyJoinable.has(meeting.id))
      : null;
    hasLoadedMeetings = true;

    if (newlyJoinable && !openingRoom) {
      openingRoom = true;
      await router.push({ name: "frontoffice-meeting-room", params: { id: newlyJoinable.id } });
    }
  } catch {
    // The store keeps the API error for the page to display.
  }
}

onMounted(() => {
  void refreshMeetings();
  refreshTimer = window.setInterval(() => {
    if (document.visibilityState === "visible") void refreshMeetings(true);
  }, 5000);
});

onBeforeUnmount(() => window.clearInterval(refreshTimer));
function openMeeting(meeting) { router.push({ name: "frontoffice-meeting-details", params: { id: meeting.id } }); }
async function requestMeeting(meeting) { try { await meetingStore.requestJoin(meeting.id); await refreshMeetings(); } catch { /* displayed above the list */ } }
function joinMeeting(meeting) { router.push({ name: "frontoffice-meeting-room", params: { id: meeting.id } }); }
async function changePage(page) { currentPage.value = page; await refreshMeetings(); }
</script>

<template>
  <section class="content-page">
    <div class="page-toolbar"><div><p class="page-eyebrow">Votre espace / Collaboration</p><h2>Réunions</h2><p class="page-description">Découvrez les réunions disponibles et retrouvez vos espaces de collaboration.</p></div><RouterLink :to="{ name: 'frontoffice-meeting-create' }" class="primary-button"><span>＋</span> Créer une réunion</RouterLink></div>
    <div v-if="meetingStore.error" class="alert" role="alert"><span>!</span>{{ meetingStore.error }}</div>
    <div v-if="meetingStore.loading" class="loading-state">Chargement des réunions…</div>
    <div v-else-if="!meetingStore.meetings.length" class="empty-state"><span>◉</span><h3>Aucune réunion disponible</h3><p>Créez votre première réunion pour commencer à collaborer.</p><RouterLink :to="{ name: 'frontoffice-meeting-create' }" class="secondary-link">Créer une réunion →</RouterLink></div>
    <div v-else><div class="meeting-grid"><MeetingCard v-for="meeting in meetingStore.meetings" :key="meeting.id" :meeting="meeting" @open="openMeeting" @request="requestMeeting" @join="joinMeeting" /></div><PaginationControls v-if="meetingStore.pagination" :current-page="meetingStore.pagination.current_page" :last-page="meetingStore.pagination.last_page" :total="meetingStore.pagination.total" @change="changePage" /></div>
  </section>
</template>

<style scoped>
.content-page { max-width: 1180px; margin: 0 auto; }.page-toolbar { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 1.7rem; }.page-eyebrow { margin: 0 0 .55rem; color: #6d91e4; font-size: .68rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }.page-toolbar h2 { margin: 0; color: #1c2d46; font-size: 2rem; letter-spacing: -.06em; }.page-description { margin: .6rem 0 0; color: #8b99ab; font-size: .8rem; }.primary-button { display: inline-flex; align-items: center; gap: .35rem; padding: .75rem 1rem; border-radius: .55rem; color: #fff; background: #4b79dc; box-shadow: 0 8px 18px rgb(59 105 218 / 17%); font-size: .76rem; font-weight: 700; text-decoration: none; }.primary-button span { font-size: 1.1rem; font-weight: 400; }.alert { display: flex; align-items: center; gap: .55rem; margin-bottom: 1rem; padding: .75rem .85rem; border: 1px solid #ffd7db; border-radius: .6rem; color: #b33e4b; background: #fff3f4; font-size: .76rem; }.alert span { display: grid; width: 1.1rem; height: 1.1rem; place-items: center; border-radius: 50%; color: #fff; background: #e36c76; font-weight: 800; }.meeting-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(285px, 1fr)); gap: 1rem; }.loading-state, .empty-state { padding: 4rem 1rem; color: #93a0b1; font-size: .78rem; text-align: center; }.empty-state { border: 1px dashed #d7e1ef; border-radius: .9rem; background: #fff; }.empty-state > span { display: grid; width: 3rem; height: 3rem; place-items: center; margin: 0 auto; border-radius: 1rem; color: #4b79dc; background: #edf3ff; font-size: 1.3rem; }.empty-state h3 { margin: 1rem 0 .4rem; color: #33445d; font-size: .9rem; }.empty-state p { margin: 0 0 1rem; }.secondary-link { color: #4b79dc; font-size: .72rem; font-weight: 700; text-decoration: none; }
@media (max-width: 650px) { .page-toolbar { display: block; }.primary-button { margin-top: 1.1rem; } }
</style>
