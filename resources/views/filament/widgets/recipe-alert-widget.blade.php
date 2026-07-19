<div class="flex items-center gap-3 p-3.5 bg-sky-50 dark:bg-sky-950/20 text-sky-800 dark:text-sky-300 rounded-xl text-sm border border-sky-100/50 dark:border-sky-900/30 mb-4">
    <svg class="w-5 h-5 flex-shrink-0 text-sky-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 111.08 1.04l-.425.85a.75.75 0 11-1.254-.834l.56-1.12zM12 7.5a.75.75 0 110-1.5.75.75 0 0 1 0 1.5zM21 12a9 9 0 11-18 0 9 9 0 0 1 18 0z" />
    </svg>
    <span>Đơn giá nguyên liệu được lấy từ module Nguyên liệu / Nhà cung cấp và dùng để tự động tính cost nguyên liệu trên 1 phần.</span>
</div>

<style>
/* --- OVERRIDE FILAMENT TABLE HEADER & FILTERS TO MATCH BLUEFIRE DEMO --- */

/* 1. Thiết lập Header Page */
.fi-header-title {
    font-size: 22px !important;
    font-weight: 800 !important;
    letter-spacing: -0.025em !important;
    color: #0f172a !important;
}
.dark .fi-header-title {
    color: #ffffff !important;
}
.fi-header-subheading {
    font-size: 13px !important;
    color: #64748b !important;
    margin-top: 4px !important;
}
.dark .fi-header-subheading {
    color: #9ca3af !important;
}

/* 2. Căn chỉnh container Header của Table để gộp Search và Filters lên cùng một hàng */
.fi-ta-header {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 16px !important;
    flex-wrap: wrap !important;
    padding: 14px 16px !important;
    background-color: #ffffff !important;
    border-bottom: 1px solid #f1f5f9 !important;
}
.dark .fi-ta-header {
    background-color: #1f2937 !important;
    border-bottom-color: #374151 !important;
}

/* Đưa ô tìm kiếm sang bên trái */
.fi-ta-header-toolbar {
    order: 1 !important;
    margin: 0 !important;
    padding: 0 !important;
    flex-shrink: 0 !important;
}

/* 3. Định dạng lại Form Bộ lọc */
.fi-ta-filters-form {
    order: 2 !important;
    margin: 0 !important;
    padding: 0 !important;
    background: transparent !important;
    border: none !important;
    box-shadow: none !important;
    flex-grow: 1 !important;
}

/* Ẩn tiêu đề "Bộ lọc" và nút "Đặt lại" mặc định của card filter Filament */
.fi-ta-filters-form-header {
    display: none !important;
}

/* Đưa các filter inputs thành hàng ngang */
.fi-ta-filters-form > div {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    flex-wrap: wrap !important;
    gap: 10px !important;
    grid-template-columns: none !important;
    padding: 0 !important;
}

/* Style cho các select dropdown bộ lọc */
.fi-ta-filters-form select {
    height: 36px !important;
    border-radius: 8px !important;
    border-color: #e2e8f0 !important;
    font-size: 12.5px !important;
    color: #334155 !important;
    background-color: #ffffff !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    min-width: 160px !important;
    transition: all 0.13s ease !important;
}
.dark .fi-ta-filters-form select {
    border-color: #374151 !important;
    color: #e5e7eb !important;
    background-color: #111827 !important;
}
.fi-ta-filters-form select:hover {
    border-color: #cbd5e1 !important;
}
.dark .fi-ta-filters-form select:hover {
    border-color: #4b5563 !important;
}
.fi-ta-filters-form select:focus {
    border-color: #93c5fd !important;
    box-shadow: 0 0 0 3px rgba(38, 125, 193, 0.07) !important;
}

/* Định dạng các trường filter grid để xếp ngang */
.fi-ta-filters-form > div > div {
    grid-column: auto !important;
    margin: 0 !important;
    padding: 0 !important;
}

/* Ẩn nhãn label nhỏ phía trên select dropdown trong filter để giống mẫu */
.fi-ta-filters-form label {
    display: none !important;
}

/* 4. Định dạng ô Tìm kiếm (Search Input) */
.fi-ta-search-field {
    width: 220px !important;
}
.fi-ta-search-field input {
    height: 36px !important;
    border-radius: 8px !important;
    background-color: #F8FAFC !important;
    border-color: #e2e8f0 !important;
    font-size: 13px !important;
    padding-left: 36px !important;
    transition: all 0.13s ease !important;
}
.dark .fi-ta-search-field input {
    background-color: #111827 !important;
    border-color: #374151 !important;
    color: #ffffff !important;
}
.fi-ta-search-field input:focus {
    background-color: #ffffff !important;
    border-color: #93c5fd !important;
    box-shadow: 0 0 0 3px rgba(38, 125, 193, 0.08) !important;
}
.dark .fi-ta-search-field input:focus {
    background-color: #1f2937 !important;
}

/* Căn chỉnh lại vị trí của icon kính lúp */
.fi-ta-search-field .absolute {
    height: 36px !important;
    display: flex !important;
    align-items: center !important;
}

/* 5. Định dạng nút Đặt lại bộ lọc (Reset filters) của Filament */
.fi-ta-filters-form-actions {
    margin-top: 0 !important;
    padding: 0 !important;
}
.fi-ta-filters-form-actions button {
    height: 36px !important;
    padding: 0 12px !important;
    font-size: 12.5px !important;
    font-weight: 600 !important;
    border-radius: 8px !important;
}
</style>
