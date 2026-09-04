import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: '/', name: 'home', component: () => import('../views/HomeView.vue') },
    { path: '/products', name: 'products', component: () => import('../views/ProductsView.vue') },
    { path: '/products/:slug', name: 'product', component: () => import('../views/ProductDetailView.vue') },
    { path: '/categories/:slug', name: 'category', component: () => import('../views/CategoryView.vue') },
    { path: '/goals/:slug', name: 'goal', component: () => import('../views/GoalView.vue') },
    { path: '/kits', name: 'kits', component: () => import('../views/KitsView.vue') },
    { path: '/ask', name: 'ask', component: () => import('../views/AskSlowView.vue') },
    { path: '/cart', name: 'cart', component: () => import('../views/CartView.vue'), meta: { auth: true } },
    { path: '/checkout', name: 'checkout', component: () => import('../views/CheckoutView.vue'), meta: { auth: true } },
    { path: '/orders', name: 'orders', component: () => import('../views/OrdersView.vue'), meta: { auth: true } },
    { path: '/orders/:id/payment', name: 'payment', component: () => import('../views/PaymentView.vue'), meta: { auth: true } },
    { path: '/orders/:id', name: 'order', component: () => import('../views/OrderDetailView.vue'), meta: { auth: true } },
    { path: '/wishlist', name: 'wishlist', component: () => import('../views/WishlistView.vue'), meta: { auth: true } },
    { path: '/profile', name: 'profile', component: () => import('../views/ProfileView.vue'), meta: { auth: true } },
    { path: '/login', name: 'login', component: () => import('../views/LoginView.vue'), meta: { guest: true } },
    { path: '/register', name: 'register', component: () => import('../views/RegisterView.vue'), meta: { guest: true } },
    {
      path: '/admin',
      component: () => import('../views/admin/AdminLayout.vue'),
      meta: { auth: true, admin: true },
      children: [
        { path: '', name: 'admin', component: () => import('../views/admin/AdminDashboard.vue') },
        { path: 'products', name: 'admin-products', component: () => import('../views/admin/AdminProducts.vue') },
        { path: 'categories', name: 'admin-categories', component: () => import('../views/admin/AdminCategories.vue') },
        { path: 'orders', name: 'admin-orders', component: () => import('../views/admin/AdminOrders.vue') },
        { path: 'reviews', name: 'admin-reviews', component: () => import('../views/admin/AdminReviews.vue') },
      ],
    },
  ],
  scrollBehavior() {
    return { top: 0 }
  },
})

router.beforeEach(async (to) => {
  const auth = useAuthStore()
  if (!auth.user && auth.token) {
    await auth.fetchUser()
  }

  if (to.meta.auth && !auth.isAuthenticated) {
    return { name: 'login', query: { redirect: to.fullPath } }
  }

  if (to.meta.admin && !auth.isAdmin) {
    return { name: 'home' }
  }

  if (to.meta.guest && auth.isAuthenticated) {
    return { name: 'home' }
  }
})

export default router
