import { Hono } from "npm:hono";
import { cors } from "npm:hono/cors";
import { logger } from "npm:hono/logger";
import * as kv from "./kv_store.tsx";
const app = new Hono();

// Enable logger
app.use('*', logger(console.log));

// Enable CORS for all routes and methods
app.use(
  "/*",
  cors({
    origin: "*",
    allowHeaders: ["Content-Type", "Authorization"],
    allowMethods: ["GET", "POST", "PUT", "DELETE", "OPTIONS"],
    exposeHeaders: ["Content-Length"],
    maxAge: 600,
  }),
);

// Health check endpoint
app.get("/make-server-39168b89/health", (c) => {
  return c.json({ status: "ok" });
});

// Get user profile with personalized data
app.get("/make-server-39168b89/user-profile/:userId", async (c) => {
  try {
    const userId = c.req.param("userId");
    const profile = await kv.get(`user_profile_${userId}`);
    
    if (!profile) {
      // Create default profile
      const defaultProfile = {
        id: userId,
        bookmarks: [],
        preferredTags: {},
        surfHistory: [],
        personalizedWeights: {},
        lastUpdated: new Date().toISOString()
      };
      await kv.set(`user_profile_${userId}`, defaultProfile);
      return c.json({ success: true, profile: defaultProfile });
    }
    
    return c.json({ success: true, profile });
  } catch (error) {
    console.log("Error fetching user profile:", error);
    return c.json({ success: false, error: error.message }, 500);
  }
});

// Update user profile
app.post("/make-server-39168b89/user-profile/:userId", async (c) => {
  try {
    const userId = c.req.param("userId");
    const body = await c.req.json();
    
    await kv.set(`user_profile_${userId}`, body.profile);
    return c.json({ success: true });
  } catch (error) {
    console.log("Error updating user profile:", error);
    return c.json({ success: false, error: error.message }, 500);
  }
});

// Get personalized website recommendations
app.get("/make-server-39168b89/recommendations/:userId", async (c) => {
  try {
    const userId = c.req.param("userId");
    const limit = parseInt(c.req.query("limit") || "10");
    
    const profile = await kv.get(`user_profile_${userId}`);
    const websites = await kv.get("websites") || [];
    
    if (!profile || !websites.length) {
      return c.json({ success: true, recommendations: [] });
    }
    
    // Calculate personalized weights and generate recommendations
    const recommendations = generatePersonalizedRecommendations(websites, profile, limit);
    
    return c.json({ success: true, recommendations });
  } catch (error) {
    console.log("Error generating recommendations:", error);
    return c.json({ success: false, error: error.message }, 500);
  }
});

// Record bookmark action
app.post("/make-server-39168b89/bookmark", async (c) => {
  try {
    const { userId, websiteId, isBookmark } = await c.req.json();
    
    const profile = await kv.get(`user_profile_${userId}`) || {
      id: userId,
      bookmarks: [],
      preferredTags: {},
      surfHistory: [],
      personalizedWeights: {},
      lastUpdated: new Date().toISOString()
    };
    
    const websites = await kv.get("websites") || [];
    const website = websites.find(w => w.id === websiteId);
    
    if (!website) {
      return c.json({ success: false, error: "Website not found" }, 404);
    }
    
    // Update profile based on bookmark action
    const updatedProfile = updateUserPreferencesFromBookmark(profile, website, isBookmark);
    await kv.set(`user_profile_${userId}`, updatedProfile);
    
    return c.json({ success: true, profile: updatedProfile });
  } catch (error) {
    console.log("Error recording bookmark:", error);
    return c.json({ success: false, error: error.message }, 500);
  }
});

// Record surf action for learning
app.post("/make-server-39168b89/surf-action", async (c) => {
  try {
    const { userId, websiteId, action } = await c.req.json();
    
    const profile = await kv.get(`user_profile_${userId}`) || {
      id: userId,
      bookmarks: [],
      preferredTags: {},
      surfHistory: [],
      personalizedWeights: {},
      lastUpdated: new Date().toISOString()
    };
    
    const websites = await kv.get("websites") || [];
    const website = websites.find(w => w.id === websiteId);
    
    if (website) {
      // Update surf history
      if (!profile.surfHistory) profile.surfHistory = [];
      profile.surfHistory.unshift({
        websiteId: website.id,
        action,
        timestamp: new Date().toISOString(),
        tags: website.keywords || []
      });
      
      // Keep only last 100 surf actions
      profile.surfHistory = profile.surfHistory.slice(0, 100);
      
      await kv.set(`user_profile_${userId}`, profile);
    }
    
    return c.json({ success: true });
  } catch (error) {
    console.log("Error recording surf action:", error);
    return c.json({ success: false, error: error.message }, 500);
  }
});

