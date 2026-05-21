# 三视图生成器 部署文档

## 项目结构
```
├── public/
│   ├── sanshitu.html    # 前端 SPA
│   └── callback.html    # Logto OIDC 回调
├── server/
│   ├── generate.php      # 图片生成代理
│   ├── translate.php     # 翻译代理
│   ├── save_image.php    # 图片本地保存
│   ├── log.php           # 日志
│   ├── zip.php           # 批量打包
│   ├── dl.php            # 下载代理
│   ├── api/
│   │   ├── templates.php # 模板数据 API
│   │   ├── templates.json
│   │   ├── user/
│   │   │   ├── config.php    # [待迁移] 用户配置
│   │   │   └── custom.php    # [待迁移] 自定义预设
│   │   └── lib/
│   │       ├── db.php        # 数据库连接
│   │       └── crypto.php    # 加密工具
│   └── legacy/               # 已废弃，参考用
│       ├── auth/login.php
│       ├── auth/register.php
│       ├── lib/auth.php      # 旧 JWT 认证
│       └── lib/jwt.php
├── .env.example
└── DEPLOY.md
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

# /sanshitu → sanshitu.html (内部 rewrite, URL 不变)
location = /sanshitu { rewrite ^ /sanshitu.html last; }

# /callback → callback.html
location = /callback { try_files /callback.html =404; }

# PHP 处理
location ~ \.php$ { include enable-php-83.conf; }
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
- generate.php 的 API Key 应从环境变量读取（已完成）
- translate.php 的 API Key 应从环境变量读取（已完成）
