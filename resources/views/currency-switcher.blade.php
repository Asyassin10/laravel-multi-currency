<div class="currency-switcher">
    <select id="currency-selector" class="form-select">
        @foreach(app('YassineAs\MultiCurrency\Services\CurrencyService')->getSupportedCurrencies() as $code => $currency)
            <option value="{{ $code }}" 
                    {{ app('YassineAs\MultiCurrency\Services\CurrencyService')->getCurrentCurrency() === $code ? 'selected' : '' }}>
                {{ $currency['symbol'] }} {{ $currency['name'] }} ({{ $code }})
            </option>
        @endforeach
    </select>
</div>

<script>
document.getElementById('currency-selector').addEventListener('change', function() {
    const currency = this.value;
    
    fetch('{{ route("currency.switch") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ currency: currency })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Reload to show new prices
        }
    });
});
</script>
