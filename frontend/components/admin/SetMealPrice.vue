<template>
  <div class="flex items-center gap-2">
    <input
      v-model.number="price"
      type="number"
      min="0"
      step="0.5"
      class="px-3 py-2 rounded-lg bg-white/10 border border-white/20 text-white w-28"
      :placeholder="placeholder"
    />
    <button
      class="px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white"
      :disabled="busy || price === '' || price === null || price < 0"
      @click="save"
    >
      {{ busy ? 'Kaydediliyor...' : 'Fiyat Ata' }}
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  userId: { type: String, required: true },
  currentPrice: { type: [Number, String, null], default: null },
  placeholder: { type: String, default: '₺' }
})

const emit = defineEmits(['updated'])

const price = ref(props.currentPrice ?? '')
const busy = ref(false)

const idOf = (raw) => {
  if (!raw) return null
  if (typeof raw === 'string') return raw
  if (raw?.$oid) return raw.$oid
  const m = String(raw).match(/ObjectId\(["']?([a-f\d]{24})["']?\)/i)
  return m ? m[1] : null
}

const save = async () => {
  if (price.value === '' || price.value === null) return
  try {
    busy.value = true
    await $fetch(`/api/admin/users/${encodeURIComponent(idOf(props.userId) || props.userId)}/set-meal-price`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: { meal_price: Number(price.value) }
    })
    alert('✅ Kişiye özel fiyat güncellendi.')
    emit('updated', Number(price.value))
  } catch (e) {
    console.error(e)
    alert(e?.data?.message || 'Fiyat güncellenemedi.')
  } finally {
    busy.value = false
  }
}
</script>
