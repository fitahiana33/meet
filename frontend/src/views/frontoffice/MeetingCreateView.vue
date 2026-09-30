<script setup>
import { RouterLink, useRouter } from "vue-router";
import MeetingForm from "@/components/meeting/MeetingForm.vue";
import { useMeetingStore } from "@/stores/meetingStore";

const router = useRouter();
const meetingStore = useMeetingStore();
async function submit(payload) { try { const meeting = await meetingStore.createMeeting(payload); router.push({ name: "frontoffice-meeting-details", params: { id: meeting.id } }); } catch { /* displayed in the form */ } }
</script>

<template>
  <section class="form-page"><RouterLink :to="{ name: 'frontoffice-meetings' }" class="back-link">← Retour aux réunions</RouterLink><div class="page-intro"><p class="page-eyebrow">Collaboration / Réunions</p><h2>Créer une réunion</h2><p>Préparez un nouvel espace vidéo et invitez les participants de votre choix.</p></div><MeetingForm :loading="meetingStore.saving" :error="meetingStore.error" @submit="submit" @cancel="router.push({ name: 'frontoffice-meetings' })" /></section>
</template>

<style scoped>
.form-page { max-width: 900px; margin: 0 auto; }.back-link { display: inline-block; margin-bottom: 1.6rem; color: #7186ae; font-size: .72rem; font-weight: 700; text-decoration: none; }.back-link:hover { color: #4777da; }.page-eyebrow { margin: 0 0 .55rem; color: #6d91e4; font-size: .68rem; font-weight: 800; letter-spacing: .15em; text-transform: uppercase; }.page-intro h2 { margin: 0; color: #1c2d46; font-size: 2rem; letter-spacing: -.06em; }.page-intro p:last-child { margin: .6rem 0 1.6rem; color: #8b99ab; font-size: .8rem; }
</style>
