# LuomorNovel

一个专为小说网站设计的 WordPress 区块主题（FSE），支持 AI 辅助创作、章节管理、在线阅读、收藏评论等功能。

## 功能特性

### 📚 小说管理
- **创建/编辑/删除** 小说作品
- **发布管理** - 支持连载、完结、暂停状态
- **封面图片** - 小说封面上传与显示
- **字数统计** - 自动统计章节和小说总字数

### 📖 章节管理
- **创建/编辑/删除** 章节内容
- **章节排序** - 拖拽排序或手动调整
- **上下章导航** - 阅读时快速切换章节
- **章节目录** - 小说详情页显示完整目录

### 🤖 AI 辅助创作
- **多平台支持** - OpenAI (GPT-4o)、Claude (Sonnet)、Gemini
- **章节生成** - 根据提示词自动生成章节内容
- **大纲生成** - AI 辅助生成小说大纲
- **角色生成** - 自动生成角色描述
- **速率限制** - 防止 API 滥用

### 👤 用户功能
- **收藏/书签** - 收藏喜欢的小说
- **阅读进度** - 自动记录上次阅读位置
- **评论系统** - 每章节支持读者评论
- **阅读工具** - 字号调节、暗色模式

### 🔍 搜索与分类
- **全文搜索** - 搜索小说名称、内容、章节
- **实时建议** - 搜索框自动补全
- **分类筛选** - 按类型（Genre）浏览
- **标签系统** - 灵活的小说标签

## 技术架构

| 层级 | 技术 |
|---|---|
| 主题类型 | WordPress Block Theme (FSE) |
| PHP 版本 | 8.0+ |
| WordPress | 6.4+ |
| REST API | 自定义端点 namespace `luomor/v1` |
| 前端 | Vanilla JS + WP API Fetch |
| 国际化 | 中文默认 + .pot 翻译文件支持 |

## 文件结构

```
LuomorNovel/
├── style.css                    # 主题头部信息
├── theme.json                   # 全局样式（颜色、字体、布局）
├── functions.php                # 引导文件
├── index.php                    # 回退模板
├── includes/
│   ├── core.php                 # CPT 和分类法注册
│   ├── meta.php                 # 自定义字段
│   ├── rest-api.php             # REST API 端点
│   ├── ai-service.php           # AI 服务（OpenAI/Claude/Gemini）
│   ├── bookmarks.php            # 收藏功能
│   ├── reading-progress.php     # 阅读进度
│   ├── template-functions.php   # 模板辅助函数
│   └── settings.php             # 主题设置页
├── templates/                   # FSE 区块模板
│   ├── home.html                # 首页
│   ├── single-novel.html        # 小说详情
│   ├── single-chapter.html      # 章节阅读
│   ├── archive-novel.html       # 小说列表
│   ├── taxonomy-novel_genre.html # 分类归档
│   ├── search.html              # 搜索结果
│   ├── 404.html                 # 404 页
│   └── index.html               # 回退
├── parts/                       # 模板部件
│   ├── header.html              # 页头
│   ├── footer.html              # 页脚
│   └── chapter-nav.html         # 章节导航
├── assets/
│   ├── css/
│   │   ├── reading.css          # 阅读样式
│   │   ├── editor.css           # 编辑器样式
│   │   └── admin.css            # 管理后台样式
│   └── js/
│       ├── main.js              # 主脚本（API 封装、暗色模式）
│       ├── bookmark.js          # 收藏功能
│       ├── reading-progress.js  # 阅读进度追踪
│       ├── search.js            # 搜索建议
│       ├── ai-writer.js         # AI 写作助手
│       └── chapter-sort.js      # 章节排序
└── patterns/
    └── novel-card.php           # 小说卡片模式
```

## 安装

1. 将主题文件上传至 `wp-content/themes/LuomorNovel/`
2. 在 WordPress 后台 **外观 > 主题** 中激活 LuomorNovel
3. 前往 **外观 > LuomorNovel 设置** 配置 AI API Key

## 自定义文章类型

| 类型 | Slug | 说明 |
|---|---|---|
| 小说 | `novel` | 小说容器（封面、简介、目录） |
| 章节 | `chapter` | 小说章节内容 |

## 分类法

| 分类法 | 层级 | 说明 |
|---|---|---|
| `novel_genre` | 是 | 小说类型（玄幻、都市、科幻等） |
| `novel_tag` | 否 | 小说标签（自由添加） |
| `novel_status` | 是 | 状态（连载中/已完结/已暂停） |

## REST API 端点

### 小说
- `GET /luomor/v1/novels` - 列表
- `GET /luomor/v1/novels/:id` - 详情（含章节）
- `POST /luomor/v1/novels` - 创建
- `PUT /luomor/v1/novels/:id` - 更新
- `DELETE /luomor/v1/novels/:id` - 删除

### 章节
- `GET /luomor/v1/novels/:id/chapters` - 章节列表
- `POST /luomor/v1/novels/:id/chapters` - 创建章节
- `POST /luomor/v1/novels/:id/chapters/reorder` - 重排章节
- `GET /luomor/v1/chapters/:id` - 章节详情
- `PUT /luomor/v1/chapters/:id` - 更新章节
- `DELETE /luomor/v1/chapters/:id` - 删除章节

### 收藏
- `POST /luomor/v1/bookmarks/:id` - 切换收藏
- `DELETE /luomor/v1/bookmarks/:id` - 取消收藏
- `GET /luomor/v1/bookmarks` - 收藏列表
- `GET /luomor/v1/bookmarks/:id/check` - 检查是否收藏

### 阅读进度
- `POST /luomor/v1/reading-progress` - 保存进度
- `GET /luomor/v1/reading-progress/:id` - 获取进度

### 搜索
- `GET /luomor/v1/search?q=关键词` - 搜索

### AI
- `POST /luomor/v1/ai/generate` - 生成内容
- `GET /luomor/v1/ai/providers` - 获取可用提供商
- `GET /luomor/v1/ai/test/:provider` - 测试连接

## 开发

### 生成翻译文件
```bash
wp i18n make-pot . languages/luomor-novel.pot
```

## 许可证

GNU General Public License v2 or later
