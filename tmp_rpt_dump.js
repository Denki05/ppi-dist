const fs = require('fs');
const zlib = require('zlib');

function cfb(file) {
  const b = fs.readFileSync(file);
  const sectorSize = 1 << b.readUInt16LE(0x1e);
  const miniSectorSize = 1 << b.readUInt16LE(0x20);
  const numFatSectors = b.readUInt32LE(0x2c);
  const dirStart = b.readUInt32LE(0x30);
  const miniCutoff = b.readUInt32LE(0x38);
  const miniFatStart = b.readUInt32LE(0x3c);
  const difatStart = b.readUInt32LE(0x44);
  const numDifat = b.readUInt32LE(0x48);

  const fatSectors = [];
  for (let i = 0; i < 109; i++) {
    const s = b.readUInt32LE(0x4c + i * 4);
    if (s === 0xFFFFFFFF) break;
    fatSectors.push(s);
  }
  let difat = difatStart;
  for (let i = 0; i < numDifat && difat !== 0xFFFFFFFF && difat !== 0xFFFFFFFE; i++) {
    const off = (difat + 1) * sectorSize;
    const n = sectorSize / 4 - 1;
    for (let j = 0; j < n; j++) {
      const s = b.readUInt32LE(off + j * 4);
      if (s === 0xFFFFFFFF) break;
      fatSectors.push(s);
    }
    difat = b.readUInt32LE(off + n * 4);
  }
  const fat = [];
  for (const s of fatSectors) {
    const off = (s + 1) * sectorSize;
    for (let i = 0; i < sectorSize / 4; i++) fat.push(b.readUInt32LE(off + i * 4));
  }
  const readChain = (start, size) => {
    const parts = [];
    let cur = start;
    while (cur !== 0xFFFFFFFE && cur !== 0xFFFFFFFF && parts.length * sectorSize < size + sectorSize) {
      parts.push(b.slice((cur + 1) * sectorSize, (cur + 2) * sectorSize));
      cur = fat[cur];
    }
    return Buffer.concat(parts).slice(0, size);
  };
  // directory
  const dirBuf = readChain(dirStart, 1e9);
  const entries = [];
  for (let i = 0; i + 128 <= dirBuf.length; i += 128) {
    const nameLen = dirBuf.readUInt16LE(i + 0x40);
    if (nameLen < 2) continue;
    const name = dirBuf.slice(i, i + nameLen - 2).toString('utf16le');
    const type = dirBuf[i + 0x42];
    const startSec = dirBuf.readUInt32LE(i + 0x74);
    const size = dirBuf.readUInt32LE(i + 0x78);
    entries.push({ name, type, startSec, size });
  }
  const root = entries.find(e => e.type === 5);
  const rootStream = readChain(root.startSec, root.size);
  // mini FAT
  const miniFat = [];
  {
    let cur = miniFatStart;
    const parts = [];
    while (cur !== 0xFFFFFFFE && cur !== 0xFFFFFFFF) {
      parts.push(b.slice((cur + 1) * sectorSize, (cur + 2) * sectorSize));
      cur = fat[cur];
    }
    const mb = Buffer.concat(parts);
    for (let i = 0; i + 4 <= mb.length; i += 4) miniFat.push(mb.readUInt32LE(i));
  }
  const readMini = (start, size) => {
    const parts = [];
    let cur = start;
    while (cur !== 0xFFFFFFFE && cur !== 0xFFFFFFFF && parts.length * miniSectorSize < size + miniSectorSize) {
      parts.push(rootStream.slice(cur * miniSectorSize, (cur + 1) * miniSectorSize));
      cur = miniFat[cur];
    }
    return Buffer.concat(parts).slice(0, size);
  };
  const get = (n) => {
    const e = entries.find(x => x.name === n);
    if (!e) return null;
    if (e.size < miniCutoff) return readMini(e.startSec, e.size);
    return readChain(e.startSec, e.size);
  };
  return { entries, get };
}

const file = process.argv[2];
const c = cfb(file);
console.log('streams:', c.entries.map(e => e.name).join(', '));
const contents = c.get('Contents');
console.log('Contents size', contents.length);
fs.writeFileSync('tmp_contents.bin', contents);

// heuristic: try inflate at each zlib-ish offset
const texts = [];
for (let i = 0; i < contents.length - 2; i++) {
  if (contents[i] === 0x78 && [0x01, 0x5e, 0x9c, 0xda].includes(contents[i + 1])) {
    try {
      const out = zlib.inflateSync(contents.slice(i), { finishFlush: zlib.constants.Z_SYNC_FLUSH });
      texts.push({ i, out });
    } catch (e) { }
  }
}
console.log('inflated blocks', texts.length, 'total bytes', texts.reduce((a, t) => a + t.out.length, 0));
let all = Buffer.concat(texts.map(t => t.out));
fs.writeFileSync('tmp_contents_inflated.bin', all);

function extract(buf, enc) {
  const out = [];
  let cur = '';
  const step = enc === 'utf16le' ? 2 : 1;
  let start = 0;
  for (let i = 0; i + step <= buf.length; i += step) {
    const ch = enc === 'utf16le' ? buf.readUInt16LE(i) : buf[i];
    if (ch === 10 || ch === 13 || ch === 9 || (ch >= 32 && ch < 127)) {
      if (!cur) start = i;
      cur += String.fromCharCode(ch);
    } else {
      if (cur.length >= 4) out.push([start, cur]);
      cur = '';
    }
  }
  if (cur.length >= 4) out.push([start, cur]);
  return out;
}
const lines = [...extract(all, 'latin1')];
const utf16 = extract(all, 'utf16le').filter(x => x[1].length >= 4);
console.log('\n===== ASCII/ANSI strings =====');
for (const [o, s] of lines) console.log(o + ': ' + s);
console.log('\n===== UTF16 strings =====');
for (const [o, s] of utf16) console.log(o + ': ' + s);
