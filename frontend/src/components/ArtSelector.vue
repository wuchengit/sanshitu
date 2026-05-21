<template>
  <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-lg p-3">
    <div class="flex items-center gap-2 mb-2">
      <span class="text-[11px] font-semibold text-[#aaa]">🎨 艺术风格</span>
      <button @click="$emit('update:modelValue','none')" class="ml-auto px-1.5 py-px bg-transparent border border-[#333] rounded text-[8px] text-[#555] hover:text-[#e74c3c]">重置</button>
      <button v-if="user" @click="$emit('edit')" class="px-1.5 py-px bg-transparent border border-[#333] rounded text-[8px] text-[#555] hover:text-[#667eea]">＋</button>
    </div>
    <div class="grid grid-cols-6 gap-1">
      <div v-for="a in items" :key="a.id" @click="$emit('update:modelValue', a.id)"
        :class="['bg-[#25252d] border rounded-md p-1 cursor-pointer text-center transition-all group relative',
          modelValue===a.id ? 'border-[#667eea] bg-[#667eea15]' : 'border-[#333] hover:border-[#667eea66]',
          a._custom ? 'border-[#667eea44]! bg-[#667eea0a]!' : '']">
        <span v-if="a._custom" class="bg-[#667eea22] text-[#667eea] text-[7px] px-0.5 rounded absolute top-0.5 left-0.5">我的</span>
        <button v-if="a._custom && user" @click.stop="$emit('delete', a._id)" class="absolute top-0.5 right-0.5 w-3.5 h-3.5 bg-[#ef444488] rounded-full text-white text-[7px] hidden group-hover:flex items-center justify-center z-10">✕</button>
        <button v-if="user" @click.stop="$emit('edit', a)" class="absolute top-0.5 right-0.5 w-4 h-4 bg-transparent text-[#666] text-[9px] hidden group-hover:flex items-center justify-center rounded hover:text-[#667eea] hover:bg-[#667eea22] z-10" :style="a._custom?'right-5':''">✏️</button>
        <div class="w-full h-10 rounded overflow-hidden mb-0.5">
          <img v-if="a.icon && !a.icon.startsWith('<')" :src="a.icon" class="w-full h-full object-cover" />
          <div v-else class="w-full h-full bg-[#222] flex items-center justify-center text-xs">🎨</div>
        </div>
        <div class="text-[9px] font-semibold text-[#ccc]">{{ a.name }}</div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { computed } from 'vue'
const props = defineProps({ modelValue: String, customPresets: Array, user: Object })
defineEmits(['update:modelValue', 'edit', 'delete'])
const defaults = [
  { id:'none', name:'无' }, { id:'3d', name:'3D CG' }, { id:'real', name:'超写实' },
  { id:'anime', name:'动漫' }, { id:'paper', name:'剪纸' }, { id:'doll', name:'布偶' },
  { id:'oil', name:'油画' }, { id:'water', name:'水彩' }, { id:'sketch', name:'素描' },
  { id:'pixel', name:'像素风' }, { id:'clay', name:'黏土' }, { id:'ink', name:'水墨' }
]
const items = computed(() => [...defaults, ...(props.customPresets||[])])
</script>
