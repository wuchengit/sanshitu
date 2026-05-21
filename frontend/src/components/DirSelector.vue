<template>
  <div class="bg-[#1a1a20] border border-[#2a2a32] rounded-lg p-3">
    <div class="flex items-center gap-2 mb-2">
      <span class="text-[11px] font-semibold text-[#aaa]">🎬 导演代表作</span>
      <button @click="$emit('update:modelValue','none')" class="ml-auto px-1.5 py-px bg-transparent border border-[#333] rounded text-[8px] text-[#555] hover:text-[#e74c3c]">重置</button>
    </div>
    <div class="grid grid-cols-6 gap-1">
      <div v-for="d in dirs" :key="d.id" @click="$emit('update:modelValue', d.id)"
        :class="['bg-[#25252d] border rounded-md p-1 cursor-pointer text-center transition-all',
          modelValue===d.id ? 'border-[#667eea] bg-[#667eea15]' : 'border-[#333] hover:border-[#667eea66]']">
        <div class="text-[9px] font-semibold text-[#ccc]">{{ d.film }}</div>
        <div class="text-[7px] text-[#777]">{{ d.name }}</div>
      </div>
    </div>
  </div>
</template>
<script setup>
defineProps({ modelValue: String })
defineEmits(['update:modelValue'])
const dirs = [
  { id:'none', name:'无', film:'无' },
  { id:'wkw', name:'王家卫', film:'重庆森林' },
  { id:'kitano', name:'北野武', film:'花火' },
  { id:'spielberg', name:'斯皮尔伯格', film:'侏罗纪公园' },
  { id:'miyazaki', name:'宫崎骏', film:'千与千寻' },
  { id:'zhang', name:'张艺谋', film:'英雄' },
  { id:'nolan', name:'诺兰', film:'盗梦空间' },
  { id:'anderson', name:'韦斯·安德森', film:'布达佩斯大饭店' },
  { id:'tarantino', name:'昆汀', film:'低俗小说' },
  { id:'kubrick', name:'库布里克', film:'2001太空漫游' },
  { id:'burton', name:'蒂姆·伯顿', film:'圣诞夜惊魂' },
  { id:'kurosawa', name:'黑泽明', film:'七武士' }
]
</script>
