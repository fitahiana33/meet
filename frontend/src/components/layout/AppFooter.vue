<script setup>
const currentYear = new Date().getFullYear();
</script>

<template>
  <footer class="app-footer"><span>© {{ currentYear }} Meeting Platform</span><span>Une expérience simple pour mieux collaborer.</span></footer>
</template>

<style scoped>
.app-footer { display: flex; justify-content: space-between; gap: 1rem; padding: 1.3rem 2.6rem; border-top: 1px solid #e8edf5; color: #94a3b8; font-size: .7rem; background: #fff; }
@media (max-width: 600px) { .app-footer { display: block; padding: 1.2rem; line-height: 1.8; } }
</style>
