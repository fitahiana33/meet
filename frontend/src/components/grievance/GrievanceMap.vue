<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from "vue";
import { useRouter } from "vue-router";
import L from "leaflet";
import "leaflet/dist/leaflet.css";

const props = defineProps({
  markers: { type: Array, default: () => [] },
  loading: Boolean,
});

const router = useRouter();
const mapElement = ref(null);
const mapError = ref("");
let map;
let markerLayer;
const statusColors = {
  submitted: "#4c7ce2",
  in_progress: "#d5a43d",
  processed: "#32a47b",
  rejected: "#c66772",
};
const statusLabels = {
  submitted: "Soumise",
  in_progress: "En cours",
  processed: "Traitée",
  rejected: "Rejetée",
};

function createPopup(marker) {
  const content = document.createElement("div");
  content.className = "grievance-map-popup";
  const heading = document.createElement("strong");
  heading.textContent = marker.entity;
  content.append(heading);
  if (marker.address) {
    const address = document.createElement("small");
    address.textContent = marker.address;
    content.append(address);
  }

  const list = document.createElement("ul");
  marker.grievances.forEach((grievance) => {
    const item = document.createElement("li");
    const link = document.createElement("a");
    link.href = router.resolve({ name: "admin-grievance-details", params: { id: grievance.id } }).href;
    link.textContent = `${grievance.code} · ${grievance.title}`;
    link.addEventListener("click", (event) => {
      event.preventDefault();
      router.push({ name: "admin-grievance-details", params: { id: grievance.id } });
    });
    const status = document.createElement("small");
    status.textContent = statusLabels[grievance.status] || grievance.status;
    item.append(link, status);
    list.append(item);
  });
  content.append(list);

  return content;
}

function updateMarkers(markers) {
  if (!map || !markerLayer) return;
  markerLayer.clearLayers();

  markers.forEach((marker) => {
    const counts = marker.grievances.reduce((result, grievance) => {
      result[grievance.status] = (result[grievance.status] || 0) + 1;
      return result;
    }, {});
    const chips = Object.entries(counts)
      .filter(([status]) => statusColors[status])
      .map(([status, count]) => `<span style="--marker-status-color:${statusColors[status]}">${count}</span>`)
      .join("");
    const icon = L.divIcon({
      className: "grievance-entity-marker",
      html: `<span class="marker-count">${marker.grievances.length}</span><span class="marker-statuses">${chips}</span>`,
      iconSize: [48, 38],
      iconAnchor: [24, 38],
      popupAnchor: [0, -34],
    });
    L.marker([marker.latitude, marker.longitude], { icon })
      .bindPopup(createPopup(marker), { maxWidth: 300 })
      .addTo(markerLayer);
  });

  if (markers.length) {
    const bounds = L.latLngBounds(markers.map((marker) => [marker.latitude, marker.longitude]));
    map.fitBounds(bounds, { padding: [32, 32], maxZoom: 15 });
  } else {
    map.setView([-18.8792, 47.5079], 12);
  }
}

onMounted(() => {
  try {
    map = L.map(mapElement.value, { scrollWheelZoom: false }).setView([-18.8792, 47.5079], 12);
    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      maxZoom: 19,
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    }).addTo(map);
    markerLayer = L.layerGroup().addTo(map);
    updateMarkers(props.markers);
    window.setTimeout(() => map?.invalidateSize(), 0);
  } catch {
    mapError.value = "La carte n’a pas pu être initialisée.";
  }
});

watch(() => props.markers, updateMarkers, { deep: true });

onBeforeUnmount(() => {
  map?.remove();
  map = null;
});
</script>

<template>
  <div class="map-frame">
    <div ref="mapElement" class="map-canvas" role="application" aria-label="Carte des doléances localisées par entité" />
    <p v-if="mapError" class="map-error" role="alert">{{ mapError }}</p>
    <div class="map-legend" aria-label="Légende des statuts">
      <span v-for="(label, status) in statusLabels" :key="status"><i :style="{ background: statusColors[status] }" />{{ label }}</span>
      <small>{{ markers.length }} entité{{ markers.length === 1 ? "" : "s" }} localisée{{ markers.length === 1 ? "" : "s" }}</small>
    </div>
    <p v-if="!loading && !markers.length" class="map-empty">Aucune doléance filtrée ne possède une entité avec des coordonnées.</p>
  </div>
</template>

<style>
.map-frame { position: relative; overflow: hidden; border: 1px solid #e5ebf3; border-radius: .7rem; background: #eef3f8; }
.map-canvas { width: 100%; height: 350px; z-index: 0; }
.map-legend { display: flex; align-items: center; flex-wrap: wrap; gap: .5rem .9rem; padding: .55rem .7rem; border-top: 1px solid #e5ebf3; background: #fff; }
.map-legend span { display: inline-flex; align-items: center; gap: .3rem; color: #66768c; font-size: .62rem; }
.map-legend i { width: .48rem; height: .48rem; border-radius: 50%; }
.map-legend small { margin-left: auto; color: #8b99ab; font-size: .61rem; }
.map-empty { position: absolute; top: 50%; left: 50%; max-width: 280px; margin: 0; padding: .65rem .8rem; border: 1px solid #e5ebf3; border-radius: .5rem; color: #66768c; background: rgb(255 255 255 / 94%); font-size: .68rem; text-align: center; transform: translate(-50%,-50%); pointer-events: none; }
.map-error { margin: 0; padding: .65rem; color: #b33e4b; background: #fff3f4; font-size: .68rem; }
.grievance-entity-marker { border: 0; background: transparent; }
.grievance-entity-marker .marker-count { display: grid; width: 27px; height: 27px; margin: 0 auto; place-items: center; border: 2px solid #fff; border-radius: 50%; color: #fff; background: #365fb7; box-shadow: 0 2px 8px rgb(20 35 60 / 35%); font-size: 10px; font-weight: 800; }
.grievance-entity-marker .marker-statuses { display: flex; justify-content: center; gap: 2px; margin-top: -3px; }
.grievance-entity-marker .marker-statuses span { display: grid; min-width: 14px; height: 14px; padding: 0 2px; place-items: center; border: 1px solid #fff; border-radius: 999px; color: #fff; background: var(--marker-status-color); box-shadow: 0 1px 3px rgb(20 35 60 / 25%); font-size: 8px; font-weight: 800; }
.grievance-map-popup strong,.grievance-map-popup>small { display: block; }
.grievance-map-popup strong { color: #263650; font-size: 12px; }
.grievance-map-popup>small { margin-top: 2px; color: #8998ab; font-size: 10px; }
.grievance-map-popup ul { display: grid; gap: 6px; max-height: 180px; overflow: auto; margin: 8px 0 0; padding: 0; list-style: none; }
.grievance-map-popup li { display: grid; gap: 2px; }
.grievance-map-popup a { color: #315fc5; font-size: 11px; font-weight: 700; text-decoration: none; }
.grievance-map-popup li small { color: #8998ab; font-size: 10px; }
@media(max-width:600px) { .map-canvas { height: 280px; }.map-legend small { width: 100%; margin-left: 0; } }
</style>
