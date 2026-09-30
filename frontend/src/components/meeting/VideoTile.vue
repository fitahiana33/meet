<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from "vue";

const props = defineProps({ participantName: { type: String, default: "Participant" }, identity: { type: String, default: "" }, videoTrack: { default: null }, audioTrack: { default: null }, isLocal: Boolean });
const videoElement = ref(null);
const audioElement = ref(null);
let attachedVideo = null;
let attachedAudio = null;

function syncTracks() {
  if (attachedVideo && videoElement.value) attachedVideo.detach(videoElement.value);
  if (attachedAudio && audioElement.value) attachedAudio.detach(audioElement.value);
  attachedVideo = props.videoTrack;
  attachedAudio = props.audioTrack;
  if (attachedVideo && videoElement.value) attachedVideo.attach(videoElement.value);
  if (attachedAudio && audioElement.value && !props.isLocal) attachedAudio.attach(audioElement.value);
}

watch(() => [props.videoTrack, props.audioTrack, props.isLocal], syncTracks);
onMounted(syncTracks);
onBeforeUnmount(() => {
  if (attachedVideo && videoElement.value) attachedVideo.detach(videoElement.value);
  if (attachedAudio && audioElement.value) attachedAudio.detach(audioElement.value);
});
</script>

<template>
  <article class="video-tile"><video ref="videoElement" class="video" autoplay playsinline :muted="isLocal" /><div v-if="!videoTrack" class="avatar-placeholder"><span>{{ participantName.slice(0, 2).toUpperCase() }}</span><small>Caméra désactivée</small></div><div class="participant-name"><span class="live-dot" />{{ participantName }}<em v-if="isLocal">Vous</em></div><audio ref="audioElement" autoplay /></article>
</template>

<style scoped>
.video-tile { position: relative; min-height: 210px; overflow: hidden; border: 1px solid #2b3d5a; border-radius: .85rem; background: #17243a; box-shadow: 0 10px 20px rgb(15 23 42 / 15%); }.video { display: block; width: 100%; height: 100%; min-height: 210px; object-fit: cover; }.avatar-placeholder { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; flex-direction: column; gap: .7rem; color: #9db7e7; background: radial-gradient(circle at 50% 35%, #2a4771, #18263d 65%); }.avatar-placeholder span { display: grid; width: 4rem; height: 4rem; place-items: center; border-radius: 1.2rem; color: #dbeafe; background: #375b94; font: 800 1.25rem "Manrope", sans-serif; }.avatar-placeholder small { color: #90a4c4; font-size: .65rem; }.participant-name { position: absolute; right: .75rem; bottom: .7rem; left: .75rem; display: flex; align-items: center; gap: .35rem; padding: .45rem .55rem; border-radius: .45rem; color: #e8f0ff; background: rgb(8 18 34 / 65%); backdrop-filter: blur(5px); font-size: .7rem; font-weight: 700; }.participant-name em { margin-left: auto; color: #9dbcf6; font-size: .6rem; font-style: normal; }.live-dot { width: .4rem; height: .4rem; border-radius: 50%; background: #59d6a2; }
</style>
