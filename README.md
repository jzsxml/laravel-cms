# Laravel CMS

基于 **Laravel 13** 的企业内容站参考实现，交互与数据约定对齐 **FastAdmin** 风格（`fa_*` 表、RBAC、`php artisan crud` 生成后台模块）。包含后台内核、企业门户（P0–P4）、会员 Web 中心、REST 风格 API 与本地插件目录，**不含远程插件市场**。

默认栈：PHP ≥ 8.3，SQLite（可换 MySQL），Blade 门户主题，Bootstrap 5 后台。

---

## 目录

- [功能一览](#功能一览)
- [快速开始](#快速开始)
- [环境变量（`.env`）](#环境变量env)
- [架构要点](#架构要点)
- [门户路由](#门户路由)
- [企业站分期（P0–P4）](#企业站分期能力-p0p4)
- [后台 CRUD 生成](#后台-crud-生成)
- [会员中心](#会员中心)
- [本地插件](#本地插件)
- [测试](#测试)
- [生产与安全建议](#生产与安全建议)
- [开发说明](#开发说明)

---

## 功能一览

### 管理后台

| 模块 | 能力 |
|------|------|
| **权限管理** | 管理员、角色组、菜单规则（RBAC）；非超管按规则节点鉴权 |
| **常规管理** | 系统配置、附件（含删除与物理文件）、管理员日志、栏目树、会员管理、个人资料、更新缓存、API 文档、短信测试 |
| **内容管理** | 文章、单页、标签、专题、区块、评论、站点配置、订单、内容统计、模型设计 / 模型数据 |
| **Ajax** | 上传（含分片）、SelectPage 联想；上传需单独勾选权限 `ajax/upload` 等 |
| **本地插件** | 安装 / 启用 / 禁用 / 卸载 `addons/` 下插件；在线命令白名单 |
| **工具** | `php artisan crud` 从数据表生成 CRUD；`php artisan cms:cache-refresh` 刷新后台缓存 |

### 企业门户

- 首页、栏目列表、文章详情（栏目 nickname + slug）、单页、专题、标签、搜索、会员公开页
- 多主题：`default` / `classic` / `cms`（在 **站点配置** 中设 `portal_theme`）
- P2+：评论、点赞、首页区块 `App\Support\CmsBlock::html('home_banner')`
- P3+：付费试读、余额购买、附件下载（本地路径校验；外链默认不跳转）
- P4：自定义模型前台列表 `/m/{name}`

### 会员中心（Web）

- 路径前缀可配置（默认 `/user`）：注册、登录、投稿（待审）、个人资料、钱包（模拟充值，生产应关闭）

### API（`routes/api.php`）

- 登录 / 注册、Token 鉴权（Header `X-Cms-Token`，见 `config/cms.php`）
- 会员资料、改密；无短信时 API 重置密码默认关闭

### 安全与合规（内置）

- 上传扩展名 / MIME 白名单（`UploadGuard`）
- 富文本 **HTMLPurifier** 入库 + 前台输出净化
- 门户 **CSP** 与安全响应头（可配置或关闭）
- 后台 / 会员 / API **登录限流**（`throttle:cms-login`）
- Logo 等 URL：`SafeUrl`；外链下载需显式开启

---

## 快速开始

```bash
cd laravel-cms
composer install
cp .env.example .env   # 若尚无 .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```

复制 `.env` 后请至少设置 `APP_KEY`；生产环境设置 `APP_URL` 为真实域名。

| 入口 | 默认地址 | 说明 |
|------|----------|------|
| 企业门户 | `http://127.0.0.1:8000/` | 前台首页 |
| 管理后台 | `http://127.0.0.1:8000/admin` | 由 `CMS_ADMIN_PATH` 决定，**建议生产改为非常见路径** |
| 会员中心 | `http://127.0.0.1:8000/user` | 由 `CMS_MEMBER_PATH` 决定 |

修改 `CMS_ADMIN_PATH` / `CMS_MEMBER_PATH` 后执行：

```bash
php artisan config:clear   # 开发
# 生产：先改 .env 再 php artisan config:cache
```

### 默认账号

| 角色 | 账号 | 密码 | 说明 |
|------|------|------|------|
| 管理员 | `admin` | `123456` | 超级管理员组 `rules=*` |
| 会员（演示） | `member` | `123456` | 由 `P4AdminSeeder` 写入 |

**生产环境务必修改默认密码。** 非超管需在 **权限管理 → 角色组** 勾选各阶段菜单（`EnterprisePortalP1Seeder` 等写入的节点）。

### 仅补企业站种子（已有库时）

```bash
php artisan migrate --force
php artisan db:seed --class=EnterprisePortalSeeder --force
php artisan db:seed --class=EnterprisePortalP1Seeder --force
php artisan db:seed --class=EnterprisePortalP2Seeder --force
php artisan db:seed --class=EnterprisePortalP3Seeder --force
php artisan db:seed --class=EnterprisePortalP4Seeder --force
```

仿站演示数据（可选）：

```bash
php artisan db:seed --class=CmsApixmlDemoSeeder --force
```

---

## 环境变量（`.env`）

完整模板见仓库根目录 [`.env.example`](.env.example)。业务项会读入 [`config/cms.php`](config/cms.php)。未列出的 Laravel 标准项（`APP_*`、`DB_*`、`SESSION_*`、`MAIL_*`、`AWS_*` 等）遵循 [Laravel 文档](https://laravel.com/docs/configuration)。

### Laravel 基础（常用）

| 变量 | 说明 |
|------|------|
| `APP_NAME` | 应用名称 |
| `APP_ENV` | `local` / `production`；生产用 `production` |
| `APP_KEY` | `php artisan key:generate` 生成，**必填** |
| `APP_DEBUG` | 生产必须为 `false` |
| `APP_URL` | 站点根 URL（生成链接、Cookie 域） |
| `DB_*` | 默认示例为 `sqlite`；生产建议 MySQL，并配置连接池与备份 |
| `SESSION_DRIVER` | 示例为 `database`；也可用 `redis` |
| `CACHE_STORE` | 示例为 `database`；高并发可用 `redis` |

### CMS — 路径与会话

| 变量 | 默认 | 说明 |
|------|------|------|
| `CMS_ADMIN_PATH` | `admin` | 后台 URL 前缀，**勿加前导 `/`**，勿与前台路由冲突 |
| `CMS_MEMBER_PATH` | `user` | 会员中心 URL 前缀 |
| `CMS_TABLE_PREFIX` | `fa_` | 业务表前缀，与迁移一致 |
| `CMS_SESSION_KEY` | `cms_admin` | 后台 Session 键名 |
| `CMS_MEMBER_SESSION_KEY` | `cms_member` | 会员 Session 键名 |
| `CMS_ADMIN_LOCALE` | `zh_CN` | 后台界面语言（`zh_CN` / `en`） |

### CMS — 上传与对象存储

| 变量 | 默认 | 说明 |
|------|------|------|
| `CMS_UPLOAD_DISK` | `public` | `config/filesystems.php` 中的 disk 名 |
| `OSS_*` | — | 阿里云 OSS（S3 兼容）；设 `CMS_UPLOAD_DISK=oss` 时使用 |
| `AWS_*` | — | 使用 `s3` disk 时配置 |

上传策略（扩展名黑名单、MIME 等）在 `config/cms.php` → `upload`，一般无需 `.env` 逐项配置。

### CMS — 支付与钱包（P3）

| 变量 | 默认 | 说明 |
|------|------|------|
| `CMS_PAYMENT_DRIVER` | `balance` | `balance`（余额扣款）/ `mock`（演示） |
| `CMS_ALLOW_MOCK_RECHARGE` | `false` | 是否允许会员中心「模拟充值」 |
| `CMS_MOCK_RECHARGE_AMOUNT` | `100` | 单次模拟充值金额 |
| `CMS_MOCK_RECHARGE_MAX` | `1000` | 模拟充值累计上限 |

### CMS — API 与短信

| 变量 | 默认 | 说明 |
|------|------|------|
| `CMS_API_ALLOW_PASSWORD_RESET` | `false` | 无短信验证码时是否开放 API「用户名+手机号」重置密码 |
| `CMS_SMS_DRIVER` | `log` | `log`（写日志）/ 扩展 `aliyun` |
| `CMS_ALIYUN_SMS_KEY` | — | 阿里云短信 AccessKey |
| `CMS_ALIYUN_SMS_SECRET` | — | 阿里云短信 Secret |
| `CMS_ALIYUN_SMS_SIGN` | — | 短信签名 |

API Token 头名称在代码中为 `X-Cms-Token`（`config/cms.php` → `api.token_header`）。

### CMS — 门户行为

| 变量 | 默认 | 说明 |
|------|------|------|
| `CMS_ALLOW_EXTERNAL_DOWNLOAD` | `false` | 付费内容 `download_url` 为 **外链** 时是否 302 跳转（钓鱼风险，生产保持 `false`） |

站点名称、Logo、备案、**门户主题**等在后台 **内容管理 → 站点配置**（写入 `fa_config`），无需单独 `.env`。

### CMS — 缓存

| 变量 | 默认 | 说明 |
|------|------|------|
| `CMS_ADMIN_CACHE_TTL` | `3600` | 后台配置/菜单/分类等缓存秒数 |

### CMS — 安全

| 变量 | 默认 | 说明 |
|------|------|------|
| `CMS_HTML_PURIFY` | `true` | 富文本 HTMLPurifier（保存 + 前台展示） |
| `CMS_CSP_ENABLED` | `true` | 门户是否发送 Content-Security-Policy |
| `CMS_CSP_POLICY` | 见 `config/cms.php` | 完整 CSP 字符串；留空则使用内置默认策略 |
| `CMS_LOGIN_MAX_ATTEMPTS` | `5` | 同一 IP+账号在窗口期内最大登录/注册尝试次数 |
| `CMS_LOGIN_DECAY_MINUTES` | `1` | 限流时间窗口（分钟） |

限流作用于：后台登录、会员登录/注册、API `login` / `register` / `resetpassword`。

### 配置示例片段（生产）

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://www.example.com

CMS_ADMIN_PATH=manage_a8f3k2
CMS_MEMBER_PATH=member

CMS_ALLOW_MOCK_RECHARGE=false
CMS_PAYMENT_DRIVER=balance
CMS_API_ALLOW_PASSWORD_RESET=false
CMS_ALLOW_EXTERNAL_DOWNLOAD=false

CMS_HTML_PURIFY=true
CMS_CSP_ENABLED=true
CMS_LOGIN_MAX_ATTEMPTS=5
CMS_LOGIN_DECAY_MINUTES=1
```

---

## 架构要点

- **后台**：`routes/admin.php`，中间件 `admin.locale` → `admin.auth` + `admin.permission`；规则名如 `cms/archives/index`。
- **门户**：`routes/web.php`，`SharePortalTheme` + `PortalSecurityHeaders`。
- **会员**：`routes/member.php`，中间件 `member.auth`。
- **API**：`routes/api.php`，中间件 `api.member`（需 Token）。
- **CRUD 生成**：`php artisan crud -t {表名} -u 1`；产物登记在 `bootstrap/cms/generated_modules.php`。

核心目录：

| 路径 | 说明 |
|------|------|
| `app/Http/Controllers/Admin` | 后台与 CMS 模块 |
| `app/Http/Controllers/Portal` | 前台 |
| `app/Services/Cms` | 内容访问、付费逻辑等 |
| `app/Crud` | CRUD 命令与生成器 |
| `database/seeders/EnterprisePortal*.php` | 企业站分期种子 |
| `resources/views/portal/themes/` | 门户主题 Blade |
| `addons/` | 本地插件（如 `demo`、`cms-portal`） |

---

## 门户路由

| 路径 | 说明 |
|------|------|
| `/` | 首页（推荐/置顶/标签云） |
| `/c/{nickname}` | 栏目列表（主栏 + 副栏 `category_ids`） |
| `/c/{nickname}/{slug}` | 文章详情（**栏目必须与文章主栏一致**，否则 404） |
| `/p/{slug}` | 单页 |
| `/s/{slug}` | 专题（可选 `?tag=`） |
| `/tag/{slug}` | 标签列表 |
| `/search?q=` | 搜索 |
| `/u/{username}` | 会员公开主页 |
| `/m/{name}` | 自定义模型前台列表（P4） |

文章 URL 使用拼音 **slug**（`overtrue/pinyin`）。

---

## 企业站分期能力（P0–P4）

### P0 — 内容 MVP

- 文章 `fa_archives`、栏目 `fa_category`（`type=article`）
- 门户首页 / 栏目 / 详情、回收站、批量状态
- 种子：`EnterprisePortalSeeder`

### P1 — 运营增强

- 单页 `fa_page`、标签、搜索、副栏目、顶栏单页（`flag` 含 `nav`）
- 种子：`EnterprisePortalP1Seeder`

### P2 — 互动与投稿

- 评论、会员投稿（待审）、专题、区块 `CmsBlock::html('home_banner')`、批量专题/标签
- 种子：`EnterprisePortalP2Seeder`

### P3 — 付费与下载

- 文章/单页：付费、试读比例、附件 URL；会员余额、订单；点赞
- 会员 **钱包** 模拟充值（受 `CMS_ALLOW_MOCK_RECHARGE` 控制）
- 演示文：`付费演示文章（P3）`（种子）
- 种子：`EnterprisePortalP3Seeder`

### P4 — 产品化

- **内容统计**：`/{CMS_ADMIN_PATH}/cms/statistics`
- **门户主题**：`portal_theme` → `default` / `classic` / **`cms`**
- **模型设计 + 模型数据** + 前台 `/m/{name}`
- 本地插件包 `addons/cms-portal`
- 种子：`EnterprisePortalP4Seeder`

### P0–P4 验收清单（简版）

| 阶段 | 建议验收项 |
|------|------------|
| **P0** | 后台发稿；前台栏目/详情；回收站；导航来自栏目树 |
| **P1** | `/p/{slug}`；标签与搜索；副栏目列表出现文章 |
| **P2** | 会员投稿→后台审核；评论；专题 `/s/...`；首页 `home_banner` |
| **P3** | 充值→购买付费文→全文+下载；后台订单列表 |
| **P4** | 统计页有数据；切换 `classic` 主题；`/m/product` 展示模型数据 |

---

## 后台 CRUD 生成

```bash
# 示例：为 fa_test 生成后台并写菜单
php artisan crud -t test -u 1 --title=测试

# 覆盖重生成
php artisan crud -t test -u 1 -f

# 卸载生成物
php artisan crud -t test -d 1
```

受保护表见 `config/cms.php` → `crud.protected_tables`（含 `archives`、`order` 等，不可被 crud 命令直接覆盖）。

---

## 会员中心

- 登录 / 注册：`/{CMS_MEMBER_PATH}/login`、`.../register`
- 投稿：`.../contribute`（状态 `pending`，后台 **文章管理** 审核）
- 钱包：`.../wallet`（P3，`CMS_ALLOW_MOCK_RECHARGE=true` 时可用模拟充值）

---

## 本地插件

- 管理：**常规管理 → 本地插件**（`addons/` 目录）
- 无在线市场；安装/启用写入 `bootstrap/cms/enabled_addons.php`
- 启用后需在 **booting** 阶段注册 Provider（项目已处理）；禁用后建议 **更新缓存**

---

## 常用依赖

| 包 | 用途 |
|----|------|
| `overtrue/pinyin` | 文章/单页 slug |
| `openspout/openspout` | 后台 Excel 导出 |
| `league/flysystem-aws-s3-v3` | OSS / S3 上传 |
| `ezyang/htmlpurifier` | 富文本净化 |

---

## 测试

测试使用内存 SQLite（见 `phpunit.xml`），首次运行会自动迁移表结构。

```bash
php artisan test
# 或
composer test
```

| 目录 | 覆盖模块 |
|------|----------|
| `tests/Unit/Support` | `ArchiveSlug`、`SiteSettings`、`CmsBlock`、`HtmlSanitizer`、`PortalNav`、`PortalTheme`、`SafeUrl`、`UploadGuard` |
| `tests/Unit/Services` | `AdminCache`、`Dictionary`、`AdminAuth` |
| `tests/Unit/Addon` | `AddonManager` 启用/禁用 |
| `tests/Feature/Portal` | 门户首页 |
| `tests/Feature/Admin` | 登录、站点配置、更新缓存 |
| `tests/Feature/Console` | `cms:cache-refresh` |

公共夹具：`tests/Support/CmsTestSeeder.php`、`tests/Concerns/ActsAsAdmin.php`。

---

## 生产与安全建议

1. `APP_DEBUG=false`，强密码，修改默认 `admin` / `member` 演示账号。
2. 将 `CMS_ADMIN_PATH` 改为非常见路径；可选移除门户页脚「管理后台」链接以免暴露入口。
3. `CMS_ALLOW_MOCK_RECHARGE=false`，真实支付接入前勿依赖 `mock` 驱动。
4. `CMS_API_ALLOW_PASSWORD_RESET=false` 直至接入短信验证码。
5. `CMS_ALLOW_EXTERNAL_DOWNLOAD=false` 除非明确需要外链下载并已评估风险。
6. 保持 `CMS_HTML_PURIFY=true`；按需收紧 `CMS_CSP_POLICY`。
7. 上传依赖 RBAC：角色需勾选 `ajax/upload`、`ajax/uploadchunk`、`ajax/uploadmerge`。
8. HTTPS、定期备份数据库与 `storage/app/public`。

---

## 开发说明

- 样式：后台 `public/css/admin.css`；门户 `public/css/portal.css`（`classic` 另加载 `portal-classic.css`，`cms` 加载 `portal-cms.css`）
- **后台数据缓存**：`App\Services\Admin\AdminCache`；TTL 见 `CMS_ADMIN_CACHE_TTL`
- **更新缓存**：常规管理 → **更新缓存**，或 `php artisan cms:cache-refresh`
- 区块缓存：`App\Support\CmsBlock::html($name)`；全量刷新时会清理区块缓存
- 站点与门户文案：**内容管理 → 站点配置**；系统级键值仍在 **常规管理 → 系统配置**

---

## 发布到 GitHub

仓库已配置 `.gitignore`（排除 `.env`、`vendor/`、本地 SQLite、编译视图缓存等）与 GitHub Actions [`.github/workflows/tests.yml`](.github/workflows/tests.yml)（PHP 8.3 / 8.4 跑 `composer test`）。

```bash
cd laravel-cms
git init -b main
git add -A
git status   # 确认未包含 .env、database/*.sqlite、vendor/
git commit -m "Initial commit: Laravel CMS enterprise portal"

# 在 GitHub 创建空仓库后：
git remote add origin https://github.com/<你的用户名>/laravel-cms.git
git push -u origin main
```

克隆后的初始化：

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # 若使用 SQLite
php artisan migrate --seed
php artisan storage:link
```

提交前请确认：**勿提交** `.env`、真实密钥、生产数据库；演示账号密码仅用于本地/种子数据。

---

## License

本项目应用代码采用 [MIT](LICENSE)。Laravel 框架部分遵循其各自许可证。