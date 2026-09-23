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
    constructor(button) { super(); this.button = button; this.method = 'post'; this.action = '/action'; }
    hasAttribute() { return false; }
    querySelector() { return this.button; }
    querySelectorAll() { return []; }
}

function setup(storage = new Map()) {
    const body = new Element();
    const timers = [];
    let submit;
    let resolveRequest;
    let request;
    let destination;
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
        window: {location: {assign: value => { destination = value; }}},
        sessionStorage: {
            getItem: key => storage.get(key) ?? null,
            setItem: (key, value) => storage.set(key, value),
            removeItem: key => storage.delete(key),
        },
        setTimeout: callback => { timers.push(callback); return timers.length; },
        clearTimeout() {},
        fetch: (url, options) => {
            request = options;
            return new Promise(resolve => { resolveRequest = resolve; });
        },
    };
    vm.runInNewContext(source, sandbox);
    return {
        submit: (form, button) => submit({target: form, submitter: button, preventDefault() {}, defaultPrevented: false}),
        respond: (data, ok = true) => resolveRequest({ok, status: ok ? 200 : 403, json: async () => data}),
        notice: () => body.children.find(node => !node.removed),
        request: () => request,
        timers, storage, destination: () => destination,
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
