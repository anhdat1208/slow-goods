import { defineStore } from 'pinia'
import { computed, ref, watch } from 'vue'
import { api } from '../api/client'
import type { CartItemLocal, Product } from '../types'
import { useAuthStore } from './auth'

const CART_KEY = 'slow_goods_cart'

export const useCartStore = defineStore('cart', () => {
  const items = ref<CartItemLocal[]>(loadCart())
  const open = ref(false)

  const totalQuantity = computed(() => items.value.reduce((sum, i) => sum + i.quantity, 0))
  const subtotal = computed(() =>
    items.value.reduce((sum, i) => sum + Number(i.product?.price || 0) * i.quantity, 0),
  )

  watch(
    items,
    (value) => {
      localStorage.setItem(
        CART_KEY,
        JSON.stringify(
          value.map((i) => ({
            product_id: i.product_id,
            quantity: i.quantity,
            product: i.product,
          })),
        ),
      )
    },
    { deep: true },
  )

  function loadCart(): CartItemLocal[] {
    try {
      return JSON.parse(localStorage.getItem(CART_KEY) || '[]')
    } catch {
      return []
    }
  }

  function addProduct(product: Product, quantity = 1) {
    const existing = items.value.find((i) => i.product_id === product.id)
    const nextQty = (existing?.quantity || 0) + quantity
    if (nextQty > product.stock) {
      throw new Error(`Only ${product.stock} in stock`)
    }
    if (existing) {
      existing.quantity = nextQty
      existing.product = product
    } else {
      items.value.push({ product_id: product.id, quantity, product })
    }
    open.value = true
  }

  function setQuantity(productId: number, quantity: number) {
    const item = items.value.find((i) => i.product_id === productId)
    if (!item) return
    const stock = item.product?.stock ?? quantity
    if (quantity < 1) {
      remove(productId)
      return
    }
    item.quantity = Math.min(quantity, stock)
  }

  function increment(productId: number) {
    const item = items.value.find((i) => i.product_id === productId)
    if (!item) return
    const stock = item.product?.stock ?? item.quantity
    if (item.quantity < stock) item.quantity += 1
  }

  function decrement(productId: number) {
    const item = items.value.find((i) => i.product_id === productId)
    if (!item) return
    if (item.quantity <= 1) remove(productId)
    else item.quantity -= 1
  }

  function remove(productId: number) {
    items.value = items.value.filter((i) => i.product_id !== productId)
  }

  function clear() {
    items.value = []
  }

  async function syncToServer() {
    const auth = useAuthStore()
    if (!auth.token || items.value.length === 0) return

    await api(
      '/cart/sync',
      {
        method: 'POST',
        body: JSON.stringify({
          items: items.value.map((i) => ({
            product_id: i.product_id,
            quantity: i.quantity,
          })),
        }),
      },
      auth.token,
    )
  }

  async function pullFromServer() {
    const auth = useAuthStore()
    if (!auth.token) return

    const data = await api<{
      items: Array<{ product_id: number; quantity: number; product: Product }>
    }>('/cart', {}, auth.token)

    if (data.items.length) {
      items.value = data.items.map((i) => ({
        product_id: i.product_id,
        quantity: i.quantity,
        product: i.product,
      }))
    }
  }

  async function prepareCheckout() {
    const auth = useAuthStore()
    if (!auth.token) throw new Error('Please sign in to checkout')

    await api('/cart', { method: 'DELETE' }, auth.token)
    await api(
      '/cart/sync',
      {
        method: 'POST',
        body: JSON.stringify({
          items: items.value.map((i) => ({
            product_id: i.product_id,
            quantity: i.quantity,
          })),
        }),
      },
      auth.token,
    )
  }

  return {
    items,
    open,
    totalQuantity,
    subtotal,
    addProduct,
    setQuantity,
    increment,
    decrement,
    remove,
    clear,
    syncToServer,
    pullFromServer,
    prepareCheckout,
  }
})
