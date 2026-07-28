<div class="recipe-ingredient-table-header" aria-hidden="true">
    <span class="recipe-ingredient-heading recipe-ingredient-heading-stt">
        {{ __('recipe.fields.row_number') }}
    </span>

    <div class="recipe-ingredient-heading-columns">
        <span class="recipe-ingredient-heading recipe-ingredient-heading-name">
            {{ __('recipe.fields.ingredient') }}
        </span>
        <span class="recipe-ingredient-heading recipe-ingredient-heading-quantity">
            {{ __('recipe.fields.quantity_per_portion') }}
        </span>
        <span class="recipe-ingredient-heading recipe-ingredient-heading-price">
            <span>{{ __('recipe.fields.ingredient_price') }}</span>
            <small>{{ __('recipe.messages.ingredient_price_source_short') }}</small>
        </span>
        <span class="recipe-ingredient-heading recipe-ingredient-heading-total">
            {{ __('recipe.fields.line_total') }}
        </span>
        <span class="recipe-ingredient-heading recipe-ingredient-heading-note">
            {{ __('recipe.fields.note') }}
        </span>
    </div>

    <span class="recipe-ingredient-heading recipe-ingredient-heading-actions">
        {{ __('recipe.fields.actions') }}
    </span>
</div>
