import { createRouter, createWebHistory } from "vue-router";

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: "/login",
      name: "login",
      component: () => import("../views/auth/Login.vue"),
    },
    {
      path: "/dashboard",
      name: "dashboard",
      component: () => import("../views/Dashboard.vue"),
    },
    {
      path: "/admin/users",
      name: "admin-users",
      component: () => import("../views/admin/UsersView.vue"),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: "/admin/users/create",
      name: "admin-user-create",
      component: () => import("../views/admin/UserCreateView.vue"),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: "/admin/users/:id/edit",
      name: "admin-user-edit",
      component: () => import("../views/admin/UserEditView.vue"),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: "/admin/roles",
      name: "admin-roles",
      component: () => import("../views/admin/RolesView.vue"),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: "/admin/roles/create",
      name: "admin-role-create",
      component: () => import("../views/admin/RoleCreateView.vue"),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
    {
      path: "/admin/roles/:id/edit",
      name: "admin-role-edit",
      component: () => import("../views/admin/RoleEditView.vue"),
      meta: { requiresAuth: true, requiresAdmin: true },
    },
  ],
});

router.beforeEach((to) => {
  const token = localStorage.getItem("token");
  const storedUser = localStorage.getItem("user");
  let user = null;

  try {
    user = storedUser ? JSON.parse(storedUser) : null;
  } catch {
    localStorage.removeItem("user");
  }

  if (to.meta.requiresAuth && !token) {
    return { name: "login", query: { redirect: to.fullPath } };
  }

  const roleName = typeof user?.role === "string" ? user.role : user?.role?.name;

  if (to.meta.requiresAdmin && roleName !== "admin") {
    return { name: token ? "dashboard" : "login" };
  }

  return true;
});

export default router;
