<div class="krow" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 18px; width: 100%;">
    <div class="kcard" style="background: #ffffff; border: 1px solid #E2E8F0; border-radius: 14px; padding: 16px; box-shadow: 0 1px 2px rgba(15,23,42,.04); position: relative; overflow: hidden;">
        <div class="ktop" style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 8px;">
            <div class="kico" style="width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; font-size: 16px; background: #EBF3FF; color: #1267E8;">
                <i class="fa-solid fa-seedling"></i>
            </div>
        </div>
        <div class="kval" style="font-size: 28px; font-weight: 800; letter-spacing: -0.03em; line-height: 1; color: #0F172A; margin-bottom: 2px;">{{ $totalIngredients }}</div>
        <div class="klbl" style="font-size: 12px; font-weight: 600; color: #334155;">{{ __('ingredient.stats.total') }}</div>
        <div class="knote" style="font-size: 11px; color: #94A3B8; margin-top: 5px; display: flex; align-items: center; gap: 4px;">
            <i class="fa-solid fa-circle-info"></i>{{ __('ingredient.stats.total_desc') }}
        </div>
    </div>

    <div class="kcard" style="background: #ffffff; border: 1px solid #E2E8F0; border-radius: 14px; padding: 16px; box-shadow: 0 1px 2px rgba(15,23,42,.04); position: relative; overflow: hidden;">
        <div class="ktop" style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 8px;">
            <div class="kico" style="width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; font-size: 16px; background: #ECFDF5; color: #059669;">
                <i class="fa-solid fa-truck"></i>
            </div>
        </div>
        <div class="kval" style="font-size: 28px; font-weight: 800; letter-spacing: -0.03em; line-height: 1; color: #0F172A; margin-bottom: 2px;">{{ $totalSuppliers }}</div>
        <div class="klbl" style="font-size: 12px; font-weight: 600; color: #334155;">{{ __('ingredient.stats.suppliers') }}</div>
        <div class="knote" style="font-size: 11px; color: #94A3B8; margin-top: 5px; display: flex; align-items: center; gap: 4px;">
            <i class="fa-solid fa-circle-info"></i>{{ __('ingredient.stats.suppliers_desc') }}
        </div>
    </div>

    <div class="kcard" style="background: #ffffff; border: 1px solid #E2E8F0; border-radius: 14px; padding: 16px; box-shadow: 0 1px 2px rgba(15,23,42,.04); position: relative; overflow: hidden;">
        <div class="ktop" style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 8px;">
            <div class="kico" style="width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; font-size: 16px; background: #FFF7ED; color: #EA580C;">
                <i class="fa-solid fa-ruler-combined"></i>
            </div>
        </div>
        <div class="kval" style="font-size: 28px; font-weight: 800; letter-spacing: -0.03em; line-height: 1; color: #0F172A; margin-bottom: 2px;">{{ $totalUnits }}</div>
        <div class="klbl" style="font-size: 12px; font-weight: 600; color: #334155;">{{ __('ingredient.stats.units') }}</div>
        <div class="knote" style="font-size: 11px; color: #94A3B8; margin-top: 5px; display: flex; align-items: center; gap: 4px;">
            <i class="fa-solid fa-circle-info"></i>{{ __('ingredient.stats.units_desc') }}
        </div>
    </div>

    <div class="kcard" style="background: #ffffff; border: 1px solid #E2E8F0; border-radius: 14px; padding: 16px; box-shadow: 0 1px 2px rgba(15,23,42,.04); position: relative; overflow: hidden;">
        <div class="ktop" style="display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 8px;">
            <div class="kico" style="width: 38px; height: 38px; border-radius: 10px; display: grid; place-items: center; font-size: 16px; background: #F5F3FF; color: #7C3AED;">
                <i class="fa-solid fa-tags"></i>
            </div>
        </div>
        <div class="kval" style="font-size: 28px; font-weight: 800; letter-spacing: -0.03em; line-height: 1; color: #0F172A; margin-bottom: 2px;">{{ $totalTypes }}</div>
        <div class="klbl" style="font-size: 12px; font-weight: 600; color: #334155;">{{ __('ingredient.stats.types') }}</div>
        <div class="knote" style="font-size: 11px; color: #94A3B8; margin-top: 5px; display: flex; align-items: center; gap: 4px;">
            <i class="fa-solid fa-circle-info"></i>{{ __('ingredient.stats.types_desc') }}
        </div>
    </div>
</div>
