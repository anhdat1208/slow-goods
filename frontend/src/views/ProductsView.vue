<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { api } from '../api/client'
import type { Category, Paginated, Product } from '../types'
import ProductGrid from '../components/ProductGrid.vue'
import SearchBar from '../components/SearchBar.vue'
import { useI18n } from '../i18n'
import { localizeCategory } from '../i18n/catalog'

const route = useRoute()
const router = useRouter()
const { t, locale } = useI18n()
const products = ref<Product[]>([])
const categories = ref<Category[]>([])
const page = ref(1)
const lastPage = ref(1)
const loading = ref(true)
const sort = ref(String(route.query.sort || 'newest'))
const category = ref(String(route.query.category || ''))

async function load() {
  loading.value = true
  const params = new URLSearchParams()
  params.set('page', String(page.value))
  params.set('per_page', '12')
  params.set('sort', sort.value)
  if (category.value) params.set('category', category.value)
  if (route.query.search) params.set('search', String(route.query.search))

  try {
    const data = await api<Paginated<Product>>(`/products?${params}`)
    products.value = data.data
    lastPage.value = data.last_page
  } finally {
    loading.value = false
  }
}

function updateFilters() {
  router.replace({
    query: {
      ...route.query,
      sort: sort.value,
      category: category.value || undefined,
      page: page.value > 1 ? String(page.value) : undefined,
    },
  })
}

onMounted(async () => {
  categories.value = await api('/categories')
  await load()
})

watch(() => route.query, () => {
  page.value = Number(route.query.page || 1)
  sort.value = String(route.query.sort || 'newest')
  category.value = String(route.query.category || '')
  load()
})
</script>

<template>
  <section class="section">
    <div class="container">
      <div class="section-head">
        <div>
          <p class="eyebrow">{{ t('collection') }}</p>
          <h2>{{ route.query.search ? t('results_for', { q: String(route.query.search) }) : t('all_goods') }}</h2>
        </div>
        <SearchBar />
      </div>

      <div class="filters">
        <select v-model="category" @change="page = 1; updateFilters()">
          <option value="">{{ t('all_categories') }}</option>
          <option v-for="c in categories" :key="c.id" :value="c.slug">{{ locale === 'vi' ? localizeCategory(c, locale).name : c.name }}</option>
        </select>
        <select v-model="sort" @change="page = 1; updateFilters()">
          <option value="newest">{{ t('sort_newest') }}</option>
          <option value="price_asc">{{ t('sort_price_asc') }}</option>
          <option value="price_desc">{{ t('sort_price_desc') }}</option>
          <option value="name">{{ t('sort_name') }}</option>
        </select>
      </div>

      <p v-if="loading" class="muted">{{ t('loading') }}</p>
      <ProductGrid v-else :products="products" />

      <div v-if="lastPage > 1" class="pager">
        <button class="btn btn-secondary" type="button" :disabled="page <= 1" @click="page--; updateFilters()">{{ t('previous') }}</button>
        <span>{{ t('page_of', { page, last: lastPage }) }}</span>
        <button class="btn btn-secondary" type="button" :disabled="page >= lastPage" @click="page++; updateFilters()">{{ t('next') }}</button>
      </div>
    </div>
  </section>
</template>

<style scoped>
.filters {
  display: flex;
  gap: 0.8rem;
  margin-bottom: 1.5rem;
}

select {
  border: 1px solid var(--line);
  background: rgba(255, 255, 255, 0.75);
  border-radius: 999px;
  padding: 0.7rem 1rem;
}

.pager {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}
</style>
