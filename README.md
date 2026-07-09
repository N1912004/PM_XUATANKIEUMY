# 📦 PM_XUATANKIEUMY - Hướng Dẫn Setup Dự Án

Dự án quản lý suất ăn công nghiệp xây dựng trên nền tảng **Laravel 12** kết hợp hệ sinh thái quản trị **Filament v3**.

---

## 🛠️ Công Nghệ Sử Dụng & Tài Liệu Tham Khảo

*   **Framework**: [Laravel 12 Documentation](https://laravel.com/docs)
*   **Admin Panel**: [Filament PHP v3 Documentation](https://filamentphp.com/docs) (Hệ thống quản trị CRUD cực mạnh)
*   **Frontend Interactivity**: [Livewire v3 Documentation](https://livewire.laravel.com/docs) & [Alpine.js](https://alpinejs.dev/docs)
*   **CSS Utility**: [Tailwind CSS v4](https://tailwindcss.com/docs)
*   **Realtime**: [Laravel Reverb](https://laravel.com/docs/reverb) (Phục vụ phân hệ Chat nhóm)
*   **Asset Bundler**: [Vite](https://vite.dev/guide/)

---

## 🚀 Hướng Dẫn Setup Dự Án Khi Clone Về Máy Mới

Vui lòng thực hiện theo thứ tự các bước dưới đây để thiết lập môi trường phát triển cục bộ:

### Bước 1: Tải mã nguồn về máy
Mở Terminal và clone nhánh phát triển `quocnghi_dev` về máy:
```bash
git clone -b quocnghi_dev https://gitlab.citgroup.vn/xuat-an-chuan-my/xuat-an-chuan-my.git
cd xuat-an-chuan-my
```

### Bước 2: Cài đặt các thư viện phụ thuộc (Dependencies)
Cài đặt toàn bộ thư viện backend (PHP) và frontend (JS):
```bash
# 1. Cài đặt các package PHP của Laravel & Filament
composer install

# 2. Cài đặt các package Javascript của Vite & Tailwind
npm install
```

### Bước 3: Thiết lập cấu hình môi trường (`.env`)
Tạo tệp cấu hình môi trường cá nhân từ tệp mẫu:
```bash
# 1. Sao chép cấu hình mẫu
cp .env.example .env

# 2. Khởi tạo mã khóa bảo mật của ứng dụng (App Key)
php artisan key:generate
```
*Sau đó, hãy mở tệp `.env` vừa tạo và chỉnh sửa thông số kết nối cơ sở dữ liệu (`DB_DATABASE`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`) cho khớp với máy local của bạn.*

> [!NOTE]
> Mặc định trong cấu hình mẫu sử dụng cổng MySQL là `3307` (`DB_PORT=3307`). Nếu máy của bạn dùng MySQL cổng mặc định `3306`, vui lòng sửa lại trong `.env`.

### Bước 4: Khởi tạo Cơ sở dữ liệu (Database)
Tùy vào nhu cầu sử dụng dữ liệu, bạn chọn 1 trong 2 cách sau:

*   **Cách A: Sử dụng lại CSDL mẫu đã sao lưu trong Repo (Khuyên dùng để có sẵn dữ liệu test)**:
    Tạo một database trống trên MySQL (ví dụ: `pm_xuatankieumy`), sau đó chạy lệnh import file SQL có sẵn trong thư mục `database/`:
    ```bash
    mysql -u root -p pm_xuatankieumy < database/backup_pm_xuatankieumy.sql
    ```
*   **Cách B: Chạy CSDL mới tinh sạch sẽ từ Migration**:
    Nếu bạn muốn tạo mới toàn bộ cấu trúc bảng và dữ liệu mẫu tự động:
    ```bash
    php artisan migrate:fresh --seed
    ```

### Bước 5: Khởi tạo liên kết lưu trữ (Storage Link)
*Đây là bước bắt buộc để hiển thị các tệp tải lên như Avatar nhân vật, hình ảnh nguyên liệu, file đính kèm...*
```bash
php artisan storage:link
```

### Bước 6: Biên dịch Assets và Chạy ứng dụng
```bash
# 1. Biên dịch toàn bộ CSS/JS (bao gồm phần CSS tối ưu vừa gộp)
npm run build

# 2. Khởi chạy máy chủ ảo Laravel
php artisan serve
```
*(Nếu hệ thống cần dùng tính năng realtime/chat, chạy thêm lệnh: `php artisan reverb:start`)*

---

## 📐 Tiêu Chuẩn Viết Code (Coding Convention)
Dự án sử dụng **Laravel Pint** để tự động chuẩn hóa định dạng code. Trước khi tạo PR commit, vui lòng chạy lệnh sau để tự động format lại code sạch:
```bash
vendor/bin/pint --format agent
```

---
*Bản quyền phát triển thuộc về CIT Group.*
