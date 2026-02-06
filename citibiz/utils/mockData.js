// Mock data for development and testing

export const mockWebsites = [
  {
    id: 1,
    url: 'https://tailwindcss.com',
    title: 'Tailwind CSS',
    description: 'A utility-first CSS framework for rapidly building custom user interfaces.',
    keywords: ['css', 'framework', 'utility', 'design'],
    thumbnail: 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400',
    owner_id: 1,
    weight: 10,
    rating: 4.8,
    views: 1250,
    clicks: 340,
    created_at: new Date('2024-01-15').toISOString()
  },
  {
    id: 2,
    url: 'https://react.dev',
    title: 'React',
    description: 'The library for web and native user interfaces. Build user interfaces out of individual pieces called components.',
    keywords: ['javascript', 'react', 'frontend', 'ui'],
    thumbnail: 'https://images.unsplash.com/photo-1633356122544-f134324a6cee?w=400',
    owner_id: 2,
    weight: 15,
    rating: 4.9,
    views: 2100,
    clicks: 630,
    created_at: new Date('2024-01-20').toISOString()
  },
  {
    id: 3,
    url: 'https://supabase.com',
    title: 'Supabase',
    description: 'The open source Firebase alternative. Build a backend in less than 2 minutes.',
    keywords: ['backend', 'database', 'auth', 'realtime'],
    thumbnail: 'https://images.unsplash.com/photo-1518186285589-2f7649de83e0?w=400',
    owner_id: 3,
    weight: 8,
    rating: 4.7,
    views: 890,
    clicks: 210,
    created_at: new Date('2024-02-01').toISOString()
  },
  {
    id: 4,
    url: 'https://vercel.com',
    title: 'Vercel',
    description: 'Deploy web projects with the best frontend developer experience and automatic optimizations.',
    keywords: ['deployment', 'hosting', 'frontend', 'performance'],
    thumbnail: 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?w=400',
    owner_id: 1,
    weight: 6,
    rating: 4.6,
    views: 750,
    clicks: 180,
    created_at: new Date('2024-02-10').toISOString()
  },
  {
    id: 5,
    url: 'https://openai.com',
    title: 'OpenAI',
    description: 'Creating safe artificial general intelligence that benefits all of humanity.',
    keywords: ['ai', 'machine learning', 'gpt', 'artificial intelligence'],
    thumbnail: 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=400',
    owner_id: 4,
    weight: 20,
    rating: 4.9,
    views: 3200,
    clicks: 960,
    created_at: new Date('2024-02-15').toISOString()
  },
  {
    id: 6,
    url: 'https://figma.com',
    title: 'Figma',
    description: 'Collaborative design tool for teams. Create, prototype, and iterate on designs.',
    keywords: ['design', 'prototype', 'collaboration', 'ui'],
    thumbnail: 'https://images.unsplash.com/photo-1558655146-364adebe7a91?w=400',
    owner_id: 5,
    weight: 12,
    rating: 4.7,
    views: 1800,
    clicks: 450,
    created_at: new Date('2024-02-20').toISOString()
  },
  {
    id: 7,
    url: 'https://github.com',
    title: 'GitHub',
    description: 'Where the world builds software. Millions of developers collaborate on GitHub.',
    keywords: ['git', 'code', 'collaboration', 'opensource'],
    thumbnail: 'https://images.unsplash.com/photo-1618477460450-e14afba0ef37?w=400',
    owner_id: 6,
    weight: 18,
    rating: 4.8,
    views: 2800,
    clicks: 720,
    created_at: new Date('2024-02-25').toISOString()
  },
  {
    id: 8,
    url: 'https://stripe.com',
    title: 'Stripe',
    description: 'Financial infrastructure for the internet. Millions of companies use Stripe to accept payments.',
    keywords: ['payments', 'api', 'fintech', 'ecommerce'],
    thumbnail: 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=400',
    owner_id: 7,
    weight: 14,
    rating: 4.6,
    views: 1400,
    clicks: 350,
    created_at: new Date('2024-03-01').toISOString()
  },
  {
    id: 9,
    url: 'https://linear.app',
    title: 'Linear',
    description: 'The issue tracking tool you\'ll enjoy using. Linear helps streamline software projects.',
    keywords: ['productivity', 'project management', 'software', 'teams'],
    thumbnail: 'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=400',
    owner_id: 8,
    weight: 9,
    rating: 4.5,
    views: 950,
    clicks: 200,
    created_at: new Date('2024-03-05').toISOString()
  },
  {
    id: 10,
    url: 'https://anthropic.com',
    title: 'Anthropic',
    description: 'AI safety company building reliable, interpretable, and steerable AI systems.',
    keywords: ['ai', 'safety', 'research', 'claude'],
    thumbnail: 'https://images.unsplash.com/photo-1677442136019-21780ecad995?w=400',
    owner_id: 9,
    weight: 16,
    rating: 4.7,
    views: 2200,
    clicks: 580,
    created_at: new Date('2024-03-10').toISOString()
  }
];

