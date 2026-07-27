<div class="ing-widget">
<style>
    .fi-page:has(.ing-widget) {
        padding: 22px 28px 36px !important;
    }
    .fi-page:has(.ing-widget) > section > .fi-header {
        margin-bottom: 18px !important;
    }
    .fi-page:has(.ing-widget) > section > .fi-header .fi-header-heading {
        font-size: 20px !important;
        font-weight: 800 !important;
        letter-spacing: -.02em !important;
    }
    .fi-page:has(.ing-widget) > section > .fi-header .fi-header-subheading {
        margin-top: 4px !important;
        font-size: 13px !important;
    }
    .fi-page:has(.ing-widget) > section > .fi-header .fi-btn {
        min-height: 40px !important;
        padding: 0 16px !important;
        border-radius: 9px !important;
        font-size: 13px !important;
    }
    .ing-krow {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        width: 100%;
        margin-bottom: 18px;
    }
    .ing-kcard {
        position: relative;
        overflow: hidden;
        padding: 16px;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        transition: transform .14s ease, box-shadow .14s ease;
    }
    .ing-kcard:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 22px rgba(15, 23, 42, .09);
    }
    .ing-kicon {
        width: 38px;
        height: 38px;
        margin-bottom: 8px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        font-size: 16px;
    }
    .ing-kvalue {
        margin-bottom: 2px;
        color: #0f172a;
        font-size: 28px;
        font-weight: 800;
        letter-spacing: -.03em;
        line-height: 1;
    }
    .ing-klabel { color: #334155; font-size: 12px; font-weight: 600; }
    .ing-knote { display: flex; align-items: center; gap: 4px; margin-top: 5px; color: #94a3b8; font-size: 11px; }

    @media (max-width: 767px) {
        .fi-page:has(.ing-widget) { padding: 16px !important; }
    }

    /* Bảng Filament giữ nguyên dữ liệu/action nhưng có đúng nhịp của bảng Bluefire. */
    .fi-ta .fi-ta-ctn {
        overflow: hidden;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        background: #fff;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
        --tw-ring-shadow: 0 0 #0000;
    }
    .fi-ta .fi-ta-header-ctn {
        display: flex;
        align-items: flex-end;
        gap: 9px;
        padding: 13px 16px !important;
    }
    .fi-ta .fi-ta-filters-above-content-ctn {
        order: 2;
        flex: 1;
        padding: 0 !important;
    }
    .fi-ta .fi-ta-filters { grid-template-columns: repeat(4, minmax(130px, 1fr)) !important; gap: 9px !important; }
    .fi-ta .fi-ta-header-toolbar { order: 1; flex: 0 1 320px; padding: 0 !important; }
    .fi-ta .fi-ta-content { border-top: 1px solid #f1f5f9; }
    .fi-ta .fi-ta-table thead { background: #f8fafc; }
    .fi-ta .fi-ta-table thead .fi-ta-cell > div,
    .fi-ta .fi-ta-table thead .fi-ta-header-cell > div { padding: 10px 12px !important; }
    .fi-ta .fi-ta-table tbody .fi-ta-cell > div { padding: 10px 12px !important; }
    .fi-ta .fi-ta-table tbody td.fi-ta-cell > div.fi-ta-col-wrp { padding: 0 !important; }
    .fi-ta .fi-ta-table tbody .fi-ta-text { padding: 10px 12px !important; }
    .fi-ta .fi-ta-table thead span,
    .fi-ta .fi-ta-table thead button {
        color: #94a3b8;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .07em;
        text-transform: uppercase;
    }
    .fi-ta .fi-ta-table tbody tr { border-bottom: 1px solid #f1f5f9; }
    .fi-ta .fi-ta-table tbody tr:hover { background: #f8fafc; }
    .fi-ta .fi-ta-table tbody .fi-ta-text-item-label { font-size: 12.5px; }
    .fi-ta .fi-pagination { padding: 12px 16px !important; }

    :root.dark .ing-kcard,
    :root.dark .fi-ta .fi-ta-ctn { border-color: #334155; background: #0f172a; }
    :root.dark .ing-kvalue { color: #fff; }
    :root.dark .ing-klabel { color: #cbd5e1; }
    :root.dark .ing-knote { color: #64748b; }
    :root.dark .fi-ta .fi-ta-content { border-color: #263449; }
    :root.dark .fi-ta .fi-ta-table thead { background: rgba(30, 41, 59, .5); }
    :root.dark .fi-ta .fi-ta-table tbody tr { border-color: #263449; }
    :root.dark .fi-ta .fi-ta-table tbody tr:hover { background: rgba(30, 41, 59, .4); }

    @media (max-width: 1279px) {
        .fi-ta .fi-ta-header-ctn { align-items: stretch; flex-direction: column; }
        .fi-ta .fi-ta-filters-above-content-ctn,
        .fi-ta .fi-ta-header-toolbar { width: 100%; flex-basis: auto; }
    }
    @media (max-width: 1023px) {
        .ing-krow { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .fi-ta .fi-ta-filters { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
    }
    @media (max-width: 639px) { .ing-krow { grid-template-columns: 1fr; } }
</style>

@php
    $cards = [
        ['value' => $totalIngredients, 'label' => __('ingredient.stats.total'), 'note' => __('ingredient.stats.total_desc'), 'icon' => 'fa-seedling', 'background' => '#EBF3FF', 'color' => '#1267E8'],
        ['value' => $totalSuppliers, 'label' => __('ingredient.stats.suppliers'), 'note' => __('ingredient.stats.suppliers_desc'), 'icon' => 'fa-truck', 'background' => '#ECFDF5', 'color' => '#059669'],
        ['value' => $totalUnits, 'label' => __('ingredient.stats.units'), 'note' => __('ingredient.stats.units_desc'), 'icon' => 'fa-ruler-combined', 'background' => '#FFF7ED', 'color' => '#EA580C'],
        ['value' => $totalTypes, 'label' => __('ingredient.stats.types'), 'note' => __('ingredient.stats.types_desc'), 'icon' => 'fa-tags', 'background' => '#F5F3FF', 'color' => '#7C3AED'],
    ];
@endphp

<div class="ing-krow">
    @foreach($cards as $card)
        <div class="ing-kcard">
            <div class="ing-kicon" style="background:{{ $card['background'] }};color:{{ $card['color'] }}">
                <i class="fa-solid {{ $card['icon'] }}"></i>
            </div>
            <div class="ing-kvalue">{{ $card['value'] }}</div>
            <div class="ing-klabel">{{ $card['label'] }}</div>
            <div class="ing-knote"><i class="fa-solid fa-circle-info"></i>{{ $card['note'] }}</div>
        </div>
    @endforeach
</div>
</div>
