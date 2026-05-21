<template>
  <div class="max-w-[1400px] mx-auto p-4">
    <header class="mb-4 flex items-center justify-between">
      <div>
        <h1 class="text-xl font-bold flex items-center gap-2">
          🎬 <span class="bg-gradient-to-r from-[#667eea] to-[#764ba2] text-transparent bg-clip-text">三视图生成器</span>
          <span class="bg-[#667eea22] text-[#667eea] border border-[#667eea44] px-1.5 rounded-xl text-[9px] font-semibold">SD-API</span>
        </h1>
        <p class="text-[#888] text-[11px] mt-0.5">高度人物面部一致性 · 专业三视图 · 适合集成 SD-API 平台</p>
      </div>
      <button v-if="!user" @click="login" class="px-4 py-1.5 bg-gradient-to-r from-[#667eea] to-[#764ba2] rounded-md text-white text-xs font-semibold hover:opacity-90">🔑 登录</button>
      <div v-else class="flex items-center gap-2 text-xs text-[#aaa]">
        <span>{{ user.username || user.sub }}</span>
        <button @click="logout" class="text-[#888] px-2 py-1 bg-[#25252d] border border-[#333] rounded text-[10px] hover:text-[#e74c3c]">退出</button>
      </div>
    </header>
    <div class="grid lg:grid-cols-[2fr_3fr] gap-4">
      <div class="space-y-2.5">
        <ApiConfig v-model:key="apiKey" v-model:model="model" v-model:ratio="ratio" v-model:resolution="resolution" />
        <PromptInput v-model:prompt="promptText" :loading="generating" :origPrompt="origPrompt" @gen="generate" @translate="translate" @restore="restoreOrig" />
      </div>
      <div class="space-y-2.5">
        <LayoutSelector v-model="selLayout" :customPresets="layoutPresets" :user="user" @edit="editCustom('layout',$event)" @delete="deleteCustom($event)" />
        <StyleSelector v-model="selStyle" :customPresets="stylePresets" :user="user" @edit="editCustom('style',$event)" @delete="deleteCustom($event)" />
        <ArtSelector v-model="selArt" :customPresets="artPresets" :user="user" @edit="editCustom('art',$event)" @delete="deleteCustom($event)" />
        <DirSelector v-model="selDir" :customPresets="dirPresets" :user="user" @edit="editCustom('dir',$event)" @delete="deleteCustom($event)" />
        <CharacterAttr v-model:gender="charAttrs.gender" v-model:race="charAttrs.race" v-model:age="charAttrs.age" v-model:height="charAttrs.height" v-model:job="charAttrs.job" v-model:era="charAttrs.era" />
        <ReferenceUpload v-model="refImages" />
        <HistoryPanel v-model:selMode="selMode" v-model:sels="sels" :items="history" :loading="generating" :loadId="loadId" @select="openViewer" @batchDelete="batchDelete" @batchDownload="batchDownload" />
      </div>
    </div>
    <ImageViewer v-if="viewerImg" :img="viewerImg" @close="viewerImg=null" />
    <CustomModal v-if="showCustom" :category="customCat" :preset="customPreset" :isNew="customIsNew" @save="onCustomSave" @close="showCustom=false" />
    <Toast :msg="toastMsg" :type="toastType" />
    <VersionBar :version="APP_VERSION" />
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import ApiConfig from './components/ApiConfig.vue'
import PromptInput from './components/PromptInput.vue'
import LayoutSelector from './components/LayoutSelector.vue'
import StyleSelector from './components/StyleSelector.vue'
import ArtSelector from './components/ArtSelector.vue'
import DirSelector from './components/DirSelector.vue'
import CharacterAttr from './components/CharacterAttr.vue'
import ReferenceUpload from './components/ReferenceUpload.vue'
import HistoryPanel from './components/HistoryPanel.vue'
import ImageViewer from './components/ImageViewer.vue'
import CustomModal from './components/CustomModal.vue'
import Toast from './components/Toast.vue'
import VersionBar from './components/VersionBar.vue'

const APP_VERSION = '2.0.0'

// Auth
const user = ref(null)
const authToken = ref('')

function login() { location.href = 'https://www.aiwuuw.com/callback' }
function logout() {
  localStorage.removeItem('sanshitu_token'); localStorage.removeItem('sanshitu_user')
  user.value = null; authToken.value = ''
  customPresets.value = []; toast('已退出登录')
}

onMounted(async () => {
  const t = localStorage.getItem('sanshitu_token')
  const u = localStorage.getItem('sanshitu_user')
  if (t && u) { user.value = JSON.parse(u); authToken.value = t; loadCustomPresets() }
  await loadTemplates()
  renderPrompt()
})

