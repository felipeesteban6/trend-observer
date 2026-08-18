<script setup>
import { Head, Link } from '@inertiajs/vue3'
import ShopLayout from '@/Layouts/ShopLayout.vue'

defineProps({
  products: Array,
})
</script>

<template>
  <Head title="Catálogo" />
  <ShopLayout>
    <h1 class="mb-6 text-xl font-semibold">Catálogo</h1>

    <div v-if="!products.length" class="rounded-lg border border-dashed border-slate-300 p-8 text-center text-slate-500">
      Todavía no hay productos publicados.
    </div>

    <div v-else class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
      <Link
        v-for="product in products"
        :key="product.id"
        :href="`/tienda/producto/${product.slug}`"
        class="group overflow-hidden rounded-lg border border-slate-200 bg-white transition hover:shadow-md"
      >
        <div class="aspect-square overflow-hidden bg-slate-100">
          <img
            v-if="product.image_url"
            :src="product.image_url"
            :alt="product.name"
            class="h-full w-full object-cover transition group-hover:scale-105"
          />
        </div>
        <div class="p-3">
          <p class="line-clamp-2 text-sm font-medium text-slate-800">{{ product.name }}</p>
          <p class="mt-1 text-sm font-semibold text-indigo-600">
            {{ product.currency }} {{ Number(product.sale_price).toFixed(2) }}
          </p>
        </div>
      </Link>
    </div>
  </ShopLayout>
</template>
