<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { RouterLink } from 'vue-router'
import { api, money } from '../api/client'
import type { Order, Paginated } from '../types'
import { useAuthStore } from '../stores/auth'
import { useI18n } from '../i18n'

const auth = useAuthStore()
const { t } = useI18n()
const orders = ref<Order[]>([])

onMounted(async () => {
  const data = await api<Paginated<Order>>('/orders', {}, auth.token)
  orders.value = data.data
})
</script>

<template>
  <section class="section">
    <div class="container">
      <div class="section-head">
        <div>
          <p class="eyebrow">{{ t('orders') }}</p>
          <h2>{{ t('order_history') }}</h2>
          <p class="muted">{{ t('order_account') }}: {{ auth.user?.email }}</p>
        </div>
      </div>
      <div v-if="!orders.length" class="muted">{{ t('no_orders') }}</div>
      <div v-else class="list">
        <RouterLink v-for="order in orders" :key="order.id" class="row" :to="`/orders/${order.id}`">
          <div>
            <strong>{{ order.order_number }}</strong>
            <p class="muted">{{ new Date(order.created_at).toLocaleString() }}</p>
          </div>
          <div class="side">
            <span class="status">{{ t('status_' + order.status) }}</span>
            <strong>{{ money(order.total) }}</strong>
          </div>
        </RouterLink>
      </div>
    </div>
  </section>
</template>

<style scoped>
.list { display: grid; gap: 0.8rem; }
.row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  padding: 1rem 0;
  border-bottom: 1px solid var(--line);
}
.side { display: grid; justify-items: end; gap: 0.25rem; }
.status { text-transform: capitalize; color: var(--muted); }
</style>
