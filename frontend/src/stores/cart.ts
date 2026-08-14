import { defineStore } from 'pinia'
import { computed, ref, watch } from 'vue'
import { api } from '../api/client'
import type { CartItemLocal, Product } from '../types'
import { useAuthStore } from './auth'
import router from '../router'

const CART_KEY = 'slow_goods_cart'

type ServerCart = {
  items: Array<{ id?: number; product_id: number; quantity: number; product: Product }>
  subtotal?: string
  total_quantity?: number
}

export const useCartStore = defineStore('cart', () => {
  const items = ref<CartItemLocal[]>(loadCart())
  const open = ref(false)
  const persisting = ref(false)

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

  function requireMember(): boolean {
    const auth = useAuthStore()
    if (auth.isAuthenticated && auth.token) return true
    router.push({
      name: 'login',
      query: { redirect: router.currentRoute.value.fullPath || '/' },
    })
    return false
  }

  function applyServerCart(data: ServerCart) {
    items.value = (data.items || []).map((i) => ({
      product_id: i.product_id,
      quantity: i.quantity,
      product: i.product,
    }))
  }

  async function persist() {
    const auth = useAuthStore()
    if (!auth.token) return

    persisting.value = true
    try {
      const data = await api<ServerCart>(
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
      applyServerCart(data)
    } finally {
      persisting.value = false
    }
  }

  async function addProduct(product: Product, quantity = 1, options: { persist?: boolean; openDrawer?: boolean } = {}) {
    if (!requireMember()) {
      throw new Error('LOGIN_REQUIRED')
    }

    const persistNow = options.persist !== false
    const existing = items.value.find((i) => i.product_id === product.id)
    const nextQty = (existing?.quantity || 0) + quantity
    if (nextQty > product.stock) {
      throw new Error(`STOCK:${product.stock}`)
    }
    if (existing) {
      existing.quantity = nextQty
      existing.product = product
    } else {
      items.value.push({ product_id: product.id, quantity, product })
    }
    if (options.openDrawer !== false) open.value = true
    if (persistNow) await persist()
  }

  async function setQuantity(productId: number, quantity: number) {
    if (!requireMember()) return
    const item = items.value.find((i) => i.product_id === productId)
    if (!item) return
    const stock = item.product?.stock ?? quantity
    if (quantity < 1) {
      await remove(productId)
      return
    }
    item.quantity = Math.min(quantity, stock)
    await persist()
  }

  async function increment(productId: number) {
    if (!requireMember()) return
    const item = items.value.find((i) => i.product_id === productId)
    if (!item) return
    const stock = item.product?.stock ?? item.quantity
    if (item.quantity < stock) {
      item.quantity += 1
      await persist()
    }
  }

  async function decrement(productId: number) {
    if (!requireMember()) return
    const item = items.value.find((i) => i.product_id === productId)
    if (!item) return
    if (item.quantity <= 1) {
      await remove(productId)
      return
    }
    item.quantity -= 1
    await persist()
  }

  async function remove(productId: number) {
    if (!requireMember()) return
    items.value = items.value.filter((i) => i.product_id !== productId)
    await persist()
  }

  function clear() {
    items.value = []
  }

  async function pullFromServer() {
    const auth = useAuthStore()
    if (!auth.token) return

    const data = await api<ServerCart>('/cart', {}, auth.token)
    applyServerCart(data)
  }

  async function adoptAccountCart() {
    const auth = useAuthStore()
    if (!auth.token) return

    const local = items.value.map((i) => ({ ...i }))
    const server = await api<ServerCart>('/cart', {}, auth.token)
    const merged = new Map<number, CartItemLocal>()

    for (const item of server.items || []) {
      merged.set(item.product_id, {
        product_id: item.product_id,
        quantity: item.quantity,
        product: item.product,
      })
    }

    for (const item of local) {
      const current = merged.get(item.product_id)
      const stock = item.product?.stock ?? current?.product?.stock ?? item.quantity
      const qty = Math.min((current?.quantity || 0) + item.quantity, stock)
      merged.set(item.product_id, {
        product_id: item.product_id,
        quantity: qty,
        product: item.product || current?.product,
      })
    }

    items.value = Array.from(merged.values()).filter((i) => i.quantity > 0)
    await persist()
  }

  async function prepareCheckout() {
    const auth = useAuthStore()
    if (!auth.token) throw new Error('LOGIN_REQUIRED')
    await persist()
  }

  function openCart() {
    if (!requireMember()) return
    open.value = true
  }

  return {
    items,
    open,
    persisting,
    totalQuantity,
    subtotal,
    addProduct,
    setQuantity,
    increment,
    decrement,
    remove,
    clear,
    persist,
    pullFromServer,
    adoptAccountCart,
    prepareCheckout,
    openCart,
  }
})
