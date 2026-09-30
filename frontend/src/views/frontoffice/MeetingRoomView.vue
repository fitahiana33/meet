<script setup>
import { onBeforeUnmount, onMounted, ref } from "vue";
import { Room, RoomEvent } from "livekit-client";
import { RouterLink, useRoute, useRouter } from "vue-router";
import JoinRequestList from "@/components/meeting/JoinRequestList.vue";
import VideoGrid from "@/components/meeting/VideoGrid.vue";
import { useMeetingStore } from "@/stores/meetingStore";

const route = useRoute();
const router = useRouter();
const meetingStore = useMeetingStore();
const room = ref(null);
const participants = ref([]);
const connected = ref(false);
const loading = ref(true);
const error = ref("");
const cameraEnabled = ref(false);
const microphoneEnabled = ref(false);
let requestsTimer;

function participantEntry(participant, isLocal = false) {
  const videoPublication = Array.from(participant.videoTrackPublications.values()).find((publication) => publication.track);
  const audioPublication = Array.from(participant.audioTrackPublications.values()).find((publication) => publication.track);
  return { identity: participant.identity, participantName: participant.name || participant.identity, videoTrack: videoPublication?.track || null, audioTrack: isLocal ? null : audioPublication?.track || null, isLocal };
}

function syncParticipants() {
  if (!room.value) return;
  participants.value = [participantEntry(room.value.localParticipant, true), ...Array.from(room.value.remoteParticipants.values()).map((participant) => participantEntry(participant))];
}

function registerRoomEvents(liveRoom) {
  [RoomEvent.ParticipantConnected, RoomEvent.ParticipantDisconnected, RoomEvent.TrackSubscribed, RoomEvent.TrackUnsubscribed, RoomEvent.LocalTrackPublished, RoomEvent.LocalTrackUnpublished].forEach((event) => liveRoom.on(event, syncParticipants));
  liveRoom.on(RoomEvent.Disconnected, () => { connected.value = false; });
}

async function connect() {
  loading.value = true;
  error.value = "";
  try {
    const meeting = await meetingStore.fetchMeeting(route.params.id);
    if (!meeting?.can_join && !meeting?.can_host) throw new Error("Vous n’avez pas encore l’autorisation de rejoindre cette réunion.");
    if (meeting.is_creator) await meetingStore.fetchJoinRequests(route.params.id);
    const credentials = await meetingStore.getAccessToken(route.params.id);
    const liveRoom = new Room({ adaptiveStream: true, dynacast: true });
    registerRoomEvents(liveRoom);
    await liveRoom.connect(credentials.server_url, credentials.participant_token);
    room.value = liveRoom;
    connected.value = true;
    await liveRoom.localParticipant.enableCameraAndMicrophone();
    cameraEnabled.value = true;
    microphoneEnabled.value = true;
    syncParticipants();
  } catch (requestError) {
    error.value = meetingStore.error || requestError?.message || "Impossible d’ouvrir la salle vidéo.";
  } finally {
    loading.value = false;
  }
}

async function decideJoinRequest(request, status) {
  try {
    await meetingStore.updateJoinRequest(route.params.id, request.id, status);
    await meetingStore.fetchJoinRequests(route.params.id);
  } catch {
    // The store keeps the API error for the room to display.
  }
}

async function refreshHostRequests() {
  if (!connected.value || !meetingStore.currentMeeting?.is_creator || document.visibilityState !== "visible") return;

  try {
    await meetingStore.fetchJoinRequests(route.params.id);
  } catch {
    // Keep the current request list when a temporary refresh fails.
  }
}

async function toggleCamera() { cameraEnabled.value = !cameraEnabled.value; await room.value?.localParticipant.setCameraEnabled(cameraEnabled.value); syncParticipants(); }
async function toggleMicrophone() { microphoneEnabled.value = !microphoneEnabled.value; await room.value?.localParticipant.setMicrophoneEnabled(microphoneEnabled.value); syncParticipants(); }
async function leaveRoom() { room.value?.disconnect(); router.push({ name: "frontoffice-meeting-details", params: { id: route.params.id } }); }
onMounted(() => {
  void connect();
  requestsTimer = window.setInterval(refreshHostRequests, 5000);
});
onBeforeUnmount(() => {
  window.clearInterval(requestsTimer);
  room.value?.disconnect();
});
</script>

