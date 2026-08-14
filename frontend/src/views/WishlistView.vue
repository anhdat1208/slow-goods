<script setup lang="ts">
import { onMounted } from 'vue'
import ProductGrid from '../components/ProductGrid.vue'
import { useWishlistStore } from '../stores/wishlist'
import { useI18n } from '../i18n'

const wishlist = useWishlistStore()
const { t } = useI18n()

onMounted(() => wishlist.fetch())
</script>

<template>
  <section class="section">
    <div class="container">
      <div class="section-head">
        <div>
          <p class="eyebrow">{{ t('nav_wishlist') }}</p>
          <h2>{{ t('saved_later') }}</h2>
        </div>
      </div>
      <p v-if="!wishlist.items.length" class="muted">{{ t('nothing_saved') }}</p>
      <ProductGrid v-else :products="wishlist.items.map((i) => i.product)" />
    </div>
  </section>
</template>