// Templates
const layouts = ref([])
const styles = ref([])
const arts = ref([])
const dirs = ref([])
const layoutPresets = computed(() => layouts.value.concat(customPresets.value.filter(p => p.category==='layout').map(toCard)))
const stylePresets = computed(() => styles.value.concat(customPresets.value.filter(p => p.category==='style').map(toCard)))
const artPresets = computed(() => arts.value.concat(customPresets.value.filter(p => p.category==='art').map(toCard)))
const dirPresets = computed(() => dirs.value.concat(customPresets.value.filter(p => p.category==='dir').map(toCard)))

function toCard(p) { return { id:'c_'+p.id+'_'+p.category, name:p.name, label:p.label, prompt:p.prompt, desc:'自定义', icon:'✨', _custom:true, _id:p.id } }

async function loadTemplates() {
  try {
    const r = await fetch('/api/templates.php')
    if (r.ok) {
      const d = await r.json()
      layouts.value = d.layouts || []
      styles.value = d.styles || []
      arts.value = d.art_styles || []
      dirs.value = d.dir_styles || []
    }
  } catch(e) { console.warn('load templates:', e) }
}

// State
const apiKey = ref(localStorage.getItem('sanshitu_apikey') || '')
const model = ref('gpt-image-2')
const ratio = ref('9:16')
const resolution = ref('1K')
const promptText = ref('')
const origPrompt = ref('')
const selLayout = ref('classic')
const selStyle = ref('none')
const selArt = ref('none')
const selDir = ref('none')
const charAttrs = reactive({ gender:'', race:'', age:'', height:'', job:'', era:'' })
const refImages = ref([])
const generating = ref(false)
const loadId = ref('')
const history = ref([])
const selMode = ref(false)
const sels = ref(new Set())
const viewerImg = ref(null)
const toastMsg = ref('')
const toastType = ref('ok')
let toastTimer = null

function toast(msg, type='ok') { toastMsg.value=msg; toastType.value=type; clearTimeout(toastTimer); toastTimer=setTimeout(()=>toastMsg.value='',2500) }

// Prompt composition
function buildLayoutPrompt(layoutId) {
  const l = layouts.value.find(x => x.id === layoutId)
  return l ? l.prompt : ''
}
function buildStylePrompt(styleId) {
  const s = [...styles.value, ...customPresets.value.filter(p=>p.category==='style').map(toCard)].find(x=>x.id===styleId)
  return s ? s.prompt : ''
}
function buildArtPrompt(artId) {
  const a = [...arts.value, ...customPresets.value.filter(p=>p.category==='art').map(toCard)].find(x=>x.id===artId)
  return a ? a.prompt : ''
}
function buildDirPrompt(dirId) {
  const d = [...dirs.value, ...customPresets.value.filter(p=>p.category==='dir').map(toCard)].find(x=>x.id===dirId)
  return d ? d.prompt : ''
}

function renderPrompt() {
  let parts = [buildLayoutPrompt(selLayout.value)]
  if (selStyle.value !== 'none') parts.push(buildStylePrompt(selStyle.value))
  if (selArt.value !== 'none') parts.push(buildArtPrompt(selArt.value))
  if (selDir.value !== 'none') parts.push(buildDirPrompt(selDir.value))
  // Character attributes
  const attrs = []
  if (charAttrs.gender) attrs.push(charAttrs.gender)
  if (charAttrs.race) attrs.push(charAttrs.race)
  if (charAttrs.age) attrs.push(charAttrs.age)
  if (charAttrs.height) attrs.push(charAttrs.height + ' stature')
  if (charAttrs.job) attrs.push(charAttrs.job)
  if (charAttrs.era) attrs.push(charAttrs.era + ' era setting')
  if (attrs.length) parts.push(attrs.join(', '))
  origPrompt.value = parts.filter(Boolean).join('. ')
  promptText.value = origPrompt.value
}

// Watch all selectors
import { watch } from 'vue'
watch([selLayout, selStyle, selArt, selDir], renderPrompt)
watch([()=>charAttrs.gender, ()=>charAttrs.race, ()=>charAttrs.age, ()=>charAttrs.height, ()=>charAttrs.job, ()=>charAttrs.era], renderPrompt)

