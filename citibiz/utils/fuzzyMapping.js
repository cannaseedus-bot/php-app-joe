// Fuzzy JSON mapping utilities for website import
// Handles various field name variations and normalizes them

const KEY_SYNONYMS = {
  url: ['url','link','href','site_url','website','site','page','page_url','web','domain'],
  title: ['title','name','site_name','headline','sitename','page_title','label'],
  description: ['description','desc','summary','excerpt','about','bio','content','text'],
  keywords: ['keywords','tags','labels','terms','categories','topics'],
  thumbnail: ['thumbnail','thumb','image','image_url','img','poster','photo','pic','avatar']
};

// Normalize string for fuzzy matching
function normalizeKey(k) {
  return String(k || '').toLowerCase().replace(/[^a-z0-9]/g, '');
}

// Find best matching key in source object for target canonical key
function findKeyFor(obj, target) {
  const candidates = Object.keys(obj);
  const synonyms = KEY_SYNONYMS[target] || [target];
  
  // Exact match first
  for (let s of synonyms) {
    if (s in obj) return s;
  }
  
  // Fuzzy match: normalized comparison
  const normSyn = synonyms.map(normalizeKey);
  for (let c of candidates) {
    const n = normalizeKey(c);
    if (normSyn.includes(n)) return c;
  }
  
  // Contains match (e.g., 'siteTitle' contains 'title')
  for (let c of candidates) {
    for (let s of synonyms) {
      if (String(c).toLowerCase().includes(String(s).toLowerCase())) return c;
    }
  }
  
  return null;
}

// Convert single record to canonical format
export function mapRecord(raw) {
  const result = {};
  
  // URL is mandatory
  const urlKey = findKeyFor(raw, 'url');
  if (!urlKey) return null;
  result.url = String(raw[urlKey]).trim();
  
  // Title
  const titleKey = findKeyFor(raw, 'title');
  result.title = titleKey ? String(raw[titleKey]).trim() : '';
  
  // Description
  const descKey = findKeyFor(raw, 'description');
  result.description = descKey ? String(raw[descKey]).trim() : '';
  
  // Keywords -> array
  const keywordsKey = findKeyFor(raw, 'keywords');
  if (keywordsKey) {
    const v = raw[keywordsKey];
    if (Array.isArray(v)) {
      result.keywords = v.map(String).filter(Boolean);
    } else if (typeof v === 'string') {
      result.keywords = v.split(/[,;|]/).map(s => s.trim()).filter(Boolean);
    } else {
      result.keywords = [];
    }
  } else {
    result.keywords = [];
  }
  
  // Thumbnail
  const thumbKey = findKeyFor(raw, 'thumbnail');
  result.thumbnail = thumbKey ? String(raw[thumbKey]).trim() : '';
  
  // Pass through original for debugging
  result.raw = raw;
  
  return result;
}

// Process JSON data and convert to canonical websites
export function mapJsonToWebsites(json) {
  let arr;
  
  // Handle different JSON structures
  if (Array.isArray(json)) {
    arr = json;
  } else if (json && Array.isArray(json.items)) {
    arr = json.items;
  } else if (json && Array.isArray(json.data)) {
    arr = json.data;
  } else if (json && Array.isArray(json.websites)) {
    arr = json.websites;
  } else if (json && typeof json === 'object') {
    arr = [json];
  } else {
    return [];
  }
  
  const mapped = [];
  for (const item of arr) {
    if (item && typeof item === 'object') {
      const m = mapRecord(item);
      if (m && m.url) mapped.push(m);
    }
  }
  
  // Deduplicate by normalized URL
  const seen = new Map();
  const normalizeUrl = url => url.replace(/\/+$/, '').toLowerCase();
  
  for (const website of mapped) {
    const key = normalizeUrl(website.url);
    if (!seen.has(key)) {
      seen.set(key, website);
    } else {
      // Merge: prefer non-empty fields
      const existing = seen.get(key);
      existing.title = existing.title || website.title;
      existing.description = existing.description || website.description;
      existing.keywords = (existing.keywords && existing.keywords.length) ? existing.keywords : website.keywords;
      existing.thumbnail = existing.thumbnail || website.thumbnail;
    }
  }
  
  return Array.from(seen.values());
}

// Test if object looks like a website record
export function looksLikeWebsite(obj) {
  if (!obj || typeof obj !== 'object') return false;
  
  const urlKey = findKeyFor(obj, 'url');
  if (!urlKey) return false;
  
  const url = String(obj[urlKey]).trim();
  return url.includes('.') && (url.startsWith('http') || !url.includes(' '));
}