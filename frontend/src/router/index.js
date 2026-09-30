import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/authStore";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/login",
      name: "login",
      component: () => import("@/views/auth/Login.vue"),
      meta: { guestOnly: true },
    },
    { path: "/", redirect: "/home" },
    {
      path: "/home",
      component: () => import("@/layouts/FrontofficeLayout.vue"),
      meta: { requiresAuth: true, role: "client" },
      children: [
        {
          path: "",
          name: "frontoffice-home",
          component: () => import("@/views/frontoffice/HomeView.vue"),
          meta: { title: "Accueil", subtitle: "Retrouvez votre espace de travail." },
        },
        {
          path: "meetings",
          name: "frontoffice-meetings",
          component: () => import("@/views/frontoffice/MeetingsView.vue"),
          meta: { title: "Réunions", subtitle: "Retrouvez les réunions accessibles et vos demandes." },
        },
        {
          path: "meetings/create",
          name: "frontoffice-meeting-create",
          component: () => import("@/views/frontoffice/MeetingCreateView.vue"),
          meta: { title: "Nouvelle réunion", subtitle: "Créez un espace de réunion pour votre équipe." },
        },
        {
          path: "meetings/:id",
          name: "frontoffice-meeting-details",
          component: () => import("@/views/frontoffice/MeetingDetailsView.vue"),
          meta: { title: "Détails de la réunion", subtitle: "Consultez l’accès et les participants." },
        },
        {
          path: "meetings/:id/room",
          name: "frontoffice-meeting-room",
          component: () => import("@/views/frontoffice/MeetingRoomView.vue"),
          meta: { title: "Salle vidéo", subtitle: "Participez à la réunion en direct." },
        },
      ],
    },
    {
      path: "/admin",
      component: () => import("@/layouts/BackofficeLayout.vue"),
      meta: { requiresAuth: true, role: "admin" },
      children: [
        {
          path: "",
          name: "backoffice-home",
          component: () => import("@/views/backoffice/HomeView.vue"),
          meta: { title: "Vue d’ensemble", subtitle: "Pilotez votre plateforme en toute simplicité." },
        },
        {
          path: "users",
          name: "admin-users",
          component: () => import("@/views/admin/UsersView.vue"),
          meta: { title: "Utilisateurs", subtitle: "Gérez les comptes et leurs accès." },
        },
        {
          path: "users/create",
          name: "admin-user-create",
          component: () => import("@/views/admin/UserCreateView.vue"),
          meta: { title: "Nouvel utilisateur", subtitle: "Ajoutez un compte à votre plateforme." },
        },
        {
          path: "users/:id/edit",
          name: "admin-user-edit",
          component: () => import("@/views/admin/UserEditView.vue"),
          meta: { title: "Modifier un utilisateur", subtitle: "Mettez à jour les informations du compte." },
        },
        {
          path: "roles",
          name: "admin-roles",
          component: () => import("@/views/admin/RolesView.vue"),
          meta: { title: "Rôles", subtitle: "Organisez les niveaux d’accès de la plateforme." },
        },
        {
          path: "roles/create",
          name: "admin-role-create",
          component: () => import("@/views/admin/RoleCreateView.vue"),
          meta: { title: "Nouveau rôle", subtitle: "Créez un rôle adapté à votre organisation." },
        },
        {
          path: "roles/:id/edit",
          name: "admin-role-edit",
          component: () => import("@/views/admin/RoleEditView.vue"),
          meta: { title: "Modifier un rôle", subtitle: "Mettez à jour les informations du rôle." },
        },
      ],
    },
  ],
});

router.beforeEach(async (to) => {
  const authStore = useAuthStore();

  if (authStore.token && !authStore.user) {
    try {
      await authStore.hydrate();
    } catch {
      // The guard below redirects to the login page when the token is invalid.
    }
  }

  if (to.meta.guestOnly && authStore.isAuthenticated) {
    return authStore.isAdmin ? { name: "backoffice-home" } : { name: "frontoffice-home" };
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return { name: "login", query: { redirect: to.fullPath } };
  }

  if (to.meta.role && authStore.roleName !== to.meta.role) {
    return authStore.isAdmin ? { name: "backoffice-home" } : { name: "frontoffice-home" };
  }

  return true;
});

export default router;
