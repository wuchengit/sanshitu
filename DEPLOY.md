# 三视图生成器 部署文档

## 项目结构
```
├── sanshitu.html         # 前端 SPA（主入口）
├── sanshitu-app/         # Vue 3 前端（新版，开发中）
│   ├── index.html
│   └── assets/
├── callback.html         # Logto OIDC 回调页
├── generate.php          # 图片生成代理
├── translate.php         # 翻译代理
├── save_image.php        # 图片本地保存（用户数据 → /var/www/sanshitu-data/）
├── log.php               # 运行日志
├── zip.php               # 批量打包 zip
├── dl.php                # 下载代理
├── api/
│   ├── templates.php     # 模板数据 API
│   ├── templates.json    # 模板数据
│   ├── auth/
│   │   ├── login.php
│   │   └── register.php
│   ├── user/
│   │   ├── config.php    # 用户配置（JWT 认证）
│   │   └── custom.php    # 自定义预设（JWT 认证）
│   └── lib/
│       ├── auth.php      # JWT 认证
│       ├── jwt.php       # JWT 工具
│       ├── db.php        # 数据库连接
│       ├── crypto.php    # AES-256-CBC 加密
│       └── logto_auth.php # Logto token 校验（新增，待迁移）
├── images/
│   ├── previews/         # 项目资源图（风格/结构预览，JPG 缩略图）
│   │   ├── styles/       # 风格图标
│   │   └── thumb/        # 结构缩略图
│   ├── placeholder.svg   # 占位图
│   └── test_orig.png     # 测试图
├── docs.html             # 项目文档（提交记录/Bug修复/产品文档）
├── version.txt           # 版本标识（用于缓存刷新检测）
├── .env.example          # 环境变量模板
├── .gitignore
├── README.md
└── DEPLOY.md

# 用户数据（独立存储，不在项目目录中）
/var/www/sanshitu-data/
└── images/
    ├── YYYYMMDD_*.png     # 用户生成的 AI 原图
    └── thumbs/YYYYMMDD_*.jpg  # 缩略图
```

## 部署方式
- Web 服务器: Nginx 1.28
- PHP: 8.3 (php-fpm)
- 数据库: MySQL (本地 localhost)
- 认证: Logto OIDC (auth.aiwuuw.com)
- SSL: Let's Encrypt (certbot 自动续期)

## Nginx 配置要点
```
root /usr/local/lighthouse/softwares/wordpress;

# Vue 前端
location /sanshitu-app/ {
    alias /usr/local/lighthouse/softwares/wordpress/sanshitu-app/;
    try_files $uri $uri/ /sanshitu-app/index.html;
}

# 用户数据图片（独立存储）
location /data/images/ {
    alias /var/www/sanshitu-data/images/;
    add_header Cache-Control "public, max-age=86400";
}

# 三视图主页面
location = /sanshitu { try_files /sanshitu.html =404; }

# OIDC 回调
location = /callback { try_files /callback.html =404; }

# PHP 处理
location ~ \.php$ { include enable-php-83.conf; }

# 兜底 → WordPress
location / { try_files $uri $uri/ /index.php?$args; }
```

## 环境变量
部署前需设置以下环境变量（通过 PHP-FPM pool 配置或 .env）：
| 变量 | 用途 |
|------|------|
| GRS_API_KEY | 图片生成 API Key |
| DEEPSEEK_API_KEY | 翻译 API Key |
| DB_HOST | 数据库地址 |
| DB_NAME | 数据库名 |
| DB_USER | 数据库用户 |
| DB_PASS | 数据库密码 |

## 发布步骤
1. `git pull`
2. 如有环境变量变更，重启 php-fpm
3. 无需重启 Nginx（静态文件）

## 回滚
`git checkout <commit>` → 刷新浏览器

## 域名与 SSL
- 主站: www.aiwuuw.com
- 认证: auth.aiwuuw.com (Logto)
- SSL 证书: /etc/letsencrypt/live/www.aiwuuw.com/
- Nginx 配置: /www/server/panel/vhost/nginx/www.aiwuuw.com.conf

## 已知问题 / TODO
- api/user/config.php 和 custom.php 仍使用旧 JWT 认证，需迁移到 Logto token 校验
- generate.php 和 translate.php 的 API Key 应从环境变量读取（已完成）
