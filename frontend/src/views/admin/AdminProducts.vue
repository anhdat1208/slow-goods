<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api, money } from '../../api/client'
import type { Category, Paginated, Product } from '../../types'
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()
const products = ref<Product[]>([])
const categories = ref<Category[]>([])
const editingId = ref<number | null>(null)
const message = ref('')

const form = reactive({
  category_id: 0,
  name: '',
  description: '',
  short_description: '',
  price: 0,
  stock: 0,
  sku: '',
  image_url: '',
  is_featured: false,
  is_active: true,
})

async function load() {
  const [p, c] = await Promise.all([
    api<Paginated<Product>>('/admin/products', {}, auth.token),
    api<Category[]>('/admin/categories', {}, auth.token),
  ])
  products.value = p.data
  categories.value = c
  if (!form.category_id && c[0]) form.category_id = c[0].id
}

function edit(product: Product) {
  editingId.value = product.id
  Object.assign(form, {
    category_id: product.category_id,
    name: product.name,
    description: product.description,
    short_description: product.short_description,
    price: Number(product.price),
    stock: product.stock,
    sku: product.sku,
    image_url: product.image_url,
    is_featured: product.is_featured,
    is_active: product.is_active ?? true,
  })
}

function reset() {
  editingId.value = null
  Object.assign(form, {
    category_id: categories.value[0]?.id || 0,
    name: '',
    description: '',
    short_description: '',
    price: 0,
    stock: 0,
    sku: '',
    image_url: '',
    is_featured: false,
    is_active: true,
  })
}

async function save() {
  if (editingId.value) {
    await api(`/admin/products/${editingId.value}`, { method: 'PUT', body: JSON.stringify(form) }, auth.token)
    message.value = 'Product updated'
  } else {
    await api('/admin/products', { method: 'POST', body: JSON.stringify(form) }, auth.token)
    message.value = 'Product created'
  }
  reset()
  await load()
}

async function remove(id: number) {
  if (!confirm('Delete this product?')) return
  await api(`/admin/products/${id}`, { method: 'DELETE' }, auth.token)
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <h3>Products</h3>
    <p v-if="message" class="muted">{{ message }}</p>

    <form class="form" @submit.prevent="save">
      <div class="grid">
        <div class="field"><label>Name</label><input v-model="form.name" required /></div>
        <div class="field"><label>SKU</label><input v-model="form.sku" required /></div>
        <div class="field">
          <label>Category</label>
          <select v-model.number="form.category_id">
            <option v-for="c in categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
        <div class="field"><label>Price</label><input v-model.number="form.price" type="number" step="0.01" required /></div>
        <div class="field"><label>Stock</label><input v-model.number="form.stock" type="number" required /></div>
        <div class="field"><label>Image URL</label><input v-model="form.image_url" /></div>
      </div>
      <div class="field"><label>Short description</label><input v-model="form.short_description" required /></div>
      <div class="field"><label>Description</label><textarea v-model="form.description" rows="3" required /></div>
      <label><input v-model="form.is_featured" type="checkbox" /> Featured</label>
      <label><input v-model="form.is_active" type="checkbox" /> Active</label>
      <div class="actions">
        <button class="btn" type="submit">{{ editingId ? 'Update' : 'Create' }}</button>
        <button v-if="editingId" class="btn btn-secondary" type="button" @click="reset">Cancel</button>
      </div>
    </form>

    <div class="table">
      <div v-for="product in products" :key="product.id" class="row">
        <div>
          <strong>{{ product.name }}</strong>
          <p class="muted">{{ product.sku }} · {{ money(product.price) }} · stock {{ product.stock }}</p>
        </div>
        <div class="actions">
          <button class="btn-ghost" type="button" @click="edit(product)">Edit</button>
          <button class="btn-ghost" type="button" @click="remove(product.id)">Delete</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.form, .table { display: grid; gap: 0.8rem; margin-top: 1rem; }
.grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 0.8rem; }
.row { display: flex; justify-content: space-between; gap: 1rem; border-bottom: 1px solid var(--line); padding: 0.7rem 0; }
.actions { display: flex; gap: 0.5rem; align-items: center; }
</style>
