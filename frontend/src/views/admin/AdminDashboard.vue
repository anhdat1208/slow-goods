<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useI18n } from '../../i18n'

const { t } = useI18n()
</script>

<template>
  <div>
    <h3>{{ t('welcome_admin') }}</h3>
    <p class="muted">{{ t('admin_intro') }}</p>
    <div class="cards">
      <RouterLink to="/admin/products">{{ t('products') }}</RouterLink>
      <RouterLink to="/admin/categories">{{ t('categories') }}</RouterLink>
      <RouterLink to="/admin/orders">{{ t('orders') }}</RouterLink>
      <RouterLink to="/admin/reviews">{{ t('reviews') }}</RouterLink>
    </div>
  </div>
</template>

<style scoped>
.cards {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  gap: 0.8rem;
  margin-top: 1.2rem;
}
.cards a {
  border: 1px solid var(--line);
  border-radius: 14px;
  padding: 1rem;
  background: rgba(255,255,255,0.65);
}
</style>
