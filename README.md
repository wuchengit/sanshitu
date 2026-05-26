# 🎬 SD-API 三视图生成器

一个基于 Stable Diffusion API 的 Web 应用，用于快速生成角色三视图（正面 / 侧面 / 背面）图像，适合游戏角色设计、插画概念稿、AI 短片制作等场景。

## 功能特性

- **三视图一键生成** — 基于 Stable Diffusion API，输入提示词即可生成角色正面、左侧、右侧、背面等多视图图像
- **多种人物资产结构** — 内置多种三视图布局模板（标准三视图、九宫格、半身/全身等）
- **参考图上传** — 支持上传角色参考图（正面/侧面/背面），为生成提供视觉引导
- **自定义预设系统** — 可自由创建和管理风格&提示词预设，一键切换，支持多维度参数微调
- **Logto OIDC 登录** — 集成 Logto 身份认证，支持个人配置持久化
- **风格卡片选择** — 内置多种艺术风格（写实、二次元、原画、像素风等），快速切换
- **提示词模板系统** — 提示词模板统一由后端管理，前端源码不暴露敏感模板
- **深色主题 UI** — 低视觉疲劳的暗色界面，专注于创作流程

## 快速开始

### 前提条件

- 一个可用的 Stable Diffusion WebUI API 端点（支持 `txt2img` 接口）
- Logto OIDC 认证服务（可选，用于登录功能）
- 任意静态文件服务器（用于部署前端）

### 部署

1. 克隆仓库：

```bash
git clone https://github.com/your-username/sanshitu.git
cd sanshitu
```

2. 配置 API 端点：

打开 `sanshitu.html`，找到 API 相关配置，将 `sdApiUrl` 修改为你自己的 Stable Diffusion 地址。

3. 配置 OIDC（可选）：

如需启用登录功能，在 `callback.html` 和 `sanshitu.html` 中更新 Logto 相关配置（`client_id`、`redirect_uri`、`endpoint` 等）。

4. 部署前端文件：

将 `sanshitu.html`、`callback.html` 以及 `templates.json` 部署到你的 Web 服务器即可。

### 使用

1. 访问部署后的页面，选择角色布局模板
2. 上传参考图（可选）
3. 配置提示词及风格参数
4. 点击生成，等待三视图产出

## 技术栈

| 技术 | 用途 |
|------|------|
| **Vanilla HTML / CSS / JS** | 前端 SPA，零依赖 |
| **Stable Diffusion API** | 图像生成后端（txt2img） |
| **Logto** | OIDC 身份认证 |
| **CSS Grid / Flexbox** | 响应式布局 |

## 配置说明

### SD API 配置

在 `sanshitu.html` 中搜索 `sdApiUrl` 配置项：

```javascript
const SD_API_URL = 'http://your-sd-server:7860/sdapi/v1/txt2img';
```

### 提示词模板

提示词模板存储在后端 `templates.json` 中，应用启动时自动加载。模板结构示例：

```json
{
  "base": "masterpiece, best quality, {character_description}",
  "front": "front view, symmetrical face, looking at viewer",
  "side": "side view, profile, {side_detail}",
  "back": "back view, {back_detail}"
}
```

### Logto OIDC

在 `callback.html` 中配置：

```javascript
const LOGTO_CONFIG = {
  endpoint: 'https://your-logto-instance.com',
  clientId: 'your-spa-app-client-id',
  redirectUri: 'https://your-domain.com/callback'
};
```

## 许可证

本项目仅供学习和个人使用。如需商业使用，请自行评估相关依赖的许可条款。