export const mockUsers = [
  {
    id: 1,
    username: 'alex_dev',
    email: 'alex@example.com',
    points: 1250,
    created_at: new Date('2024-01-01').toISOString()
  },
  {
    id: 2,
    username: 'sarah_designer',
    email: 'sarah@example.com',
    points: 890,
    created_at: new Date('2024-01-05').toISOString()
  },
  {
    id: 3,
    username: 'mike_fullstack',
    email: 'mike@example.com',
    points: 2100,
    created_at: new Date('2024-01-10').toISOString()
  },
  {
    id: 4,
    username: 'ai_enthusiast',
    email: 'ai@example.com',
    points: 3400,
    created_at: new Date('2024-01-12').toISOString()
  }
];

export const mockAnalytics = {
  totalWebsites: 127,
  totalPings: 5430,
  totalViews: 18520,
  totalClicks: 4200,
  avgCTR: 0.227,
  topKeywords: [
    { keyword: 'javascript', count: 45 },
    { keyword: 'css', count: 38 },
    { keyword: 'react', count: 32 },
    { keyword: 'ai', count: 28 },
    { keyword: 'design', count: 25 }
  ],
  recentActivity: [
    { action: 'ping', website: 'React', user: 'sarah_designer', timestamp: new Date(Date.now() - 1000 * 60 * 5).toISOString() },
    { action: 'surf', website: 'Tailwind CSS', user: 'alex_dev', timestamp: new Date(Date.now() - 1000 * 60 * 15).toISOString() },
    { action: 'boost', website: 'OpenAI', user: 'ai_enthusiast', timestamp: new Date(Date.now() - 1000 * 60 * 30).toISOString() },
    { action: 'import', user: 'mike_fullstack', count: 12, timestamp: new Date(Date.now() - 1000 * 60 * 45).toISOString() }
  ]
};

// Weighted random selection utility
export function pickWeighted(items, weightKey = 'weight') {
  if (!items || items.length === 0) return null;
  
  const total = items.reduce((sum, item) => sum + (item[weightKey] || 1), 0);
  let random = Math.random() * total;
  
  for (const item of items) {
    random -= (item[weightKey] || 1);
    if (random <= 0) return item;
  }
  
  return items[items.length - 1];
}

// Calculate CTR
export function calculateCTR(clicks, views) {
  if (!views || views === 0) return 0;
  return (clicks / views * 100).toFixed(2);
}

// Format numbers for display
export function formatNumber(num) {
  if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
  if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
  return num.toString();
}

// Generate points for actions
export const POINT_VALUES = {
  ping_submit: 5,
  ping_accepted: 10,
  surf_view: 1,
  review_posted: 15,
  like_received: 2,
  referral: 50,
  boost_purchase: -100, // cost
  daily_login: 5
};

export function awardPoints(action, multiplier = 1) {
  return (POINT_VALUES[action] || 0) * multiplier;
}