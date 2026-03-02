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
            return html;
        }).join('');
    },
    sortProducts: function (criteria) {
        App.updateParameter('sort', criteria);
        App.loadProducts();
    },

    loadProductDetails: function (productId) {
        App.fetch(App.api.productDetails + '?id=' + productId)
            .then(response => {
                App.showProductPopup(response.product);
            })
            .catch(error => {
                console.error('Error fetching product details:', error);
            });
    },
    showProductPopup: function (product) {
        const template = document.getElementById('product-popup-template').innerHTML;
        let html = template;
        Object.keys(product).forEach(key => {
            html = html.replace(new RegExp(`{{${key}}}`, 'g'), product[key]);
        });
        const popup = document.createElement('div');
        popup.innerHTML = html;
        document.body.appendChild(popup);
        popup.addEventListener('click', function () {
            document.body.removeChild(popup);
        });
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
        const link = document.querySelector(`.category-link[data-category-id="${categoryId}"]`);
        if (link) {
            link.closest('.category-item').open = true;
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