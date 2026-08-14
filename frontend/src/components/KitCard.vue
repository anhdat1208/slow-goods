<script setup lang="ts">
import { computed } from 'vue'
import type { Kit } from '../types'
import { money } from '../api/client'
import { useCartStore } from '../stores/cart'
import { useI18n } from '../i18n'
import { localizeKit } from '../i18n/catalog'

const props = defineProps<{ kit: Kit }>()
const cart = useCartStore()
const emit = defineEmits<{ added: [] }>()
const { t, locale } = useI18n()
const localized = computed(() => localizeKit(props.kit, locale.value))

function addKit() {
  for (const product of props.kit.products) {
    if (product.stock > 0) {
      try {
        cart.addProduct(product, 1)
      } catch {
        // skip unavailable
      }
    }
  }
  emit('added')
}
</script>

<template>
  <article class="kit">
    <div>
      <p class="eyebrow">{{ t('curated_kit') }}</p>
      <h3>{{ localized.name }}</h3>
      <p class="muted">{{ localized.description }}</p>
    </div>
    <ul>
      <li v-for="product in localized.products" :key="product.id">
        <span>{{ product.name }}</span>
        <strong>{{ money(product.price) }}</strong>
      </li>
    </ul>
    <div class="foot">
      <strong>{{ money(kit.total_price) }}</strong>
      <button class="btn" type="button" @click="addKit">{{ t('add_kit') }}</button>
    </div>
  </article>
</template>

<style scoped>
.kit {
  display: grid;
  gap: 1.2rem;
  padding: 1.5rem 0;
  border-top: 1px solid var(--line);
}

h3 {
  margin: 0.3rem 0;
  font-size: 1.8rem;
}

ul {
  list-style: none;
  padding: 0;
  margin: 0;
  display: grid;
  gap: 0.55rem;
}

li {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  color: var(--muted);
}

.foot {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 1rem;
}
</style>
