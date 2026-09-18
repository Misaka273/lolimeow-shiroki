/**
 * 追番页面交互脚本
 * @package lolimeow
 */
(function () {
    'use strict';

    var activeTrigger = null;
    var animeRevealObserver = null;
    var animeCardsReady = false;
    var boundAnimeWrap = window.__shirokiAnimeBoundWrap || null;
    var animeRevealSelector = [
        '.single-title',
        '.anime-page-desc',
        '.anime-toolbar',
        '.anime-tag-filter-bar',
        '.anime-list',
        '.anime-pagination',
        '.anime-empty-state'
    ].join(',');

    var animeFilterState = {
        status: 'all',
        poolItems: [],
        searchEmpty: null
    };

    var animePagination = {
        perPage: 6,
        currentPage: 1,
        nav: null,
        infoEl: null,
        currentEl: null,
        inputEl: null,
        prevBtn: null,
        nextBtn: null,
        goBtn: null,
        listEl: null
    };

    function initAnimeReveal() {
        var wrap = document.querySelector('.anime-page-wrap');
        if (!wrap) {
            return;
        }

        if (!('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            wrap.querySelectorAll(animeRevealSelector).forEach(function (node) {
                node.classList.add('anime-reveal', 'is-visible');
            });
            wrap.querySelectorAll('.anime-item.is-visible').forEach(function (node) {
                node.classList.add('anime-reveal', 'is-visible');
            });
            wrap.classList.remove('anime-cards-pending');
            animeCardsReady = true;
            return;
        }

        animeRevealObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                entry.target.classList.add('is-visible');
                animeRevealObserver.unobserve(entry.target);
            });
        }, {
            threshold: 0.12,
            rootMargin: '0px 0px -4% 0px'
        });

        bindAnimeRevealTargets(wrap);
        initAnimeCardReveal(wrap);
    }

    function initAnimeCardReveal(wrap) {
        var cardNodes = wrap.querySelectorAll('.anime-item.is-visible:not(.is-page-hidden):not(.is-search-hidden)');

        if (!cardNodes.length) {
            wrap.classList.remove('anime-cards-pending');
            animeCardsReady = true;
            return;
        }

        wrap.classList.add('anime-cards-pending');
        cardNodes.forEach(function (node, index) {
            node.classList.add('anime-reveal');
            node.style.setProperty('--anime-reveal-delay', String(index * 0.10) + 's');
        });

        Promise.all(Array.prototype.map.call(cardNodes, function (node) {
            var image = node.querySelector('.anime-cover img');

            if (!image) {
                return Promise.resolve();
            }

            return ensureAnimeCardImageLoaded(image);
        })).then(function () {
            animeCardsReady = true;
            revealAnimeCards(wrap);
        });
    }

    function ensureAnimeCardImageLoaded(image) {
        var source = image.getAttribute('data-src') || image.getAttribute('src');

        if (source && image.getAttribute('src') !== source) {
            image.setAttribute('src', source);
        }

        if (image.complete && image.naturalWidth > 0 && image.getAttribute('src') === source) {
            return Promise.resolve();
        }

        return new Promise(function (resolve) {
            var settled = false;
            var settle = function () {
                if (settled) {
                    return;
                }

                settled = true;
                image.removeEventListener('load', settle);
                image.removeEventListener('error', settle);
                resolve();
            };

            image.addEventListener('load', settle, { once: true });
            image.addEventListener('error', settle, { once: true });
            window.setTimeout(settle, 2500);

            if (image.complete) {
                settle();
            }
        });
    }

    function revealAnimeCards(wrap) {
        if (!animeCardsReady) {
            return;
        }

        wrap.classList.remove('anime-cards-pending');
        window.requestAnimationFrame(function () {
            wrap.querySelectorAll('.anime-item.is-visible:not(.is-page-hidden):not(.is-search-hidden)').forEach(function (node) {
                node.classList.add('is-visible');
            });
        });
    }

    function bindAnimeRevealTargets(root) {
        if (!root || !animeRevealObserver) {
            return;
        }

        var cardNodes = root.querySelectorAll('.anime-item.is-visible:not(.is-page-hidden):not(.is-search-hidden)');
        var revealNodes = root.querySelectorAll(animeRevealSelector);
        var sectionNodes = Array.prototype.filter.call(revealNodes, function (node) {
            return !node.classList.contains('anime-item');
        });

        root.querySelectorAll(animeRevealSelector).forEach(function (node) {
            if (node.classList.contains('anime-reveal')) {
                if (!node.classList.contains('is-visible') && isAnimeRevealCandidate(node)) {
                    animeRevealObserver.observe(node);
                }
                return;
            }

            node.classList.add('anime-reveal');

            if (node.classList.contains('anime-item')) {
                var cardIndex = Array.prototype.indexOf.call(cardNodes, node);
                node.style.setProperty('--anime-reveal-delay', String(Math.max(cardIndex, 0) * 0.10) + 's');
            } else {
                var sectionIndex = Array.prototype.indexOf.call(sectionNodes, node);
                node.style.setProperty('--anime-reveal-delay', String(Math.max(sectionIndex, 0) * 0.12) + 's');
            }

            animeRevealObserver.observe(node);
        });
    }

    function isAnimeRevealCandidate(node) {
        if (!node.classList.contains('anime-item')) {
            return true;
        }

        return !node.classList.contains('is-page-hidden') && !node.classList.contains('is-search-hidden');
    }

    function refreshAnimeRevealTargets() {
        var wrap = document.querySelector('.anime-page-wrap');
        if (!wrap) {
            return;
        }

        if (!animeRevealObserver) {
            wrap.querySelectorAll(animeRevealSelector).forEach(function (node) {
                node.classList.add('anime-reveal', 'is-visible');
            });
            wrap.querySelectorAll('.anime-item.is-visible').forEach(function (node) {
                node.classList.add('anime-reveal', 'is-visible');
            });
            wrap.classList.remove('anime-cards-pending');
            return;
        }

        bindAnimeRevealTargets(wrap);
        if (animeCardsReady) {
            revealAnimeCards(wrap);
        }
    }

    function bindFilterNavigation(selector, paramName) {
        var items = document.querySelectorAll(selector);
        if (!items.length) {
            return;
        }

        items.forEach(function (item) {
            item.addEventListener('click', function () {
                var filter = this.getAttribute('data-filter');
                if (!filter) {
                    return;
                }

                var url = new URL(window.location.href);
                if (filter === 'all') {
                    url.searchParams.delete(paramName);
                } else {
                    url.searchParams.set(paramName, filter);
                }

                url.searchParams.delete('anime_paged');
                window.location.href = url.toString();
            });
        });
    }

    function initStatusFilter() {
        var items = document.querySelectorAll('.anime-stats-item[data-filter]');
        animeFilterState.poolItems = Array.prototype.slice.call(
            document.querySelectorAll('.anime-item[data-anime-pool="1"]')
        );
        animeFilterState.searchEmpty = document.getElementById('anime-search-empty');

        if (!items.length) {
            return;
        }

        var active = document.querySelector('.anime-stats-item.active[data-filter]');
        animeFilterState.status = (active && active.getAttribute('data-filter')) || 'all';

        items.forEach(function (item) {
            item.addEventListener('click', function () {
                var filter = this.getAttribute('data-filter');
                if (!filter || filter === animeFilterState.status) {
                    return;
                }

                applyStatusFilter(filter, true);
            });
        });
    }

    function syncStatusUrl(status) {
        var url = new URL(window.location.href);
        if (status === 'all') {
            url.searchParams.delete('anime_status');
        } else {
            url.searchParams.set('anime_status', status);
        }
        url.searchParams.delete('anime_paged');
        window.history.replaceState({}, '', url.toString());
    }

    var animeRevealGeneration = 0;

    function replayAnimeCardReveal() {
        var wrap = document.querySelector('.anime-page-wrap');
        if (!wrap) {
            return;
        }

        var cards = Array.prototype.slice.call(
            wrap.querySelectorAll('.anime-item.is-visible:not(.is-hidden):not(.is-page-hidden):not(.is-search-hidden)')
        );

        if (!cards.length) {
            return;
        }

        var generation = ++animeRevealGeneration;
        var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (reduceMotion) {
            cards.forEach(function (node) {
                node.classList.add('anime-reveal', 'is-visible');
                node.style.removeProperty('--anime-reveal-delay');
            });
            wrap.classList.remove('anime-cards-pending');
            return;
        }

        cards.forEach(function (node, index) {
            if (animeRevealObserver) {
                animeRevealObserver.unobserve(node);
            }
            node.classList.add('anime-reveal');
            node.classList.remove('is-visible');
            node.style.setProperty('--anime-reveal-delay', String(index * 0.08) + 's');
        });

        wrap.classList.add('anime-cards-pending');
        void wrap.offsetHeight;

        Promise.all(cards.map(function (node) {
            var image = node.querySelector('.anime-cover img');
            return image ? ensureAnimeCardImageLoaded(image) : Promise.resolve();
        })).then(function () {
            if (generation !== animeRevealGeneration) {
                return;
            }

            wrap.classList.remove('anime-cards-pending');
            window.requestAnimationFrame(function () {
                if (generation !== animeRevealGeneration) {
                    return;
                }

                cards.forEach(function (node) {
                    node.classList.add('is-visible');
                });
            });
        });
    }

    function applyStatusFilter(status, syncUrl) {
        animeFilterState.status = status || 'all';

        document.querySelectorAll('.anime-stats-item[data-filter]').forEach(function (item) {
            var isActive = item.getAttribute('data-filter') === animeFilterState.status;
            item.classList.toggle('active', isActive);
            item.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        applyAnimeListFilters(1, false);
        replayAnimeCardReveal();

        if (syncUrl) {
            syncStatusUrl(animeFilterState.status);
        }
    }

    function matchesAnimeSearch(haystack, query) {
        var normalizedHaystack = String(haystack || '').toLowerCase().trim();
        var normalizedQuery = String(query || '').toLowerCase().trim();

        if (!normalizedQuery) {
            return true;
        }

        var tokens = normalizedQuery.split(/\s+/);
        for (var i = 0; i < tokens.length; i++) {
            if (!tokens[i]) {
                continue;
            }
            if (normalizedHaystack.indexOf(tokens[i]) === -1) {
                return false;
            }
        }

        return true;
    }

    function applyAnimeListFilters(page, syncHistory) {
        var searchInput = document.getElementById('anime-search-input');
        var query = searchInput ? searchInput.value.trim() : '';
        var visibleCount = 0;

        animeFilterState.poolItems.forEach(function (item) {
            var itemStatus = item.getAttribute('data-status') || 'planned';
            var statusMatched = animeFilterState.status === 'all' || itemStatus === animeFilterState.status;
            var searchMatched = matchesAnimeSearch(item.getAttribute('data-search-text') || '', query);

            item.classList.toggle('is-visible', statusMatched);
            item.classList.toggle('is-hidden', !statusMatched);
            item.classList.toggle('is-search-hidden', statusMatched && !searchMatched);

            if (statusMatched && searchMatched) {
                visibleCount++;
            }
        });

        if (animeFilterState.searchEmpty) {
            animeFilterState.searchEmpty.hidden = visibleCount > 0;
        }

        applyAnimePagination(page || 1, !!syncHistory);
        refreshAnimeRevealTargets();
    }

    function initAnimePagination() {
        animePagination.nav = document.getElementById('anime-pagination');
        if (!animePagination.nav) {
            return;
        }

        animePagination.infoEl = document.getElementById('anime-pagination-info');
        animePagination.currentEl = document.getElementById('anime-pagination-current');
        animePagination.inputEl = document.getElementById('anime-page-input');
        animePagination.goBtn = document.getElementById('anime-page-go');
        animePagination.listEl = document.querySelector('.anime-list');
        animePagination.prevBtn = animePagination.nav.querySelector('[data-action="prev"]');
        animePagination.nextBtn = animePagination.nav.querySelector('[data-action="next"]');
        animePagination.perPage = parseInt(animePagination.nav.getAttribute('data-per-page'), 10) || 6;
        animePagination.currentPage = parseInt(animePagination.nav.getAttribute('data-current-page'), 10) || 1;

        if (animePagination.prevBtn) {
            animePagination.prevBtn.addEventListener('click', function () {
                goToAnimePage(animePagination.currentPage - 1);
            });
        }

        if (animePagination.nextBtn) {
            animePagination.nextBtn.addEventListener('click', function () {
                goToAnimePage(animePagination.currentPage + 1);
            });
        }

        if (animePagination.goBtn && animePagination.inputEl) {
            animePagination.goBtn.addEventListener('click', function () {
                goToAnimePage(animePagination.inputEl.value);
            });

            animePagination.inputEl.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    goToAnimePage(animePagination.inputEl.value);
                }
            });
        }
    }

    function getEligibleAnimeItems() {
        return Array.prototype.slice.call(
            document.querySelectorAll('.anime-item.is-visible:not(.is-search-hidden)')
        );
    }

    function getAnimeTotalPages(itemCount) {
        if (itemCount <= 0) {
            return 1;
        }
        return Math.max(1, Math.ceil(itemCount / animePagination.perPage));
    }

    function applyAnimePagination(page, syncHistory) {
        if (!animePagination.nav) {
            return;
        }

        var items = getEligibleAnimeItems();
        var totalItems = items.length;
        var totalPages = getAnimeTotalPages(totalItems);
        var targetPage = parseInt(page, 10);

        if (isNaN(targetPage) || targetPage < 1) {
            targetPage = 1;
        }
        if (targetPage > totalPages) {
            targetPage = totalPages;
        }

        animePagination.currentPage = targetPage;

        items.forEach(function (item, index) {
            var itemPage = Math.floor(index / animePagination.perPage) + 1;
            item.classList.toggle('is-page-hidden', itemPage !== targetPage);
        });

        document.querySelectorAll('.anime-item.is-visible:not(.is-search-hidden)').forEach(function (item) {
            if (items.indexOf(item) === -1) {
                item.classList.add('is-page-hidden');
            }
        });

        renderAnimePagination(totalItems, totalPages, targetPage);

        if (syncHistory) {
            syncAnimePageUrl(targetPage);
        }

        refreshAnimeRevealTargets();
    }

    function renderAnimePagination(totalItems, totalPages, currentPage) {
        if (!animePagination.nav) {
            return;
        }

        animePagination.nav.hidden = totalPages <= 1;
        animePagination.nav.setAttribute('data-total-pages', String(totalPages));
        animePagination.nav.setAttribute('data-total-items', String(totalItems));
        animePagination.nav.setAttribute('data-current-page', String(currentPage));

        if (animePagination.infoEl) {
            animePagination.infoEl.textContent = '共 ' + totalItems + ' 条 · 第 ' + currentPage + ' / ' + totalPages + ' 页';
        }

        if (animePagination.currentEl) {
            animePagination.currentEl.textContent = String(currentPage);
        }

        if (animePagination.inputEl) {
            animePagination.inputEl.max = String(totalPages);
            animePagination.inputEl.value = String(currentPage);
        }

        if (animePagination.prevBtn) {
            animePagination.prevBtn.disabled = currentPage <= 1;
        }

        if (animePagination.nextBtn) {
            animePagination.nextBtn.disabled = currentPage >= totalPages;
        }
    }

    function syncAnimePageUrl(page) {
        var url = new URL(window.location.href);
        if (page > 1) {
            url.searchParams.set('anime_paged', String(page));
        } else {
            url.searchParams.delete('anime_paged');
        }
        window.history.replaceState({}, '', url.toString());
    }

    function goToAnimePage(page) {
        applyAnimePagination(page, true);

        if (animePagination.listEl) {
            animePagination.listEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    function initAnimeSearch() {
        var input = document.getElementById('anime-search-input');
        if (!input) {
            if (animePagination.nav) {
                applyAnimePagination(animePagination.currentPage, false);
            }
            return;
        }

        var field = input.closest('.anime-search-field');
        var clearBtn = document.querySelector('.anime-search-clear');
        var debounceTimer = null;

        function syncFloatingLabel() {
            if (!field) {
                return;
            }
            field.classList.toggle('is-filled', input.value.trim() !== '');
        }

        function syncSearchUrl(query) {
            var url = new URL(window.location.href);
            if (query) {
                url.searchParams.set('anime_search', query);
            } else {
                url.searchParams.delete('anime_search');
            }
            url.searchParams.delete('anime_paged');
            window.history.replaceState({}, '', url.toString());
        }

        function applySearch(resetPage) {
            if (clearBtn) {
                clearBtn.hidden = input.value.trim() === '';
            }

            applyAnimeListFilters(resetPage ? 1 : (animePagination.currentPage || 1), false);
        }

        input.addEventListener('input', function () {
            syncFloatingLabel();
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(function () {
                applySearch(true);
                syncSearchUrl(input.value.trim());
            }, 180);
        });

        input.addEventListener('search', function () {
            applySearch(true);
            syncSearchUrl(input.value.trim());
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                input.value = '';
                syncFloatingLabel();
                applySearch(true);
                syncSearchUrl('');
                input.focus();
            });
        }

        syncFloatingLabel();
        if (input.value.trim() !== '') {
            applySearch(true);
        } else if (animePagination.nav) {
            applyAnimePagination(animePagination.currentPage, false);
        }
    }

    function eventElement(event) {
        var target = event && event.target;
        if (target && target.nodeType !== 1) {
            target = target.parentElement;
        }
        return target && target.closest ? target : null;
    }

    function createAnimeTagPopup() {
        var popup = document.createElement('div');
        popup.className = 'anime-tag-popup';
        popup.id = 'anime-tag-popup';
        popup.hidden = true;
        popup.innerHTML =
            '<div class="anime-tag-popup-backdrop" data-close-popup></div>' +
            '<div class="anime-tag-popup-panel" role="dialog" aria-modal="true" aria-labelledby="anime-tag-popup-title">' +
                '<div class="anime-tag-popup-header">' +
                    '<span class="anime-tag-popup-title" id="anime-tag-popup-title"></span>' +
                    '<button type="button" class="anime-tag-popup-close" aria-label="关闭" data-close-popup>&times;</button>' +
                '</div>' +
                '<div class="anime-tag-popup-masonry" id="anime-tag-popup-masonry"></div>' +
            '</div>';
        return popup;
    }

    function ensureAnimeTagPopup() {
        var popups = Array.prototype.slice.call(document.querySelectorAll('.anime-tag-popup'));
        var popup = popups[0] || createAnimeTagPopup();

        popups.forEach(function (node, index) {
            if (index > 0) {
                node.remove();
            }
        });

        if (popup.parentElement !== document.body) {
            document.body.appendChild(popup);
        }

        popup.hidden = true;
        popup.classList.remove('is-visible');
        popup.setAttribute('aria-hidden', 'true');
        return popup;
    }

    function getTagPopupRefs() {
        var popup = document.querySelector('body > .anime-tag-popup') ||
            document.getElementById('anime-tag-popup') ||
            ensureAnimeTagPopup();
        if (!popup) {
            return null;
        }

        var panel = popup.querySelector('.anime-tag-popup-panel');
        var titleEl = popup.querySelector('#anime-tag-popup-title');
        var masonryEl = popup.querySelector('#anime-tag-popup-masonry');
        if (!panel || !titleEl || !masonryEl) {
            popup.remove();
            popup = ensureAnimeTagPopup();
            panel = popup.querySelector('.anime-tag-popup-panel');
            titleEl = popup.querySelector('#anime-tag-popup-title');
            masonryEl = popup.querySelector('#anime-tag-popup-masonry');
        }

        if (!panel || !titleEl || !masonryEl) {
            return null;
        }

        return {
            popup: popup,
            panel: panel,
            titleEl: titleEl,
            masonryEl: masonryEl
        };
    }

    function handleAnimeTagPageClick(event) {
        var target = eventElement(event);
        if (!target) {
            return;
        }

        var trigger = target.closest('.anime-tag-more-btn');
        if (trigger) {
            event.preventDefault();
            event.stopPropagation();
            if (event.stopImmediatePropagation) {
                event.stopImmediatePropagation();
            }
            var refs = getTagPopupRefs();
            if (refs) {
                openTagPopup(trigger, refs.popup, refs.panel, refs.titleEl, refs.masonryEl);
            }
            return;
        }

        var current = getTagPopupRefs();
        if (!current || current.popup.hidden || !current.popup.classList.contains('is-visible')) {
            return;
        }

        if (target.closest('[data-close-popup]') || !target.closest('.anime-tag-popup-panel')) {
            closeTagPopup(current.popup, current.panel);
        }
    }

    function bindAnimeTagPopupEvents() {
        window.__shirokiAnimeHandleTagClick = handleAnimeTagPageClick;

        if (window.__shirokiAnimePageDocBound) {
            return;
        }

        window.__shirokiAnimePageDocBound = true;

        document.addEventListener('click', function (event) {
            if (typeof window.__shirokiAnimeHandleTagClick === 'function') {
                window.__shirokiAnimeHandleTagClick(event);
            }
        });

        document.addEventListener('keydown', function (event) {
            var current = getTagPopupRefs();
            if (event.key === 'Escape' && current && !current.popup.hidden) {
                closeTagPopup(current.popup, current.panel);
            }
        });

        window.addEventListener('resize', function () {
            var current = getTagPopupRefs();
            if (current && !current.popup.hidden && activeTrigger) {
                positionTagPopup(activeTrigger, current.panel);
            }
        });

        window.addEventListener('scroll', function () {
            var current = getTagPopupRefs();
            if (current && !current.popup.hidden && activeTrigger) {
                positionTagPopup(activeTrigger, current.panel);
            }
        }, true);
    }

    function initTagPopup() {
        ensureAnimeTagPopup();
        bindAnimeTagPopupEvents();
    }

    function collectExtraTags(trigger) {
        var items = trigger.querySelectorAll('.anime-tag-more-item');
        var tags = [];

        if (items.length) {
            Array.prototype.forEach.call(items, function (item) {
                var text = (item.textContent || '').trim();
                if (text) {
                    tags.push(text);
                }
            });
            return tags;
        }

        try {
            tags = JSON.parse(trigger.getAttribute('data-extra-tags') || '[]');
        } catch (error) {
            tags = [];
        }

        return Array.isArray(tags) ? tags : [];
    }

    function openTagPopup(trigger, popup, panel, titleEl, masonryEl) {
        var label = trigger.getAttribute('data-tag-label') || '标签';
        var type = trigger.getAttribute('data-tag-type') || 'category';
        var extraTags = collectExtraTags(trigger);

        if (!extraTags.length) {
            return;
        }

        activeTrigger = trigger;
        titleEl.textContent = label + ' · 更多';
        masonryEl.innerHTML = '';
        masonryEl.className = 'anime-tag-popup-masonry anime-tag-popup-masonry-' + type;

        extraTags.forEach(function (tag) {
            if (!tag) {
                return;
            }

            var item = document.createElement('span');
            item.className = 'anime-tag anime-tag-' + type + ' anime-tag-popup-item';
            item.textContent = String(tag);
            masonryEl.appendChild(item);
        });

        popup.hidden = false;
        popup.removeAttribute('hidden');
        popup.classList.add('is-visible');
        popup.setAttribute('aria-hidden', 'false');

        requestAnimationFrame(function () {
            requestAnimationFrame(function () {
                positionTagPopup(trigger, panel);
            });
        });
    }

    function closeTagPopup(popup, panel) {
        popup.hidden = true;
        popup.setAttribute('hidden', '');
        popup.classList.remove('is-visible');
        popup.setAttribute('aria-hidden', 'true');
        panel.style.top = '';
        panel.style.left = '';
        activeTrigger = null;
    }

    function positionTagPopup(trigger, panel) {
        var rect = trigger.getBoundingClientRect();
        var panelRect = panel.getBoundingClientRect();
        var margin = 12;
        var offset = 8;
        var top = rect.bottom + offset;
        var left = rect.left;

        if (left + panelRect.width > window.innerWidth - margin) {
            left = window.innerWidth - panelRect.width - margin;
        }

        if (left < margin) {
            left = margin;
        }

        if (top + panelRect.height > window.innerHeight - margin) {
            top = rect.top - panelRect.height - offset;
        }

        if (top < margin) {
            top = margin;
        }

        panel.style.top = top + 'px';
        panel.style.left = left + 'px';
    }

    function initProgressBars() {
        var progressBars = document.querySelectorAll('.anime-progress-fill');
        if (!('IntersectionObserver' in window) || !progressBars.length) {
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    var bar = entry.target;
                    var width = bar.getAttribute('style') || '';
                    bar.style.width = '0%';
                    requestAnimationFrame(function () {
                        bar.setAttribute('style', width);
                    });
                    observer.unobserve(bar);
                }
            });
        }, { threshold: 0.2 });

        progressBars.forEach(function (bar) {
            observer.observe(bar);
        });
    }

    function initAnimeCardTooltips() {
        var cards = document.querySelectorAll('.anime-card[data-shiroki-title]');
        if (!cards.length || !window.ShirokiBubbleTooltip) {
            return;
        }

        cards.forEach(function (card) {
            var title = card.getAttribute('data-shiroki-title');
            if (!title) {
                return;
            }

            card.addEventListener('mouseenter', function () {
                window.ShirokiBubbleTooltip.show(title, card, {
                    type: 'info',
                    panel: true,
                    placement: 'top'
                });
            });

            card.addEventListener('mouseleave', function () {
                window.ShirokiBubbleTooltip.hide();
            });
        });
    }

    function bindAnimeExternalLinks() {
        if (window.__shirokiAnimeExternalLinksBound) {
            return;
        }

        window.__shirokiAnimeExternalLinksBound = true;

        document.addEventListener('click', function (event) {
            var link = event.target.closest('a.anime-cover-link-tag, .anime-page-wrap .anime-title a');
            if (!link) {
                return;
            }

            var href = link.getAttribute('href');
            if (!href) {
                return;
            }

            event.preventDefault();
            event.stopPropagation();
            window.open(href, '_blank', 'noopener,noreferrer');
        });
    }

    function initAnimePage() {
        var wrap = document.querySelector('.anime-page-wrap');
        if (!wrap) {
            return;
        }

        initTagPopup();

        if (wrap === boundAnimeWrap) {
            return;
        }

        boundAnimeWrap = wrap;
        wrap.removeAttribute('data-anime-page-ready');
        animeCardsReady = false;
        activeTrigger = null;

        if (animeRevealObserver) {
            animeRevealObserver.disconnect();
            animeRevealObserver = null;
        }

        window.__shirokiAnimeBoundWrap = wrap;
        initStatusFilter();
        bindFilterNavigation('.anime-tag-filter-item[data-filter-type="category"]', 'anime_category');
        bindFilterNavigation('.anime-tag-filter-item[data-filter-type="protagonist"]', 'anime_protagonist');
        bindFilterNavigation('.anime-tag-filter-item[data-filter-type="voice"]', 'anime_voice_actor');
        initAnimeReveal();
        initAnimePagination();
        initAnimeSearch();
        initProgressBars();
        initAnimeCardTooltips();
        bindAnimeExternalLinks();
    }

    window.initShirokiAnimePage = initAnimePage;

    if (!window.__shirokiAnimePageBooted) {
        window.__shirokiAnimePageBooted = true;
        document.addEventListener('shiroki:content:loaded', function () {
            if (typeof window.initShirokiAnimePage === 'function') {
                window.initShirokiAnimePage();
            }
        });
    }

    if (document.readyState !== 'loading') {
        initAnimePage();
    } else {
        document.addEventListener('DOMContentLoaded', initAnimePage);
    }
})();
