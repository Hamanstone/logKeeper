# 📘 《Log Explorer Web 系統》開發計畫書

**版本：1.0**
**撰寫者：ChatGPT**
**提供給：石頭（產品測試主管）**
**最後更新：2025/11**

---

# 1. 專案簡介

本專案旨在為工廠測試生產線打造一個統一的「Log Explorer Web 平台」，提供操作人員、PE、TE 快速瀏覽、搜尋及下載產線測試 log。
系統將整合：

* FTP 上傳 log → 事件寫入 Redis Stream → Worker 入庫 MySQL
* Web UI 即時讀取 MySQL 紀錄
* 可安全地讓使用者查詢 log，不直接暴露伺服器檔案系統

---

# 2. 系統架構

## 2.1 前端（Front-end）

* Bootstrap 5
* jQuery
* DataTables
* ECharts（統計圖表）
* Prism.js（Syntax Highlight）
* AJAX API（RESTful 風格）

## 2.2 後端（Backend）

* PHP 8（支援 PDO、Slim Framework optional）
* MySQL（log metadata）
* Nginx/Apache （提供 Web 端）

## 2.3 檔案儲存

* Log 實體檔案儲存在：

  ```
  /mnt/raid0/ftp/users/<user>/...
  ```
* Web API 透過 `readfile()` 提供下載（經過授權驗證）

---

# 3. 功能需求規格（Functional Requirements）

## 3.1 使用者登入驗證

### 功能描述

* 使用者必須登入才能查看或下載 log
* 權限控制：

  * **Viewer**：能查詢、預覽、下載
  * **Admin**：能管理帳號、設定

### 技術細節

* 實作簡單安全登入（PHP session）
* 密碼使用 bcrypt 儲存（password_hash）
* API 全部需驗證 Session

### API

```
POST /api/login.php
POST /api/logout.php
GET  /api/session.php
```

---

## 3.2 Log 列表與分組顯示

### 功能描述

* 可依 **客戶 / 機種（SKU） / 日期** 進行快速分組瀏覽
* 支援三階層結構式 UI

### 分組邏輯

透過 pathname 自動解析：

```
/mnt/raid0/ftp/.../PEGA/MU310/20251114/...
```

即可自動辨識：

| 屬性 | 範例         |
| -- | ---------- |
| 客戶 | PEGA       |
| 機種 | MU310      |
| 日期 | 2025-11-14 |

### API

```
GET /api/groups.php       → 取得所有客戶/機種/日期分類
GET /api/logs.php         → 查詢特定客戶/機種/日期
```

---

## 3.3 Log 預覽（支援大型檔案 Chunk 讀取 + Syntax Highlight）

### 功能描述

* 不一次載入整個 log（避免大檔造成前端卡死）
* 用「分段讀取 Chunk」方式載入，例如一次讀 4 KB
* 支援 Prism.js Syntax Highlight（例如 shell、json、ini）

### 前端 UI 行為

* 使用 Modal 彈出 log 預覽視窗
* 可選擇「下一頁 / 上一頁」讀取後續 chunk
* 可完整搜尋 log 字串（前端 JS 自帶）
* 工具列集中 Copy / Download / 行號切換 / 搜尋輸入框 / 檔案資訊顯示
* Modal 內建 Light / Dark Mode 切換按鈕

### API

```
GET /api/log_preview.php?path=<file>&offset=0&length=4096
```

回傳：

```json
{
  "content": "log內容",
  "next_offset": 4096,
  "has_more": true
}
```

---

## 3.4 Log 下載功能

### 功能描述

* 使用者可在表格中按「下載」按鈕
* 檔案由後端安全檢查後輸出

### API

```
GET /api/download.php?path=<encoded_path>
```

---

## 3.5 ECharts Log 數量統計 Dashboard

### 功能描述

* 可圖形化顯示一天內 log 數量，按時段聚合（例如每 5 分鐘一點）
* 可選擇：

  * 全部客戶
  * 特定客戶
  * 特定機種
  * 特定日期

### API

```
GET /api/stats.php?customer=PEGA&sku=MU310&date=2025-11-14
```

回傳：

```json
{
  "labels": ["09:00", "09:05", ...],
  "values": [32, 28, ...]
}
```

---

