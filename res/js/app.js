App = {
    apiBaseUrl: '/api',
    api: {
        products: '/products',
        productDetails: '/product/details'
    },


    fetch(endpoint, options = {}) {
        return fetch(this.apiBaseUrl + endpoint, options)
            .then(response => response.json());
    },


    currentParameter: (param) => {
        const params = new URLSearchParams(window.location.search);
        return params.get(param) || '1';
    },
    updateParameter: (param, value) => {
        const params = new URLSearchParams(window.location.search);
        params.set(param, value);
        const newUrl = window.location.pathname + '?' + params.toString();
        window.history.pushState({}, '', newUrl);
    },
    getParameters() {
        const params = new URLSearchParams(window.location.search);
        return params;
    },


    loadProducts() {
        const container = document.getElementById('product-list-container');
        container.classList.add('loading');

        App.fetch(App.api.products + '?' + this.getParameters().toString())
            .then(response => {
                App.renderProductList(response.products);
                App.updatePaginator(response.pagination);
            })
            .catch(error => {
                console.error('Error fetching products:', error);
            })
            .finally(() => {
                container.classList.remove('loading');
            });
        App.activeCategorySelection();
    },
    renderProductList: function (products) {
        const container = document.getElementById('product-list-container');
        const template = document.getElementById('product-item-template').innerHTML;

        container.innerHTML = products.map(product => {
            let html = template;
            Object.keys(product).forEach(key => {
                html = html.replace(new RegExp(`{{${key}}}`, 'g'), product[key]);
            });

            let img_url = product['image_url'] || '/res/img/product-placeholder-' + Math.floor(Math.random() * 9 + 1) + '.png';
            html = html.replace(new RegExp(`{{image_url}}`, 'g'), img_url);

            return html;
        }).join('');

        container.querySelectorAll('.product-card').forEach((card, index) => {
            card.style.setProperty('--card-delay', `${Math.min(index * 60, 540)}ms`);
            card.classList.add('product-card-enter');
        });
    },
    sortProducts: function (criteria) {
        App.updateParameter('sort', criteria);
        App.loadProducts();
    },

    loadProductDetails: function (productId) {
        const popup = App.openProductPopupLoading();

        App.fetch(App.api.productDetails + '?id=' + productId)
            .then(response => {
                App.showProductPopup(response.product, popup);
            })
            .catch(error => {
                console.error('Error fetching product details:', error);
                App.showProductPopupError(popup);
            });
    },
    openProductPopupLoading: function () {
        const template = document.getElementById('product-popup-loading-template');
        const popup = document.createElement('div');
        popup.innerHTML = template
            ? template.innerHTML
            : '<div class="product-popup-backdrop"></div><div class="product-popup"><div class="product-popup-loading">Loading product...</div></div>';
        document.body.appendChild(popup);
        popup.addEventListener('click', function () {
            document.body.removeChild(popup);
        });

        return popup;
    },
    showProductPopup: function (product, popup = null) {
        const template = document.getElementById('product-popup-template').innerHTML;
        let html = template;
        Object.keys(product).forEach(key => {
            html = html.replace(new RegExp(`{{${key}}}`, 'g'), product[key]);
        });

        let img_url = product['image_url'] || '/res/img/product-placeholder-' + Math.floor(Math.random() * 9 + 1) + '.png';
        html = html.replace(new RegExp(`{{image_url}}`, 'g'), img_url);

        if (!popup) {
            popup = document.createElement('div');
            document.body.appendChild(popup);
            popup.addEventListener('click', function () {
                document.body.removeChild(popup);
            });
        }

        const loadingNode = popup.querySelector('.product-popup-loading');
        if (loadingNode) {
            loadingNode.remove();
        }

        popup.innerHTML = html;
    },
    showProductPopupError: function (popup) {
        if (!popup) {
            return;
        }

        popup.innerHTML = '<div class="product-popup-backdrop"></div><div class="product-popup"><div class="product-popup-loading">Failed to load product details.</div></div>';
    },


    updatePaginator: function (pagination) {
        const container = document.getElementById('pagination-container');
        container.innerHTML = '';
        for (let page = 1; page <= pagination.pages; page++) {
            const button = document.createElement('button');
            button.classList.add('pagination-button');
            button.textContent = page;
            button.disabled = page === pagination.page;
            button.addEventListener('click', function () {
                App.updateParameter('page', page);
                App.loadProducts();
            });
            container.appendChild(button);
        }
    },


    activeCategorySelection() {
        document.querySelectorAll('.category-link').forEach(link => {
            link.classList.toggle('active', link.getAttribute('data-category-id') === this.currentParameter('category_id'));
        });
    },
    openCategory(categoryId) {
        const link = document.querySelector(`aside .category-link[data-category-id="${categoryId}"]`);
        const categoryItem = link ? link.closest('.category-item') : null;
        if (categoryItem) {
            categoryItem.open = true;
        }

        let parent = link ? link.closest('.category-children') : null;
        while (parent) {
            if (parent.classList.contains('category-children')) {
                parent.parentElement.open = true;
            }
            parent = parent.parentElement.closest('.category-children');
        }
    },

    syncSortControl() {
        const sort = this.currentParameter('sort') || 'price';
        document.getElementById('sort-select').value = sort;
    }
};

document.addEventListener('DOMContentLoaded', function () {
    App.syncSortControl();
    App.loadProducts();
    App.openCategory(App.currentParameter('category_id'));

    document.querySelectorAll('.category-link').forEach(link => {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            const categoryId = this.getAttribute('data-category-id');
            App.updateParameter('category_id', categoryId);
            App.updateParameter('page', 1);
            App.loadProducts();
            App.activeCategorySelection();
            App.openCategory(categoryId);
        });
    });

    document.getElementById('sort-select').addEventListener('change', function () {
        App.sortProducts(this.value);
    });
});