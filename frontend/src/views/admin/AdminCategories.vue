<script setup lang="ts">
import { onMounted, reactive, ref } from 'vue'
import { api } from '../../api/client'
import type { Category } from '../../types'
import { useAuthStore } from '../../stores/auth'

const auth = useAuthStore()
const categories = ref<Category[]>([])
const editingId = ref<number | null>(null)
const form = reactive({ name: '', description: '', image_url: '' })

async function load() {
  categories.value = await api('/admin/categories', {}, auth.token)
}

function edit(category: Category) {
  editingId.value = category.id
  Object.assign(form, {
    name: category.name,
    description: category.description,
    image_url: category.image_url,
  })
}

function reset() {
  editingId.value = null
  Object.assign(form, { name: '', description: '', image_url: '' })
}

async function save() {
  if (editingId.value) {
    await api(`/admin/categories/${editingId.value}`, { method: 'PUT', body: JSON.stringify(form) }, auth.token)
  } else {
    await api('/admin/categories', { method: 'POST', body: JSON.stringify(form) }, auth.token)
  }
  reset()
  await load()
}

async function remove(id: number) {
  if (!confirm('Delete category?')) return
  await api(`/admin/categories/${id}`, { method: 'DELETE' }, auth.token)
  await load()
}

onMounted(load)
</script>

<template>
  <div>
    <h3>Categories</h3>
    <form class="form" @submit.prevent="save">
      <div class="field"><label>Name</label><input v-model="form.name" required /></div>
      <div class="field"><label>Description</label><textarea v-model="form.description" rows="2" /></div>
      <div class="field"><label>Image URL</label><input v-model="form.image_url" /></div>
      <button class="btn" type="submit">{{ editingId ? 'Update' : 'Create' }}</button>
    </form>
    <div v-for="category in categories" :key="category.id" class="row">
      <div>
        <strong>{{ category.name }}</strong>
        <p class="muted">{{ category.slug }} · {{ category.products_count || 0 }} products</p>
      </div>
      <div>
        <button class="btn-ghost" type="button" @click="edit(category)">Edit</button>
        <button class="btn-ghost" type="button" @click="remove(category.id)">Delete</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.form { display: grid; gap: 0.8rem; margin: 1rem 0; }
.row { display: flex; justify-content: space-between; gap: 1rem; border-bottom: 1px solid var(--line); padding: 0.7rem 0; }
</style>
