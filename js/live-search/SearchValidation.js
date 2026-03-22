/**
 * SearchValidation handles all input validation for the live search feature.
 * Sanitises and validates user input before it is sent to the server.
 */
export class SearchValidation {
  constructor() {
    this.minLength = 1;
    this.maxLength = 100;
  }

  /**
   * Validates the search query string.
   * Returns sanitised query or null if invalid.
   */
  validateQuery(query) {
    if (typeof query !== 'string') return null;

    // Trim whitespace
    query = query.trim();

    if (query.length < this.minLength) return null;
    if (query.length > this.maxLength) return null;

    // Strip HTML tags
    query = query.replace(/<[^>]*>/g, '');

    return query;
  }

  /**
   * Validates species filter value.
   * Only allows known species values.
   */
  validateSpecies(species) {
    let allowed = ['', 'dog', 'cat', 'bird'];
    species = species.toLowerCase().trim();
    return allowed.includes(species) ? species : '';
  }

  /**
   * Validates status filter value.
   * Only allows known status values.
   */
  validateStatus(status) {
    let allowed = ['', 'lost', 'found'];
    status = status.toLowerCase().trim();
    return allowed.includes(status) ? status : '';
  }

  /**
   * Validates page number.
   * Must be a positive integer.
   */
  validatePage(page) {
    page = parseInt(page);
    return (isNaN(page) || page < 1) ? 1 : page;
  }

  /**
   * Validates pet ID.
   * Must be a positive integer.
   */
  validatePetId(petId) {
    petId = parseInt(petId);
    return (isNaN(petId) || petId < 1) ? null : petId;
  }
}