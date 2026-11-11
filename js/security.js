/* Shared output encoding and same-origin synchronizer-token handling. */
function escapeHTML(value) {
    return String(value ?? '').replace(/[&<>"'`]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','`':'&#96;'}[c]));
}
function safeImageUrl(value) {
    try {
        const url = new URL(String(value ?? ''), document.baseURI);
        if (!['http:', 'https:'].includes(url.protocol) || url.username || url.password) throw new Error('Invalid image URL');
        return url.href;
    } catch { return new URL('img/placeholder.svg', document.baseURI).href; }
}
function jsArg(value) { return escapeHTML(JSON.stringify(value)); }
function readItems(key) {
    try {
        const items = JSON.parse(localStorage.getItem(key) || '[]');
        if (!Array.isArray(items)) return [];
        return items.filter(item => item && Number.isSafeInteger(Number(item.id)) && Number(item.id) > 0 && Number.isFinite(Number(item.price)) && Number(item.price) >= 0)
            .map(item => ({...item, id:Number(item.id), price:Number(item.price), name:String(item.name ?? ''), image:String(item.image ?? ''), color:typeof item.color==='string' ? item.color : null, quantity:Math.max(1, Math.min(100, Number.parseInt(item.quantity,10) || 1))}));
    } catch { return []; }
}
function addProductToCart(id) {
    const product = products.find(p => p.id === Number(id));
    if (product) addToCart({id: product.id, name: product.name, price: product.price, image: product.image});
}
(() => {
    const originalFetch = window.fetch.bind(window);
    let tokenPromise;
    const token = () => tokenPromise ??= originalFetch('php/csrf.php', {credentials:'same-origin', cache:'no-store'})
        .then(r => { if (!r.ok) throw new Error('Form token unavailable'); return r.json(); }).then(data => data.token);
    // Checkout must establish this same session before issuing its retry token.
    window.getCsrfToken = token;
    window.fetch = async (input, options = {}) => {
        const url = new URL(typeof input === 'string' ? input : input.url, document.baseURI);
        const method = String(options.method || (typeof input !== 'string' && input.method) || 'GET').toUpperCase();
        if (url.origin === location.origin) {
            // Establish one session before parallel catalog/token requests can
            // each create a different cookie on a customer's first page load.
            const csrf = await token();
            if (!['GET','HEAD','OPTIONS'].includes(method)) {
                const headers = new Headers(options.headers || (typeof input !== 'string' && input.headers) || {});
                headers.set('X-CSRF-Token', csrf);
                options = {...options, headers};
            }
        }
        return originalFetch(input, options);
    };
    document.addEventListener('DOMContentLoaded', () => {
        for (const form of document.forms) {
            if (form.method.toUpperCase() !== 'POST' || new URL(form.action).origin !== location.origin) continue;
            const ready = token().then(value => {
                let field = form.querySelector('input[name="csrf_token"]');
                if (!field) { field = document.createElement('input'); field.type='hidden'; field.name='csrf_token'; form.append(field); }
                field.value=value;
            });
            form.addEventListener('submit', event => {
                if (form.querySelector('input[name="csrf_token"]')?.value) return;
                event.preventDefault(); event.stopImmediatePropagation();
                ready.then(() => form.requestSubmit(event.submitter)).catch(() => alert('Reload the page before submitting.'));
            }, true);
        }
    });
})();
