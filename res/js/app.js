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
        paginationContainerTop: 'pagination-container-top',
        sortSelect: 'sort-select',
        hideSidebar: 'hide-sidebar',
        showSidebar: 'show-sidebar',
        mobileCategoriesToggle: 'mobile-categories-toggle',
        mobileSidebarBackdrop: 'sidebar-mobile-backdrop'
    },

    storage: {
        sidebarHidden: 'sidebar-hidden'
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
        return params.get(param);
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

    isMobileViewport() {
        return window.matchMedia('(max-width: 52rem)').matches;
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
        const containerTop = this.element(this.selectors.paginationContainerTop);
        this.renderPaginator(containerTop, pagination);
    },

    renderPaginator(container, pagination) {
        if (!container) {
            return;
        }

        container.innerHTML = '';

        for (let page = 1; page <= pagination.pages; page++) {
            const button = document.createElement('button');
            button.classList.add('pagination-button');
            button.textContent = page;
            const isActive = page === pagination.page;
            button.disabled = isActive;

            if (isActive) {
                button.classList.add('pagination-button-active');
                button.setAttribute('aria-current', 'p');
            }

            button.addEventListener('click', () => {
                this.updateParameter('p', page);
                this.loadProducts();
            });
            container.appendChild(button);
        }
    },


    activeCategorySelection() {
        const activeCategoryId = this.currentParameter('cid') || '1';

        document.querySelectorAll('.category-link').forEach(link => {
            link.classList.toggle('active', link.getAttribute('data-category-id') === activeCategoryId);
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
        const sort = this.currentParameter('sort') || 'a-z';
        this.element(this.selectors.sortSelect).value = sort;
    },

    onCategoryClick(event) {
        event.preventDefault();
        const categoryId = event.currentTarget.getAttribute('data-category-id');

        this.updateParameter('cid', categoryId);
        this.updateParameter('p', 1);
        this.loadProducts();
        this.activeCategorySelection();
        this.openCategory(categoryId);

        if (this.isMobileViewport()) {
            this.setSidebarHidden(true, false);
        }
    },

    bindEvents() {
        document.querySelectorAll('.category-link').forEach(link => {
            link.addEventListener('click', this.onCategoryClick.bind(this));
        });

        this.element(this.selectors.sortSelect).addEventListener('change', (event) => {
            this.sortProducts(event.target.value);
        });

        const hideButton = this.element(this.selectors.hideSidebar);
        if (hideButton) {
            hideButton.addEventListener('click', () => {
                this.setSidebarHidden(true);
            });
        }

        const showButton = this.element(this.selectors.showSidebar);
        if (showButton) {
            showButton.addEventListener('click', () => {
                this.setSidebarHidden(false);
            });
        }

        const mobileToggle = this.element(this.selectors.mobileCategoriesToggle);
        if (mobileToggle) {
            mobileToggle.addEventListener('click', () => {
                const hidden = document.body.classList.contains('sidebar-hidden');
                this.setSidebarHidden(!hidden, false);
            });
        }

        const backdrop = this.element(this.selectors.mobileSidebarBackdrop);
        if (backdrop) {
            backdrop.addEventListener('click', () => {
                if (this.isMobileViewport()) {
                    this.setSidebarHidden(true, false);
                }
            });
        }

        window.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && this.isMobileViewport() && !document.body.classList.contains('sidebar-hidden')) {
                this.setSidebarHidden(true, false);
            }
        });

        window.addEventListener('resize', this.handleViewportChange.bind(this));
    },

    setSidebarHidden(hidden, persist = true) {
        document.body.classList.toggle('sidebar-hidden', hidden);

        if (this.isMobileViewport()) {
            document.body.classList.toggle('sidebar-mobile-open', !hidden);
        } else {
            document.body.classList.remove('sidebar-mobile-open');
        }

        if (persist) {
            localStorage.setItem(this.storage.sidebarHidden, hidden ? '1' : '0');
        }

        this.syncSidebarButtons();
    },

    syncSidebarButtons() {
        const hideButton = this.element(this.selectors.hideSidebar);
        const showButton = this.element(this.selectors.showSidebar);
        const mobileToggle = this.element(this.selectors.mobileCategoriesToggle);
        const backdrop = this.element(this.selectors.mobileSidebarBackdrop);

        const hidden = document.body.classList.contains('sidebar-hidden');

        if (hideButton) {
            hideButton.setAttribute('aria-pressed', hidden ? 'true' : 'false');
        }

        if (showButton) {
            showButton.setAttribute('aria-pressed', hidden ? 'true' : 'false');
        }

        if (mobileToggle) {
            mobileToggle.setAttribute('aria-expanded', hidden ? 'false' : 'true');
            mobileToggle.textContent = hidden ? 'Категорії' : 'Закрити категорії';
        }

        if (backdrop) {
            backdrop.setAttribute('aria-hidden', hidden ? 'true' : 'false');
        }
    },

    handleViewportChange() {
        if (this.isMobileViewport()) {
            const hidden = document.body.classList.contains('sidebar-hidden');
            document.body.classList.toggle('sidebar-mobile-open', !hidden);
            return;
        }

        document.body.classList.remove('sidebar-mobile-open');
        const savedHidden = localStorage.getItem(this.storage.sidebarHidden) === '1';
        this.setSidebarHidden(savedHidden, false);
    },

    restoreSidebarState() {
        if (this.isMobileViewport()) {
            this.setSidebarHidden(true, false);
            return;
        }

        const isHidden = localStorage.getItem(this.storage.sidebarHidden) === '1';
        this.setSidebarHidden(isHidden, false);
    },

    init() {
        this.restoreSidebarState();
        this.syncSortControl();
        this.loadProducts();
        this.openCategory(this.currentParameter('cid') || '1');
        this.bindEvents();
    }
};

document.addEventListener('DOMContentLoaded', function () {
    App.init();
});