<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute } from 'vue-router'
import { api, money } from '../api/client'
import type { Product } from '../types'
import WishlistButton from '../components/WishlistButton.vue'
import ProductRating from '../components/ProductRating.vue'
import { useCartStore } from '../stores/cart'
import { useAuthStore } from '../stores/auth'
import { useI18n } from '../i18n'
import { localizeProduct } from '../i18n/catalog'
import SafeImage from '../components/SafeImage.vue'

const route = useRoute()
const cart = useCartStore()
const auth = useAuthStore()
const { t, locale } = useI18n()
const product = ref<Product | null>(null)
const qty = ref(1)
const rating = ref(5)
const comment = ref('')
const message = ref('')
const error = ref('')

const maxQty = computed(() => product.value?.stock || 1)
const localized = computed(() => (product.value ? localizeProduct(product.value, locale.value) : null))

async function load() {
  product.value = await api(`/products/${route.params.slug}`)
  qty.value = 1
}

async function addToCart() {
  if (!product.value) return
  try {
    await cart.addProduct(product.value, qty.value)
    message.value = t('added_to_cart')
  } catch (e) {
    if (e instanceof Error && e.message === 'LOGIN_REQUIRED') return
    if (e instanceof Error && e.message.startsWith('STOCK:')) {
      error.value = t('only_in_stock', { n: e.message.replace('STOCK:', '') })
      return
    }
    error.value = e instanceof Error ? e.message : t('could_not_add')
  }
}

async function submitReview() {
  if (!product.value || !auth.token) return
  await api(
    `/products/${product.value.id}/reviews`,
    { method: 'POST', body: JSON.stringify({ rating: rating.value, comment: comment.value }) },
    auth.token,
  )
  comment.value = ''
  await load()
  message.value = t('review_saved')
}

onMounted(load)
watch(() => route.params.slug, load)
</script>

<template>
  <section v-if="localized" class="section">
    <div class="container detail">
      <div class="media">
        <SafeImage :src="localized.image_url" :alt="localized.name" />
      </div>
      <div class="info fade-up">
        <p class="eyebrow">{{ localized.category?.name }}</p>
        <h1>{{ localized.name }}</h1>
        <ProductRating :rating="Number(localized.average_rating || 0)" />
        <p class="price">{{ money(localized.price) }}</p>
        <p>{{ localized.description }}</p>
        <p class="muted">{{ t('stock', { n: localized.stock, sku: localized.sku }) }}</p>

        <div class="actions">
          <label>
            {{ t('qty') }}
            <input v-model.number="qty" type="number" min="1" :max="maxQty" />
          </label>
          <button class="btn" type="button" @click="addToCart">{{ t('add_to_cart') }}</button>
          <WishlistButton :product-id="localized.id" />
        </div>

        <p v-if="message" class="muted">{{ message }}</p>
        <p v-if="error" class="error-text">{{ error }}</p>

        <div v-if="auth.isAuthenticated" class="review-form">
          <h3>{{ t('write_review') }}</h3>
          <div class="field">
            <label>{{ t('rating') }}</label>
            <select v-model.number="rating">
              <option v-for="n in 5" :key="n" :value="n">{{ t('stars', { n }) }}</option>
            </select>
          </div>
          <div class="field">
            <label>{{ t('comment') }}</label>
            <textarea v-model="comment" rows="3" />
          </div>
          <button class="btn btn-secondary" type="button" @click="submitReview">{{ t('submit_review') }}</button>
        </div>

        <div v-if="localized.visible_reviews?.length" class="reviews">
          <h3>{{ t('reviews') }}</h3>
          <article v-for="review in localized.visible_reviews" :key="review.id">
            <ProductRating :rating="review.rating" />
            <p>{{ review.comment }}</p>
            <small class="muted">{{ review.user?.name }}</small>
          </article>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.detail {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2.5rem;
  align-items: start;
}

.media :deep(img) {
  width: 100%;
  border-radius: 22px;
  aspect-ratio: 4 / 5;
  object-fit: cover;
}

h1 {
  margin: 0.3rem 0 0.6rem;
  font-size: clamp(2rem, 4vw, 3rem);
}

.price {
  font-size: 1.4rem;
  font-weight: 600;
}

.actions {
  display: flex;
  flex-wrap: wrap;
  gap: 0.8rem;
  align-items: end;
  margin: 1.4rem 0;
}

.actions input {
  width: 72px;
  margin-left: 0.4rem;
  border: 1px solid var(--line);
  border-radius: 10px;
  padding: 0.55rem;
}

.review-form,
.reviews {
  margin-top: 2rem;
  display: grid;
  gap: 0.8rem;
}

@media (max-width: 900px) {
  .detail {
    grid-template-columns: 1fr;
  }
}
</style>
