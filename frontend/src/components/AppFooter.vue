<script setup lang="ts">
import { RouterLink } from 'vue-router'
import { useI18n } from '../i18n'

const { t } = useI18n()
</script>

<template>
  <footer class="footer">
    <div class="container footer-inner">
      <div>
        <p class="brand">Slow Goods</p>
        <p class="tag">{{ t('tagline') }}</p>
      </div>
      <div class="cols">
        <div>
          <p class="eyebrow">{{ t('footer_explore') }}</p>
          <RouterLink to="/products">{{ t('nav_collection') }}</RouterLink>
          <RouterLink to="/kits">{{ t('nav_kits') }}</RouterLink>
          <RouterLink to="/ask">{{ t('nav_ask') }}</RouterLink>
        </div>
        <div>
          <p class="eyebrow">{{ t('footer_account') }}</p>
          <RouterLink to="/orders">{{ t('footer_orders') }}</RouterLink>
          <RouterLink to="/wishlist">{{ t('nav_wishlist') }}</RouterLink>
          <RouterLink to="/login">{{ t('nav_signin') }}</RouterLink>
        </div>
      </div>
    </div>
    <div class="container copy muted">{{ t('footer_copy') }}</div>
  </footer>
</template>

<style scoped>
.footer {
  border-top: 1px solid var(--line);
  padding: 3rem 0 2rem;
  margin-top: 3rem;
}

.footer-inner {
  display: flex;
  justify-content: space-between;
  gap: 2rem;
  margin-bottom: 2rem;
}

.brand {
  font-family: var(--font-display);
  font-size: 1.5rem;
  margin: 0;
}

.tag {
  color: var(--muted);
  margin: 0.4rem 0 0;
}

.cols {
  display: flex;
  gap: 3rem;
}

.cols div {
  display: grid;
  gap: 0.45rem;
}

.copy {
  font-size: 0.9rem;
}

@media (max-width: 720px) {
  .footer-inner {
    flex-direction: column;
  }
}
</style>
