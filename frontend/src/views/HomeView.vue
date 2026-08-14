<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { api } from '../api/client'
import type { Category, Goal, Kit, Product } from '../types'
import ProductGrid from '../components/ProductGrid.vue'
import CategoryCard from '../components/CategoryCard.vue'
import KitCard from '../components/KitCard.vue'
import AIChat from '../components/AIChat.vue'
import { useI18n } from '../i18n'
import { localizeCategory, localizeGoal, localizeKit, localizeProduct } from '../i18n/catalog'

const featured = ref<Product[]>([])
const categories = ref<Category[]>([])
const goals = ref<Goal[]>([])
const kits = ref<Kit[]>([])
const loading = ref(true)
const { t, locale } = useI18n()

const localizedFeatured = computed(() => featured.value.map((p) => localizeProduct(p, locale.value)))
const localizedCategories = computed(() => categories.value.map((c) => localizeCategory(c, locale.value)))
const localizedGoals = computed(() => goals.value.map((g) => localizeGoal(g, locale.value)))
const localizedKits = computed(() => kits.value.map((k) => localizeKit(k, locale.value)))

onMounted(async () => {
  try {
    const [featuredRes, cats, goalList, kitList] = await Promise.all([
      api<{ data: Product[] }>('/products?featured=1&per_page=8'),
      api<Category[]>('/categories'),
      api<Goal[]>('/goals'),
      api<Kit[]>('/kits'),
    ])
    featured.value = featuredRes.data
    categories.value = cats
    goals.value = goalList
    kits.value = kitList
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div>
    <section class="hero">
      <div class="hero-copy fade-up">
        <p class="brand-mark">Slow Goods</p>
        <h1>{{ t('tagline') }}</h1>
        <p class="lede">
          {{ t('hero_lede') }}
        </p>
        <div class="cta">
          <RouterLink class="btn" to="/products">{{ t('cta_explore') }}</RouterLink>
          <RouterLink class="btn btn-secondary" to="/ask">{{ t('nav_ask') }}</RouterLink>
        </div>
      </div>
      <div class="hero-visual" aria-hidden="true">
        <img
          src="https://images.unsplash.com/photo-1512820790803-83ca734da794?w=1600&q=80"
          alt=""
        />
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head">
          <div>
            <p class="eyebrow">{{ t('featured') }}</p>
            <h2>{{ t('featured_title') }}</h2>
          </div>
          <RouterLink to="/products">{{ t('view_all') }}</RouterLink>
        </div>
        <p v-if="loading" class="muted">{{ t('loading') }}</p>
        <ProductGrid v-else :products="localizedFeatured" />
      </div>
    </section>

    <section class="section categories-section">
      <div class="container">
        <div class="section-head">
          <div>
            <p class="eyebrow">{{ t('categories') }}</p>
            <h2>{{ t('browse_by_rhythm') }}</h2>
          </div>
        </div>
        <div class="category-grid">
          <CategoryCard v-for="category in localizedCategories" :key="category.id" :category="category" />
        </div>
      </div>
    </section>

    <section class="section">
      <div class="container">
        <div class="section-head">
          <div>
            <p class="eyebrow">{{ t('intentions') }}</p>
            <h2>{{ t('what_do_you_want') }}</h2>
          </div>
        </div>
        <div class="goals">
          <RouterLink v-for="goal in localizedGoals" :key="goal.slug" class="goal" :to="`/goals/${goal.slug}`">
            {{ goal.label }}
          </RouterLink>
        </div>
      </div>
    </section>

    <section class="section kits-section">
      <div class="container">
        <div class="section-head">
          <div>
            <p class="eyebrow">{{ t('build_a_kit') }}</p>
            <h2>{{ t('start_small_set') }}</h2>
          </div>
          <RouterLink to="/kits">{{ t('all_kits') }}</RouterLink>
        </div>
        <KitCard v-for="kit in localizedKits" :key="kit.slug" :kit="kit" />
      </div>
    </section>

    <section class="section story">
      <div class="container story-grid">
        <div>
          <p class="eyebrow">{{ t('brand_story') }}</p>
          <h2>{{ t('story_title') }}</h2>
          <p>
            {{ t('story_body') }}
          </p>
        </div>
        <AIChat />
      </div>
    </section>
  </div>
</template>

<style scoped>
.hero {
  min-height: calc(100vh - 74px);
  display: grid;
  grid-template-columns: 1.05fr 0.95fr;
  position: relative;
  overflow: hidden;
}

.hero-copy {
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: clamp(2rem, 6vw, 5rem);
  max-width: 680px;
}

.brand-mark {
  font-family: var(--font-display);
  font-size: clamp(2.4rem, 5vw, 4rem);
  margin: 0 0 1rem;
  animation: fadeUp 0.8s ease both;
}

h1 {
  margin: 0;
  font-size: clamp(2rem, 4.4vw, 3.6rem);
  max-width: 12ch;
}

.lede {
  color: var(--muted);
  font-size: 1.1rem;
  max-width: 38ch;
  margin: 1.2rem 0 1.8rem;
}

.cta {
  display: flex;
  flex-wrap: wrap;
  gap: 0.8rem;
}

.hero-visual {
  position: relative;
  min-height: 420px;
}

.hero-visual img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  animation: drift 7s ease-in-out infinite alternate;
}

.category-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.goals {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
}

.goal {
  border: 1px solid var(--line);
  background: rgba(255, 255, 255, 0.55);
  border-radius: 999px;
  padding: 0.85rem 1.15rem;
  transition: background 0.25s ease, transform 0.25s ease;
}

.goal:hover {
  background: white;
  transform: translateY(-2px);
}

.story-grid {
  display: grid;
  grid-template-columns: 0.85fr 1.15fr;
  gap: 2.5rem;
  align-items: start;
}

@media (max-width: 960px) {
  .hero,
  .category-grid,
  .story-grid {
    grid-template-columns: 1fr;
  }

  .hero-visual {
    min-height: 320px;
  }
}
</style>
