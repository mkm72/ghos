// js/navbar.js

function syncCurrency() {
    const exchangeRate = 3.75; // 1 USD = 3.75 SAR
    const selector = document.getElementById('currencySelector');
    
    // 1. Check if the user already chose a currency previously
    const savedCurrency = localStorage.getItem('userCurrency') || 'USD';
    
    // 2. Force the dropdown to visually match the saved choice
    if (selector) {
        selector.value = savedCurrency;
        // This loop explicitly tells the browser which option to highlight
        for (let i = 0; i < selector.options.length; i++) {
            if (selector.options[i].value === savedCurrency) {
                selector.options[i].selected = true;
            } else {
                selector.options[i].selected = false;
            }
        }
    }
    
    // 3. Apply the currency calculations
    applyCurrency(savedCurrency, exchangeRate);

    // 4. Listen for when the user changes the dropdown
    if (selector) {
        // We use onchange here to prevent duplicate listeners
        selector.onchange = function() {
            const newCurrency = this.value;
            localStorage.setItem('userCurrency', newCurrency); // Save the new choice
            applyCurrency(newCurrency, exchangeRate); // Update the numbers
        };
    }
}

function applyCurrency(currency, exchangeRate) {
    // Find every element on the page with the class "price-display"
    const priceElements = document.querySelectorAll('.price-display');
    
    priceElements.forEach(el => {
        // Get the original USD value from the data attribute
        const usdVal = parseFloat(el.getAttribute('data-usd'));
        if (isNaN(usdVal)) return;

        if (currency === 'SAR') {
            const sarVal = (usdVal * exchangeRate).toFixed(2);
            // USE innerHTML and <bdi> to prevent RTL text from breaking the layout
            el.innerHTML = sarVal + ' <bdi>ر.س</bdi>';
        } else {
            el.innerHTML = '$' + usdVal.toFixed(2);
        }
    });
}

// Run the sync function when the page normally loads
document.addEventListener('DOMContentLoaded', syncCurrency);

// Run the sync function if the user navigates using the browser's "Back" button
window.addEventListener('pageshow', syncCurrency);
