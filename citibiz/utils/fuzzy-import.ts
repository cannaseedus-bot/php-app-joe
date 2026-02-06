// Fuzzy JSON import utility - maps various JSON formats to canonical Website objects
// Handles different key names and structures tolerantly

export interface Website {
  id?: string;
  url: string;
  title: string;
  description: string;
  keywords: string[];
  thumbnail: string;
  owner_id?: string;
  weight: number;
  created_at?: string;
  raw?: any;
}

// Common synonyms for keys we want to map
const KEY_SYNONYMS = {
  url: ['url', 'link', 'href', 'site_url', 'website', 'site', 'page', 'page_url', 'web_url'],
  title: ['title', 'name', 'site_name', 'headline', 'page_title', 'sitename'],
  description: ['description', 'desc', 'summary', 'excerpt', 'about', 'content', 'text'],
  keywords: ['keywords', 'tags', 'labels', 'terms', 'categories', 'topics'],
  thumbnail: ['thumbnail', 'thumb', 'image', 'image_url', 'img', 'poster', 'photo', 'picture']
};

// Normalize string: lower + strip non-alnum (for matching)
function normalizeKey(k: string): string {
  return String(k || '').toLowerCase().replace(/[^a-z0-9]/g, '');
}

// Find best matching key in source object for target canonical key
function findKeyFor(obj: any, target: keyof typeof KEY_SYNONYMS): string | null {
  const candidates = Object.keys(obj);
  const synonyms = KEY_SYNONYMS[target] || [target];
  
  // Exact match first
  for (const s of synonyms) {
    if (s in obj) return s;
  }
  
  // Fuzzy match: normalized match
  const normSyn = synonyms.map(normalizeKey);
  for (const c of candidates) {
    const n = normalizeKey(c);
    if (normSyn.includes(n)) return c;
  }
  
  // Fallback: try contains (e.g., 'siteTitle' contains 'title')
  for (const c of candidates) {
    for (const s of synonyms) {
      if (String(c).toLowerCase().includes(String(s).toLowerCase())) return c;
    }
  }
  
  return null;
}

// Canonicalize single record
function mapRecord(raw: any): Website | null {
  // URL is mandatory
  const urlKey = findKeyFor(raw, 'url');
  if (!urlKey || !raw[urlKey]) return null;
  
  const url = String(raw[urlKey]).trim();
  if (!url || !isValidUrl(url)) return null;

  const result: Website = {
    id: raw.id || generateId(),
    url,
    title: '',
    description: '',
    keywords: [],
    thumbnail: '',
    weight: 1.0,
    created_at: new Date().toISOString(),
    raw
  };

  // Title
  const tKey = findKeyFor(raw, 'title');
  result.title = tKey ? String(raw[tKey]).trim() : extractTitleFromUrl(url);

  // Description
  const dKey = findKeyFor(raw, 'description');
  result.description = dKey ? String(raw[dKey]).trim() : '';

  // Keywords -> array
  const kKey = findKeyFor(raw, 'keywords');
  if (kKey) {
    const v = raw[kKey];
    if (Array.isArray(v)) {
      result.keywords = v.map(String).filter(Boolean);
    } else if (typeof v === 'string') {
      result.keywords = v.split(/[,;|]/).map(s => s.trim()).filter(Boolean);
    }
  }

  // Thumbnail
  const thKey = findKeyFor(raw, 'thumbnail');
  result.thumbnail = thKey ? String(raw[thKey]).trim() : '';

  return result;
}

// Process uploaded JSON content
export function mapJsonToWebsites(json: any): Website[] {
  let arr: any[];
  
  if (Array.isArray(json)) {
    arr = json;
  } else if (json && Array.isArray(json.items)) {
    arr = json.items;
  } else if (json && Array.isArray(json.data)) {
    arr = json.data;
  } else if (json && Array.isArray(json.results)) {
    arr = json.results;
  } else if (json && typeof json === 'object') {
    arr = [json];
  } else {
    return [];
  }

  const mapped: Website[] = [];
  for (const item of arr) {
    const m = mapRecord(item);
    if (m) mapped.push(m);
  }

  // Dedupe by normalized URL
  const seen = new Map<string, Website>();
  const normalizeUrl = (url: string) => url.replace(/\/+$/, '').toLowerCase();
  
  for (const w of mapped) {
    const key = normalizeUrl(w.url);
    if (!seen.has(key)) {
      seen.set(key, w);
    } else {
      // Merge: prefer non-empty fields
      const prev = seen.get(key)!;
      prev.title = prev.title || w.title;
      prev.description = prev.description || w.description;
      prev.keywords = (prev.keywords && prev.keywords.length) ? prev.keywords : w.keywords;
      prev.thumbnail = prev.thumbnail || w.thumbnail;
    }
  }

  return Array.from(seen.values());
}

// Utility functions
function isValidUrl(url: string): boolean {
  try {
    new URL(url);
    return true;
  } catch {
    return false;
  }
}

function extractTitleFromUrl(url: string): string {
  try {
    const u = new URL(url);
    return u.hostname.replace(/^www\./, '');
  } catch {
    return url;
  }
}

function generateId(): string {
  return Math.random().toString(36).substr(2, 9);
}

// Weighted random selection
export function pickWeighted<T extends { weight: number }>(items: T[]): T | null {
  if (!items.length) return null;
  
  const total = items.reduce((s, i) => s + (i.weight || 1), 0);
  let r = Math.random() * total;
  
  for (const item of items) {
    r -= (item.weight || 1);
    if (r <= 0) return item;
  }
  
  return items[items.length - 1];
}