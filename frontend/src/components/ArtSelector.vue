<template>
  <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-lg p-3">
    <div class="flex items-center gap-2 mb-2">
      <span class="text-[11px] font-semibold text-[#aaa]">🎨 艺术风格</span>
      <button @click="$emit('update:modelValue','none')" class="ml-auto px-1.5 py-px bg-transparent border border-[#333] rounded text-[8px] text-[#555] hover:text-[#e74c3c]">重置</button>
    </div>
    <div class="grid grid-cols-6 gap-1">
      <div v-for="a in arts" :key="a.id" @click="$emit('update:modelValue', a.id)"
        :class="['bg-[#25252d] border rounded-md p-1 cursor-pointer text-center transition-all',
          modelValue===a.id ? 'border-[#667eea] bg-[#667eea15]' : 'border-[#333] hover:border-[#667eea66]']">
        <div class="w-full h-10 rounded overflow-hidden mb-0.5" v-html="a.icon"></div>
        <div class="text-[9px] font-semibold text-[#ccc]">{{ a.name }}</div>
      </div>
    </div>
  </div>
</template>
<script setup>
defineProps({ modelValue: String })
defineEmits(['update:modelValue'])
const arts = [
  { id:'none', name:'无', icon:'<div class="w-full h-full bg-[#222] flex items-center justify-center text-xs">—</div>' },
  { id:'3d', name:'3D CG', icon:'<div class="w-full h-full bg-[#1a2030] flex items-center justify-center text-xs">3D</div>' },
  { id:'real', name:'超写实', icon:'<div class="w-full h-full bg-[#201a15] flex items-center justify-center text-xs">📷</div>' },
  { id:'anime', name:'动漫', icon:'<div class="w-full h-full bg-[#2a1525] flex items-center justify-center text-xs">🎌</div>' },
  { id:'paper', name:'剪纸', icon:'<div class="w-full h-full bg-[#2a2010] flex items-center justify-center text-xs">✂️</div>' },
  { id:'doll', name:'布偶', icon:'<div class="w-full h-full bg-[#252015] flex items-center justify-center text-xs">🧸</div>' },
  { id:'oil', name:'油画', icon:'<div class="w-full h-full bg-[#1a1510] flex items-center justify-center text-xs">🖼️</div>' },
  { id:'water', name:'水彩', icon:'<div class="w-full h-full bg-[#152030] flex items-center justify-center text-xs">🎨</div>' },
  { id:'sketch', name:'素描', icon:'<div class="w-full h-full bg-[#1a1a1a] flex items-center justify-center text-xs">✏️</div>' },
  { id:'pixel', name:'像素风', icon:'<div class="w-full h-full bg-[#102010] flex items-center justify-center text-xs">👾</div>' },
  { id:'clay', name:'黏土', icon:'<div class="w-full h-full bg-[#201810] flex items-center justify-center text-xs">🏺</div>' },
  { id:'ink', name:'水墨', icon:'<div class="w-full h-full bg-[#1a1a20] flex items-center justify-center text-xs">🖌️</div>' }
]
</script>
