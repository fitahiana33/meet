<script setup>
const currentYear = new Date().getFullYear();
</script>

<template>
  <footer class="app-footer"><span>© {{ currentYear }} Meeting Platform</span><span>Une expérience simple pour mieux collaborer.</span></footer>
</template>

<style scoped>
.app-footer { display: flex; justify-content: space-between; gap: 1rem; padding: .8rem 1.5rem; border-top: 1px solid #e8edf5; color: #94a3b8; font-size: .64rem; background: #fff; }
@media (max-width: 600px) { .app-footer { display: block; padding: .65rem .8rem; line-height: 1.7; } }
</style>