async function generate() {
  if (!origPrompt.value) return
  const id = Date.now().toString(36)
  loadId.value = id; generating.value = true
  history.value.unshift({ loadId:id, loading:true, url:'', thumb:'', model:model.value, ratio:ratio.value, info:'' })
  const body = { model:model.value, prompt:origPrompt.value, aspectRatio:ratio.value, replyType:'json' }
  if (apiKey.value) body.key = apiKey.value
  if (model.value==='gpt-image-2-vip' && resolution.value!=='1K') body.resolution = resolution.value
  try {
    const r = await fetch('/generate.php', { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body) })
    const d = await r.json()
    const h = history.value.find(x=>x.loadId===id)
    if (h) { h.loading=false; if (d.url) { h.url=d.url; h.thumb=d.url; h.info=`${model.value} | ${ratio.value}` } else { h.error=d.error||'生成失败' } }
    if (d.url) { toast('生成成功','ok'); fetch('/save_image.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({url:d.url})}) }
    else toast('生成失败','err')
  } catch(e) { const h=history.value.find(x=>x.loadId===id); if(h){h.loading=false;h.error=e.message}; toast('网络错误','err') }
  finally { generating.value=false; loadId.value='' }
}

// Translation
async function translate(lang) {
  if (!origPrompt.value) return
  try {
    const r = await fetch('/translate.php?_t='+Date.now(), { method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify({text:origPrompt.value, target:lang}) })
    if (r.ok) { const d = await r.json(); promptText.value = d.result || d.translated || origPrompt.value }
  } catch(e) { toast('翻译失败','err') }
}
function restoreOrig() { promptText.value = origPrompt.value }

// Custom presets
const showCustom = ref(false)
const customCat = ref('')
const customPreset = ref(null)
const customIsNew = ref(true)
const customPresets = ref([])
const customEditId = ref(null)
const customOrigName = ref('')

async function loadCustomPresets() {
  if (!authToken.value) return
  try {
    const r = await fetch('/api/user/custom.php', { headers:{'Authorization':'Bearer '+authToken.value} })
    if (r.ok) { const d = await r.json(); customPresets.value = d.presets || [] }
  } catch(e) {}
}

function editCustom(cat, item) {
  if (!user.value) { login(); return }
  customCat.value = cat
  if (item && item._custom) {
    customPreset.value = { name:item.name, label:item.label||'', prompt:item.prompt||'' }
    customIsNew.value = false
    customEditId.value = item._id
    customOrigName.value = item.name
  } else if (item) {
    customPreset.value = { name:item.name, label:item.label||item.desc||'', prompt:item.prompt||item.desc||'' }
    customIsNew.value = true
    customEditId.value = null
    customOrigName.value = item.name
  } else {
    customPreset.value = null
    customIsNew.value = true
    customEditId.value = null
    customOrigName.value = ''
  }
  showCustom.value = true
}

async function onCustomSave(data) {
  if (!authToken.value) return
  const method = (customEditId.value && !data.asCopy) ? 'PUT' : 'POST'
  const body = { category:customCat.value, name:data.name, label:data.label||'', prompt:data.prompt }
  if (customEditId.value && !data.asCopy) body.id = customEditId.value

  // Name auto-increment for new copies
  if ((customIsNew.value || data.asCopy) && data.name === customOrigName.value) {
    let base = data.name.replace(/ \d+$/, '')
    const catPresets = customPresets.value.filter(p => p.category === customCat.value)
    let n = 1
    while (catPresets.some(p => p.name === (base + ' ' + n))) n++
    body.name = base + ' ' + n
  }

  try {
    const r = await fetch('/api/user/custom.php', { method, headers:{'Content-Type':'application/json','Authorization':'Bearer '+authToken.value}, body:JSON.stringify(body) })
    if (r.ok) { showCustom.value = false; await loadCustomPresets(); toast(method==='PUT'?'已更新':'已保存','ok') }
    else { const d = await r.json(); toast(d.error||'保存失败','err') }
  } catch(e) { toast('网络错误','err') }
}

async function deleteCustom(id) {
  if (!authToken.value || !confirm('确定删除？')) return
  try {
    const r = await fetch('/api/user/custom.php', { method:'DELETE', headers:{'Content-Type':'application/json','Authorization':'Bearer '+authToken.value}, body:JSON.stringify({id}) })
    if (r.ok) { await loadCustomPresets(); toast('已删除','ok') }
  } catch(e) { toast('网络错误','err') }
}

function openViewer(item) { viewerImg.value = item }

function batchDelete() {
  if (sels.value.size === 0) return
  history.value = history.value.filter(h => !sels.value.has(h.loadId))
  sels.value = new Set()
  toast('已删除选中')
}

async function batchDownload() {
  const urls = history.value.filter(h => sels.value.has(h.loadId) && h.url).map(h => h.url)
  if (urls.length === 0) return
  if (urls.length === 1) { window.open(urls[0]); return }
  const form = document.createElement('form'); form.method='POST'; form.action='/zip.php'; form.target='_blank'
  const input = document.createElement('input'); input.type='hidden'; input.name='urls'; input.value=JSON.stringify(urls)
  form.appendChild(input); document.body.appendChild(form); form.submit(); document.body.removeChild(form)
}
</script>
