<!-- Nhúng FontAwesome CDN để hiển thị các icon chuẩn theo demo -->

@php
    $record = $getRecord();
    $ingredientsCount = $record->ingredients->count();
    $totalWeight = $record->ingredients->sum('pivot.quantity_per_portion');
    // Cost hiệu lực: ưu tiên cost override (nếu có) để khớp bảng danh sách & Báo cáo;
    // breakdown % từng nguyên liệu vẫn tính trên cost tự tính bên dưới.
    $autoCost = $record->ingredients->sum(function ($ingredient) {
        return $ingredient->pivot->quantity_per_portion * $ingredient->reference_price;
    });
    $totalCost = $record->cost_override !== null ? (float) $record->cost_override : $autoCost;

    $colors = ['#267DC1', '#059669', '#7C3AED', '#EA580C', '#ef4444', '#ec4899', '#6b7280'];
    $ingredientsWithCosts = [];
    
    foreach ($record->ingredients as $index => $ingredient) {
        $cost = $ingredient->pivot->quantity_per_portion * $ingredient->reference_price;
        $percentage = $totalCost > 0 ? ($cost / $totalCost) * 100 : 0;
        
        $ingredientsWithCosts[] = [
            'name' => $ingredient->name,
            'cost' => $cost,
            'percentage' => round($percentage, 1),
            'color' => $colors[$index % count($colors)],
        ];
    }

    // Lịch sử cập nhật giá nguyên liệu
    $priceHistory = [];
    foreach ($record->ingredients as $ingredient) {
        $supplierName = $ingredient->supplier?->name ?? __('recipe.detail.supplier');
        $priceHistory[] = [
            'time' => $ingredient->updated_at,
            'message' => __('recipe.detail.price_synced_from', ['supplier' => $supplierName]),
        ];
    }
    usort($priceHistory, function ($a, $b) {
        return $b['time'] <=> $a['time'];
    });
    $priceHistory = array_slice($priceHistory, 0, 5);
@endphp

<style>
.md-detail-container {
  --bl:#267DC1;--bl-d:#1F669E;--bl-s:#E9F2F8;--bl-m:#A8CBE6;
  --gn:#059669;--gn-s:#ECFDF5;--gn-t:#065F46;
  --or:#EA580C;--or-s:#FFF7ED;--or-t:#9A3412;
  --pu:#7C3AED;--pu-s:#F5F3FF;
  --rd:#DC2626;--rd-s:#FEF2F2;--rd-t:#991B1B;
  --am:#D97706;--am-s:#FFFBEB;
  --sk:#0284C7;--sk-s:#F0F9FF;
  --pk:#E11D48;--pk-s:#FFF1F2;
  --bg:#F8FAFC;--wh:#fff;
  --tx:#0F172A;--su:#334155;--mu:#64748B;--fa:#94A3B8;
  --bd:#E2E8F0;--bd2:#F1F5F9;
  --sh:0 1px 3px rgba(15,23,42,.05),0 4px 16px rgba(15,23,42,.05);
  --sh2:0 1px 2px rgba(15,23,42,.04);
  --r:12px;
}

/* Tương thích chế độ Dark Mode của Filament */
.dark .md-detail-container {
  --wh: #1f2937;
  --bg: #111827;
  --tx: #f3f4f6;
  --su: #e5e7eb;
  --mu: #9ca3af;
  --fa: #6b7280;
  --bd: #374151;
  --bd2: #1f2937;
  --bl-s: rgba(30, 58, 138, 0.3);
  --bl-m: #1d4ed8;
  --gn-s: rgba(6, 78, 59, 0.3);
  --or-s: rgba(120, 53, 4, 0.3);
}

