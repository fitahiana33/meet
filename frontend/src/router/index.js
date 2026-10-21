import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/authStore";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/",
      name: "root-login",
      component: () => import("@/views/auth/Login.vue"),
    },
    {
      path: "/login",
      name: "login",
      component: () => import("@/views/auth/Login.vue"),
      meta: { guestOnly: true },
    },
    {
      path: "/forgot-password",
      name: "forgot-password",
      component: () => import("@/views/auth/Login.vue"),
      meta: { guestOnly: true, passwordReset: true },
    },
    {
      path: "/reset-password",
      name: "reset-password",
      component: () => import("@/views/auth/ResetPassword.vue"),
    },
    {
      path: "/home",
      component: () => import("@/layouts/FrontofficeLayout.vue"),
      meta: { requiresAuth: true },
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
      component: () => import("@/layouts/BackofficeLayoutV2.vue"),
      meta: { requiresAuth: true },
      children: [
        { path: "departments/create", name: "admin-department-create", component: () => import("@/views/admin/DepartmentCreateView.vue"), meta: { title: "Nouveau département", subtitle: "Ajoutez un département responsable." } },
        { path: "departments/:id/edit", name: "admin-department-edit", component: () => import("@/views/admin/DepartmentEditView.vue"), meta: { title: "Modifier un département", subtitle: "Mettez à jour ce référentiel." } },
        { path: "sectors/create", name: "admin-sector-create", component: () => import("@/views/admin/SectorCreateView.vue"), meta: { title: "Nouveau secteur", subtitle: "Ajoutez un secteur de doléances." } },
        { path: "sectors/:id/edit", name: "admin-sector-edit", component: () => import("@/views/admin/SectorEditView.vue"), meta: { title: "Modifier un secteur", subtitle: "Mettez à jour ce référentiel." } },
        { path: "grievances", name: "admin-grievances", component: () => import("@/views/admin/GrievancesViewFinal.vue"), meta: { title: "Doléances", subtitle: "Suivez les demandes et leur traitement." } },
        { path: "grievances/create", name: "admin-grievance-create", component: () => import("@/views/admin/GrievanceCreateView.vue"), meta: { title: "Nouvelle doléance", subtitle: "Enregistrez une demande à suivre." } },
        { path: "grievances/:id/edit", name: "admin-grievance-edit", component: () => import("@/views/admin/GrievanceEditView.vue"), meta: { title: "Modifier une doléance", subtitle: "Mettez à jour la demande en conservant la traçabilité." } },
        { path: "grievances/:id", name: "admin-grievance-details", component: () => import("@/views/admin/GrievanceDetailsView.vue"), meta: { title: "Détail de la doléance", subtitle: "Consultez les informations et l’historique." } },
        { path: "departments", name: "admin-departments", component: () => import("@/views/admin/DepartmentsView.vue"), meta: { title: "Départements", subtitle: "Gérez les équipes responsables des doléances." } },
        { path: "sectors", name: "admin-sectors", component: () => import("@/views/admin/SectorsView.vue"), meta: { title: "Secteurs", subtitle: "Organisez les domaines des doléances." } },
        {
          path: "",
          name: "backoffice-home",
          component: () => import("@/views/backoffice/HomeViewV2.vue"),
          meta: { title: "Vue d’ensemble", subtitle: "Pilotez votre plateforme en toute simplicité." },
        },
        {
          path: "users",
          name: "admin-users",
          component: () => import("@/views/admin/UsersView.vue"),
          meta: { role: "admin", title: "Utilisateurs", subtitle: "Gérez les comptes et leurs accès." },
        },
        {
          path: "users/create",
          name: "admin-user-create",
          component: () => import("@/views/admin/UserCreateView.vue"),
          meta: { role: "admin", title: "Nouvel utilisateur", subtitle: "Ajoutez un compte à votre plateforme." },
        },
        {
          path: "users/:id/edit",
          name: "admin-user-edit",
          component: () => import("@/views/admin/UserEditView.vue"),
          meta: { role: "admin", title: "Modifier un utilisateur", subtitle: "Mettez à jour les informations du compte." },
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
    if (authStore.isAdmin) return { name: "backoffice-home" };
    if (authStore.isClient) return { name: "frontoffice-home" };
    return { name: "login" };
  }

  return true;
});

export default router;
