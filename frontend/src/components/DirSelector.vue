<template>
  <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-lg p-3">
    <div class="flex items-center gap-2 mb-2">
      <span class="text-[11px] font-semibold text-[#aaa]">🎬 导演代表作</span>
      <button @click="$emit('update:modelValue','none')" class="ml-auto px-1.5 py-px bg-transparent border border-[#333] rounded text-[8px] text-[#555] hover:text-[#e74c3c]">重置</button>
      <button v-if="user" @click="$emit('edit')" class="px-1.5 py-px bg-transparent border border-[#333] rounded text-[8px] text-[#555] hover:text-[#667eea]">＋</button>
    </div>
    <div class="grid grid-cols-6 gap-1">
      <div v-for="d in items" :key="d.id" @click="$emit('update:modelValue', d.id)"
        :class="['bg-[#25252d] border rounded-md p-1 cursor-pointer text-center transition-all group relative',
          modelValue===d.id ? 'border-[#667eea] bg-[#667eea15]' : 'border-[#333] hover:border-[#667eea66]',
          d._custom ? 'border-[#667eea44]! bg-[#667eea0a]!' : '']">
        <span v-if="d._custom" class="bg-[#667eea22] text-[#667eea] text-[7px] px-0.5 rounded absolute top-0.5 left-0.5">我的</span>
        <button v-if="d._custom && user" @click.stop="$emit('delete', d._id)" class="absolute top-0.5 right-0.5 w-3.5 h-3.5 bg-[#ef444488] rounded-full text-white text-[7px] hidden group-hover:flex items-center justify-center z-10">✕</button>
        <button v-if="user" @click.stop="$emit('edit', d)" class="absolute top-0.5 right-0.5 w-4 h-4 bg-transparent text-[#666] text-[9px] hidden group-hover:flex items-center justify-center rounded hover:text-[#667eea] hover:bg-[#667eea22] z-10" :style="d._custom?'right-5':''">✏️</button>
        <div class="text-[9px] font-semibold text-[#ccc]">{{ d._custom ? d.name : (d.label||d.name) }}</div>
        <div class="text-[7px] text-[#777]">{{ d._custom ? '自定义' : (d.name||'') }}</div>
      </div>
    </div>
  </div>
</template>
<script setup>
import { computed } from 'vue'
const props = defineProps({ modelValue: String, customPresets: Array, user: Object })
defineEmits(['update:modelValue', 'edit', 'delete'])
const defaults = [
  { id:'none', name:'无', label:'无' },
  { id:'wkw', name:'王家卫', label:'重庆森林' }, { id:'kitano', name:'北野武', label:'花火' },
  { id:'spielberg', name:'斯皮尔伯格', label:'侏罗纪公园' }, { id:'miyazaki', name:'宫崎骏', label:'千与千寻' },
  { id:'zhang', name:'张艺谋', label:'英雄' }, { id:'nolan', name:'诺兰', label:'盗梦空间' },
  { id:'anderson', name:'韦斯·安德森', label:'布达佩斯大饭店' }, { id:'tarantino', name:'昆汀', label:'低俗小说' },
  { id:'kubrick', name:'库布里克', label:'2001太空漫游' }, { id:'burton', name:'蒂姆·伯顿', label:'圣诞夜惊魂' },
  { id:'kurosawa', name:'黑泽明', label:'七武士' }
]
const items = computed(() => [...defaults, ...(props.customPresets||[])])
</script>