.md-detail-container .md-layout{display:grid;grid-template-columns:1fr 288px;gap:16px;align-items:start}
.md-detail-container .md-layout > div{min-width:0}
.md-detail-container .md-card{background:var(--wh);border:1px solid var(--bd);border-radius:var(--r);padding:20px 22px;box-shadow:var(--sh2);margin-bottom:14px}
.md-detail-container .md-card:last-child{margin-bottom:0}
.md-detail-container .md-hero{display:flex;align-items:flex-start;gap:16px;margin-bottom:16px;padding-bottom:16px;border-bottom:1px solid var(--bd2)}
.md-detail-container .md-hero-ico{width:70px;height:70px;border-radius:16px;background:linear-gradient(135deg,var(--bl-s, #E9F2F8),var(--bl-m, #DBEAFE));display:grid;place-items:center;font-size:30px;flex-shrink:0;color:var(--bl)}
.md-detail-container .md-hero-name{font-size:22px;font-weight:800;color:var(--tx);margin-bottom:6px}
.md-detail-container .md-meta-grid{display:grid;grid-template-columns:1fr 1fr;gap:6px 20px}
.md-detail-container .md-meta-item{display:flex;align-items:baseline;gap:8px;font-size:13px}
.md-detail-container .md-meta-k{color:var(--mu);min-width:110px;flex-shrink:0}
.md-detail-container .md-meta-v{font-weight:600;color:var(--tx)}
.md-detail-container .md-stat-row{display:grid;grid-template-columns:repeat(3,1fr);gap:12px}
.md-detail-container .md-stat{background:var(--bg);border-radius:10px;padding:14px;text-align:center}
.md-detail-container .md-stat-ico{font-size:22px;margin-bottom:6px}
.md-detail-container .md-stat-lbl{font-size:11.5px;color:var(--mu);margin-bottom:4px}
.md-detail-container .md-stat-val{font-size:22px;font-weight:800;color:var(--tx)}
.md-detail-container .md-stat-unit{font-size:11.5px;color:var(--fa);margin-top:2px}
.md-detail-container .md-ing-table{width:100%;border-collapse:collapse;border:1px solid var(--bd);border-radius:10px;overflow:hidden}
.md-detail-container .md-ing-table th{padding:9px 12px;text-align:left;font-size:11px;font-weight:700;color:var(--fa);text-transform:uppercase;letter-spacing:.06em;background:var(--bd2, #F8FAFC);border-bottom:1px solid var(--bd)}
.dark .md-detail-container .md-ing-table th{background:#111827}
.md-detail-container .md-ing-table td{padding:11px 12px;font-size:13px;border-bottom:1px solid var(--bd2);color:var(--tx)}
.md-detail-container .md-ing-table tr:last-child td{border-bottom:none}
.md-detail-container .md-total-bar{display:flex;align-items:center;justify-content:center;gap:16px;background:linear-gradient(135deg,#EFF6FF,#DBEAFE);border-radius:10px;padding:14px;margin-top:12px}
.dark .md-detail-container .md-total-bar{background:rgba(30, 58, 138, 0.2)}
.md-detail-container .md-total-lbl{font-size:14px;font-weight:600;color:var(--bl)}
.dark .md-detail-container .md-total-lbl{color:#60a5fa}
.md-detail-container .md-total-val{font-size:22px;font-weight:800;color:var(--bl)}
.md-detail-container .md-note{display:flex;align-items:flex-start;gap:8px;background:var(--bd2, #F8FAFC);border-radius:8px;padding:10px 12px;font-size:12px;color:var(--mu);margin-top:10px;line-height:1.6}
.dark .md-detail-container .md-note{background:#111827}
.md-detail-container .md-note i{color:var(--bl);font-size:13px;flex-shrink:0;margin-top:1px}
.md-detail-container .md-cost-rp{background:var(--wh);border:1px solid var(--bd);border-radius:var(--r);padding:16px;box-shadow:var(--sh2);margin-bottom:14px}
.md-detail-container .md-cost-rp-ttl{font-size:13px;font-weight:700;color:var(--tx);margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid var(--bd2)}
.md-detail-container .md-hist-item{display:flex;align-items:flex-start;gap:8px;margin-bottom:10px;position:relative}
.md-detail-container .md-hist-item:not(:last-child)::after{content:'';position:absolute;left:7px;top:18px;width:1.5px;height:calc(100% - 8px);background:var(--bd2)}
.md-detail-container .md-hist-dot{width:14px;height:14px;border-radius:50%;flex-shrink:0;margin-top:2px;z-index:1}
.md-detail-container .md-hist-body{flex:1;min-width:0}
.md-detail-container .md-hist-dt{font-size:11px;color:var(--fa);margin-top:2px}
.md-detail-container .md-hist-action{font-size:12px;font-weight:600;color:var(--tx)}
.md-detail-container .md-hist-by{font-size:11px;color:var(--mu);float:right}

.md-detail-container .ms-active{background:var(--gn-s, #ECFDF5);color:var(--gn-t, #065F46);border:1px solid var(--gn, #A7F3D0);border-radius:20px;padding:3px 10px;font-size:11.5px;font-weight:700;display:inline-block}
.md-detail-container .ms-review{background:var(--or-s, #FFF7ED);color:var(--or, #92400E);border:1px solid var(--or, #FED7AA);border-radius:20px;padding:3px 10px;font-size:11.5px;font-weight:700;display:inline-block}
.md-detail-container .ms-inactive{background:var(--bd2, #F1F5F9);color:var(--su, #475569);border:1px solid var(--bd);border-radius:20px;padding:3px 10px;font-size:11.5px;font-weight:700;display:inline-block}

.md-detail-container .dl-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;font-size:11.5px}
.md-detail-container .dl-left{display:flex;align-items:center;gap:6px;color:var(--su)}
.md-detail-container .dl-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0}
.md-detail-container .dl-pct{font-weight:600;color:var(--tx)}

@media(max-width:960px){
  .md-detail-container .md-layout{grid-template-columns:1fr}
  .md-detail-container .md-stat-row{grid-template-columns:1fr}
}

@media(max-width:640px){
  .md-detail-container .md-card { padding: 16px 14px; }
  .md-detail-container .md-hero { flex-direction: column; align-items: center; text-align: center; gap: 12px; width: 100%; }
  .md-detail-container .md-hero-ico { width: 60px; height: 60px; font-size: 24px; flex-shrink: 0; }
  .md-detail-container .md-hero-info { display: flex; flex-direction: column; align-items: center; width: 100%; }
  .md-detail-container .md-hero-name { text-align: center; font-size: 20px; }
  .md-detail-container .md-hero div[style*="display:flex;align-items:center"] { flex-direction: column; gap: 6px; width: 100%; align-items: center; }
  .md-detail-container .md-meta-grid { grid-template-columns: 1fr; gap: 8px; width: 100%; max-width: 320px; margin: 0 auto; }
  .md-detail-container .md-meta-item { justify-content: flex-start; width: 100%; border-bottom: 1px solid var(--bd2); padding-bottom: 6px; }
  .md-detail-container .md-meta-item:last-child { border-bottom: none; padding-bottom: 0; }
  .md-detail-container .md-meta-k { min-width: 120px; text-align: left; }
  .md-detail-container .md-meta-v { text-align: left; }
  .md-detail-container .md-stat-row { grid-template-columns: 1fr; gap: 10px; width: 100%; }
  .md-detail-container .md-stat { width: 100%; box-sizing: border-box; }
  .md-detail-container .md-total-bar { flex-direction: column; text-align: center; gap: 8px; }
  .md-detail-container .md-table-wrapper { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; border: 1px solid var(--bd); border-radius: 10px; margin-bottom: 14px; }
  .md-detail-container .md-ing-table { border: none; border-radius: 0; }
}
</style>

<div class="md-detail-container">
  <div class="md-layout">
    <!-- LEFT -->
    <div>
      <div class="md-card">
        <!-- Hero -->
        <div class="md-hero">
          <div class="md-hero-ico">
            <i class="fa-solid fa-bowl-food"></i>
          </div>
          <div class="md-hero-info">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
              <div class="md-hero-name">{{ $record->name }}</div>
              @php
                $statusClass = match($record->status) {
                    'active' => 'ms-active',
                    'pending' => 'ms-review',
                    'inactive' => 'ms-inactive',
                    default => 'ms-inactive'
                };
                $statusText = match($record->status) {
                    'active' => __('recipe.status.active'),
                    'pending' => __('recipe.status.pending'),
                    'inactive' => __('recipe.status.inactive'),
                    default => $record->status
                };
              @endphp
              <span class="{{ $statusClass }}">{{ $statusText }}</span>
            </div>
            
            <div class="md-meta-grid">
              <div class="md-meta-item">
                <span class="md-meta-k">{{ __('recipe.detail.code') }}</span>
                <span class="md-meta-v">{{ $record->code }}</span>
              </div>
              <div class="md-meta-item">
                <span class="md-meta-k">{{ __('recipe.detail.actual_price') }}</span>
                <span class="md-meta-v">{{ $record->price_option === 'Có' ? __('recipe.detail.by_unit') : __('recipe.detail.currency', ['value' => number_format($record->actual_price, 0, ',', '.')]) }}</span>
              </div>
              <div class="md-meta-item">
                <span class="md-meta-k">{{ __('recipe.detail.type') }}</span>
                <span class="md-meta-v">{{ $record->type }}</span>
              </div>
              <div class="md-meta-item">
                <span class="md-meta-k">{{ __('recipe.detail.created_by') }}</span>
                <span class="md-meta-v" style="display:flex;align-items:center;gap:6px">
                  <div style="width:20px;height:20px;border-radius:50%;overflow:hidden;flex-shrink:0">
                    <img src="https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=40&h=40&fit=crop&crop=face" style="width:100%;height:100%;object-fit:cover">
                  </div>
                  {{ auth()->user()?->name ?? __('recipe.detail.unknown_user') }}
                </span>
              </div>
              <div class="md-meta-item">
                <span class="md-meta-k">{{ __('recipe.detail.price_level') }}</span>
                <span class="md-meta-v">{{ __('recipe.detail.currency', ['value' => number_format($record->price_level, 0, ',', '.')]) }}</span>
              </div>
              <div class="md-meta-item">
                <span class="md-meta-k">{{ __('recipe.detail.updated_at') }}</span>
                <span class="md-meta-v">{{ $record->updated_at->format('d/m/Y H:i') }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats 3 cols -->
        <div class="md-stat-row">
          <div class="md-stat">
            <div class="md-stat-ico">🍴</div>
            <div class="md-stat-lbl">{{ __('recipe.detail.ingredient_count') }}</div>
            <div class="md-stat-val">{{ $ingredientsCount }}</div>
            <div class="md-stat-unit">{{ __('recipe.detail.types_unit') }}</div>
          </div>
          <div class="md-stat">
            <div class="md-stat-ico">⚖️</div>
            <div class="md-stat-lbl">{{ __('recipe.detail.total_weight') }}</div>
            <div class="md-stat-val">{{ (float) $totalWeight }}</div>
            <div class="md-stat-unit">kg</div>
          </div>
          <div class="md-stat" style="background:var(--bl-s)">
            <div class="md-stat-ico">💰</div>
            <div class="md-stat-lbl">{{ __('recipe.detail.total_cost') }}</div>
            <div class="md-stat-val" style="color:var(--bl)">{{ __('recipe.detail.currency', ['value' => number_format($totalCost, 0, ',', '.')]) }}</div>
            <div class="md-stat-unit">{{ __('recipe.detail.currency_unit') }}</div>
          </div>
        </div>
      </div>

      <!-- Ingredient detail -->
      <div class="md-card">
        <div class="md-sec-ttl" style="font-size:14px;font-weight:700;color:var(--tx);margin-bottom:6px">{{ __('recipe.detail.ingredients_title') }}</div>
        <div class="md-note" style="margin-bottom:14px">
          <i class="fa-solid fa-circle-info"></i>
          {{ __('recipe.detail.ingredient_price_notice') }}
        </div>
        <div class="md-table-wrapper">
          <table class="md-ing-table">
            <thead>
              <tr>
                <th>STT</th>
                <th>{{ __('recipe.fields.ingredient') }}</th><th>{{ __('recipe.detail.quantity_per_portion') }}</th><th>{{ __('recipe.fields.ingredient_price') }}</th><th>{{ __('recipe.fields.line_total') }}</th><th>{{ __('recipe.detail.supplier') }}</th><th>{{ __('recipe.fields.note') }}</th>
              </tr>
            </thead>
            <tbody>
              @foreach ($record->ingredients as $index => $ingredient)
                @php
                  $qty = $ingredient->pivot->quantity_per_portion;
                  $price = $ingredient->reference_price;
                  $itemTotal = $qty * $price;
                @endphp
                <tr>
                  <td style="font-weight:600;color:var(--mu)">{{ $index + 1 }}</td>
                  <td style="font-weight:600">{{ $ingredient->name }}</td>
                  <td>{{ (float) $qty }}</td>
                  <td>{{ __('recipe.detail.currency', ['value' => number_format($price, 0, ',', '.')]) }}</td>
                  <td style="font-weight:700;color:var(--or)">{{ __('recipe.detail.currency', ['value' => number_format($itemTotal, 0, ',', '.')]) }}</td>
                  <td style="font-size:12px;color:var(--mu)">{{ $ingredient->supplier?->name ?? '—' }}</td>
                  <td style="color:var(--fa)">{{ $ingredient->pivot->note ?? '–' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
        
        <div class="md-total-bar">
          <span class="md-total-lbl">{{ __('recipe.detail.total_cost_for_dish', ['name' => $record->name]) }}</span>
          <span class="md-total-val">{{ __('recipe.detail.currency', ['value' => number_format($totalCost, 0, ',', '.')]) }}</span>
        </div>
        
        <div class="md-note">
          <i class="fa-solid fa-circle-info"></i>
          {{ __('recipe.detail.price_sync_notice') }}
        </div>
      </div>
    </div>

    <!-- RIGHT -->
    <div>
      <!-- Tóm tắt cost -->
      <div class="md-cost-rp">
        <div class="md-cost-rp-ttl">{{ __('recipe.detail.cost_summary') }}</div>
        <div style="display:flex;justify-content:center;margin-bottom:12px">
          @php
            $r = 48;
            $circumference = 2 * 3.14159265 * $r;
            $currentOffset = 0;
          @endphp
          <svg width="130" height="130" viewBox="0 0 130 130">
            <circle cx="65" cy="65" r="{{ $r }}" fill="none" stroke="#F1F5F9" stroke-width="16"/>
            @foreach ($ingredientsWithCosts as $item)
                @php
                    $percentage = $totalCost > 0 ? ($item['cost'] / $totalCost) : 0;
                    $dashArray = ($percentage * $circumference) . ' ' . ($circumference - ($percentage * $circumference));
                    $dashOffset = -$currentOffset;
                    $currentOffset += $percentage * $circumference;
                @endphp
                <circle cx="65" cy="65" r="{{ $r }}" fill="none" stroke="{{ $item['color'] }}" stroke-width="16" 
                        stroke-dasharray="{{ $dashArray }}" stroke-dashoffset="{{ $dashOffset }}" stroke-linecap="butt"
                        transform="rotate(-90 65 65)" />
            @endforeach
            <text x="65" y="61" text-anchor="middle" font-size="12" font-weight="800" fill="#267DC1" font-family="Inter,sans-serif">{{ __('recipe.detail.currency', ['value' => number_format($totalCost, 0, ',', '.')]) }}</text>
            <text x="65" y="75" text-anchor="middle" font-size="8.5" fill="#64748B" font-family="Inter,sans-serif">{{ __('recipe.detail.chart_total') }}</text>
          </svg>
        </div>
        
        <div style="display:flex;flex-direction:column;gap:5px">
          @foreach ($ingredientsWithCosts as $item)
            <div class="dl-row">
              <div class="dl-left">
                <span class="dl-dot" style="background:{{ $item['color'] }}"></span>
                {{ $item['name'] }}
              </div>
              <span class="dl-pct" style="font-size:11px">{{ __('recipe.detail.currency', ['value' => number_format($item['cost'], 0, ',', '.')]) }} &nbsp;{{ $item['percentage'] }}%</span>
            </div>
          @endforeach
        </div>
      </div>

      <!-- Thông tin tìm kiếm -->
      <div class="md-cost-rp">
        <div class="md-cost-rp-ttl" style="display:flex;align-items:center;gap:7px">
          <i class="fa-solid fa-magnifying-glass" style="font-size:13px;color:var(--bl)"></i>
          {{ __('recipe.detail.search_information') }}
        </div>
        <div style="font-size:12px;color:var(--mu);line-height:1.6;margin-bottom:8px">{{ __('recipe.detail.search_hint') }}</div>
        <ul class="lf-rule" style="list-style:none;padding:0;display:flex;flex-direction:column;gap:4px">
          <li style="font-size:12px;color:var(--su);display:flex;gap:5px;align-items:flex-start">
            <span style="color:var(--bl)">•</span>
            {{ __('recipe.fields.name') }}
          </li>
          <li style="font-size:12px;color:var(--su);display:flex;gap:5px;align-items:flex-start">
            <span style="color:var(--bl)">•</span>
            {{ __('recipe.fields.actual_price') }}
          </li>
        </ul>
      </div>

      <!-- Lịch sử cập nhật giá nguyên liệu -->
      <div class="md-cost-rp" style="margin-bottom:0">
        <div class="md-cost-rp-ttl">{{ __('recipe.detail.price_history') }}</div>
        @if (count($priceHistory) > 0)
          @foreach ($priceHistory as $index => $history)
            @php
              $dotColor = match($index % 3) {
                  0 => 'var(--gn)', // green
                  1 => 'var(--bl)', // blue
                  default => 'var(--pu)' // purple
              };
            @endphp
            <div class="md-hist-item">
              <div class="md-hist-dot" style="background:{{ $dotColor }}"></div>
              <div class="md-hist-body">
                <div style="display:flex;justify-content:space-between">
                  <div class="md-hist-action">{{ $history['time']->format('d/m/Y H:i') }}</div>
                  <span class="md-hist-by" style="font-size:11px;color:var(--fa)">admin</span>
                </div>
                <div class="md-hist-dt">{{ $history['message'] }}</div>
              </div>
            </div>
          @endforeach
        @else
          <div style="font-size:12px;color:var(--fa);text-align:center;padding:10px 0">{{ __('recipe.detail.no_price_history') }}</div>
        @endif
      </div>
    </div>
  </div>
</div>
