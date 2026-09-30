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
      name: "frontoffice-home",
      component: () => import("@/views/frontoffice/HomeView.vue"),
      meta: { requiresAuth: true, role: "client" },
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
        },
        {
          path: "users",
          name: "admin-users",
          component: () => import("@/views/admin/UsersView.vue"),
        },
        {
          path: "users/create",
          name: "admin-user-create",
          component: () => import("@/views/admin/UserCreateView.vue"),
        },
        {
          path: "users/:id/edit",
          name: "admin-user-edit",
          component: () => import("@/views/admin/UserEditView.vue"),
        },
        {
          path: "roles",
          name: "admin-roles",
          component: () => import("@/views/admin/RolesView.vue"),
        },
        {
          path: "roles/create",
          name: "admin-role-create",
          component: () => import("@/views/admin/RoleCreateView.vue"),
        },
        {
          path: "roles/:id/edit",
          name: "admin-role-edit",
          component: () => import("@/views/admin/RoleEditView.vue"),
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
