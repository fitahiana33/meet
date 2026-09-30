<script setup>
defineProps({ meeting: { type: Object, required: true } });
defineEmits(["open", "request", "join"]);

function statusLabel(status) {
  return { scheduled: "Programmée", live: "En direct", ended: "Terminée", cancelled: "Annulée" }[status] || status;
}

function formatDate(value) {
  if (!value) return "Date à définir";
  return new Intl.DateTimeFormat("fr-FR", { dateStyle: "medium", timeStyle: "short" }).format(new Date(value));
}
</script>

<template>
  <article class="meeting-card">
    <div class="card-top"><span class="meeting-symbol">◉</span><span class="status" :class="meeting.status"><i /> {{ statusLabel(meeting.status) }}</span></div>
    <button type="button" class="card-body" @click="$emit('open', meeting)"><h3>{{ meeting.title }}</h3><p>{{ meeting.description || "Aucune description" }}</p></button>
    <div class="card-meta"><span>◷ {{ formatDate(meeting.scheduled_at) }}</span><span>♙ {{ meeting.participants_count }} participant{{ meeting.participants_count > 1 ? "s" : "" }}</span></div>
    <div class="card-footer"><span>Créée par <strong>{{ meeting.is_creator ? "vous" : meeting.creator?.name || "Utilisateur" }}</strong></span><button v-if="meeting.is_creator" type="button" class="manage-button" @click="$emit('open', meeting)">Gérer les demandes</button><button v-else-if="meeting.can_join" type="button" class="join-button" @click="$emit('join', meeting)">Rejoindre <span>→</span></button><button v-else-if="meeting.my_request?.status === 'pending'" type="button" class="pending-button" disabled>Demande envoyée</button><button v-else-if="meeting.my_request?.status === 'accepted'" type="button" class="pending-button" disabled>{{ meeting.status === 'live' ? "Accès accepté" : "Acceptée — en attente du démarrage" }}</button><button v-else type="button" class="request-button" @click="$emit('request', meeting)">Demander à rejoindre</button></div>
  </article>
</template>

<style scoped>
.meeting-card { display: flex; min-height: 235px; flex-direction: column; padding: 1.15rem; border: 1px solid #e8eef6; border-radius: .9rem; background: #fff; box-shadow: 0 5px 15px rgb(30 52 84 / 3%); transition: .2s; }.meeting-card:hover { border-color: #c7d7f7; box-shadow: 0 12px 25px rgb(30 52 84 / 7%); transform: translateY(-2px); }.card-top, .card-footer, .card-meta { display: flex; align-items: center; justify-content: space-between; gap: .75rem; }.meeting-symbol { display: grid; width: 2.2rem; height: 2.2rem; place-items: center; border-radius: .65rem; color: #4b7bdd; background: #eaf1ff; }.status { display: inline-flex; align-items: center; gap: .35rem; padding: .3rem .45rem; border-radius: .35rem; color: #a07b2e; background: #fff7df; font-size: .62rem; font-weight: 700; }.status.live { color: #2f9b73; background: #eaf9f2; }.status.ended { color: #8592a2; background: #f1f4f7; }.status i { width: .38rem; height: .38rem; border-radius: 50%; background: currentColor; }.card-body { display: block; flex: 1; padding: 1.15rem 0 .9rem; border: 0; color: inherit; background: none; text-align: left; cursor: pointer; }.card-body h3 { margin: 0; overflow: hidden; color: #253650; font-size: .95rem; text-overflow: ellipsis; white-space: nowrap; }.card-body p { display: -webkit-box; margin: .5rem 0 0; overflow: hidden; color: #8c9bad; font-size: .73rem; line-height: 1.5; -webkit-box-orient: vertical; -webkit-line-clamp: 2; }.card-meta { padding-bottom: 1rem; border-bottom: 1px solid #eff2f6; color: #9aa7b7; font-size: .64rem; }.card-footer { min-height: 2.2rem; padding-top: .8rem; color: #98a5b5; font-size: .64rem; }.card-footer strong { color: #65758c; }.card-footer button { padding: .45rem .6rem; border: 0; border-radius: .4rem; font-size: .64rem; font-weight: 700; cursor: pointer; }.join-button, .request-button { color: #fff; background: #4b79dc; }.join-button span { margin-left: .25rem; font-size: .85rem; }.request-button { color: #4b79dc; border: 1px solid #d8e4fb !important; background: #f1f5ff; }.manage-button { color: #4b79dc; background: #edf3ff; }.pending-button { color: #a07b2e; background: #fff7df; cursor: default !important; }
</style>
