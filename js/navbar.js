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
            // Use SVG for the Rial icon to ensure it shows on Linux/Android
            const rialIcon = `<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" style="vertical-align: middle; margin-left: 2px;"><path d="M19.5,18.5L18.5,19.5L17.5,18.5L16.5,19.5L15.5,18.5L14.5,19.5L13.5,18.5L12.5,19.5L11.5,18.5L10.5,19.5L9.5,18.5L8.5,19.5L7.5,18.5L6.5,19.5L5.5,18.5V5.5L6.5,4.5L7.5,5.5L8.5,4.5L9.5,5.5L10.5,4.5L11.5,5.5L12.5,4.5L13.5,5.5L14.5,4.5L15.5,5.5L16.5,4.5L17.5,5.5L18.5,4.5L19.5,5.5V18.5Z M17,14V8H7V14H17 M15,12H9V10H15V12Z" opacity="0" /><text x="50%" y="55%" dominant-baseline="middle" text-anchor="middle" font-family="Arial" font-size="14" font-weight="bold">﷼</text></svg>`;
            
            // Refined SVG Path for the Rial Ligature (﷼)
            const rialSvg = `<svg width="18" height="18" viewBox="0 0 40 40" fill="currentColor" style="vertical-align: middle; margin-left: 2px; display: inline-block;">
                <path d="M28.5,25.4c-1.1,0-2.1-0.3-3-0.8c-1-0.6-1.8-1.5-2.3-2.6c-0.5,1.1-1.3,2-2.3,2.6c-1,0.6-2,0.8-3.1,0.8c-1.5,0-2.8-0.5-3.9-1.4 c-1.1-0.9-1.6-2.2-1.6-3.7c0-1.3,0.4-2.4,1.1-3.3c0.7-0.9,1.7-1.4,2.9-1.5c-0.8-0.8-1.2-1.7-1.2-2.8c0-1.1,0.4-2,1.2-2.7 c0.8-0.7,1.8-1.1,3-1.1c1.2,0,2.2,0.4,3,1.1c0.8,0.7,1.2,1.6,1.2,2.7c0,1-0.3,1.9-1,2.6c1.1,0.3,1.9,0.9,2.6,1.7 c0.6-0.8,1.5-1.4,2.5-1.7c-0.7-0.7-1-1.6-1-2.6c0-1.1,0.4-2,1.2-2.7c0.8-0.7,1.8-1.1,3-1.1c1.2,0,2.2,0.4,3,1.1 c0.8,0.7,1.2,1.6,1.2,2.7c0,1.1-0.4,2-1.2,2.8c-0.8,0.7-1.7,1.1-2.8,1.2c1.2,0.2,2.1,0.7,2.8,1.6c0.7,0.9,1.1,1.9,1.1,3.2 c0,1.6-0.5,2.9-1.6,3.8C31.3,24.9,30,25.4,28.5,25.4z M20.3,13.6c0-0.6-0.2-1.1-0.6-1.5c-0.4-0.4-0.9-0.6-1.5-0.6 c-0.6,0-1.1,0.2-1.5,0.6c-0.4,0.4-0.6,0.9-0.6,1.5c0,0.6,0.2,1.1,0.6,1.5c0.4,0.4,0.9,0.6,1.5,0.6c0.6,0,1.1-0.2,1.5-0.6 C20.1,14.7,20.3,14.2,20.3,13.6z M28.5,13.6c0-0.6-0.2-1.1-0.6-1.5c-0.4-0.4-0.9-0.6-1.5-0.6c-0.6,0-1.1,0.2-1.5,0.6 c-0.4,0.4-0.6,0.9-0.6,1.5c0,0.6,0.2,1.1,0.6,1.5c0.4,0.4,0.9,0.6,1.5,0.6c0.6,0,1.1-0.2,1.5-0.6C28.3,14.7,28.5,14.2,28.5,13.6z M18,23.3c0.8,0,1.5-0.3,2-0.8c0.5-0.5,0.8-1.2,0.8-2c0-0.8-0.3-1.5-0.8-2c-0.5-0.5-1.2-0.8-2-0.8s-1.5,0.3-2,0.8 c-0.5,0.5-0.8,1.2-0.8,2c0,0.8,0.3,1.5,0.8,2C16.5,23,17.2,23.3,18,23.3z M28.5,23.3c0.8,0,1.5-0.3,2-0.8c0.5-0.5,0.8-1.2,0.8-2 c0-0.8-0.3-1.5-0.8-2c-0.5-0.5-1.2-0.8-2-0.8c-0.8,0-1.5,0.3-2,0.8c-0.5,0.5-0.8,1.2-0.8,2c0,0.8,0.3,1.5,0.8,2 C27,23,27.7,23.3,28.5,23.3z"/>
            </svg>`;

            el.innerHTML = sarVal + ' <bdi>' + rialSvg + '</bdi>';
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
                            const rialSvg = `<svg width="14" height="14" viewBox="0 0 40 40" fill="currentColor" style="vertical-align: middle; margin-left: 1px; display: inline-block;"><path d="M28.5,25.4c-1.1,0-2.1-0.3-3-0.8c-1-0.6-1.8-1.5-2.3-2.6c-0.5,1.1-1.3,2-2.3,2.6c-1,0.6-2,0.8-3.1,0.8c-1.5,0-2.8-0.5-3.9-1.4 c-1.1-0.9-1.6-2.2-1.6-3.7c0-1.3,0.4-2.4,1.1-3.3c0.7-0.9,1.7-1.4,2.9-1.5c-0.8-0.8-1.2-1.7-1.2-2.8c0-1.1,0.4-2,1.2-2.7 c0.8-0.7,1.8-1.1,3-1.1c1.2,0,2.2,0.4,3,1.1c0.8,0.7,1.2,1.6,1.2,2.7c0,1-0.3,1.9-1,2.6c1.1,0.3,1.9,0.9,2.6,1.7 c0.6-0.8,1.5-1.4,2.5-1.7c-0.7-0.7-1-1.6-1-2.6c0-1.1,0.4-2,1.2-2.7c0.8-0.7,1.8-1.1,3-1.1c1.2,0,2.2,0.4,3,1.1 c0.8,0.7,1.2,1.6,1.2,2.7c0,1.1-0.4,2-1.2,2.8c-0.8,0.7-1.7,1.1-2.8,1.2c1.2,0.2,2.1,0.7,2.8,1.6c0.7,0.9,1.1,1.9,1.1,3.2 c0,1.6-0.5,2.9-1.6,3.8C31.3,24.9,30,25.4,28.5,25.4z M20.3,13.6c0-0.6-0.2-1.1-0.6-1.5c-0.4-0.4-0.9-0.6-1.5-0.6 c-0.6,0-1.1,0.2-1.5,0.6c-0.4,0.4-0.6,0.9-0.6,1.5c0,0.6,0.2,1.1,0.6,1.5c0.4,0.4,0.9,0.6,1.5,0.6c0.6,0,1.1-0.2,1.5-0.6 C20.1,14.7,20.3,14.2,20.3,13.6z M28.5,13.6c0-0.6-0.2-1.1-0.6-1.5c-0.4-0.4-0.9-0.6-1.5-0.6c-0.6,0-1.1,0.2-1.5,0.6 c-0.4,0.4-0.6,0.9-0.6,1.5c0,0.6,0.2,1.1,0.6,1.5c0.4,0.4,0.9,0.6,1.5,0.6c0.6,0,1.1-0.2,1.5-0.6C28.3,14.7,28.5,14.2,28.5,13.6z M18,23.3c0.8,0,1.5-0.3,2-0.8c0.5-0.5,0.8-1.2,0.8-2c0-0.8-0.3-1.5-0.8-2c-0.5-0.5-1.2-0.8-2-0.8s-1.5,0.3-2,0.8 c-0.5,0.5-0.8,1.2-0.8,2c0,0.8,0.3,1.5,0.8,2C16.5,23,17.2,23.3,18,23.3z M28.5,23.3c0.8,0,1.5-0.3,2-0.8c0.5-0.5,0.8-1.2,0.8-2 c0-0.8-0.3-1.5-0.8-2c-0.5-0.5-1.2-0.8-2-0.8c-0.8,0-1.5,0.3-2,0.8c-0.5,0.5-0.8,1.2-0.8,2c0,0.8,0.3,1.5,0.8,2 C27,23,27.7,23.3,28.5,23.3z"/></svg>`;
                            const displayPrice = currency === 'SAR'
                                ? (price * 3.75).toFixed(2) + ' <bdi>' + rialSvg + '</bdi>'
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
