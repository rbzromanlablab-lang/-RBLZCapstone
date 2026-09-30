const {test} = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');

const source = fs.readFileSync(path.join(__dirname, '../../resources/views/partials/ajax-forms.blade.php'), 'utf8')
    .match(/<script>([\s\S]*?)<\/script>/)[1];

class Element {
    constructor(text = '') {
        this.textContent = text;
        this.innerHTML = text;
        this.dataset = {};
        this.children = [];
        this.classList = {add() {}, remove() {}};
    }
    setAttribute(name, value) { this[name] = value; }
    removeAttribute(name) { delete this[name]; }
    append(...children) { this.children.push(...children); }
    replaceChildren(...children) { this.children = children; }
    remove() { this.removed = true; }
}
class Input extends Element {}
class Form extends Element {
    constructor(button) {
        super();
        this.button = button;
        this.method = 'post';
        this.action = '/action';
        this.token = new Input();
        this.token.value = 'stale-token';
    }
    hasAttribute() { return false; }
    querySelector(selector = '') { return selector.includes('_token') ? this.token : this.button; }
    querySelectorAll() { return []; }
}

function setup(storage = new Map()) {
    const body = new Element();
    const timers = [];
    let submit;
    const resolvers = [];
    let request;
    const requests = [];
    let destination;
    let navigationMethod;
    const document = {
        body,
        createElement: () => new Element(),
        createTextNode: text => ({textContent: text}),
        querySelector: () => body.children.find(node => !node.removed),
        addEventListener: (name, callback) => { if (name === 'submit') submit = callback; },
    };
    const sandbox = {
        document, HTMLFormElement: Form, HTMLInputElement: Input,
        FormData: class extends Map {
            constructor() { super(); }
            append(key, value) { this.set(key, value); }
        },
        CSS: {escape: value => value},
        window: {location: {
            href: '/login',
            assign: value => { destination = value; navigationMethod = 'assign'; },
            replace: value => { destination = value; navigationMethod = 'replace'; },
        }},
        DOMParser: class {
            parseFromString() {
                return {querySelector: () => ({value: 'fresh-token'})};
            }
        },
        sessionStorage: {
            getItem: key => storage.get(key) ?? null,
            setItem: (key, value) => storage.set(key, value),
            removeItem: key => storage.delete(key),
        },
        setTimeout: callback => { timers.push(callback); return timers.length; },
        clearTimeout() {},
        fetch: (url, options) => {
            request = options;
            requests.push({url, options});
            return new Promise(resolve => { resolvers.push(resolve); });
        },
    };
    vm.runInNewContext(source, sandbox);
    return {
        submit: (form, button) => submit({target: form, submitter: button, preventDefault() {}, defaultPrevented: false}),
        respond: (data, ok = true, status = ok ? 200 : 403) => resolvers.shift()({
            ok, status, json: async () => data, text: async () => String(data),
        }),
        respondHtml: (html, ok = true) => resolvers.shift()({
            ok, status: ok ? 200 : 500, json: async () => ({}), text: async () => html,
        }),
        notice: () => body.children.find(node => !node.removed),
        request: () => request, requests: () => requests,
        timers, storage, destination: () => destination, navigationMethod: () => navigationMethod,
    };
}

test('logout uses the clicked button, submits its value, and carries confirmation across redirect', async () => {
    const page = setup();
    const firstButton = new Element('Save User');
    const logout = new Element('Logout');
    logout.name = 'action';
    logout.value = 'logout';
    const form = new Form(firstButton);
    const pending = page.submit(form, logout);
    assert.equal(logout.children[1].textContent, 'Logging out…');
    assert.equal(firstButton.disabled, undefined);
    assert.equal(page.request().body.get('action'), 'logout');
    page.respond({message: 'Logged out successfully.', redirect: '/login'});
    await pending;
    assert.equal(page.notice().children[0].children[1].textContent, 'Logout');
    assert.equal(page.notice().children[1].textContent, 'Logged out successfully.');
    page.timers.at(-1)();
    assert.equal(page.destination(), '/login');
    assert.equal(page.navigationMethod(), 'replace');
    const loginPage = setup(page.storage);
    assert.equal(loginPage.notice().children[1].textContent, 'Logged out successfully.');
    assert.equal(page.storage.size, 0);
});

test('OTP submission displays sending feedback and restores the button after failure', async () => {
    const page = setup();
    const button = new Element('Send OTP');
    const form = new Form(button);
    const pending = page.submit(form, button);
    assert.equal(button.children[1].textContent, 'Sending OTP…');
    page.respond({message: 'Email could not be sent.'}, false);
    await pending;
    assert.equal(button.innerHTML, 'Send OTP');
    assert.equal(button.disabled, false);
    assert.equal(button['aria-busy'], undefined);
    assert.equal(form.dataset.ajaxSubmitting, undefined);
    assert.equal(page.notice().children[0].children[1].textContent, 'Action needed');
    assert.equal(page.storage.size, 0);
});

test('input submit buttons get action-specific feedback and keep their original label', async () => {
    const page = setup();
    const button = new Input();
    button.value = 'Delete Property';
    const pending = page.submit(new Form(button), button);
    assert.equal(button.value, 'Deleting Property…');
    page.respond({message: 'Property deleted successfully.'});
    await pending;
    assert.equal(button.value, 'Delete Property');
    assert.equal(button.disabled, false);
    assert.equal(page.notice().children[0].children[1].textContent, 'Delete Property');
});

test('enter-key submission uses the form submit button and preserves the server message', async () => {
    const page = setup();
    const button = new Element('Save Decision');
    const pending = page.submit(new Form(button), null);
    assert.equal(button.children[1].textContent, 'Saving Decision…');
    page.respond({message: 'Request approved successfully.'});
    await pending;
    assert.equal(page.notice().children[1].textContent, 'Request approved successfully.');
});

test('an expired CSRF token refreshes the page token and retries the submission', async () => {
    const page = setup();
    const button = new Element('Log in');
    const form = new Form(button);
    const pending = page.submit(form, button);

    page.respond({message: 'Your session expired.'}, false, 419);
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(page.requests().length, 2);
    assert.equal(page.requests()[1].url, '/login');
    assert.equal(page.requests()[1].options.cache, 'no-store');

    page.respondHtml('<input name="_token" value="fresh-token">');
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(page.requests().length, 3);
    assert.equal(form.token.value, 'fresh-token');
    assert.equal(page.requests()[2].options.body.get('_token'), 'fresh-token');

    page.respond({message: 'Logged in successfully.'});
    await pending;
    assert.equal(page.notice().children[1].textContent, 'Logged in successfully.');
});
