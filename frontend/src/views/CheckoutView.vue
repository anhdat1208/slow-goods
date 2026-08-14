<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { api, ApiError, money } from '../api/client'
import type { Order } from '../types'
import { useAuthStore } from '../stores/auth'
import { useCartStore } from '../stores/cart'
import { useI18n } from '../i18n'
import { localizeProduct } from '../i18n/catalog'

const auth = useAuthStore()
const cart = useCartStore()
const router = useRouter()
const { t, locale } = useI18n()
const error = ref('')
const loading = ref(false)

const form = reactive({
  full_name: auth.user?.name || '',
  email: auth.user?.email || '',
  phone: auth.user?.phone || '',
  address: '',
  city: '',
  postal_code: '',
  payment_method: 'cash_on_delivery',
})

async function submit() {
  if (!cart.items.length) {
    error.value = t('cart_empty_error')
    return
  }
  loading.value = true
  error.value = ''
  try {
    await cart.prepareCheckout()
    const order = await api<Order>(
      '/orders',
      { method: 'POST', body: JSON.stringify(form) },
      auth.token,
    )
    cart.clear()
    router.push({ name: 'order', params: { id: order.id }, query: { confirmed: '1' } })
  } catch (e) {
    error.value = e instanceof ApiError ? e.message : t('checkout_failed')
    if (e instanceof ApiError && e.errors) {
      error.value = Object.values(e.errors).flat().join(' ')
    }
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <section class="section">
    <div class="container layout">
      <div>
        <p class="eyebrow">{{ t('checkout_eyebrow') }}</p>
        <h2>{{ t('checkout_title') }}</h2>
        <p class="muted">{{ t('checkout_lede') }}</p>

        <form class="form" @submit.prevent="submit">
          <div class="field"><label>{{ t('full_name') }}</label><input v-model="form.full_name" required /></div>
          <div class="field"><label>{{ t('email') }}</label><input v-model="form.email" type="email" required /></div>
          <div class="field"><label>{{ t('phone') }}</label><input v-model="form.phone" required /></div>
          <div class="field"><label>{{ t('address') }}</label><input v-model="form.address" required /></div>
          <div class="grid-2">
            <div class="field"><label>{{ t('city') }}</label><input v-model="form.city" required /></div>
            <div class="field"><label>{{ t('postal_code') }}</label><input v-model="form.postal_code" required /></div>
          </div>
          <div class="field">
            <label>{{ t('payment_method') }}</label>
            <select v-model="form.payment_method">
              <option value="cash_on_delivery">{{ t('pay_cod') }}</option>
              <option value="demo_card">{{ t('pay_card') }}</option>
            </select>
          </div>
          <p v-if="error" class="error-text">{{ error }}</p>
          <button class="btn" type="submit" :disabled="loading">{{ loading ? t('placing_order') : t('confirm_order') }}</button>
        </form>
      </div>

      <aside>
        <h3>{{ t('order_summary') }}</h3>
        <div v-for="item in cart.items" :key="item.product_id" class="line">
          <span>{{ item.product ? localizeProduct(item.product, locale).name : item.product_id }} × {{ item.quantity }}</span>
          <strong>{{ money(Number(item.product?.price || 0) * item.quantity) }}</strong>
        </div>
        <div class="total">
          <span>{{ t('estimated_subtotal') }}</span>
          <strong>{{ money(cart.subtotal) }}</strong>
        </div>
      </aside>
    </div>
  </section>
</template>

<style scoped>
.layout {
  display: grid;
  grid-template-columns: 1.2fr 0.8fr;
  gap: 2rem;
}
.form, aside { display: grid; gap: 0.9rem; }
.grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 0.9rem; }
.line, .total { display: flex; justify-content: space-between; gap: 1rem; }
.total { border-top: 1px solid var(--line); padding-top: 0.8rem; }
@media (max-width: 900px) {
  .layout, .grid-2 { grid-template-columns: 1fr; }
}
</style>
