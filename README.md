# 📘 Log Explorer Web 系統

**版本：2.0**  
**最後更新：2025/11/24**

---

## 專案簡介

Log Explorer 是一個現代化的 Web 平台，專為工廠測試生產線打造，提供操作人員、PE、TE 快速瀏覽、搜尋、比對及下載產線測試 log。

### 核心特色

- 🎨 **現代化 UI/UX**：採用 Slate & Indigo 設計系統，支援 Light/Dark Mode
- 📊 **即時統計圖表**：Dashboard 每 5 秒自動更新，即時掌握 log 數量趨勢
- 🔍 **強大搜尋功能**：支援日期範圍、客戶、機種多維度篩選
- 📁 **智慧分組**：自動依客戶/機種/日期三階層結構分類
- 📝 **Markdown 支援**：自動識別並渲染 `.md` 檔案，支援程式碼高亮
- 🔄 **檔案比對**：內建 Diff 功能，可同時比對兩個 log 檔案
- 📦 **批次下載**：支援多檔案選取並打包下載為 ZIP
- ⚡ **高效能**：大型檔案分段讀取，避免前端卡頓

---

## 系統架構

### 前端技術棧

- **UI Framework**: Bootstrap 5
- **JavaScript**: jQuery
- **表格**: DataTables
- **圖表**: ECharts 5
- **語法高亮**: Prism.js
- **Markdown**: Marked.js
- **檔案比對**: Diff2html
- **日期選擇**: Flatpickr

### 後端技術棧

- **語言**: PHP 8+
- **資料庫**: MySQL 8.0
- **Web Server**: Nginx/Apache
- **檔案儲存**: 本地檔案系統

---

## 功能特色

### 1. 使用者驗證與權限

- ✅ Session 基礎登入系統
- ✅ 密碼 bcrypt 加密儲存
- ✅ 全 API 端點驗證保護
- ✅ 登入頁面支援 Dark Mode

### 2. Log 管理與瀏覽

#### 智慧分組
- 自動解析路徑結構：`/客戶/機種/日期/檔案`
- 可折疊式側邊欄分組顯示
- 支援分組搜尋與快速篩選

#### 進階搜尋
- 日期範圍選擇（Flatpickr）
- 客戶/機種下拉選單
- 即時搜尋結果更新

#### 檔案操作
- **預覽**：Modal 彈窗，支援語法高亮
- **下載**：單檔下載或批次打包
- **比對**：選取兩個檔案進行 Diff 比對
- **複製**：一鍵複製檔案內容

### 3. Log 預覽功能

#### 基礎功能
- 分段讀取（Chunk mode），支援大型檔案
- 行號顯示/隱藏切換
- 全文搜尋與高亮
- 上一檔/下一檔快速切換
- 鍵盤快捷鍵支援（方向鍵、ESC）

#### Markdown 模式
- 自動識別 `.md` / `.markdown` 檔案
- 支援標題、列表、表格、引用、程式碼區塊
- 程式碼區塊獨立 Copy 按鈕
- Raw/Markdown 視圖切換

#### 檔案資訊
- 檔案名稱、大小、修改時間
- 右上角顯示，不干擾內容閱讀

### 4. 批次操作

#### 多選功能
- Checkbox 選取檔案
- Shift+Click 範圍選取
- 全選/取消全選

#### 右鍵選單
- Preview（預覽）
- Download（下載）
- Batch Download（批次下載，顯示選取數量）
- Compare Files（比對兩個檔案）
- 動態顯示/隱藏選項

#### 批次下載
- 自動打包為 ZIP
- 保留原始目錄結構
- 顯示選取檔案數量 Badge

### 5. 檔案比對

- 並排顯示兩個檔案差異
- 語法高亮支援
- 新增/刪除/修改行標示
- 行號對齊顯示

### 6. Dashboard 統計

#### 即時圖表
- 每 5 秒自動更新數據
- 折線圖顯示 log 數量趨勢
- 平滑曲線與區域填色
- 響應式設計，自動調整大小

#### Dark Mode 適配
- 圖表顏色自動切換
- 座標軸、網格線、文字顏色適配
- Tooltip 深色主題

### 7. Dark Mode

#### 全域支援
- 一鍵切換 Light/Dark Mode
- localStorage 記憶偏好設定
- 所有頁面同步主題

