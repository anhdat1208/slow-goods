<script setup lang="ts">
import { computed, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { api, money } from '../api/client'
import type { Product } from '../types'
import { useI18n } from '../i18n'
import { localizeProduct } from '../i18n/catalog'
import SafeImage from './SafeImage.vue'

const question = ref('')
const loading = ref(false)
const answer = ref('')
const mode = ref('')
const products = ref<Product[]>([])
const error = ref('')
const { t, locale } = useI18n()

const suggestions = computed(() => [
  t('suggest_1'),
  t('suggest_2'),
  t('suggest_3'),
  t('suggest_4'),
])

const localizedProducts = computed(() =>
  products.value.map((p) => localizeProduct(p, locale.value)),
)

async function ask(text?: string) {
  const q = (text || question.value).trim()
  if (!q) return
  question.value = q
  loading.value = true
  error.value = ''
  try {
    const data = await api<{ mode: string; answer: string; products: Product[] }>('/ai/ask', {
      method: 'POST',
      body: JSON.stringify({ question: q, locale: locale.value }),
    })
    answer.value = data.answer
    mode.value = data.mode
    products.value = data.products || []
  } catch (e) {
    error.value = e instanceof Error ? e.message : t('something_wrong')
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="ai">
    <div class="intro">
      <p class="eyebrow">{{ t('nav_ask') }}</p>
      <h2>{{ t('ask_title') }}</h2>
      <p class="muted">{{ t('ask_lede') }}</p>
    </div>

    <div class="suggestions">
      <button v-for="s in suggestions" :key="s" type="button" class="chip" @click="ask(s)">{{ s }}</button>
    </div>

    <form class="ask-form" @submit.prevent="ask()">
      <input v-model="question" type="text" :placeholder="t('ask_placeholder')" />
      <button class="btn" type="submit" :disabled="loading">{{ loading ? t('thinking') : t('ask_btn') }}</button>
    </form>

    <p v-if="error" class="error-text">{{ error }}</p>

    <div v-if="answer" class="response fade-up">
      <p class="mode">{{ mode === 'openai' ? t('live_assistant') : t('catalog_guide') }}</p>
      <pre>{{ answer }}</pre>
      <div v-if="localizedProducts.length" class="recs">
        <RouterLink v-for="p in localizedProducts" :key="p.id" class="rec" :to="`/products/${p.slug}`">
          <SafeImage :src="p.image_url" :alt="p.name" />
          <div>
            <strong>{{ p.name }}</strong>
            <span>{{ money(p.price) }}</span>
          </div>
        </RouterLink>
      </div>
    </div>
  </section>
</template>

<style scoped>
.ai {
  display: grid;
  gap: 1.4rem;
}

.suggestions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.6rem;
}

.chip {
  border: 1px solid var(--line);
  background: rgba(255, 255, 255, 0.65);
  border-radius: 999px;
  padding: 0.55rem 0.9rem;
  cursor: pointer;
  text-align: left;
}

.ask-form {
  display: grid;
  grid-template-columns: 1fr auto;
  gap: 0.7rem;
}

.ask-form input {
  border: 1px solid var(--line);
  border-radius: 999px;
  padding: 0.9rem 1.1rem;
  background: rgba(255, 255, 255, 0.8);
}

.response {
  border-top: 1px solid var(--line);
  padding-top: 1.2rem;
}

.mode {
  color: var(--muted);
  font-size: 0.85rem;
  letter-spacing: 0.08em;
  text-transform: uppercase;
}

pre {
  white-space: pre-wrap;
  font-family: var(--font-body);
  margin: 0.5rem 0 1rem;
}

.recs {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
  gap: 0.9rem;
}

.rec {
  display: grid;
  grid-template-columns: 64px 1fr;
  gap: 0.7rem;
  align-items: center;
}

.rec :deep(img) {
  width: 64px;
  height: 80px;
  object-fit: cover;
  border-radius: 10px;
}

.rec span {
  display: block;
  color: var(--muted);
}

@media (max-width: 720px) {
  .ask-form {
    grid-template-columns: 1fr;
  }
}
</style>
