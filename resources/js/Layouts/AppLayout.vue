<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <div class="min-h-screen bg-slate-50 text-slate-900">
    <nav class="border-b border-slate-200 bg-white">
      <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
        <Link href="/" class="font-semibold text-slate-800">Observador de Tendencias</Link>
        <div class="flex gap-4 text-sm text-slate-600">
          <Link href="/" class="hover:text-slate-900">Ranking</Link>
          <Link href="/keywords" class="hover:text-slate-900">Keywords</Link>
        </div>
      </div>
    </nav>
    <main class="mx-auto max-w-6xl px-4 py-8">
      <slot />
    </main>
  </div>
</template>
