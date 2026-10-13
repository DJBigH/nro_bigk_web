# 🐉 HƯỚNG DẪN CÀI ĐẶT & SỬ DỤNG HỆ THỐNG WEB ADMIN NRO BIGK

Hệ thống Website & Admin Panel quản trị máy chủ **Ngọc Rồng Online (NRO BigK)** được xây dựng trên nền tảng **Laravel 10**, giao diện **Cyber Dark Modern**, tích hợp cơ chế **ĐỒNG BỘ REALTIME TỨC THÌ** vào Game Server qua cổng **14446** (người chơi nhận chỉ số và quà ngay lập tức mà **KHÔNG CẦN OUT GAME**).

---

## 📋 1. YÊU CẦU MÔI TRƯỜNG

* **Hệ điều hành:** Windows 10/11 hoặc Windows Server.
* **XAMPP:** Phiên bản hỗ trợ **PHP 8.1 trở lên** (khuyên dùng PHP 8.2).
* **Dịch vụ cần bật trong XAMPP Control Panel:**
  * ✅ **Apache** (Port 80 / 443)
  * ✅ **MySQL** (Port 3306)
* **Game Server:** Chạy mã nguồn Java `soicon.jar` (Port game: 14445, Port Admin Realtime: 14446).

---

## ⚡ 2. CÁC BƯỚC KHỞI CHẠY 1-CLICK (SIÊU NHANH)

Bạn không cần mở cửa sổ dòng lệnh gõ từng lệnh phức tạp, chỉ cần làm như sau:

1. Mở **XAMPP Control Panel** và nhấn **Start** cho **Apache** và **MySQL**.
2. Tìm đến file sau và **click đúp chuột**:
   ```text
   D:\nro\srcnrobigk03032026\nrobigkv7\BAT_MO_WEB_ADMIN.bat
   ```
   *(hoặc file `C:\xampp\htdocs\webnrobigk\run_web.bat`)*
3. **Trình duyệt sẽ tự động mở lên** tại địa chỉ:
   ```text
   http://127.0.0.1:8000/admin
   ```
   *(Giữ nguyên cửa sổ đen cmd đang chạy để website duy trì hoạt động).*

---

## 🔑 3. TÀI KHOẢN QUẢN TRỊ VIÊN (ADMIN)

* **Tài khoản Admin mặc định:**
  * **Tài khoản:** `1`
  * **Mật khẩu:** `1`
* **Cách cấp quyền Admin cho tài khoản bất kỳ:**
  * **Cách 1 (Trên Web):** Đăng nhập Admin $\rightarrow$ Vào menu `THÀNH VIÊN & ITEM` $\rightarrow$ Tìm tên người chơi $\rightarrow$ Bấm nút **"Cấp Admin"**.
  * **Cách 2 (Trong MySQL phpMyAdmin):**
    ```sql
    UPDATE account SET is_admin = 1 WHERE username = 'ten_tai_khoan';
    ```

---

## ⚙️ 4. CẤU HÌNH HỆ THỐNG (`.env`)

File cấu hình đặt tại: `C:\xampp\htdocs\webnrobigk\.env`

```env
APP_NAME="NRO BigK"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bigk
DB_USERNAME=root
DB_PASSWORD=
```

---

## 🛠️ 5. CÁC TÍNH NĂNG CHÍNH TRONG WEB ADMIN

### 1. 📊 Dashboard & Điều Khiển Realtime
* Xem số người chơi đang online, mức tiêu thụ RAM máy chủ Java.
* **Thanh trượt EXP Server:** Kéo chỉnh tỉ lệ EXP từ x1 đến x50, áp dụng thẳng vào RAM máy chủ ngay lập tức.
* **Thông báo toàn server:** Gửi thông báo chạy chữ đến tất cả người chơi đang online.
* **Bảo trì máy chủ:** Kích hoạt đếm ngược 30 giây bảo trì an toàn.

### 2. 👥 Thành Viên & Soi/Sửa Túi Đồ (Item Inspector)
* Tìm kiếm người chơi theo tài khoản hoặc tên nhân vật.
* **Set Point 50 Tỷ:** Chỉnh trực tiếp HP, KI, Sức đánh lên tới 50 TỶ và Sức mạnh lên tới 200 TỶ mà không sợ tràn số hay mất trắng dữ liệu.
* **Soi túi đồ (Item Inspector):** Xem đồ đang mặc (Body) và đồ trong hành trang (Bag) của nhân vật đang online.
* **Sửa chỉ số trực tiếp:** Đổi số lượng và thay đổi các chỉ số Option (Sức đánh %, HP %, Chí mạng...) thẳng vào RAM nhân vật.

