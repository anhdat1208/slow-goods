<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/client'
import type { Goal, Product } from '../types'
import ProductGrid from '../components/ProductGrid.vue'
import { useI18n } from '../i18n'
import { localizeGoal } from '../i18n/catalog'

const route = useRoute()
const { t, locale } = useI18n()
const goals = ref<Goal[]>([])
const products = ref<Product[]>([])

const label = computed(() => {
  const goal = goals.value.find((g) => g.slug === route.params.slug)
  return goal ? localizeGoal(goal, locale.value).label : String(route.params.slug)
})

async function load() {
  goals.value = await api('/goals')
  products.value = await api(`/goals/${route.params.slug}/products`)
}

onMounted(load)
watch(() => route.params.slug, load)
</script>

<template>
  <section class="section">
    <div class="container">
      <div class="section-head">
        <div>
          <p class="eyebrow">{{ t('goal_eyebrow') }}</p>
          <h2>{{ label }}</h2>
          <p class="muted">{{ t('goal_lede') }}</p>
        </div>
      </div>
      <ProductGrid :products="products" />
    </div>
  </section>
</template>
