document.addEventListener('DOMContentLoaded', () => {
    const tariffSelect = document.querySelector('#tariff_plan');
    if (tariffSelect) {
        fetch('/api.php?action=get_tariffs', { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                tariffSelect.innerHTML = '';
                data.tariffs.forEach(tariff => {
                    tariffSelect.innerHTML += `<option value="${tariff.tariff_id}">${tariff.tariff_name} (₦${tariff.rate_per_kwh}/kWh)</option>`;
                });
            });
    }

    const calculateBtn = document.querySelector('#calculate_bill');
    if (calculateBtn) {
        calculateBtn.addEventListener('click', () => {
            const units = document.querySelector('#units_used').value;
            const tariffId = tariffSelect.value;
            fetch('/api.php?action=get_tariffs', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    const tariff = data.tariffs.find(t => t.tariff_id == tariffId);
                    const cost = units * tariff.rate_per_kwh;
                    document.querySelector('#bill_result').innerText = `Estimated Bill: ₦${cost.toFixed(2)}`;
                });
        });
    }
});