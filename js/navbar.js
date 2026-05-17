// js/navbar.js

function syncCurrency() {
    const exchangeRate = 3.75; // 1 USD = 3.75 SAR
    const selector = document.getElementById('currencySelector');
    
    // 1. Check if the user already chose a currency previously
    const savedCurrency = localStorage.getItem('userCurrency') || 'USD';
    
    // 2. Force the dropdown to visually match the saved choice
    if (selector) {
        selector.value = savedCurrency;
        for (let i = 0; i < selector.options.length; i++) {
            selector.options[i].selected = (selector.options[i].value === savedCurrency);
        }
    }
    
    // 3. Apply the currency calculations
    applyCurrency(savedCurrency, exchangeRate);

    // 4. Listen for when the user changes the dropdown
    if (selector) {
        selector.onchange = function() {
            const newCurrency = this.value;
            localStorage.setItem('userCurrency', newCurrency);
            applyCurrency(newCurrency, exchangeRate);
        };
    }
}

function applyCurrency(currency, exchangeRate) {
    const priceElements = document.querySelectorAll('.price-display');
    
    priceElements.forEach(el => {
        const usdVal = parseFloat(el.getAttribute('data-usd'));
        if (isNaN(usdVal)) return;

        if (currency === 'SAR') {
            const sarVal = (usdVal * exchangeRate).toFixed(2);
            // Use the word 'ريال' for total consistency and safety
            el.innerHTML = sarVal + ' <bdi>ريال</bdi>';
        } else {
            el.innerHTML = '$' + usdVal.toFixed(2);
        }
    });
}

// ========================================================
// LIVE SEARCH
// ========================================================
function initSearch() {
    const desktopInput    = document.getElementById('searchInput');
    const desktopDropdown = document.getElementById('searchDropdown');
    const mobileInput     = document.getElementById('searchInputMobile');
    const mobileDropdown  = document.getElementById('searchDropdownMobile');

    function setupSearch(input, dropdown) {
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
                                    ? (price * 3.75).toFixed(2) + ' <bdi>ريال</bdi>'
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
            }, 250);
        });

        document.addEventListener('click', (e) => {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.classList.remove('active');
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                dropdown.classList.remove('active');
                input.blur();
            }
        });
    }

    setupSearch(desktopInput, desktopDropdown);
    setupSearch(mobileInput, mobileDropdown);
}

// ========================================================
// CONTACT MODAL
// ========================================================
function initContactModal() {
    const modal = document.getElementById('contactModal');
    const contactBtn = document.querySelector('.contact-link');
    const closeBtn = document.querySelector('.close-btn');
 
    if (!modal) return;
 
    if (contactBtn) {
        contactBtn.addEventListener('click', (e) => {
            e.preventDefault();
            modal.style.display = 'flex';
        });
    }
 
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    }
 
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
}

// Global initialization
document.addEventListener('DOMContentLoaded', () => {
    syncCurrency();
    initContactModal();
    initSearch();
    
    // Aggressive re-application to beat any stubborn caches
    [500, 1000, 2500].forEach(delay => {
        setTimeout(syncCurrency, delay);
    });
});

window.addEventListener('pageshow', syncCurrency);
