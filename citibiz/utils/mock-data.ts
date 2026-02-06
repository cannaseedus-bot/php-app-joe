// Mock data for the website discovery platform
import { Website } from './fuzzy-import';

export interface User {
  id: string;
  username: string;
  email: string;
  points: number;
  created_at: string;
}

export interface Ping {
  id: string;
  website_id: string;
  user_id: string;
  action: 'view' | 'manual' | 'widget';
  created_at: string;
}

export interface Boost {
  id: string;
  website_id: string;
  user_id: string;
  points_spent: number;
  multiplier: number;
  starts_at: string;
  ends_at: string;
  active: boolean;
}

export interface Analytics {
  website_id: string;
  views: number;
  clicks: number;
  ctr: number;
  avg_rating: number;
  total_pings: number;
  boost_roi: number;
}

// Mock current user
export const mockUser: User = {
  id: 'user-1',
  username: 'surfmaster',
  email: 'user@example.com',
  points: 1250,
  created_at: '2024-01-01T00:00:00Z'
};

// Mock websites with various weights (some boosted)
export const mockWebsites: Website[] = [
  {
    id: 'site-1',
    url: 'https://github.com',
    title: 'GitHub',
    description: 'Where the world builds software. GitHub is where over 100 million developers shape the future of software.',
    keywords: ['programming', 'code', 'open source', 'git'],
    thumbnail: 'https://github.githubassets.com/images/modules/logos_page/GitHub-Mark.png',
    owner_id: 'user-2',
    weight: 5.0, // boosted
    created_at: '2024-01-15T10:00:00Z'
  },
  {
    id: 'site-2',
    url: 'https://reactjs.org',
    title: 'React',
    description: 'A JavaScript library for building user interfaces. React makes it painless to create interactive UIs.',
    keywords: ['javascript', 'ui', 'library', 'components'],
    thumbnail: '',
    owner_id: 'user-3',
    weight: 3.5,
    created_at: '2024-01-16T14:30:00Z'
  },
  {
    id: 'site-3',
    url: 'https://tailwindcss.com',
    title: 'Tailwind CSS',
    description: 'A utility-first CSS framework packed with classes that can be composed to build any design.',
    keywords: ['css', 'framework', 'utility', 'design'],
    thumbnail: '',
    owner_id: 'user-1',
    weight: 2.0,
    created_at: '2024-01-17T09:15:00Z'
  },
  {
    id: 'site-4',
    url: 'https://figma.com',
    title: 'Figma',
    description: 'Figma is the leading collaborative design tool for building meaningful products.',
    keywords: ['design', 'prototype', 'collaborate', 'ui/ux'],
    thumbnail: '',
    owner_id: 'user-4',
    weight: 4.2, // boosted
    created_at: '2024-01-18T16:45:00Z'
  },
  {
    id: 'site-5',
    url: 'https://vercel.com',
    title: 'Vercel',
    description: 'The platform for frontend developers, providing the speed and reliability innovators need.',
    keywords: ['hosting', 'deployment', 'frontend', 'performance'],
    thumbnail: '',
    owner_id: 'user-5',
    weight: 1.8,
    created_at: '2024-01-19T11:20:00Z'
  },
  {
    id: 'site-6',
    url: 'https://openai.com',
    title: 'OpenAI',
    description: 'Creating safe artificial general intelligence that benefits all of humanity.',
    keywords: ['ai', 'machine learning', 'gpt', 'artificial intelligence'],
    thumbnail: '',
    owner_id: 'user-6',
    weight: 6.0, // highly boosted
    created_at: '2024-01-20T13:10:00Z'
  }
];

// Mock analytics data
export const mockAnalytics: Record<string, Analytics> = {
  'site-1': {
    website_id: 'site-1',
    views: 1250,
    clicks: 89,
    ctr: 7.1,
    avg_rating: 4.3,
    total_pings: 45,
    boost_roi: 2.4
  },
  'site-2': {
    website_id: 'site-2',
    views: 892,
    clicks: 67,
    ctr: 7.5,
    avg_rating: 4.6,
    total_pings: 32,
    boost_roi: 0
  },
  'site-3': {
    website_id: 'site-3',
    views: 634,
    clicks: 41,
    ctr: 6.5,
    avg_rating: 4.1,
    total_pings: 28,
    boost_roi: 1.8
  }
};

// Mock recent pings
export const mockPings: Ping[] = [
  {
    id: 'ping-1',
    website_id: 'site-1',
    user_id: 'user-1',
    action: 'manual',
    created_at: '2024-01-21T10:30:00Z'
  },
  {
    id: 'ping-2',
    website_id: 'site-2',
    user_id: 'user-2',
    action: 'widget',
    created_at: '2024-01-21T11:15:00Z'
  },
  {
    id: 'ping-3',
    website_id: 'site-3',
    user_id: 'user-1',
    action: 'view',
    created_at: '2024-01-21T12:00:00Z'
  }
];

// Mock active boosts
export const mockBoosts: Boost[] = [
  {
    id: 'boost-1',
    website_id: 'site-1',
    user_id: 'user-2',
    points_spent: 200,
    multiplier: 2.5,
    starts_at: '2024-01-21T00:00:00Z',
    ends_at: '2024-01-22T00:00:00Z',
    active: true
  },
  {
    id: 'boost-2',
    website_id: 'site-4',
    user_id: 'user-4',
    points_spent: 150,
    multiplier: 2.1,
    starts_at: '2024-01-21T06:00:00Z',
    ends_at: '2024-01-21T18:00:00Z',
    active: true
  }
];

// Point values for different actions
export const POINT_VALUES = {
  PING_SUBMITTED: 5,
  PING_VIEWED: 0.1,
  REVIEW_POSTED: 3,
  UPVOTE_RECEIVED: 0.5,
  REFERRAL: 10
};

// Boost pricing
export const BOOST_PRICING = [
  { points: 50, multiplier: 1.5, hours: 6, label: 'Mini Boost' },
  { points: 100, multiplier: 2.0, hours: 12, label: 'Standard Boost' },
  { points: 200, multiplier: 2.5, hours: 24, label: 'Power Boost' },
  { points: 500, multiplier: 4.0, hours: 48, label: 'Mega Boost' }
];

export function getActiveBoosts(websiteId: string): Boost[] {
  const now = new Date();
  return mockBoosts.filter(boost => 
    boost.website_id === websiteId && 
    boost.active && 
    new Date(boost.ends_at) > now
  );
}

export function getTotalWeight(website: Website): number {
  const activeBoosts = getActiveBoosts(website.id!);
  const boostMultiplier = activeBoosts.reduce((total, boost) => total + boost.multiplier - 1, 0);
  return website.weight * (1 + boostMultiplier);
}