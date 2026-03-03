window.App = {
    apiBaseUrl: '/api',
    api: {
        products: '/products',
        productDetails: '/product/details'
    },

    selectors: {
        productsContainer: 'product-list-container',
        productItemTemplate: 'product-item-template',
        productPopupTemplate: 'product-popup-template',
        productPopupContentTemplate: 'product-popup-content-template',
        productPopupSkeletonTemplate: 'product-popup-skeleton-template',
        paginationContainer: 'pagination-container',
        sortSelect: 'sort-select'
    },

    getPlaceholderImage() {
        return '/res/img/product-placeholder-' + Math.floor(Math.random() * 9 + 1) + '.png';
    },

    withImage(product) {
        return {
            ...product,
            image_url: product.image_url || this.getPlaceholderImage()
        };
    },

    element(id) {
        return document.getElementById(id);
    },

    template(id) {
        const node = this.element(id);
        return node ? node.innerHTML : '';
    },

    renderTemplate(template, data = {}) {
        let html = template;

        Object.keys(data).forEach(key => {
            html = html.replace(new RegExp(`{{${key}}}`, 'g'), data[key]);
        });

        return html;
    },

    endpointUrl(endpoint) {
        return this.apiBaseUrl + endpoint;
    },


    fetch(endpoint, options = {}) {
        return fetch(this.endpointUrl(endpoint), options)
            .then(response => response.json());
    },


    currentParameter(param) {
        const params = new URLSearchParams(window.location.search);
        return params.get(param) || '1';
    },

    updateParameter(param, value) {
        const params = new URLSearchParams(window.location.search);
        params.set(param, value);
        const newUrl = window.location.pathname + '?' + params.toString();
        window.history.pushState({}, '', newUrl);
    },

    getParameters() {
        return new URLSearchParams(window.location.search);
    },

    closePopup(popup) {
        if (popup && popup.parentNode) {
            popup.parentNode.removeChild(popup);
        }
    },

    createPopupContainer(contentHtml = '') {
        const popup = document.createElement('div');
        popup.innerHTML = contentHtml;
        document.body.appendChild(popup);

        const backdrop = popup.querySelector('.product-popup-backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', () => {
                this.closePopup(popup);
            });
        }

        const dialog = popup.querySelector('.product-popup');
        if (dialog) {
            dialog.addEventListener('click', (event) => {
                event.stopPropagation();
            });
        }

        return popup;
    },

    setPopupContent(popup, html, animate = false) {
        if (!popup) {
            return;
        }

        const popupContent = popup.querySelector('.product-popup-content');
        if (!popupContent) {
            return;
        }

        popupContent.innerHTML = html;

        if (animate) {
            popupContent.classList.remove('product-popup-content-enter');
            void popupContent.offsetWidth;
            popupContent.classList.add('product-popup-content-enter');
        }
    },

    animateCards(container) {
        container.querySelectorAll('.product-card').forEach((card, index) => {
            card.style.setProperty('--card-delay', `${Math.min(index * 60, 540)}ms`);
            card.classList.add('product-card-enter');
        });
    },


    loadProducts() {
        const container = this.element(this.selectors.productsContainer);
        container.classList.add('loading');

        this.fetch(this.api.products + '?' + this.getParameters().toString())
            .then(response => {
                this.renderProductList(response.products);
                this.updatePaginator(response.pagination);
            })
            .catch(error => {
                console.error('Error fetching products:', error);
            })
            .finally(() => {
                container.classList.remove('loading');
            });
        this.activeCategorySelection();
    },

    renderProductList(products) {
        const container = this.element(this.selectors.productsContainer);
        const template = this.template(this.selectors.productItemTemplate);

        container.innerHTML = products.map(product => {
            return this.renderTemplate(template, this.withImage(product));
        }).join('');

        this.animateCards(container);
    },

    sortProducts(criteria) {
        this.updateParameter('sort', criteria);
        this.loadProducts();
    },

    loadProductDetails(productId) {
        const popup = this.openProductPopup();
        this.showProductPopupSkeleton(popup);

        this.fetch(this.api.productDetails + '?id=' + productId)
            .then(response => {
                this.showProductPopup(response.product, popup);
            })
            .catch(error => {
                console.error('Error fetching product details:', error);
                this.showProductPopupError(popup);
            });
    },

    openProductPopup() {
        const popupTemplate = this.template(this.selectors.productPopupTemplate);
        return this.createPopupContainer(popupTemplate);
    },

    showProductPopupSkeleton(popup) {
        const skeletonTemplate = this.template(this.selectors.productPopupSkeletonTemplate);
        this.setPopupContent(popup, skeletonTemplate);
    },

    showProductPopup(product, popup = null) {
        const template = this.template(this.selectors.productPopupContentTemplate);
        const html = this.renderTemplate(template, this.withImage(product));

        if (!popup) {
            popup = this.openProductPopup();
        }

        this.setPopupContent(popup, html, true);
    },

    showProductPopupError(popup) {
        if (!popup) {
            return;
        }

        this.setPopupContent(
            popup,
            '<div class="product-popup-error">Failed to load product details.</div>'
        );
    },


    updatePaginator(pagination) {
        const container = this.element(this.selectors.paginationContainer);
        container.innerHTML = '';

        for (let page = 1; page <= pagination.pages; page++) {
            const button = document.createElement('button');
            button.classList.add('pagination-button');
            button.textContent = page;
            const isActive = page === pagination.page;
            button.disabled = isActive;

            if (isActive) {
                button.classList.add('pagination-button-active');
                button.setAttribute('aria-current', 'page');
            }

            button.addEventListener('click', () => {
                this.updateParameter('page', page);
                this.loadProducts();
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
        this.element(this.selectors.sortSelect).value = sort;
    },

    onCategoryClick(event) {
        event.preventDefault();
        const categoryId = event.currentTarget.getAttribute('data-category-id');

        this.updateParameter('category_id', categoryId);
        this.updateParameter('page', 1);
        this.loadProducts();
        this.activeCategorySelection();
        this.openCategory(categoryId);
    },

    bindEvents() {
        document.querySelectorAll('.category-link').forEach(link => {
            link.addEventListener('click', this.onCategoryClick.bind(this));
        });

        this.element(this.selectors.sortSelect).addEventListener('change', (event) => {
            this.sortProducts(event.target.value);
        });
    },

    init() {
        this.syncSortControl();
        this.loadProducts();
        this.openCategory(this.currentParameter('category_id'));
        this.bindEvents();
    }
};

document.addEventListener('DOMContentLoaded', function () {
    App.init();
});