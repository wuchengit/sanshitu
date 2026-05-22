# 测试环境搭建方案

## 1. 创建测试数据库

```sql
CREATE DATABASE sanshitu_test DEFAULT CHARACTER SET utf8mb4;
```

建表（跟生产一致）：
```sql
-- 用户表（旧 JWT 认证用）
CREATE TABLE sanshitu_test.users LIKE wordpress.users;

-- 用户配置
CREATE TABLE sanshitu_test.user_configs LIKE wordpress.user_configs;

-- 自定义预设（如果存在）
-- CREATE TABLE sanshitu_test.user_presets LIKE wordpress.user_presets;
```

或者直接 dump 生产表结构到测试库（不含数据）：
```bash
mysqldump -u wordpress -p --no-data wordpress users user_configs | mysql -u wordpress -p sanshitu_test
```

## 2. API 层改造

### api/lib/db.php
增加 X-Test-Mode header 检测：
```php
function db() {
  static $pdo = null;
  if ($pdo === null) {
    $isTest = !empty(getallheaders()['X-Test-Mode']) || 
              (isset($_SERVER['HTTP_X_TEST_MODE']) && $_SERVER['HTTP_X_TEST_MODE'] === '1');
    $dbname = $isTest ? 'sanshitu_test' : DB_NAME;
    $pdo = new PDO(/* ... 使用 $dbname ... */);
  }
  return $pdo;
}
```

### 测试客户端发请求带 header
```javascript
fetch('/api/templates.php', {
  headers: { 'X-Test-Mode': '1' }
});
```

## 3. 测试账号
在测试库中创建测试用户（通过 Logto 注册一个测试账号），或：
- 在 `sanshitu_test.users` 中手动插入一条测试用户记录
- Logto 也注册一个同名测试账号

## 4. 回滚方案
- 测试数据仅在 `sanshitu_test` 库中
- 切换回生产只需去掉 X-Test-Mode header
- 随时可清空测试库
