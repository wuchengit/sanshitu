<template>
  <div class="max-w-[1400px] mx-auto p-4">
    <!-- Header -->
    <header class="mb-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-xl font-bold flex items-center gap-2">
            🎬 <span class="bg-gradient-to-r from-[#667eea] to-[#764ba2] text-transparent bg-clip-text">三视图生成器</span>
            <span class="bg-[#667eea22] text-[#667eea] border border-[#667eea44] px-1.5 rounded-xl text-[9px] font-semibold">SD-API</span>
          </h1>
          <p class="text-[#888] text-[11px] mt-0.5">高度人物面部一致性 · 专业三视图 · 适合集成 SD-API 平台</p>
        </div>
        <button v-if="!user" @click="login"
          class="px-4 py-1.5 bg-gradient-to-r from-[#667eea] to-[#764ba2] rounded-md text-white text-xs font-semibold hover:opacity-90">
          🔑 登录
        </button>
        <div v-else class="flex items-center gap-2 text-xs text-[#aaa]">
          <span>{{ user.username }}</span>
          <button @click="logout" class="text-[#888] px-2 py-1 bg-[#25252d] border border-[#333] rounded text-[10px] hover:text-[#e74c3c]">退出</button>
        </div>
      </div>
    </header>

    <!-- Main Grid -->
    <div class="grid lg:grid-cols-[2fr_3fr] gap-4">
      <!-- Left Column -->
      <div class="space-y-2.5">
        <ApiConfig v-model="apiConfig" />
        <PromptInput v-model="prompt" @gen="generate" :loading="generating" />
      </div>
      <!-- Right Column -->
      <div class="space-y-2.5">
        <LayoutSelector v-model="selLayout" />
        <StyleSelector v-model="selStyle" />
        <ArtSelector v-model="selArt" />
        <DirSelector v-model="selDir" />
        <CharacterAttr v-model="charAttrs" />
        <ReferenceUpload v-model="refImages" />
        <HistoryPanel :items="history" :loading="generating" :loadId="loadId" @select="openViewer" />
      </div>
    </div>

    <!-- Image Viewer Overlay -->
    <ImageViewer v-if="viewerImg" :img="viewerImg" @close="viewerImg=null" />

    <!-- Custom Preset Modal -->
    <CustomModal v-if="showCustom" :category="customCat" :preset="customPreset" :isNew="customIsNew"
      @save="onCustomSave" @close="showCustom=false" />

    <!-- Toast -->
    <Toast :msg="toastMsg" :type="toastType" />
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

// Auth
const user = ref(null)
const LOGTO_APP_ID = 'sanshitu-spa-app'
const LOGTO_URL = 'https://auth.aiwuuw.com'

function login() {
  location.href = 'https://www.aiwuuw.com/callback'
}

function logout() {
  localStorage.removeItem('sanshitu_token')
  localStorage.removeItem('sanshitu_user')
  user.value = null
  customPresets.value = []
}

onMounted(() => {
  const token = localStorage.getItem('sanshitu_token')
  const u = localStorage.getItem('sanshitu_user')
  if (token && u) user.value = JSON.parse(u)
})

// State
const apiConfig = reactive({ key: '', model: 'gpt-image-2', ratio: '9:16', resolution: '1K' })
const prompt = ref('')
const selLayout = ref('classic')
const selStyle = ref('none')
const selArt = ref('none')
const selDir = ref('none')
const charAttrs = reactive({ gender: '', race: '', age: '', height: '', job: '', era: '' })
const refImages = ref([])
const generating = ref(false)
const loadId = ref('')
const history = ref([])

// Custom preset
const showCustom = ref(false)
const customCat = ref('')
const customPreset = ref(null)
const customIsNew = ref(true)

// Viewer
const viewerImg = ref(null)

// Toast
const toastMsg = ref('')
const toastType = ref('ok')
let toastTimer = null
function toast(msg, type='ok') {
  toastMsg.value = msg
  toastType.value = type
  clearTimeout(toastTimer)
  toastTimer = setTimeout(() => toastMsg.value = '', 2500)
}

async function generate() {
  if (!prompt.value) return
  const id = Date.now().toString(36)
  loadId.value = id
  generating.value = true
  history.value.unshift({ loadId: id, loading: true, url: '', thumb: '', model: apiConfig.model, ratio: apiConfig.ratio, info: '' })

  const body = {
    model: apiConfig.model,
    prompt: prompt.value,
    aspectRatio: apiConfig.ratio,
    replyType: 'json'
  }
  if (apiConfig.key) body.key = apiConfig.key
  if (apiConfig.model === 'gpt-image-2-vip' && apiConfig.resolution !== '1K') body.resolution = apiConfig.resolution

  try {
    const r = await fetch('/generate.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body)
    })
    const d = await r.json()
    const h = history.value.find(x => x.loadId === id)
    if (h) {
      h.loading = false
      if (d.url) {
        h.url = d.url
        h.thumb = d.url
        h.info = `${apiConfig.model} | ${apiConfig.ratio}`
        // Save image
        fetch('/save_image.php', { method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ url: d.url })
        }).then(r => r.json()).then(d2 => {
          if (d2.local_url) h.url = d2.local_url
        })
      } else {
        h.error = d.error || '生成失败'
      }
    }
    toast(d.url ? '生成成功' : '生成失败', d.url ? 'ok' : 'err')
  } catch(e) {
    const h = history.value.find(x => x.loadId === id)
    if (h) { h.loading = false; h.error = e.message }
    toast('网络错误', 'err')
  } finally {
    generating.value = false
    loadId.value = ''
  }
}

function openViewer(item) {
  viewerImg.value = item
}

function onCustomSave(data) {
  showCustom.value = false
  loadCustomPresets()
  toast('保存成功')
}

const customPresets = ref([])
async function loadCustomPresets() {
  if (!user.value) return
  try {
    const token = localStorage.getItem('sanshitu_token')
    const r = await fetch('/api/user/custom.php', {
      headers: { 'Authorization': 'Bearer ' + token }
    })
    if (r.ok) {
      const d = await r.json()
      customPresets.value = d.presets || []
    }
  } catch(e) { console.warn('load custom presets:', e) }
}

onMounted(() => {
  const token = localStorage.getItem('sanshitu_token')
  const u = localStorage.getItem('sanshitu_user')
  if (token && u) {
    user.value = JSON.parse(u)
    loadCustomPresets()
  }
})
</script>
