<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import { computed } from 'vue'

const page = usePage()
const cartCount = computed(() => page.props.cartCount ?? 0)
const flashSuccess = computed(() => page.props.flash?.success)
const flashError = computed(() => page.props.flash?.error)
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <nav class="border-b border-slate-200 bg-white">
      <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
        <Link href="/tienda" class="font-semibold text-slate-800">Tienda</Link>
        <Link href="/tienda/carrito" class="flex items-center gap-1 text-sm text-slate-600 hover:text-slate-900">
          Carrito
          <span v-if="cartCount > 0" class="rounded-full bg-indigo-600 px-2 py-0.5 text-xs font-medium text-white">
            {{ cartCount }}
          </span>
        </Link>
      </div>
    </nav>

    <div v-if="flashSuccess" class="mx-auto mt-4 max-w-6xl px-4">
      <div class="rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">{{ flashSuccess }}</div>
    </div>
    <div v-if="flashError" class="mx-auto mt-4 max-w-6xl px-4">
      <div class="rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">{{ flashError }}</div>
    </div>

    <main class="mx-auto max-w-6xl px-4 py-8">
      <slot />
    </main>
  </div>
</template>
