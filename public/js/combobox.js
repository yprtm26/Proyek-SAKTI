/**
 * Komponen combobox aksesibel SAKTI (B-08).
 *
 * Bagian logika murni (filterOptions, moveIndex, clampIndex, resolveKey)
 * dipisahkan dari DOM agar dapat diuji dengan `node --test`.
 */

export const normalizeText = (value) => String(value ?? '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim();

/**
 * Cari opsi berdasarkan label, deskripsi, dan kode.
 * Setiap kata pada pencarian harus muncul (AND), sehingga
 * "teknik informatika" tetap menemukan "Teknik Informatika".
 */
export function filterOptions(options, query, keys = ['label', 'description', 'kode', 'nidn']) {
    const list = Array.isArray(options) ? options : [];
    const term = normalizeText(query);

    if (term === '') {
        return list.slice();
    }

    const tokens = term.split(/\s+/).filter(Boolean);

    return list.filter((option) => {
        const haystack = normalizeText(
            keys.map((key) => option?.[key]).filter(Boolean).join(' ')
        );

        return tokens.every((token) => haystack.includes(token));
    });
}

export const clampIndex = (index, count) => {
    if (count <= 0) {
        return -1;
    }

    return Math.min(Math.max(index, 0), count - 1);
};

/**
 * Geser indeks aktif dengan pembungkusan (wrap-around).
 */
export const moveIndex = (index, count, delta) => {
    if (count <= 0) {
        return -1;
    }

    if (index < 0) {
        return delta > 0 ? 0 : count - 1;
    }

    return (index + delta + count) % count;
};

/**
 * Terjemahkan tombol keyboard menjadi aksi combobox.
 *
 * Aksi: open | close | select | move | first | last | none
 * - Tab saat tertutup membuka dropdown (fokus pindah ke kolom cari).
 * - Tab saat terbuka menutup dropdown dan membiarkan fokus berpindah.
 * - Enter membuka saat tertutup dan memilih opsi aktif saat terbuka.
 * - Escape menutup dan mengembalikan fokus ke tombol pemicu.
 */
export function resolveKey(key, state = {}) {
    const {
        open = false,
        activeIndex = -1,
        count = 0,
        isTrigger = false,
    } = state;

    switch (key) {
        case 'Tab':
            if (open) {
                return { type: 'close', allowTab: true, restoreFocus: false };
            }

            return isTrigger ? { type: 'open' } : { type: 'none', allowTab: true };

        case 'Escape':
        case 'Esc':
            return open ? { type: 'close', restoreFocus: true } : { type: 'none' };

        case 'Enter':
            if (!open) {
                return { type: 'open' };
            }

            return count > 0 ? { type: 'select', index: activeIndex } : { type: 'none' };

        case ' ':
        case 'Spacebar':
            // Spasi hanya membuka/memilih dari tombol; di kolom cari
            // spasi harus tetap bisa diketik.
            if (!isTrigger) {
                return { type: 'none' };
            }

            return open ? { type: 'select', index: activeIndex } : { type: 'open' };

        case 'ArrowDown':
            return open ? { type: 'move', delta: 1 } : { type: 'open' };

        case 'ArrowUp':
            return open ? { type: 'move', delta: -1 } : { type: 'open' };

        case 'Home':
            return open ? { type: 'first' } : { type: 'none' };

        case 'End':
            return open ? { type: 'last' } : { type: 'none' };

        default:
            return { type: 'none' };
    }
}

const parseJson = (value, fallback) => {
    try {
        const parsed = JSON.parse(value ?? '');
        return parsed ?? fallback;
    } catch (error) {
        return fallback;
    }
};

/**
 * Pasang perilaku combobox pada satu elemen [data-combobox].
 */
export function createCombobox(root) {
    if (!root || root.dataset.comboboxReady === 'true') {
        return;
    }

    const trigger = root.querySelector('[data-combobox-trigger]');
    const search = root.querySelector('[data-combobox-search]');
    const popup = root.querySelector('[data-combobox-popup]');
    const listbox = root.querySelector('[data-combobox-list]');
    const hidden = root.querySelector('[data-combobox-input]');
    const valueElement = root.querySelector('[data-combobox-value]');
    const loadingElement = root.querySelector('[data-combobox-loading]');
    const emptyElement = root.querySelector('[data-combobox-empty]');
    const errorElement = root.querySelector('[data-combobox-error]');

    if (!trigger || !search || !popup || !listbox || !hidden || !valueElement) {
        return;
    }

    root.dataset.comboboxReady = 'true';

    const endpoint = root.dataset.endpoint ?? '';
    const params = parseJson(root.dataset.params, {});
    const placeholder = root.dataset.placeholder ?? 'Pilih opsi...';
    const initialOptions = parseJson(root.dataset.options, []);
    const disabled = root.dataset.disabled === '1' || trigger.disabled;

    let allOptions = Array.isArray(initialOptions) ? initialOptions.slice() : [];
    let visibleOptions = allOptions.slice();
    let activeIndex = -1;
    let isOpen = false;
    let hasLoaded = false;
    let requestCounter = 0;

    const optionPrefix = listbox.id || `${trigger.id || 'combobox'}-listbox`;

    const clearError = () => {
        if (errorElement) {
            errorElement.textContent = '';
            errorElement.classList.add('hidden');
        }
    };

    const showError = (message) => {
        if (!errorElement) {
            return;
        }

        errorElement.textContent = message;
        errorElement.classList.remove('hidden');
    };

    const setActive = (index) => {
        activeIndex = clampIndex(index, visibleOptions.length);
        const items = Array.from(listbox.children);

        items.forEach((item, itemIndex) => {
            const isActive = itemIndex === activeIndex;
            item.setAttribute('aria-selected', isActive ? 'true' : 'false');
            item.classList.toggle('bg-orange-50', isActive);
            item.classList.toggle('text-gray-900', isActive);
        });

        if (activeIndex >= 0 && items[activeIndex]) {
            search.setAttribute('aria-activedescendant', items[activeIndex].id);
            items[activeIndex].scrollIntoView({ block: 'nearest' });
        } else {
            search.removeAttribute('aria-activedescendant');
        }
    };

    const render = (options) => {
        visibleOptions = Array.isArray(options) ? options : [];
        listbox.innerHTML = '';

        visibleOptions.forEach((option, index) => {
            const item = document.createElement('li');
            item.id = `${optionPrefix}-option-${index}`;
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', 'false');
            item.dataset.index = String(index);
            item.dataset.value = String(option.id ?? option.value ?? '');
            item.className = 'cursor-pointer px-3 py-2 text-sm hover:bg-orange-50 focus:bg-orange-50';

            const label = document.createElement('span');
            label.className = 'block font-medium text-gray-800';
            label.textContent = option.label ?? String(option.id ?? '');
            item.appendChild(label);

            if (option.description) {
                const description = document.createElement('span');
                description.className = 'block text-xs text-gray-500';
                description.textContent = option.description;
                item.appendChild(description);
            }

            item.addEventListener('mousedown', (event) => {
                event.preventDefault();
                select(index);
            });
            item.addEventListener('mousemove', () => setActive(index));

            listbox.appendChild(item);
        });

        if (emptyElement) {
            emptyElement.classList.toggle('hidden', visibleOptions.length > 0);
        }

        setActive(visibleOptions.length > 0 ? 0 : -1);
    };

    const select = (index) => {
        const option = visibleOptions[index];

        if (!option) {
            return;
        }

        const value = option.id ?? option.value ?? '';
        hidden.value = String(value);
        valueElement.textContent = option.label ?? String(value);
        valueElement.classList.remove('text-gray-400');
        valueElement.classList.add('text-gray-800');
        root.dataset.selectedLabel = option.label ?? '';

        hidden.dispatchEvent(new Event('change', { bubbles: true }));
        hidden.dispatchEvent(new CustomEvent('combobox:change', {
            bubbles: true,
            detail: { option },
        }));

        clearError();
        close({ restoreFocus: true });
    };

    const close = ({ restoreFocus = false } = {}) => {
        if (!isOpen) {
            return;
        }

        isOpen = false;
        popup.classList.add('hidden');
        trigger.setAttribute('aria-expanded', 'false');
        search.removeAttribute('aria-activedescendant');
        activeIndex = -1;

        if (restoreFocus) {
            trigger.focus();
        }
    };

    const load = async (query = '') => {
        if (endpoint === '') {
            return;
        }

        const requestId = ++requestCounter;

        loadingElement?.classList.remove('hidden');
        emptyElement?.classList.add('hidden');

        try {
            const url = new URL(endpoint, window.location.origin);

            Object.entries(params ?? {}).forEach(([key, value]) => {
                if (value !== null && value !== undefined && value !== '') {
                    url.searchParams.set(key, String(value));
                }
            });

            if (query !== '') {
                url.searchParams.set('q', query);
            }

            const response = await fetch(url.toString(), {
                headers: { Accept: 'application/json' },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const payload = await response.json();

            if (requestId !== requestCounter) {
                return;
            }

            allOptions = Array.isArray(payload?.data) ? payload.data : [];
            hasLoaded = true;
            render(filterOptions(allOptions, search.value));
        } catch (error) {
            if (requestId !== requestCounter) {
                return;
            }

            visibleOptions = [];
            listbox.innerHTML = '';

            if (emptyElement) {
                emptyElement.textContent = 'Gagal memuat data. Silakan coba lagi.';
                emptyElement.classList.remove('hidden');
            }
        } finally {
            if (requestId === requestCounter) {
                loadingElement?.classList.add('hidden');
            }
        }
    };

    const open = () => {
        if (isOpen || disabled) {
            return;
        }

        isOpen = true;
        popup.classList.remove('hidden');
        trigger.setAttribute('aria-expanded', 'true');
        search.value = '';
        render(filterOptions(allOptions, ''));
        search.focus();

        if (!hasLoaded) {
            load('');
        }
    };

    const applyAction = (action, event) => {
        switch (action.type) {
            case 'open':
                event.preventDefault();
                open();
                break;
            case 'select':
                event.preventDefault();
                select(activeIndex >= 0 ? activeIndex : 0);
                break;
            case 'move':
                event.preventDefault();
                setActive(activeIndex < 0
                    ? (action.delta > 0 ? 0 : visibleOptions.length - 1)
                    : moveIndex(activeIndex, visibleOptions.length, action.delta));
                break;
            case 'first':
                event.preventDefault();
                setActive(0);
                break;
            case 'last':
                event.preventDefault();
                setActive(visibleOptions.length - 1);
                break;
            case 'close':
                if (!action.allowTab) {
                    event.preventDefault();
                }

                close({ restoreFocus: Boolean(action.restoreFocus) });
                break;
            default:
                break;
        }
    };

    trigger.addEventListener('click', () => {
        if (isOpen) {
            close();
        } else {
            open();
        }
    });

    trigger.addEventListener('keydown', (event) => {
        if (disabled) {
            return;
        }

        const action = resolveKey(event.key, {
            open: isOpen,
            activeIndex,
            count: visibleOptions.length,
            isTrigger: true,
        });

        applyAction(action, event);
    });

    search.addEventListener('input', () => {
        clearError();
        render(filterOptions(allOptions, search.value));
    });

    search.addEventListener('keydown', (event) => {
        if (event.key === ' ') {
            return;
        }

        const action = resolveKey(event.key, {
            open: isOpen,
            activeIndex,
            count: visibleOptions.length,
            isTrigger: false,
        });

        applyAction(action, event);
    });

    document.addEventListener('mousedown', (event) => {
        if (isOpen && !root.contains(event.target)) {
            close();
        }
    });

    if (hidden.dataset.required === '1') {
        const form = root.closest('form');

        form?.addEventListener('submit', (event) => {
            if (hidden.value !== '') {
                return;
            }

            event.preventDefault();
            showError(`Silakan pilih ${root.dataset.label || 'opsi'} terlebih dahulu.`);
            trigger.focus();
            open();
        });
    }
}

export function initAll(scope = document) {
    if (!scope || typeof scope.querySelectorAll !== 'function') {
        return;
    }

    scope.querySelectorAll('[data-combobox]').forEach((element) => {
        createCombobox(element);
    });
}

if (typeof document !== 'undefined') {
    const boot = () => initAll(document);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot, { once: true });
    } else {
        boot();
    }

    if (typeof window !== 'undefined') {
        window.SaktiCombobox = { init: createCombobox, initAll };
    }
}
