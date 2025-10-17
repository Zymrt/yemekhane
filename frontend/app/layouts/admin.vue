<template>
  <div class="bg-green-700 text-white p-10">
  <slot />
</div>
  <div class="min-h-screen text-white bg-gradient-to-br from-gray-900 via-blue-900 to-gray-800 fixed inset-0 overflow-y-auto">
    <header class="backdrop-blur-md bg-black/20 border-b border-white/10 sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <NuxtLink to="/admin" class="text-xl font-bold hover:opacity-80 transition">
          Yönetim Paneli
        </NuxtLink>
        <slot name="header-right" />
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-8">
      <slot />
    </main>
  </div>
</template>
