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
            el.innerHTML = sarVal + ' <bdi>﷼</bdi>';
        } else {
            el.innerHTML = '$' + usdVal.toFixed(2);
        }
    });
}

// ========================================================
// LIVE SEARCH
// ========================================================
function initSearch() {
    const input    = document.getElementById('searchInput');
    const dropdown = document.getElementById('searchDropdown');

    if (!input || !dropdown) return;

    let debounceTimer;

    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const query = input.value.trim();

        if (query.length === 0) {
            dropdown.classList.remove('active');
            dropdown.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch('php/search.php?q=' + encodeURIComponent(query))
                .then(res => res.json())
                .then(results => {
                    dropdown.innerHTML = '';

                    if (results.length === 0) {
                        dropdown.innerHTML = '<div class="search-no-results">No games found</div>';
                    } else {
                        results.forEach(game => {
                            const price = parseFloat(game.price);
                            const currency = localStorage.getItem('userCurrency') || 'USD';
                            const displayPrice = currency === 'SAR'
                                ? (price * 3.75).toFixed(2) + ' <bdi>﷼</bdi>'
                                : '$' + price.toFixed(2);

                            const item = document.createElement('a');
                            item.className   = 'search-item';
                            item.href        = 'product.php?id=' + game.id;
                            const imgPath = game.cover_image ? game.cover_image.replace(/^\//, '') : '';
                            item.innerHTML   = `
                                <img class="search-item-img"
                                     src="${imgPath}"
                                     onerror="this.style.background='#1e1b4b'; this.src='';">
                                <div class="search-item-info">
                                    <div class="search-item-name">${game.name}</div>
                                    <div class="search-item-price">${displayPrice}</div>
                                </div>
                            `;
                            dropdown.appendChild(item);
                        });
                    }

                    dropdown.classList.add('active');
                });
        }, 250); // 250ms debounce — waits for user to stop typing
    });

    // Close dropdown when clicking outside
    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });

    // Close on Escape key
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            dropdown.classList.remove('active');
            input.blur();
        }
    });
}

// ========================================================
// CONTACT MODAL
// ========================================================
function initContactModal() {
    const modal = document.getElementById('contactModal');
    const contactBtn = document.querySelector('.contact-link');
    const closeBtn = document.querySelector('.close-btn');
 
    if (!modal) return; // Safety check: modal must exist on the page
 
    // Open modal
    if (contactBtn) {
        contactBtn.addEventListener('click', (e) => {
            e.preventDefault();
            modal.style.display = 'flex';
        });
    }
 
    // Close via X button
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }
 
    // Close by clicking outside modal content
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
}

// Run the sync function when the page normally loads
// document.addEventListener('DOMContentLoaded', syncCurrency);
// Run on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    syncCurrency();
    initContactModal();
    initSearch()
});

// Run the sync function if the user navigates using the browser's "Back" button
window.addEventListener('pageshow', syncCurrency);
