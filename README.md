# 三视图生成器 (Sanshitu)

AI 人物三视图生成工具，前端 SPA + PHP 后端。

## 技术栈
- 前端: HTML/CSS/JS (单文件 SPA)
- 后端: PHP 8.3
- 图片生成: grsai API (gpt-image-2)
- 翻译: DeepSeek API
- 认证: Logto OIDC
- Web 服务器: Nginx

## 认证说明
- 登录走 Logto OIDC → `public/callback.html` 处理回调 → token 存入 localStorage
- `server/api/user/*.php` 目前使用旧 JWT 认证（`legacy/lib/auth.php`）
- 需要迁移：校验 Logto access_token 替代旧 JWT
- 迁移方案：在 api/lib/ 新增 `logto_auth.php`，解析 Authorization header 中的 Bearer token，向 auth.aiwuuw.com/oidc/me 验证
