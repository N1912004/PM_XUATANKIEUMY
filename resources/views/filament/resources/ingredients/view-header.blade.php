@php
    $record = $getRecord();
@endphp

<div style="display:flex;align-items:center;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--po-bd);margin-bottom:12px">
    <div style="display:flex;align-items:center;gap:14px">
        <div style="width:52px;height:52px;border-radius:14px;background:linear-gradient(135deg,var(--po-bl-s),var(--po-bl-m));display:flex;align-items:center;justify-content:center;flex-shrink:0">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="{{ \App\Models\Setting::get('primary_color', '#267DC1') }}" style="width:24px;height:24px">
                <path d="M64 96H0c0 123.7 100.3 224 224 224v144c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16V320C288 196.3 187.7 96 64 96zm384-64c-84.2 0-157.4 46.5-195.7 115.2 27.7 30.2 48.2 66.9 59 107.6C424 243.1 512 147.9 512 32h-64z"/>
            </svg>
        </div>
        <div>
            <div style="font-size:17px;font-weight:700;color:var(--po-tx)">{{ $record->name }}</div>
            <div style="font-size:13px;color:var(--po-mu);margin-top:2px">{{ $record->code }}</div>
        </div>
    </div>
    <div>
        @if($record->status)
            <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;background:var(--po-gn-s);color:var(--po-gn);font-size:13px;font-weight:600">
                <span style="width:8px;height:8px;border-radius:50%;background:var(--po-gn);display:inline-block"></span>
                {{ __('ingredient.status.active') }}
            </span>
        @else
            <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;background:var(--po-rd-s);color:var(--po-rd);font-size:13px;font-weight:600">
                <span style="width:8px;height:8px;border-radius:50%;background:var(--po-rd);display:inline-block"></span>
                {{ __('ingredient.status.inactive') }}
            </span>
        @endif
    </div>
</div>