### 3. 👹 Quản Lý & Triệu Hồi Boss Realtime
* Giám sát danh sách toàn bộ Boss đang xuất hiện (Máu hiện tại, Map đang đứng, Khu vực).
* Nút **Triệu Hồi Boss**: Chọn boss tùy ý (Kuku, Broly, Fide, Beerus, Whis, Gohan Beast...) triệu hồi tức thì vào game.
* Nút **Tiêu Diệt Boss**: Xóa boss khỏi bản đồ ngay lập tức.

### 4. 🤖 Quản Trị Bot AI Ảo
* Xem danh sách các Bot đang farm quái.
* Nút thả nhanh **+5 Bot**, **+10 Bot**, **+20 Bot** tự động cày cuốc.
* Nút **Dọn Dẹp / Dừng Toàn Bộ Bot**.
* Công tắc Bật/Tắt **Bot Auto Chat** thế giới.

### 5. 🔨 Cấu Hình Tỉ Lệ Đập Đồ / Pha Lê Hóa
* Điều chỉnh phần trăm (%) đập đồ từ **1 Sao đến 8 Sao** tại NPC Bà Hạt Mít.
* Có sẵn các cấu hình mẫu Preset: Chuẩn NRO, Dễ thở x2, Cày cuốc vui vẻ, Test 100%.
* Áp dụng thẳng vào RAM máy chủ Java tức thời.

### 6. 🎯 Nhiệm Vụ Bò Mộng & Thành Tựu
* Quản lý các mốc thành tựu trong bảng `archive_template`.
* Sửa yêu cầu nhiệm vụ và phần thưởng Hồng Ngọc (Ruby) nhận tại NPC Bò Mộng.

### 7. 🎁 Quản Lý Rơi Đồ Quái (Mob Drops)
* Quản lý bảng `mob_drop`.
* Thêm vật phẩm rơi (Thỏi vàng, Hồng ngọc, Bí kíp...), đặt tỉ lệ rơi % và số lượng min-max theo từng Map hoặc toàn server.
* Công tắc Bật/Tắt nhanh rơi đồ.

### 8. 🗺️ Visual Map Editor 2D (HTML5 Canvas)
* Chọn bản đồ trong hơn 170 map game NRO.
* Vẽ không gian 2D với mặt đất và tọa độ pixel chuẩn.
* **Kéo thả chuột (Drag & Drop)** để dời tọa độ NPC, Quái Mob và Cổng dịch chuyển.
* Bấm **Lưu Tọa Độ Vào Database** là cập nhật ngay bảng `map_template`.

### 9. 📦 Thư Viện Item Grid (2.103 Items)
* Tra cứu toàn bộ 2.103 vật phẩm trong game dạng thẻ bài Grid Cyber hiện đại.
* Lọc nhanh theo 40 danh mục Tag (Cải trang, Heroes Z, Trang bị, Pet...).
* Nút **Copy ID** và nút **1-Click Gửi thẳng vào Rương Web** của người chơi.

---

## ❓ 6. CÁC SỰ CỐ THƯỜNG GẶP (FAQ & TROUBLESHOOTING)

### Q1: Bị lỗi `404 | NOT FOUND` khi truy cập `http://127.0.0.1:8000/admin`?
* **Khắc phục:** Hệ thống đã được cấu hình tự động redirect từ `/admin` sang `/admin/dashboard`. Nếu bạn gặp màn hình trắng hoặc 404, chỉ cần nhấn `Ctrl + F5` để xóa cache trình duyệt.

### Q2: Bị lỗi `Table 'bigk.web_chest' doesn't exist`?
* **Khắc phục:** Bảng `web_chest` và `payment_transactions` đã được tự động tạo sẵn trong database `bigk`. Bạn chỉ cần refresh lại trang là chạy bình thường.

### Q3: Chuyển trang có bị lag hoặc nghẽn mạng không?
* **Khắc phục:** Hệ thống đã được tối ưu triệt để:
  * Trạng thái Game Server được cache 10 giây trong bộ nhớ, không bị nghẽn socket.
  * Toàn bộ truy vấn Item Template, Tài khoản bán acc đều được tối ưu eager loading (chống triệt để lỗi N+1 Query).
  * Font chữ đã chuyển sang **Plus Jakarta Sans** siêu nét, hỗ trợ 100% tiếng Việt có dấu.

---

Chúc bạn quản trị máy chủ Ngọc Rồng BigK thành công và phát triển rực rỡ! 🚀
