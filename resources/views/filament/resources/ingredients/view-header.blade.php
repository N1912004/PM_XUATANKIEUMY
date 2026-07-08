@php
    $record = $getRecord();
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid #e5e7eb;margin-bottom:12px">
    <div style="display:flex;align-items:center;gap:14px">
        <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,#EBF3FF,#BFDBFE);display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="#1267E8" style="width:24px;height:24px">
                <path d="M64 96H0c0 123.7 100.3 224 224 224v144c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V320C288 196.3 187.7 96 64 96zm384-64c-84.2 0-157.4 46.5-195.7 115.2 27.7 30.2 48.2 66.9 59 107.6C424 243.1 512 147.9 512 32h-64z"/>
            </svg>
        </div>
        <div>
            <div style="font-size:17px;font-weight:700;color:#1e293b">{{ $record->name }}</div>
            <div style="font-size:13px;color:#6b7280;margin-top:2px">{{ $record->code }}</div>
        </div>
    </div>
    <div>
        @if($record->status)
            <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;background:#ECFDF5;color:#059669;font-size:13px;font-weight:600">
                <span style="width:8px;height:8px;border-radius:50%;background:#059669;display:inline-block"></span>
                Đang hoạt động
            </span>
        @else
            <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;background:#FEF2F2;color:#DC2626;font-size:13px;font-weight:600">
                <span style="width:8px;height:8px;border-radius:50%;background:#DC2626;display:inline-block"></span>
                Ngừng hoạt động
            </span>
        @endif
    </div>
</div>
