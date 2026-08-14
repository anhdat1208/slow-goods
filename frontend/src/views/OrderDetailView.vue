<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRoute } from 'vue-router'
import { api, money } from '../api/client'
import type { Order } from '../types'
import { useAuthStore } from '../stores/auth'
import { useI18n } from '../i18n'

const route = useRoute()
const auth = useAuthStore()
const { t } = useI18n()
const order = ref<Order | null>(null)

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
      <p class="muted">{{ t('status') }}: <strong>{{ t('status_' + order.status) }}</strong></p>

      <div class="block">
        <h3>{{ t('ship_to') }}</h3>
        <p>{{ order.full_name }}</p>
        <p>{{ order.address }}, {{ order.city }} {{ order.postal_code }}</p>
        <p>{{ order.email }} · {{ order.phone }}</p>
        <p class="muted">{{ t('payment') }}: {{ order.payment_method === 'cash_on_delivery' ? t('pay_cod') : t('pay_card') }}</p>
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
</style>
