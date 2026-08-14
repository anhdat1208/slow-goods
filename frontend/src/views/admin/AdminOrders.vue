<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { api, money } from '../../api/client'
import type { Order, Paginated } from '../../types'
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()
const orders = ref<Order[]>([])
const statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled']

async function load() {
  const data = await api<Paginated<Order>>('/admin/orders', {}, auth.token)
  orders.value = data.data
}

async function updateStatus(order: Order, status: string) {
  await api(`/admin/orders/${order.id}/status`, { method: 'PATCH', body: JSON.stringify({ status }) }, auth.token)
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <h3>Orders</h3>
    <div v-for="order in orders" :key="order.id" class="row">
      <div>
        <strong>{{ order.order_number }}</strong>
        <p class="muted">{{ order.full_name }} · {{ money(order.total) }}</p>
      </div>
      <select :value="order.status" @change="updateStatus(order, ($event.target as HTMLSelectElement).value)">
        <option v-for="status in statuses" :key="status" :value="status">{{ status }}</option>
      </select>
    </div>
  </div>
</template>

<style scoped>
.row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  align-items: center;
  border-bottom: 1px solid var(--line);
  padding: 0.8rem 0;
}
select {
  border: 1px solid var(--line);
  border-radius: 999px;
  padding: 0.5rem 0.8rem;
  background: white;
}
</style>
