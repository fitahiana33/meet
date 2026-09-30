<script setup>
import VideoTile from "@/components/meeting/VideoTile.vue";
defineProps({ participants: { type: Array, default: () => [] } });
</script>

<template><div class="video-grid"><VideoTile v-for="participant in participants" :key="participant.identity" v-bind="participant" /></div></template>

<style scoped>
.video-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(min(100%, 300px), 1fr)); gap: .85rem; }
</style>
