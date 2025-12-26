# 🚀 Hướng Dẫn Khởi Chạy WebSec - CHI TIẾT

## ✅ Các bước cần làm:

### 1. XAMPP Control Panel (Đã mở)
Trong cửa sổ XAMPP Control Panel:
- ✅ MySQL đã chạy (có dấu tích xanh)
- ❌ Apache chưa chạy → **NHẤN NÚT "Start" bên cạnh Apache**

### 2. Import Database qua phpMyAdmin

**Cách 1: Qua trình duyệt (Khuyến nghị)**
1. Mở: http://localhost/phpmyadmin
2. Click tab "**SQL**" ở menu trên
3. Xóa nội dung trong textbox (nếu có)
4. Copy toàn bộ nội dung file `store.sql` và paste vào
5. Click nút "**Go**" để chạy

**Cách 2: Qua Import**
1. Mở: http://localhost/phpmyadmin  
2. Click tab "**Import**"
3. Click "**Choose File**" và chọn: `d:\lap trinh kiem com\Web\WebSec\store.sql`
4. Click "**Import**" ở cuối trang

### 3. Copy Project vào htdocs
```cmd
xcopy "d:\lap trinh kiem com\Web\WebSec" "C:\xampp\htdocs\WebSec\" /E /I /Y
```

### 4. Truy cập Website
- Frontend: http://localhost/WebSec
- Admin: http://localhost/WebSec/admin_login.php
  - Username: `admin`
  - Password: `admin123`

## 🔧 Nếu gặp lỗi

### Lỗi: "Connection failed"
→ Kiểm tra MySQL đang chạy trong XAMPP

### Lỗi: "Database not found"
→ Import lại file store.sql qua phpMyAdmin

### Lỗi: "Can't send email"
→ Cấu hình file `.env` với thông tin email thật

---
**Ghi chú:** Project hiện đang ở `d:\lap trinh kiem com\Web\WebSec\`
Cần copy vào `C:\xampp\htdocs\WebSec\` để Apache có thể chạy.
