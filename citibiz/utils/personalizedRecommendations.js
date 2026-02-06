// Personalized recommendation system for SurfPing
// Handles user preferences, bookmark-based learning, and tag similarity

// User preferences and bookmark data structure
export const defaultUserProfile = {
  id: 1,
  bookmarks: [],
  preferredTags: {},
  surfHistory: [],
  personalizedWeights: {},
  lastUpdated: new Date().toISOString()
};

// Calculate tag similarity between two websites
export function calculateTagSimilarity(tags1, tags2) {
  if (!tags1 || !tags2 || tags1.length === 0 || tags2.length === 0) return 0;
  
  const set1 = new Set(tags1.map(tag => tag.toLowerCase()));
  const set2 = new Set(tags2.map(tag => tag.toLowerCase()));
  
  const intersection = new Set([...set1].filter(x => set2.has(x)));
  const union = new Set([...set1, ...set2]);
  
  return intersection.size / union.size; // Jaccard similarity
}

// Update user preferences based on bookmark action
export function updateUserPreferences(userProfile, website, isBookmark = true) {
  const updatedProfile = { ...userProfile };
  
  if (isBookmark) {
    // Add to bookmarks if not already there
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
    
    // Increase preference for these tags
    (website.keywords || []).forEach(tag => {
      const normalizedTag = tag.toLowerCase();
      updatedProfile.preferredTags[normalizedTag] = 
        (updatedProfile.preferredTags[normalizedTag] || 0) + 1;
    });
  } else {
    // Remove bookmark
    updatedProfile.bookmarks = updatedProfile.bookmarks.filter(b => b.id !== website.id);
    
    // Decrease preference for these tags
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

// Calculate personalized weight for a website based on user preferences
export function calculatePersonalizedWeight(website, userProfile) {
  let baseWeight = website.weight || 1;
  let personalizedMultiplier = 1;
  
  // If user has no preferences yet, return base weight
  if (!userProfile.preferredTags || Object.keys(userProfile.preferredTags).length === 0) {
    return baseWeight;
  }
  
  // Calculate preference score based on tag matches
  const websiteTags = (website.keywords || []).map(tag => tag.toLowerCase());
  let preferenceScore = 0;
  let totalPreferences = Object.values(userProfile.preferredTags).reduce((sum, count) => sum + count, 0);
  
  websiteTags.forEach(tag => {
    const tagPreference = userProfile.preferredTags[tag] || 0;
    preferenceScore += tagPreference / totalPreferences;
  });
  
  // Boost weight based on preference score (up to 5x multiplier)
  personalizedMultiplier = 1 + (preferenceScore * 4);
  
  // Additional boost for similar sites to bookmarks
  const bookmarkSimilarityBoost = calculateBookmarkSimilarityBoost(website, userProfile);
  personalizedMultiplier *= (1 + bookmarkSimilarityBoost);
  
  return baseWeight * personalizedMultiplier;
}

// Calculate boost based on similarity to bookmarked sites
function calculateBookmarkSimilarityBoost(website, userProfile) {
  if (!userProfile.bookmarks || userProfile.bookmarks.length === 0) return 0;
  
  let maxSimilarity = 0;
  
  userProfile.bookmarks.forEach(bookmark => {
    const similarity = calculateTagSimilarity(website.keywords || [], bookmark.tags || []);
    maxSimilarity = Math.max(maxSimilarity, similarity);
  });
  
  // Return boost factor (0 to 2x additional multiplier)
  return maxSimilarity * 2;
}

// Generate AI-powered recommendations based on user preferences
export function generateRecommendations(allWebsites, userProfile, limit = 5) {
  if (!userProfile.preferredTags || Object.keys(userProfile.preferredTags).length === 0) {
    // No preferences yet, return popular sites
    return allWebsites
      .sort((a, b) => (b.rating * b.views) - (a.rating * a.views))
      .slice(0, limit);
  }
  
  // Score all websites based on user preferences
  const scoredWebsites = allWebsites.map(website => {
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

// Calculate diversity bonus to avoid echo chamber
function calculateDiversityBonus(website, userProfile) {
  const websiteTags = new Set((website.keywords || []).map(tag => tag.toLowerCase()));
  const userTagPreferences = Object.keys(userProfile.preferredTags || {});
  
  // Bonus for introducing new tags not in user preferences
  const newTags = [...websiteTags].filter(tag => !userTagPreferences.includes(tag));
  const diversityBonus = newTags.length * 0.5; // Small bonus for diversity
  
  return diversityBonus;
}

// Create personalized websites list with adjusted weights
export function createPersonalizedWebsitesList(websites, userProfile) {
  return websites.map(website => ({
    ...website,
    personalizedWeight: calculatePersonalizedWeight(website, userProfile),
    originalWeight: website.weight
  }));
}

// Track surf history for learning
export function updateSurfHistory(userProfile, website, action = 'view') {
  const updatedProfile = { ...userProfile };
  
  if (!updatedProfile.surfHistory) {
    updatedProfile.surfHistory = [];
  }
  
  updatedProfile.surfHistory.unshift({
    websiteId: website.id,
    action,
    timestamp: new Date().toISOString(),
    tags: website.keywords || []
  });
  
  // Keep only last 100 surf actions
  updatedProfile.surfHistory = updatedProfile.surfHistory.slice(0, 100);
  
  return updatedProfile;
}

// Analyze user's tag preferences for insights
export function analyzeUserPreferences(userProfile) {
  const tagCounts = userProfile.preferredTags || {};
  const totalPreferences = Object.values(tagCounts).reduce((sum, count) => sum + count, 0);
  
  if (totalPreferences === 0) {
    return {
      topTags: [],
      diversity: 0,
      totalBookmarks: 0,
      preferenceStrength: 0
    };
  }
  
  const topTags = Object.entries(tagCounts)
    .sort(([,a], [,b]) => b - a)
    .slice(0, 5)
    .map(([tag, count]) => ({
      tag,
      count,
      percentage: (count / totalPreferences * 100).toFixed(1)
    }));
  
  // Calculate diversity (how spread out preferences are)
  const diversity = Object.keys(tagCounts).length / Math.max(1, totalPreferences);
  
  return {
    topTags,
    diversity: Math.min(1, diversity),
    totalBookmarks: userProfile.bookmarks?.length || 0,
    preferenceStrength: Math.min(1, totalPreferences / 10) // Normalized strength
  };
}

// Suggest new tags based on user activity
export function suggestNewTags(userProfile, allWebsites) {
  const userTags = new Set(Object.keys(userProfile.preferredTags || {}));
  const allTags = new Set();
  
  // Collect all tags from websites
  allWebsites.forEach(website => {
    (website.keywords || []).forEach(tag => {
      allTags.add(tag.toLowerCase());
    });
  });
  
  // Find tags user hasn't explored yet
  const unexploredTags = [...allTags].filter(tag => !userTags.has(tag));
  
  // Score unexplored tags based on similarity to user preferences
  const scoredTags = unexploredTags.map(tag => {
    let score = 0;
    
    // Find websites with this tag and check similarity to user bookmarks
    const websitesWithTag = allWebsites.filter(website => 
      (website.keywords || []).map(k => k.toLowerCase()).includes(tag)
    );
    
    userProfile.bookmarks?.forEach(bookmark => {
      websitesWithTag.forEach(website => {
        score += calculateTagSimilarity(bookmark.tags || [], website.keywords || []);
      });
    });
    
    return { tag, score };
  });
  
  return scoredTags
    .sort((a, b) => b.score - a.score)
    .slice(0, 10)
    .filter(item => item.score > 0);
}