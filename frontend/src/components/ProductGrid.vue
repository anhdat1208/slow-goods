<script setup lang="ts">
import type { Product } from '../types'
import ProductCard from './ProductCard.vue'

defineProps<{ products: Product[] }>()
</script>

<template>
  <div class="grid-products">
    <ProductCard v-for="product in products" :key="product.id" :product="product" />
  </div>
</template>
