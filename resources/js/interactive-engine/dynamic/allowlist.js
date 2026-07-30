/** Allowlisted library keys — must match SchemaValidator::ALLOWED_LIBRARIES */
export const ALLOWED_LIBRARIES = Object.freeze(['katex', 'icons', 'stickers', 'lottie', 'tts']);

export const ALLOWED_BLOCKS = Object.freeze(['text', 'math', 'icon', 'sticker', 'image', 'audio', 'scene']);

/**
 * Curated sticker/icon name → emoji fallback. Every key here also has a matching
 * SVG file at public/assets/emoji-svg/<key>.svg (extracted from Noto Emoji via
 * scripts/extract-emoji-svgs.cjs) — resolveSticker() prefers the SVG and only
 * falls back to the emoji glyph if the file fails to load or the name is unknown.
 */
export const STICKER_MAP = Object.freeze({
    apple: '🍎',
    banana: '🍌',
    grape: '🍇',
    cat: '🐱',
    dog: '🐶',
    lion: '🦁',
    star: '⭐',
    sun: '☀️',
    moon: '🌙',
    book: '📚',
    rocket: '🚀',
    ball: '⚽',
    car: '🚗',
    tree: '🌳',
    flower: '🌸',
    fish: '🐟',
    bird: '🐦',
    'number-1': '1️⃣',
    'number-2': '2️⃣',
    'number-3': '3️⃣',
    'number-4': '4️⃣',
    'number-5': '5️⃣',
    'number-6': '6️⃣',
    'number-7': '7️⃣',
    'number-8': '8️⃣',
    'number-9': '9️⃣',
    'number-10': '🔟',

    // animals
    tiger: '🐯',
    elephant: '🐘',
    monkey: '🐵',
    rabbit: '🐰',
    bear: '🐻',
    panda: '🐼',
    fox: '🦊',
    wolf: '🐺',
    horse: '🐴',
    cow: '🐮',
    pig: '🐷',
    sheep: '🐑',
    goat: '🐐',
    chicken: '🐔',
    duck: '🦆',
    penguin: '🐧',
    owl: '🦉',
    eagle: '🦅',
    parrot: '🦜',
    whale: '🐳',
    dolphin: '🐬',
    shark: '🦈',
    octopus: '🐙',
    turtle: '🐢',
    snake: '🐍',
    frog: '🐸',
    bee: '🐝',
    butterfly: '🦋',
    ladybug: '🐞',
    spider: '🕷️',
    snail: '🐌',
    camel: '🐫',
    kangaroo: '🦘',
    koala: '🐨',
    hedgehog: '🦔',
    'mouse-animal': '🐭',
    bat: '🦇',
    'tropical-fish': '🐠',

    // tech / computers
    computer: '🖥️',
    laptop: '💻',
    'computer-mouse': '🖱️',
    keyboard: '⌨️',
    printer: '🖨️',
    phone: '📱',
    telephone: '☎️',
    camera: '📷',
    'video-camera': '📹',
    tv: '📺',
    'video-game': '🎮',
    joystick: '🕹️',
    'floppy-disk': '💾',
    battery: '🔋',
    satellite: '🛰️',

    // food
    orange: '🍊',
    strawberry: '🍓',
    watermelon: '🍉',
    pineapple: '🍍',
    cherries: '🍒',
    peach: '🍑',
    pear: '🍐',
    carrot: '🥕',
    corn: '🌽',
    bread: '🍞',
    cheese: '🧀',
    pizza: '🍕',
    hamburger: '🍔',
    fries: '🍟',
    'ice-cream': '🍦',
    cake: '🎂',
    cookie: '🍪',
    'hot-dog': '🌭',

    // nature / weather
    'palm-tree': '🌴',
    cactus: '🌵',
    tulip: '🌷',
    rose: '🌹',
    sunflower: '🌻',
    'four-leaf-clover': '🍀',
    cloud: '☁️',
    rainbow: '🌈',
    snowflake: '❄️',
    lightning: '⚡',
    umbrella: '☂️',
    thermometer: '🌡️',

    // vehicles
    taxi: '🚕',
    bus: '🚌',
    truck: '🚚',
    ambulance: '🚑',
    'fire-engine': '🚒',
    'police-car': '🚓',
    bicycle: '🚲',
    motorcycle: '🏍️',
    airplane: '✈️',
    sailboat: '⛵',
    ship: '🚢',
    train: '🚆',
    helicopter: '🚁',

    // school / objects
    pencil: '✏️',
    pen: '🖋️',
    backpack: '🎒',
    'graduation-cap': '🎓',
    school: '🏫',
    ruler: '📏',
    scissors: '✂️',
    clipboard: '📋',
    calendar: '📅',
    'alarm-clock': '⏰',
    'light-bulb': '💡',
    'magnifying-glass': '🔍',
    globe: '🌍',
    key: '🔑',
    lock: '🔒',

    // sports
    basketball: '🏀',
    tennis: '🎾',
    baseball: '⚾',
    trophy: '🏆',
    medal: '🏅',
    running: '🏃',
    swimming: '🏊',

    // tools / science / misc
    gear: '⚙️',
    wrench: '🔧',
    hammer: '🛠️',
    microscope: '🔬',
    'test-tube': '🧪',
    stethoscope: '🩺',
    syringe: '💉',
    pill: '💊',
    'shopping-cart': '🛒',
    'money-bag': '💰',
    palette: '🎨',
    paintbrush: '🖌️',
    'puzzle-piece': '🧩',
    gem: '💎',
    compass: '🧭',
    anchor: '⚓',
    gift: '🎁',
    balloon: '🎈',
});

/** Sticker keys backed by a generated SVG file under public/assets/emoji-svg/. */
const SVG_KEYS = new Set(Object.keys(STICKER_MAP));

export function resolveSticker(name) {
    const key = String(name || '')
        .trim()
        .toLowerCase()
        .replace(/\s+/g, '-');
    if (STICKER_MAP[key]) {
        return SVG_KEYS.has(key)
            ? { kind: 'svg', key, value: STICKER_MAP[key], src: `/assets/emoji-svg/${key}.svg` }
            : { kind: 'emoji', value: STICKER_MAP[key] };
    }
    // raw single emoji
    if (/^\p{Extended_Pictographic}$/u.test(name?.trim?.() || '')) {
        return { kind: 'emoji', value: name.trim() };
    }
    return { kind: 'svg', key: 'star', value: STICKER_MAP.star, src: '/assets/emoji-svg/star.svg' };
}

function escAttr(s) {
    return String(s ?? '').replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;');
}

function escHtml(s) {
    return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
}

/**
 * Render a resolveSticker() result as HTML. SVGs render as an <img> sized to
 * the surrounding font-size (1em), with a hidden emoji sibling that onerror
 * reveals if the file fails to load — so a broken/missing SVG never breaks
 * the layout, it just silently falls back to the exact emoji shown before.
 */
export function stickerHtml(resolved) {
    if (!resolved) return '';
    if (resolved.kind === 'svg') {
        return `<span class="ile-sticker">`
            + `<img class="ile-sticker__img" src="${escAttr(resolved.src)}" alt="" `
            + `onerror="this.style.display='none';this.nextElementSibling.style.display='inline'">`
            + `<span class="ile-sticker__emoji" style="display:none" aria-hidden="true">${escHtml(resolved.value)}</span>`
            + `</span>`;
    }
    return `<span class="ile-sticker__emoji" aria-hidden="true">${escHtml(resolved.value)}</span>`;
}
