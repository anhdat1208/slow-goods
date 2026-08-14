<script setup lang="ts">
import { RouterLink, RouterView } from 'vue-router'
import { useI18n } from '../../i18n'

const { t } = useI18n()
</script>

<template>
  <section class="section">
    <div class="container admin">
      <aside>
        <p class="eyebrow">{{ t('admin') }}</p>
        <h2>{{ t('dashboard') }}</h2>
        <nav>
          <RouterLink to="/admin">{{ t('overview') }}</RouterLink>
          <RouterLink to="/admin/products">{{ t('products') }}</RouterLink>
          <RouterLink to="/admin/categories">{{ t('categories') }}</RouterLink>
          <RouterLink to="/admin/orders">{{ t('orders') }}</RouterLink>
          <RouterLink to="/admin/reviews">{{ t('reviews') }}</RouterLink>
        </nav>
      </aside>
      <div class="content">
        <RouterView />
      </div>
    </div>
  </section>
</template>

<style scoped>
.admin {
  display: grid;
  grid-template-columns: 220px 1fr;
  gap: 2rem;
}
nav {
  display: grid;
  gap: 0.55rem;
  margin-top: 1rem;
}
nav a.router-link-active {
  color: var(--accent);
  font-weight: 600;
}
@media (max-width: 800px) {
  .admin { grid-template-columns: 1fr; }
}
</style>
