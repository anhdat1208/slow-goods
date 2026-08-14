import { defineStore } from 'pinia'
import { ref } from 'vue'
import { api } from '../api/client'
import type { Product } from '../types'
import { useAuthStore } from './auth'

export const useWishlistStore = defineStore('wishlist', () => {
  const productIds = ref<number[]>([])
  const items = ref<Array<{ id: number; product_id: number; product: Product }>>([])

  async function fetch() {
    const auth = useAuthStore()
    if (!auth.token) {
      productIds.value = []
      items.value = []
      return
    }
    items.value = await api('/wishlist', {}, auth.token)
    productIds.value = items.value.map((i) => i.product_id)
  }

  async function toggle(productId: number) {
    const auth = useAuthStore()
    if (!auth.token) throw new Error('Please sign in to use wishlist')

    if (productIds.value.includes(productId)) {
      await api(`/wishlist/${productId}`, { method: 'DELETE' }, auth.token)
      productIds.value = productIds.value.filter((id) => id !== productId)
      items.value = items.value.filter((i) => i.product_id !== productId)
    } else {
      await api('/wishlist', { method: 'POST', body: JSON.stringify({ product_id: productId }) }, auth.token)
      productIds.value.push(productId)
      await fetch()
    }
  }

  function has(productId: number) {
    return productIds.value.includes(productId)
  }

  return { productIds, items, fetch, toggle, has }
})
