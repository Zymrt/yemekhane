<template>
  <div class="max-w-[1200px] mx-auto animate-fade-in">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <span class="p-2 bg-purple-500/10 rounded-xl border border-purple-500/20 text-purple-400">
            <BuildingOffice2Icon class="w-8 h-8" />
          </span>
          Birim Yönetimi
        </h1>
        <p class="text-slate-400 mt-2 ml-1">Departmanları ve fiyatlandırmayı yapılandırın.</p>
      </div>
      <NuxtLink to="/admin" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm hover:bg-white/10 transition flex items-center gap-2 text-slate-300">
        <ArrowLeftIcon class="w-4 h-4" /> Menüye Dön
      </NuxtLink>
    </div>

    <!-- İÇERİK -->
    <div class="max-w-4xl mx-auto grid md:grid-cols-3 gap-8">
      
      <!-- SOL PANEL: EKLEME / DÜZENLEME FORMU -->
      <div class="md:col-span-1">
        <div class="bg-[#121212]/80 border border-white/5 rounded-3xl p-6 backdrop-blur-xl shadow-2xl sticky top-6 transition-all duration-300"
             :class="editingId ? 'border-purple-500/50 ring-1 ring-purple-500/20' : ''">
          
          <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
            <span v-if="editingId" class="w-2 h-2 bg-purple-500 rounded-full animate-pulse"></span>
            {{ editingId ? 'Birimi Düzenle' : 'Yeni Birim Ekle' }}
          </h2>
          
          <div class="space-y-5">
            <div class="group">
              <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 block group-focus-within:text-purple-400 transition-colors">Birim Adı</label>
              <input 
                v-model="form.name" 
                type="text" 
                placeholder="Örn: Belediye Personeli" 
                class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500/50 outline-none transition-all placeholder-slate-600"
              >
            </div>
            
            <div class="group">
              <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 block group-focus-within:text-purple-400 transition-colors">Varsayılan Ücret (TL)</label>
              <div class="relative">
                 <input 
                   v-model="form.price" 
                   type="number" 
                   placeholder="125" 
                   class="w-full bg-black/40 border border-white/10 rounded-xl pl-4 pr-10 py-3 text-white focus:border-purple-500 focus:ring-1 focus:ring-purple-500/50 outline-none transition-all font-mono"
                  >
                 <span class="absolute right-4 top-3.5 text-slate-500 font-bold">₺</span>
              </div>
            </div>

            <!-- BUTONLAR -->
            <div class="pt-2 flex gap-3">
              <button 
                @click="saveUnit" 
                :disabled="loading" 
                class="flex-1 text-white font-bold py-3 rounded-xl transition-all shadow-lg active:scale-95 flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
                :class="editingId 
                  ? 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 shadow-emerald-900/20' 
                  : 'bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 shadow-purple-900/20'"
              >
                <span v-if="loading" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
                <span>{{ loading ? 'İşleniyor...' : (editingId ? 'Güncelle' : 'Kaydet') }}</span>
              </button>

              <button 
                v-if="editingId" 
                @click="cancelEdit" 
                class="px-4 bg-white/5 hover:bg-white/10 border border-white/5 text-slate-300 rounded-xl transition active:scale-95 flex items-center justify-center"
                title="Vazgeç"
              >
                <XMarkIcon class="w-5 h-5" />
              </button>
            </div>

          </div>
        </div>
      </div>

      <!-- SAĞ PANEL: LİSTE -->
      <div class="md:col-span-2 space-y-4">
        <div v-if="units.length === 0" class="flex flex-col items-center justify-center py-20 bg-white/5 rounded-3xl border border-dashed border-white/10 text-slate-500">
           <BuildingOffice2Icon class="w-16 h-16 mb-4 opacity-20" />
           <p>Henüz birim eklenmemiş.</p>
        </div>

        <transition-group name="list" tag="div" class="space-y-3">
          <div 
            v-for="unit in units" 
            :key="unit.id" 
            class="group bg-[#121212]/60 border border-white/5 rounded-2xl p-4 flex justify-between items-center hover:bg-white/5 hover:border-purple-500/30 transition-all duration-300 backdrop-blur-sm"
            :class="editingId === unit.id ? 'bg-purple-900/10 border-purple-500/50' : ''"
          >
            <!-- SOL: İSİM VE FİYAT -->
            <div class="flex items-center gap-5">
              <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-gray-800 to-black border border-white/10 flex items-center justify-center font-black text-xl text-slate-300 shadow-inner">
                 {{ unit.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h3 class="font-bold text-white text-lg group-hover:text-purple-300 transition-colors">{{ unit.name }}</h3>
                <div class="flex items-center gap-2 mt-1">
                  <span 
                    class="px-2 py-0.5 rounded text-xs font-mono font-bold"
                    :class="unit.price === 0 ? 'bg-slate-700/50 text-slate-300' : 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-400'"
                  >
                    {{ unit.price }} ₺
                  </span>
                </div>
              </div>
            </div>
            
            <!-- SAĞ: BUTONLAR -->
            <div class="flex gap-2 opacity-60 group-hover:opacity-100 transition-opacity">
              <button 
                @click="startEdit(unit)" 
                class="p-2.5 bg-white/5 text-blue-400 hover:bg-blue-500/20 border border-white/5 hover:border-blue-500/50 rounded-lg transition" 
              >
                <PencilSquareIcon class="w-5 h-5" />
              </button>

              <button 
                @click="deleteUnit(unit.id)" 
                class="p-2.5 bg-white/5 text-red-400 hover:bg-red-500/20 border border-white/5 hover:border-red-500/50 rounded-lg transition" 
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
  // Price'ı Number olarak atıyoruz ki input düzgün çalışsın
  form.value = { name: unit.name, price: Number(unit.price) }
  editingId.value = unit.id 
}

const cancelEdit = () => {
  form.value = { name: '', price: '' }
  editingId.value = null
}

const saveUnit = async () => {
  // 💥 KRİTİK KONTROL DÜZELTİLDİ: 0 (sıfır) değeri kabul ediliyor, sadece boş string/null reddediliyor.
  if (!form.value.name || form.value.price === '' || form.value.price === null || form.value.price < 0) {
    return alert("Lütfen geçerli bir isim girin ve ücreti (sıfır bile olsa) belirtin.")
  }
  
  loading.value = true
  try {
    const payloadPrice = Number(form.value.price); // Backend'e sayı olarak gönder

    if (editingId.value) {
      // GÜNCELLEME
      await $fetch(`/api/admin/units/${editingId.value}`, {
        method: 'PUT',
        body: { 
          name: form.value.name,
          price: payloadPrice
        }
      })
      // Manuel güncelleme (Hızlı UI için)
      const index = units.value.findIndex(u => u.id === editingId.value)
      if (index !== -1) {
        units.value[index] = { ...units.value[index], name: form.value.name, price: payloadPrice }
      }
    } else {
      // YENİ KAYIT
      const newUnit = await $fetch('/api/admin/units', {
        method: 'POST',
        body: { 
          name: form.value.name,
          price: payloadPrice
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
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
</style>