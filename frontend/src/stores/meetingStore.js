import { computed, ref } from "vue";
import { defineStore } from "pinia";
import * as meetingApi from "@/services/api/meetingApi";

function envelope(response) {
  return response?.data ?? response;
}

function resourceOf(response) {
  const data = envelope(response);
  return data?.data ?? data;
}

function collectionOf(response) {
  const data = envelope(response);
  return Array.isArray(data) ? data : data?.data ?? data?.meetings ?? [];
}

function errorOf(requestError, fallback) {
  const data = requestError?.response?.data;
  const firstValidationError = data?.errors && Object.values(data.errors).flat()[0];
  return firstValidationError || data?.message || requestError?.message || fallback;
}

export const useMeetingStore = defineStore("meetings", () => {
  const meetings = ref([]);
  const currentMeeting = ref(null);
  const joinRequests = ref([]);
  const loading = ref(false);
  const saving = ref(false);
  const requestsLoading = ref(false);
  const error = ref(null);
  const hasMeetings = computed(() => meetings.value.length > 0);

  async function fetchMeetings(params = {}, { silent = false } = {}) {
    if (!silent) {
      loading.value = true;
      error.value = null;
    }
    try {
      const response = await meetingApi.getMeetings(params);
      meetings.value = collectionOf(response);
      return meetings.value;
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de charger les réunions.");
      throw requestError;
    } finally {
      if (!silent) loading.value = false;
    }
  }

  async function fetchMeeting(id) {
    loading.value = true;
    error.value = null;
    try {
      const response = await meetingApi.getMeeting(id);
      currentMeeting.value = resourceOf(response);
      return currentMeeting.value;
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de charger cette réunion.");
      throw requestError;
    } finally {
      loading.value = false;
    }
  }

  async function createMeeting(payload) {
    saving.value = true;
    error.value = null;
    try {
      const response = await meetingApi.createMeeting(payload);
      return resourceOf(response);
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de créer la réunion.");
      throw requestError;
    } finally {
      saving.value = false;
    }
  }

  async function requestJoin(id) {
    saving.value = true;
    error.value = null;
    try {
      const response = await meetingApi.requestJoin(id);
      return resourceOf(response);
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible d’envoyer la demande.");
      throw requestError;
    } finally {
      saving.value = false;
    }
  }

  async function fetchJoinRequests(id) {
    requestsLoading.value = true;
    error.value = null;
    try {
      const response = await meetingApi.getJoinRequests(id);
      const data = envelope(response);
      joinRequests.value = Array.isArray(data) ? data : data?.data ?? data?.join_requests ?? [];
      return joinRequests.value;
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de charger les demandes.");
      throw requestError;
    } finally {
      requestsLoading.value = false;
    }
  }

  async function updateJoinRequest(meetingId, requestId, status) {
    saving.value = true;
    error.value = null;
    try {
      const response = await meetingApi.updateJoinRequest(meetingId, requestId, status);
      const updated = resourceOf(response);
      joinRequests.value = joinRequests.value.map((item) => item.id === updated.id ? updated : item);
      return updated;
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de traiter la demande.");
      throw requestError;
    } finally {
      saving.value = false;
    }
  }

  async function startMeeting(id) {
    saving.value = true;
    error.value = null;
    try {
      const response = await meetingApi.startMeeting(id);
      currentMeeting.value = resourceOf(response);
      return currentMeeting.value;
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de démarrer la réunion.");
      throw requestError;
    } finally {
      saving.value = false;
    }
  }

  async function getAccessToken(id) {
    try {
      const response = await meetingApi.getAccessToken(id);
      return envelope(response);
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible d’ouvrir la salle vidéo.");
      throw requestError;
    }
  }

  async function endMeeting(id) {
    saving.value = true;
    error.value = null;
    try {
      const response = await meetingApi.endMeeting(id);
      currentMeeting.value = resourceOf(response);
      return currentMeeting.value;
    } catch (requestError) {
      error.value = errorOf(requestError, "Impossible de terminer la réunion.");
      throw requestError;
    } finally {
      saving.value = false;
    }
  }

  return {
    meetings, currentMeeting, joinRequests, loading, saving, requestsLoading, error, hasMeetings,
    fetchMeetings, fetchMeeting, createMeeting, requestJoin, fetchJoinRequests, updateJoinRequest, startMeeting,
    getAccessToken, endMeeting,
  };
});
