<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import { api, money } from '../api/client'
import type { Order } from '../types'
import { useAuthStore } from '../stores/auth'
import { useI18n } from '../i18n'

const route = useRoute()
const auth = useAuthStore()
const { t, locale } = useI18n()
const order = ref<Order | null>(null)

const paymentMethodLabel = computed(() => {
  if (!order.value) return ''
  if (order.value.payment_method === 'cash_on_delivery') return t('pay_cod')
  if (order.value.payment_method === 'bank_transfer') return t('pay_bank_transfer')
  return t('pay_card')
})

const isAwaitingBankPayment = computed(
  () =>
    order.value?.payment_method === 'bank_transfer' &&
    order.value?.payment_status === 'pending',
)

function formatPaidAt(value?: string | null) {
  if (!value) return ''
  return new Date(value).toLocaleString(locale.value === 'vi' ? 'vi-VN' : 'en-GB')
}

onMounted(async () => {
  order.value = await api(`/orders/${route.params.id}`, {}, auth.token)
})
</script>

<template>
  <section v-if="order" class="section">
    <div class="container narrow">
      <p v-if="route.query.confirmed" class="confirm fade-up">{{ t('order_confirmed') }}</p>
      <p class="eyebrow">{{ t('order') }}</p>
      <h2>{{ order.order_number }}</h2>
      <p class="muted">{{ t('order_account') }}: {{ order.user?.email || auth.user?.email }}</p>
      <p class="muted">{{ t('status') }}: <strong>{{ t('status_' + order.status) }}</strong></p>

      <div class="block">
        <h3>{{ t('payment') }}</h3>
        <p>{{ paymentMethodLabel }}</p>
        <p v-if="order.payment_status === 'paid'" class="pay-state paid">
          {{ t('payment_status_paid_label') }}
        </p>
        <p v-else class="pay-state pending">
          {{ t('payment_status_pending_label') }}
        </p>
        <p>{{ t('amount') }}: <strong>{{ money(order.total) }}</strong></p>
        <p v-if="order.paid_at" class="muted">{{ t('paid_at') }}: {{ formatPaidAt(order.paid_at) }}</p>
        <RouterLink
          v-if="isAwaitingBankPayment"
          class="btn"
          :to="{ name: 'payment', params: { id: order.id } }"
        >
          {{ t('continue_payment') }}
        </RouterLink>
      </div>

      <div class="block">
        <h3>{{ t('ship_to') }}</h3>
        <p>{{ order.full_name }}</p>
        <p>{{ order.address }}, {{ order.city }} {{ order.postal_code }}</p>
        <p>{{ order.email }} · {{ order.phone }}</p>
      </div>

      <div class="block">
        <h3>{{ t('items') }}</h3>
        <div v-for="item in order.items" :key="item.id" class="line">
          <span>{{ item.product_name }} × {{ item.quantity }}</span>
          <strong>{{ money(item.line_total) }}</strong>
        </div>
        <div class="line total">
          <span>{{ t('total') }}</span>
          <strong>{{ money(order.total) }}</strong>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
.narrow { max-width: 720px; }
.confirm {
  background: rgba(47, 79, 62, 0.1);
  padding: 0.9rem 1rem;
  border-radius: 12px;
}
.block { margin-top: 1.5rem; display: grid; gap: 0.4rem; }
.line { display: flex; justify-content: space-between; gap: 1rem; padding: 0.35rem 0; }
.total { border-top: 1px solid var(--line); margin-top: 0.5rem; padding-top: 0.7rem; }
.pay-state { font-weight: 600; }
.pay-state.paid { color: var(--accent, #2f4f3e); }
.btn { margin-top: 0.6rem; width: fit-content; }
</style>
