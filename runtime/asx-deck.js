
export const ASX = {
  validateDeck(deck) {
    return deck && deck.type === 'asx-deck' && Array.isArray(deck.pairs);
  }
};
