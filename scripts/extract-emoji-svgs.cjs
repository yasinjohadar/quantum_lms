// One-off extraction script: pulls curated icons from @iconify-json/noto into
// public/assets/emoji-svg/<key>.svg. Not part of the runtime build — run manually,
// then the temporary @iconify-json/noto devDependency can be removed.
const fs = require('fs');
const path = require('path');

const data = require('../node_modules/@iconify-json/noto/icons.json');
const OUT_DIR = path.join(__dirname, '..', 'public', 'assets', 'emoji-svg');

// key -> [iconify-noto name, emoji fallback]
const CURATED = {
  // existing STICKER_MAP keys — emoji fallback preserved exactly, now backed by SVG
  apple: ['red-apple', '🍎'],
  banana: ['banana', '🍌'],
  grape: ['grapes', '🍇'],
  cat: ['cat-face', '🐱'],
  dog: ['dog-face', '🐶'],
  lion: ['lion', '🦁'],
  star: ['star', '⭐'],
  sun: ['sun', '☀️'],
  moon: ['crescent-moon', '🌙'],
  book: ['books', '📚'],
  rocket: ['rocket', '🚀'],
  ball: ['soccer-ball', '⚽'],
  car: ['automobile', '🚗'],
  tree: ['deciduous-tree', '🌳'],
  flower: ['cherry-blossom', '🌸'],
  fish: ['fish', '🐟'],
  bird: ['bird', '🐦'],
  'number-1': ['keycap-1', '1️⃣'],
  'number-2': ['keycap-2', '2️⃣'],
  'number-3': ['keycap-3', '3️⃣'],
  'number-4': ['keycap-4', '4️⃣'],
  'number-5': ['keycap-5', '5️⃣'],
  'number-6': ['keycap-6', '6️⃣'],
  'number-7': ['keycap-7', '7️⃣'],
  'number-8': ['keycap-8', '8️⃣'],
  'number-9': ['keycap-9', '9️⃣'],
  'number-10': ['keycap-10', '🔟'],

  // animals
  tiger: ['tiger-face', '🐯'],
  elephant: ['elephant', '🐘'],
  monkey: ['monkey-face', '🐵'],
  rabbit: ['rabbit-face', '🐰'],
  bear: ['bear', '🐻'],
  panda: ['panda', '🐼'],
  fox: ['fox', '🦊'],
  wolf: ['wolf', '🐺'],
  horse: ['horse-face', '🐴'],
  cow: ['cow-face', '🐮'],
  pig: ['pig-face', '🐷'],
  sheep: ['ewe', '🐑'],
  goat: ['goat', '🐐'],
  chicken: ['chicken', '🐔'],
  duck: ['duck', '🦆'],
  penguin: ['penguin', '🐧'],
  owl: ['owl', '🦉'],
  eagle: ['eagle', '🦅'],
  parrot: ['parrot', '🦜'],
  whale: ['whale', '🐳'],
  dolphin: ['dolphin', '🐬'],
  shark: ['shark', '🦈'],
  octopus: ['octopus', '🐙'],
  turtle: ['turtle', '🐢'],
  snake: ['snake', '🐍'],
  frog: ['frog', '🐸'],
  bee: ['honeybee', '🐝'],
  butterfly: ['butterfly', '🦋'],
  ladybug: ['lady-beetle', '🐞'],
  spider: ['spider', '🕷️'],
  snail: ['snail', '🐌'],
  camel: ['camel', '🐫'],
  kangaroo: ['kangaroo', '🦘'],
  koala: ['koala', '🐨'],
  hedgehog: ['hedgehog', '🦔'],
  'mouse-animal': ['mouse-face', '🐭'],
  bat: ['bat', '🦇'],
  'tropical-fish': ['tropical-fish', '🐠'],

  // tech / computers
  computer: ['desktop-computer', '🖥️'],
  laptop: ['desktop-computer', '💻'],
  'computer-mouse': ['computer-mouse', '🖱️'],
  keyboard: ['keyboard', '⌨️'],
  printer: ['printer', '🖨️'],
  phone: ['mobile-phone', '📱'],
  telephone: ['telephone-receiver', '☎️'],
  camera: ['camera', '📷'],
  'video-camera': ['video-camera', '📹'],
  tv: ['television', '📺'],
  'video-game': ['video-game', '🎮'],
  joystick: ['joystick', '🕹️'],
  'floppy-disk': ['floppy-disk', '💾'],
  battery: ['battery', '🔋'],
  satellite: ['satellite', '🛰️'],

  // food
  orange: ['tangerine', '🍊'],
  strawberry: ['strawberry', '🍓'],
  watermelon: ['watermelon', '🍉'],
  pineapple: ['pineapple', '🍍'],
  cherries: ['cherries', '🍒'],
  peach: ['peach', '🍑'],
  pear: ['pear', '🍐'],
  carrot: ['carrot', '🥕'],
  corn: ['ear-of-corn', '🌽'],
  bread: ['bread', '🍞'],
  cheese: ['cheese-wedge', '🧀'],
  pizza: ['pizza', '🍕'],
  hamburger: ['hamburger', '🍔'],
  fries: ['french-fries', '🍟'],
  'ice-cream': ['ice-cream', '🍦'],
  cake: ['birthday-cake', '🎂'],
  cookie: ['cookie', '🍪'],
  'hot-dog': ['hot-dog', '🌭'],

  // nature / weather
  'palm-tree': ['palm-tree', '🌴'],
  cactus: ['cactus', '🌵'],
  tulip: ['tulip', '🌷'],
  rose: ['rose', '🌹'],
  sunflower: ['sunflower', '🌻'],
  'four-leaf-clover': ['four-leaf-clover', '🍀'],
  cloud: ['cloud', '☁️'],
  rainbow: ['rainbow', '🌈'],
  snowflake: ['snowflake', '❄️'],
  lightning: ['high-voltage', '⚡'],
  umbrella: ['umbrella', '☂️'],
  thermometer: ['thermometer', '🌡️'],

  // vehicles
  taxi: ['taxi', '🚕'],
  bus: ['bus', '🚌'],
  truck: ['delivery-truck', '🚚'],
  ambulance: ['ambulance', '🚑'],
  'fire-engine': ['fire-engine', '🚒'],
  'police-car': ['police-car', '🚓'],
  bicycle: ['bicycle', '🚲'],
  motorcycle: ['motorcycle', '🏍️'],
  airplane: ['airplane', '✈️'],
  sailboat: ['sailboat', '⛵'],
  ship: ['ship', '🚢'],
  train: ['train', '🚆'],
  helicopter: ['helicopter', '🚁'],

  // school / objects
  pencil: ['pencil', '✏️'],
  pen: ['fountain-pen', '🖋️'],
  backpack: ['backpack', '🎒'],
  'graduation-cap': ['graduation-cap', '🎓'],
  school: ['school', '🏫'],
  ruler: ['straight-ruler', '📏'],
  scissors: ['scissors', '✂️'],
  clipboard: ['clipboard', '📋'],
  calendar: ['calendar', '📅'],
  'alarm-clock': ['alarm-clock', '⏰'],
  'light-bulb': ['light-bulb', '💡'],
  'magnifying-glass': ['magnifying-glass-tilted-left', '🔍'],
  globe: ['globe-showing-europe-africa', '🌍'],
  key: ['key', '🔑'],
  lock: ['locked', '🔒'],

  // sports
  basketball: ['basketball', '🏀'],
  tennis: ['tennis', '🎾'],
  baseball: ['baseball', '⚾'],
  trophy: ['trophy', '🏆'],
  medal: ['sports-medal', '🏅'],
  running: ['person-running', '🏃'],
  swimming: ['person-swimming', '🏊'],

  // tools / science / misc
  gear: ['gear', '⚙️'],
  wrench: ['wrench', '🔧'],
  hammer: ['hammer-and-wrench', '🛠️'],
  microscope: ['microscope', '🔬'],
  'test-tube': ['test-tube', '🧪'],
  stethoscope: ['stethoscope', '🩺'],
  syringe: ['syringe', '💉'],
  pill: ['pill', '💊'],
  'shopping-cart': ['shopping-cart', '🛒'],
  'money-bag': ['money-bag', '💰'],
  palette: ['artist-palette', '🎨'],
  paintbrush: ['paintbrush', '🖌️'],
  'puzzle-piece': ['puzzle-piece', '🧩'],
  gem: ['gem-stone', '💎'],
  compass: ['compass', '🧭'],
  anchor: ['anchor', '⚓'],
  gift: ['wrapped-gift', '🎁'],
  balloon: ['balloon', '🎈'],
};

let ok = 0;
const missing = [];
const manifest = {};

for (const [key, [iconName, emoji]] of Object.entries(CURATED)) {
  const icon = data.icons[iconName];
  if (!icon) {
    missing.push(`${key} -> ${iconName}`);
    continue;
  }
  const width = icon.width || data.width || 128;
  const height = icon.height || data.height || 128;
  const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${width} ${height}">${icon.body}</svg>`;
  fs.writeFileSync(path.join(OUT_DIR, `${key}.svg`), svg, 'utf8');
  manifest[key] = emoji;
  ok++;
}

fs.writeFileSync(
  path.join(__dirname, 'emoji-svg-manifest.json'),
  JSON.stringify(manifest, null, 2),
  'utf8'
);

console.log(`Extracted ${ok} SVGs to ${OUT_DIR}`);
if (missing.length) {
  console.log(`MISSING (${missing.length}):`);
  missing.forEach((m) => console.log(' - ' + m));
}
