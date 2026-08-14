<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { api } from '../api/client'
import type { Category } from '../types'
import ProductGrid from '../components/ProductGrid.vue'
import { useI18n } from '../i18n'
import { localizeCategory } from '../i18n/catalog'

const route = useRoute()
const { t, locale } = useI18n()
const category = ref<(Category & { products?: any[] }) | null>(null)
const localized = computed(() => (category.value ? localizeCategory(category.value, locale.value) : null))

async function load() {
  category.value = await api(`/categories/${route.params.slug}`)
}

onMounted(load)
watch(() => route.params.slug, load)
</script>

<template>
  <section v-if="localized" class="section">
    <div class="container">
      <div class="section-head">
        <div>
          <p class="eyebrow">{{ t('category') }}</p>
          <h2>{{ localized.name }}</h2>
          <p class="muted">{{ localized.description }}</p>
        </div>
      </div>
      <ProductGrid :products="category?.products || []" />
    </div>
  </section>
</template>