// Helper functions
function generatePersonalizedRecommendations(websites, userProfile, limit) {
  if (!userProfile.preferredTags || Object.keys(userProfile.preferredTags).length === 0) {
    // No preferences yet, return popular sites
    return websites
      .sort((a, b) => (b.rating * b.views) - (a.rating * a.views))
      .slice(0, limit);
  }
  
  // Score all websites based on user preferences
  const scoredWebsites = websites.map(website => {
    const personalizedWeight = calculatePersonalizedWeight(website, userProfile);
    const diversityBonus = calculateDiversityBonus(website, userProfile);
    const qualityScore = (website.rating || 0) * Math.log(website.views + 1);
    
    return {
      ...website,
      recommendationScore: personalizedWeight + diversityBonus + qualityScore,
      personalizedWeight
    };
  });
  
  // Filter out already bookmarked sites and sort by score
  const bookmarkedIds = new Set(userProfile.bookmarks.map(b => b.id));
  
  return scoredWebsites
    .filter(website => !bookmarkedIds.has(website.id))
    .sort((a, b) => b.recommendationScore - a.recommendationScore)
    .slice(0, limit);
}

function calculatePersonalizedWeight(website, userProfile) {
  let baseWeight = website.weight || 1;
  let personalizedMultiplier = 1;
  
  if (!userProfile.preferredTags || Object.keys(userProfile.preferredTags).length === 0) {
    return baseWeight;
  }
  
  const websiteTags = (website.keywords || []).map(tag => tag.toLowerCase());
  let preferenceScore = 0;
  let totalPreferences = Object.values(userProfile.preferredTags).reduce((sum, count) => sum + count, 0);
  
  websiteTags.forEach(tag => {
    const tagPreference = userProfile.preferredTags[tag] || 0;
    preferenceScore += tagPreference / totalPreferences;
  });
  
  personalizedMultiplier = 1 + (preferenceScore * 4);
  
  const bookmarkSimilarityBoost = calculateBookmarkSimilarityBoost(website, userProfile);
  personalizedMultiplier *= (1 + bookmarkSimilarityBoost);
  
  return baseWeight * personalizedMultiplier;
}

function calculateBookmarkSimilarityBoost(website, userProfile) {
  if (!userProfile.bookmarks || userProfile.bookmarks.length === 0) return 0;
  
  let maxSimilarity = 0;
  
  userProfile.bookmarks.forEach(bookmark => {
    const similarity = calculateTagSimilarity(website.keywords || [], bookmark.tags || []);
    maxSimilarity = Math.max(maxSimilarity, similarity);
  });
  
  return maxSimilarity * 2;
}

function calculateTagSimilarity(tags1, tags2) {
  if (!tags1 || !tags2 || tags1.length === 0 || tags2.length === 0) return 0;
  
  const set1 = new Set(tags1.map(tag => tag.toLowerCase()));
  const set2 = new Set(tags2.map(tag => tag.toLowerCase()));
  
  const intersection = new Set([...set1].filter(x => set2.has(x)));
  const union = new Set([...set1, ...set2]);
  
  return intersection.size / union.size;
}

function calculateDiversityBonus(website, userProfile) {
  const websiteTags = new Set((website.keywords || []).map(tag => tag.toLowerCase()));
  const userTagPreferences = Object.keys(userProfile.preferredTags || {});
  
  const newTags = [...websiteTags].filter(tag => !userTagPreferences.includes(tag));
  return newTags.length * 0.5;
}

function updateUserPreferencesFromBookmark(userProfile, website, isBookmark) {
  const updatedProfile = { ...userProfile };
  
  if (isBookmark) {
    if (!updatedProfile.bookmarks.find(b => b.id === website.id)) {
      updatedProfile.bookmarks.push({
        id: website.id,
        websiteId: website.id,
        url: website.url,
        title: website.title,
        tags: website.keywords || [],
        bookmarkedAt: new Date().toISOString()
      });
    }
    
    (website.keywords || []).forEach(tag => {
      const normalizedTag = tag.toLowerCase();
      updatedProfile.preferredTags[normalizedTag] = 
        (updatedProfile.preferredTags[normalizedTag] || 0) + 1;
    });
  } else {
    updatedProfile.bookmarks = updatedProfile.bookmarks.filter(b => b.id !== website.id);
    
    (website.keywords || []).forEach(tag => {
      const normalizedTag = tag.toLowerCase();
      if (updatedProfile.preferredTags[normalizedTag]) {
        updatedProfile.preferredTags[normalizedTag] = Math.max(0, 
          updatedProfile.preferredTags[normalizedTag] - 1);
      }
    });
  }
  
  updatedProfile.lastUpdated = new Date().toISOString();
  return updatedProfile;
}

Deno.serve(app.fetch);