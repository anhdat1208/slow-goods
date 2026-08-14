<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { api } from '../../api/client'
import type { Paginated, Review } from '../../types'
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()
const reviews = ref<Array<Review & { product?: { name: string }; user?: { name: string; email: string }; is_hidden?: boolean }>>([])

async function load() {
  const data = await api<Paginated<any>>('/admin/reviews', {}, auth.token)
  reviews.value = data.data
}

async function hide(id: number) {
  await api(`/admin/reviews/${id}/hide`, { method: 'PATCH' }, auth.token)
  await load()
}

async function remove(id: number) {
  if (!confirm('Delete review?')) return
  await api(`/admin/reviews/${id}`, { method: 'DELETE' }, auth.token)
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <h3>Reviews</h3>
    <div v-for="review in reviews" :key="review.id" class="row">
      <div>
        <strong>{{ review.product?.name }}</strong>
        <p>{{ review.rating }}★ — {{ review.comment }}</p>
        <p class="muted">{{ review.user?.name }} · {{ review.is_hidden ? 'hidden' : 'visible' }}</p>
      </div>
      <div>
        <button class="btn-ghost" type="button" @click="hide(review.id)">Hide</button>
        <button class="btn-ghost" type="button" @click="remove(review.id)">Delete</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.row {
  display: flex;
  justify-content: space-between;
  gap: 1rem;
  border-bottom: 1px solid var(--line);
  padding: 0.8rem 0;
}
</style>