<template>
  <section class="room-page"><div class="room-header"><div><RouterLink :to="{ name: 'frontoffice-meeting-details', params: { id: route.params.id } }" class="back-link">← Quitter la salle</RouterLink><div class="room-title"><span class="live-indicator" :class="{ connected }" /><h2>{{ meetingStore.currentMeeting?.title || "Salle vidéo" }}</h2></div><p>{{ participants.length }} participant{{ participants.length > 1 ? 's' : '' }} connecté{{ participants.length > 1 ? 's' : '' }}</p></div><span class="room-status">{{ connected ? "Session en direct" : "Connexion…" }}</span></div><div v-if="error" class="room-error"><strong>Accès impossible</strong><span>{{ error }}</span><button type="button" @click="connect">Réessayer</button></div><div v-else-if="loading" class="room-loading"><span class="spinner" />Préparation de votre salle vidéo…</div><template v-else><VideoGrid :participants="participants" /><JoinRequestList v-if="meetingStore.currentMeeting?.is_creator" :requests="meetingStore.joinRequests" :loading="meetingStore.requestsLoading" :processing="meetingStore.saving" @decide="decideJoinRequest" /></template><div v-if="meetingStore.error" class="room-error" role="alert">{{ meetingStore.error }}</div><div v-if="connected" class="room-controls"><button type="button" :class="{ active: microphoneEnabled }" @click="toggleMicrophone">{{ microphoneEnabled ? "Micro activé" : "Micro désactivé" }}</button><button type="button" :class="{ active: cameraEnabled }" @click="toggleCamera">{{ cameraEnabled ? "Caméra activée" : "Caméra désactivée" }}</button><button type="button" class="leave-button" @click="leaveRoom">Quitter la réunion</button></div></section>
</template>

<style scoped>
.room-page { max-width: 1180px; margin: 0 auto; }.room-header { display: flex; align-items: flex-end; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }.back-link { display: inline-block; margin-bottom: .9rem; color: #7186ae; font-size: .72rem; font-weight: 700; text-decoration: none; }.room-title { display: flex; align-items: center; gap: .6rem; }.room-title h2 { margin: 0; color: #1c2d46; font-size: 1.8rem; letter-spacing: -.06em; }.room-header p { margin: .45rem 0 0 1.2rem; color: #8b99ab; font-size: .72rem; }.live-indicator { width: .55rem; height: .55rem; border-radius: 50%; background: #f2bf54; box-shadow: 0 0 0 4px rgb(242 191 84 / 13%); }.live-indicator.connected { background: #4fc392; box-shadow: 0 0 0 4px rgb(79 195 146 / 13%); }.room-status { padding: .45rem .65rem; border-radius: .4rem; color: #3a9f78; background: #eaf9f2; font-size: .67rem; font-weight: 700; }.room-loading { display: flex; min-height: 360px; align-items: center; justify-content: center; gap: .7rem; border: 1px dashed #d7e1ef; border-radius: .9rem; color: #93a0b1; background: #fff; font-size: .76rem; }.spinner { width: 1rem; height: 1rem; border: 2px solid #d7e4fa; border-top-color: #4b79dc; border-radius: 50%; animation: spin .8s linear infinite; }.room-error { display: grid; gap: .5rem; min-height: 220px; place-content: center; padding: 1.5rem; border: 1px solid #ffd7db; border-radius: .9rem; color: #b33e4b; background: #fff5f5; font-size: .75rem; text-align: center; }.room-error strong { font-size: .95rem; }.room-error button { justify-self: center; margin-top: .5rem; padding: .6rem .85rem; border: 0; border-radius: .45rem; color: #fff; background: #d96773; font-size: .7rem; font-weight: 700; cursor: pointer; }.room-controls { display: flex; align-items: center; justify-content: center; gap: .6rem; margin-top: 1rem; padding: .8rem; border: 1px solid #e8eef6; border-radius: .8rem; background: #fff; }.room-controls button { padding: .65rem .8rem; border: 1px solid #dce5f3; border-radius: .45rem; color: #718096; background: #f8fafc; font-size: .68rem; font-weight: 700; cursor: pointer; }.room-controls button.active { color: #3c73d4; border-color: #c7d8f7; background: #eff4ff; }.room-controls .leave-button { color: #c35460; border-color: #f5d4d8; background: #fff3f4; }
@keyframes spin { to { transform: rotate(360deg); } }
@media (max-width: 650px) { .room-header { display: block; }.room-status { display: inline-block; margin-top: 1rem; }.room-controls { align-items: stretch; flex-direction: column; }.room-controls button { width: 100%; } }
</style>
