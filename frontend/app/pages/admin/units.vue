<template>
  <div class="min-h-screen bg-slate-950 text-slate-100 px-6 py-10">
    
    <!-- HEADER -->
    <div class="max-w-4xl mx-auto mb-8 flex flex-col md:flex-row justify-between items-center gap-4">
      <div>
        <h1 class="text-3xl font-extrabold text-white flex items-center gap-3">
          <BuildingOffice2Icon class="text-purple-400 w-10 h-10" />
          Birim Yönetimi
        </h1>
        <p class="text-slate-400 mt-1">Sistemdeki birimleri ve varsayılan yemek ücretlerini yönetin.</p>
      </div>
      <NuxtLink to="/admin" class="px-4 py-2 bg-slate-800 border border-slate-700 rounded-lg text-sm hover:bg-slate-700 transition flex items-center gap-2">
        <ArrowLeftIcon class="w-4 h-4" /> Menüye Dön
      </NuxtLink>
    </div>

    <!-- İÇERİK -->
    <div class="max-w-4xl mx-auto grid md:grid-cols-3 gap-8">
      
      <!-- SOL: EKLEME / DÜZENLEME FORMU -->
      <div class="md:col-span-1">
        <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 sticky top-6 transition-all" :class="editingId ? 'border-purple-500/50 shadow-lg shadow-purple-500/10' : ''">
          
          <h2 class="font-bold text-white mb-4 flex justify-between items-center">
            <span>{{ editingId ? 'Birimi Düzenle' : 'Yeni Birim Ekle' }}</span>
            <span v-if="editingId" class="text-xs text-purple-400 bg-purple-500/10 px-2 py-1 rounded">Düzenleniyor</span>
          </h2>
          
          <div class="space-y-4">
            <div>
              <label class="text-xs text-slate-400 block mb-1">Birim Adı</label>
              <input 
                v-model="form.name" 
                type="text" 
                placeholder="Örn: Belediye Personeli" 
                class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-white focus:border-purple-500 outline-none transition"
              >
            </div>
            
            <div>
              <label class="text-xs text-slate-400 block mb-1">Varsayılan Ücret (TL)</label>
              <div class="relative">
                 <input 
                   v-model="form.price" 
                   type="number" 
                   placeholder="125" 
                   class="w-full bg-slate-950 border border-slate-800 rounded-lg pl-3 pr-8 py-2 text-white focus:border-purple-500 outline-none transition"
                  >
                 <span class="absolute right-3 top-2 text-slate-500 text-sm">₺</span>
              </div>
            </div>

            <!-- BUTONLAR -->
            <div class="flex gap-2">
              <button 
                @click="saveUnit" 
                :disabled="loading" 
                class="flex-1 text-white font-medium py-2 rounded-lg transition flex justify-center items-center gap-2"
                :class="editingId ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-purple-600 hover:bg-purple-500'"
              >
                <span v-if="loading" class="animate-spin">⟳</span>
                <span>{{ loading ? 'İşleniyor...' : (editingId ? 'Güncelle' : 'Kaydet') }}</span>
              </button>

              <button 
                v-if="editingId" 
                @click="cancelEdit" 
                class="px-3 bg-slate-800 hover:bg-slate-700 text-slate-300 rounded-lg transition text-sm flex items-center justify-center"
                title="Vazgeç"
              >
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>

          </div>
        </div>
      </div>

      <!-- SAĞ: LİSTE -->
      <div class="md:col-span-2 space-y-4">
        <div v-if="units.length === 0" class="text-center py-10 text-slate-500 bg-slate-900/30 rounded-2xl border border-dashed border-slate-800">
           Henüz birim eklenmemiş.
        </div>

        <transition-group name="list" tag="div" class="space-y-4">
          <div 
            v-for="unit in units" 
            :key="unit.id" 
            class="group bg-slate-900/60 border border-slate-800 rounded-xl p-4 flex justify-between items-center hover:border-purple-500/30 transition"
            :class="editingId === unit.id ? 'border-purple-500 ring-1 ring-purple-500/50 bg-purple-900/10' : ''"
          >
            <!-- SOL: İSİM VE FİYAT -->
            <div class="flex items-center gap-4">
              <div class="w-10 h-10 rounded-lg bg-purple-500/10 text-purple-400 flex items-center justify-center font-bold text-lg">
                 {{ unit.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h3 class="font-bold text-slate-200">{{ unit.name }}</h3>
                <p class="text-sm text-slate-500">
                  <span class="text-emerald-400 font-mono font-bold">{{ unit.price }} ₺</span>
                </p>
              </div>
            </div>
            
            <!-- SAĞ: BUTONLAR -->
            <div class="flex gap-2">
              <button 
                @click="startEdit(unit)" 
                class="p-2 bg-slate-800 text-blue-400 hover:bg-blue-500/20 border border-slate-700 hover:border-blue-500/50 rounded-lg transition" 
                title="Düzenle"
              >
                <PencilSquareIcon class="w-5 h-5" />
              </button>

              <button 
                @click="deleteUnit(unit.id)" 
                class="p-2 bg-slate-800 text-red-400 hover:bg-red-500/20 border border-slate-700 hover:border-red-500/50 rounded-lg transition" 
                title="Sil"
              >
                <TrashIcon class="w-5 h-5" />
              </button>
            </div>

          </div>
        </transition-group>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { 
  BuildingOffice2Icon, 
  ArrowLeftIcon, 
  PencilSquareIcon, 
  TrashIcon, 
  XMarkIcon 
} from '@heroicons/vue/24/outline'

definePageMeta({ layout: 'admin' })

const units = ref([])
const loading = ref(false)
const form = ref({ name: '', price: '' })
const editingId = ref(null) 

const fetchUnits = async () => {
  try {
    const data = await $fetch('/api/admin/units')
    units.value = data
  } catch (e) {
    console.error(e)
  }
}

const startEdit = (unit) => {
  form.value = { name: unit.name, price: unit.price }
  editingId.value = unit.id 
}

const cancelEdit = () => {
  form.value = { name: '', price: '' }
  editingId.value = null
}

const saveUnit = async () => {
  // 👇 DÜZELTME BURADA: Fiyatın 0 olabilmesine izin veriyoruz (Boş değilse ve null değilse kabul et)
  if (!form.value.name || form.value.price === '' || form.value.price === null) {
    return alert("Lütfen tüm alanları doldurun")
  }
  
  loading.value = true
  try {
    if (editingId.value) {
      await $fetch(`/api/admin/units/${editingId.value}`, {
        method: 'PUT',
        body: { 
          name: form.value.name,
          price: Number(form.value.price)
        }
      })
      // Manuel güncelleme
      const index = units.value.findIndex(u => u.id === editingId.value)
      if (index !== -1) {
        units.value[index] = { ...units.value[index], name: form.value.name, price: Number(form.value.price) }
      }
    } else {
      const newUnit = await $fetch('/api/admin/units', {
        method: 'POST',
        body: { 
          name: form.value.name,
          price: Number(form.value.price)
        }
      })
      units.value.push(newUnit)
    }

    form.value = { name: '', price: '' }
    editingId.value = null
  } catch (e) {
    alert("İşlem başarısız: " + (e.data?.message || 'Hata'))
  } finally {
    loading.value = false
  }
}

const deleteUnit = async (id) => {
  if(!confirm("Bu birimi silmek istediğine emin misin?")) return
  try {
    await $fetch(`/api/admin/units/${id}`, { method: 'DELETE' })
    units.value = units.value.filter(u => u.id !== id)
    if (editingId.value === id) cancelEdit()
  } catch (e) {
    console.error(e)
    alert("Silinemedi.")
  }
}

onMounted(fetchUnits)
</script>

<style scoped>
.list-enter-active,
.list-leave-active {
  transition: all 0.3s ease;
}
.list-enter-from,
.list-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}
</style>