#### 覆蓋範圍
- ✅ 登入頁面
- ✅ 主頁面（表格、側邊欄、搜尋）
- ✅ Dashboard（圖表）
- ✅ Modal（預覽、比對）
- ✅ Context Menu
- ✅ 表單與輸入框

#### 設計細節
- Slate & Indigo 配色系統
- 平滑過渡動畫
- 高對比度確保可讀性

---

## API 端點

### 認證
```
POST /api/auth/login.php      - 使用者登入
POST /api/auth/logout.php     - 使用者登出
GET  /api/auth/session.php    - 檢查 Session 狀態
```

### Log 管理
```
GET  /api/groups.php           - 取得分組資訊（客戶/機種/日期）
GET  /api/logs.php             - 查詢 log 列表
GET  /api/search.php           - 進階搜尋（時間範圍、客戶、機種）
```

### 檔案操作
```
GET  /api/log_preview.php      - 預覽 log 內容（支援 chunk）
GET  /api/download.php         - 下載單一檔案
POST /api/batch_download.php  - 批次下載（ZIP）
POST /api/compare.php          - 比對兩個檔案
```

### 統計
```
GET  /api/stats.php            - Dashboard 統計數據
```

---

## 資料表結構

```sql
CREATE TABLE file_tracking_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pathname VARCHAR(1024) NOT NULL,
    upload_dt INT NOT NULL,
    file_dt INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX(pathname),
    INDEX(upload_dt),
    INDEX(file_dt)
);
```

---

## 安裝與部署

### 環境需求

- PHP 8.0+
- MySQL 8.0+
- Nginx/Apache
- Composer（可選）

### 安裝步驟

1. **Clone 專案**
   ```bash
   git clone <repository-url>
   cd logKeeper
   ```

2. **設定資料庫**
   ```bash
   mysql -u root -p < database.sql
   ```

3. **設定 config.php**
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'log_explorer');
   define('DB_USER', 'your_username');
   define('DB_PASS', 'your_password');
   ```

4. **設定 Web Server**
   - 將專案目錄設為 document root
   - 確保 PHP 可讀取 log 檔案目錄

5. **初始化資料**
   ```bash
   php populate_database.php
   ```

---

## 使用說明

### 登入
- 預設帳號密碼請參考資料庫設定
- 支援 Dark Mode 切換

### 瀏覽 Log
1. 使用左側分組選單快速定位
2. 或使用頂部搜尋功能篩選
3. 點擊檔案名稱預覽內容
4. 使用右鍵選單進行批次操作

### 批次下載
1. 勾選多個檔案
2. 右鍵點擊任一選取的檔案
3. 選擇「Batch Download」
4. 自動下載 ZIP 壓縮檔

### 檔案比對
1. 勾選兩個檔案
2. 右鍵選擇「Compare Files」
3. 查看並排差異比對

### Dashboard
- 訪問 `dashboard.php`
- 圖表每 5 秒自動更新
- 支援 Dark Mode

---

## 安全性

### 已實現
- ✅ Session 驗證保護所有 API
- ✅ 路徑遍歷攻擊防護（`../` 檢查）
- ✅ 密碼 bcrypt 加密
- ✅ XSS 防護（輸出轉義）

### 建議加強
- [ ] CSRF Token
- [ ] Rate Limiting
- [ ] HTTPS 強制
- [ ] LDAP/AD 整合

---

## 未來規劃

### 短期
- [ ] 檔案上傳功能
- [ ] Log 標籤系統
- [ ] 進階篩選（檔案大小、類型）

### 中期
- [ ] WebSocket 即時通知
- [ ] Streaming Tail（`tail -f` 模式）
- [ ] 使用者權限分級

### 長期
- [ ] ML 自動分類
- [ ] 異常 Log Alert（Email/Line/Teams）
- [ ] 多語言支援

---

## 技術亮點

### UI/UX
- 現代化設計系統（Slate & Indigo）
- 完整 Dark Mode 支援
- 響應式設計
- 平滑動畫與過渡效果

### 效能優化
- 大檔案分段讀取
- DataTables 分頁與虛擬滾動
- 圖表自動更新節流
- CSS 變數統一管理

### 開發體驗
- 模組化 API 設計
- 一致的錯誤處理
- 清晰的程式碼結構
- 完整的註解文件

---

## 授權

本專案為內部使用系統，版權所有。

---

## 聯絡資訊

如有問題或建議，請聯繫開發團隊。
