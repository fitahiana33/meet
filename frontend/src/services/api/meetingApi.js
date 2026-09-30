import api from "@/services/api";

export const getMeetings = (params = {}) => api.get("/meetings", { params });
export const getMeeting = (id) => api.get(`/meetings/${id}`);
export const createMeeting = (payload) => api.post("/meetings", payload);
export const requestJoin = (meetingId) => api.post(`/meetings/${meetingId}/join-requests`);
export const getJoinRequests = (meetingId) => api.get(`/meetings/${meetingId}/join-requests`);
export const updateJoinRequest = (meetingId, requestId, status) => api.patch(`/meetings/${meetingId}/join-requests/${requestId}`, { status });
export const startMeeting = (meetingId) => api.post(`/meetings/${meetingId}/start`);
export const getAccessToken = (meetingId) => api.post(`/meetings/${meetingId}/access-token`);
export const endMeeting = (meetingId) => api.post(`/meetings/${meetingId}/end`);
