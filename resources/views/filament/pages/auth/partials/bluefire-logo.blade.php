{{-- Logo ngọn lửa BlueFire (SVG thuần — không phụ thuộc ảnh upload trong Cài đặt) --}}
@php $h = $height ?? 44; @endphp
<svg width="{{ round($h * 0.82) }}" height="{{ $h }}" viewBox="0 0 41 50" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="bf-flame-{{ $h }}" x1="6" y1="4" x2="36" y2="48" gradientUnits="userSpaceOnUse">
            <stop stop-color="#2F7BE8"/>
            <stop offset="1" stop-color="#0B3E9C"/>
        </linearGradient>
    </defs>
    {{-- Ngọn lửa lớn --}}
    <path d="M20.5 0C22.5 7.5 27.5 11 32.5 15.5C38 20.5 41 26 41 32.5C41 42.5 32 50 20.5 50C24 46 26 42 26 37.5C26 31.5 22 27.5 17.5 23C12.5 18 10.5 12.5 12 6.5C14.5 4 17.5 2 20.5 0Z" fill="url(#bf-flame-{{ $h }})"/>
    {{-- Ngọn lửa nhỏ bên trái --}}
    <path d="M9.5 12C10.5 16.5 13.5 19.5 16.5 22.5C20 26 22 29.5 22 34C22 41 16.5 46.5 9 47.5C11.5 44.5 12.5 41.5 12.5 38.5C12.5 34.5 10 31.5 7 28.5C3.5 25 2 21 3 16.5C5 14.5 7 13 9.5 12Z" fill="url(#bf-flame-{{ $h }})" opacity=".92"/>
</svg>
