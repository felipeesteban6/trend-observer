<script setup>
import { useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({ keywords: Array })

const form = useForm({ term: '', language: 'es', geo: '', category: '' })

function submit() {
  form.post('/keywords', { onSuccess: () => form.reset('term', 'category') })
}

function toggle(keyword) {
  router.patch(`/keywords/${keyword.id}`, { is_active: !keyword.is_active }, { preserveScroll: true })
}

function remove(keyword) {
  if (confirm(`¿Eliminar "${keyword.term}"?`)) {
    router.delete(`/keywords/${keyword.id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <AppLayout>
    <h1 class="mb-4 text-xl font-semibold">Keywords a monitorear</h1>

    <form @submit.prevent="submit" class="mb-8 grid grid-cols-1 gap-3 rounded-lg border border-slate-200 bg-white p-4 sm:grid-cols-5">
      <input v-model="form.term" placeholder="Ej: mochila anti robo" class="rounded-md border-slate-300 sm:col-span-2" required />
      <select v-model="form.language" class="rounded-md border-slate-300">
        <option value="es">Español</option>
        <option value="en">Inglés</option>
      </select>
      <input v-model="form.geo" placeholder="Geo (CL, MX, ... vacío = mundial)" class="rounded-md border-slate-300" />
      <input v-model="form.category" placeholder="Categoría (opcional)" class="rounded-md border-slate-300" />
      <button type="submit" class="col-span-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 sm:col-span-1">
        Agregar
      </button>
    </form>

    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white">
      <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
          <tr>
            <th class="px-4 py-3">Término</th>
            <th class="px-4 py-3">Idioma</th>
            <th class="px-4 py-3">Geo</th>
            <th class="px-4 py-3">Snapshots</th>
            <th class="px-4 py-3">Activa</th>
            <th class="px-4 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="k in keywords" :key="k.id">
            <td class="px-4 py-3 font-medium">{{ k.term }}</td>
            <td class="px-4 py-3 text-slate-500">{{ k.language }}</td>
            <td class="px-4 py-3 text-slate-500">{{ k.geo || 'Mundial' }}</td>
            <td class="px-4 py-3 text-slate-500">{{ k.snapshots_count }}</td>
            <td class="px-4 py-3">
              <button @click="toggle(k)" class="rounded-full px-2 py-1 text-xs" :class="k.is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500'">
                {{ k.is_active ? 'Activa' : 'Pausada' }}
              </button>
            </td>
            <td class="px-4 py-3 text-right">
              <button @click="remove(k)" class="text-xs text-red-600 hover:underline">Eliminar</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </AppLayout>
</template>