## 3.6 MySQL 時間範圍查詢

### 功能描述

使用者可輸入：

* 起始時間
* 結束時間
* 客戶
* 機種

系統即從 MySQL 撈出符合條件的 log。

### API

```
GET /api/search.php?start=2025-11-14T09:00&end=2025-11-14T11:00&customer=PEGA&sku=MU310
```

---

## 3.7 Markdown 說明檔預覽

### 功能描述

* 副檔名 `.md` / `.markdown` 之檔案會自動以 Markdown 模式開啟，可隨時切換回 Raw 文字。
* 使用 Marked.js 解析，支援標題、段落、列表、引用、表格與程式碼區塊等元素。
* 每個程式碼區塊皆會顯示獨立 Copy 按鈕，方便複製 JSON / shell 指令片段。
* Markdown 視圖同樣提供 Light / Dark Mode，並對表格、程式碼設定專屬版面與顏色。

### 技術細節

* Markdown 與 Raw 模式共用同一個 Modal，因此仍可使用上一檔 / 下一檔、搜尋輸入框等功能。
* Copy 功能採用 Clipboard API，並保留 `execCommand` 後備方案確保舊版瀏覽器亦可使用。

---

# 4. 系統設計細節（System Design Detail）

## 4.1 資料表結構（file_tracking_logs）

```sql
CREATE TABLE file_tracking_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pathname VARCHAR(1024) NOT NULL,
    upload_dt INT NOT NULL,
    file_dt INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(pathname),
    INDEX(upload_dt)
);
```

---

## 4.2 後端 API 結構

```
/api/
│
├── auth/
│   ├── login.php
│   ├── logout.php
│   └── session.php
│
├── logs.php
├── groups.php
├── search.php
├── log_preview.php
├── download.php
└── stats.php
```

---

# 5. 前端介面（UI/UX）

## 5.1 主頁

* 上方：搜尋框、日期選擇器、客戶/機種 Dropdown
* 左邊：層級式選單（客戶 → 機種 → 日期）
* 中間：DataTables（log 列表）
* 右上：登入/登出

## 5.2 log 預覽頁

* Modal 彈窗
* Prism.js highlight
* Chunk mode
* Copy / Download / 行號切換 / 搜尋輸入框
* Light / Dark Mode 切換
* Markdown 視圖：自動解析 `.md` 檔，程式碼區塊附 Copy 鈕，表格具備專屬樣式

## 5.3 Dashboard（ECharts）

* 折線圖（log count vs time）
* 客戶/機種切換
* 日期切換

---

# 6. 開發時程規劃（Roadmap）

| 週數    | 工作項目                       |
| ----- | -------------------------- |
| 第 1 週 | 架構設計、資料表確認、API 初版          |
| 第 2 週 | 登入系統、基本 log 列表 API         |
| 第 3 週 | 分組 API、DataTables UI、下載功能  |
| 第 4 週 | Log Chunk 預覽、Prism.js      |
| 第 5 週 | ECharts Dashboard、後端統計 API |
| 第 6 週 | MySQL 篩選（日期區間、客戶、機種）       |
| 第 7 週 | 整合測試、效能優化（index、快取）        |
| 第 8 週 | 上線 & 系統驗收                  |

---

# 7. 安全性與存取控制

* 全 API 必須檢查 session
* download.php 必須檢查檔案路徑不可跳脫 (`../` 防止 traversal)
* 建議新增 Nginx 限制：

  * 只有登入後才能 access `/api`
* 若未來需要：可整合 LDAP/AD/Keycloak

---

# 8. 未來擴充（Future Enhancements）

* Streaming Tail（像 `tail -f`）
* WebSocket 即時通知有新 log
* 自動分類（以 ML 辨識 log 類型）
* 異常 log Alert（email / Line Notify / Teams）

---

# 9. 總結

這份開發計畫書完成後，你會得到：

* 一個現代化 Web Log Explorer
* 能登入、查詢、分類、預覽、下載
* 支援大型 log 分段讀取
* 有圖形化 Dashboard（ECharts）
* 能以 MySQL 時間範圍進行查詢
* UI/UX 一致、現代、可維護、可擴充

整個系統能讓生產線工程師快速定位 log、分類 log、分析 log，大幅提升效率與 traceability。
