/**
 * This class validates input data that recieves from user on pet map page.
 * We use export to use this class on MapMediatorApp.js to implement Mediator pattern.
 */
export class MapAndSightingDataValidation {
  constructor() {}

  /**
   * This method aims to prevents XSS (Cross-Site Scripting) attack.
   * If attacker injects malicious script via pet name, comment, or other fields.
   * We escape all HTML special characters before rendering in innerHTML.
   */
  static escapeHTML(str) {
    if (!str && str !== 0) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  /**
   * This method handles invalid or oversized input
   * If attacker sends excessively long comments to overflow storage or degrade UI-
   * this method helps us enforce max 200 character limit on client side (also enforced server-side).
   */
  static validateComment(comment) {
    if (!comment || comment.trim().length === 0) {
      return 'Please enter a comment.';
    }
    if (comment.length > 200) {
      return 'Comment must be 200 characters or less.';
    }
    return null;
  }

  /**
   * This method handles forged location data
   * Meaning if attacker submits impossible coordinates (e.g., lat=9999) to corrupt map data.
   * Therefore this method Validate lat (-90 to 90) and lng (-180 to 180) range.
   */
  static validateCoordinates(lat, lng) {
    if (lat === null || lng === null || isNaN(lat) || isNaN(lng)) {
      return 'Please select a valid location.';
    }
    if (lat < -90 || lat > 90 || lng < -180 || lng > 180) {
      return 'Coordinates are out of range.';
    }
    return null;
  }

  /**
   * This method handles parameter tampering
   * If an attacker manipulates pet id to associate sightings with wrong pets or inject values.
   * Therefore the method ensure pet id is a positive integer.
   */
  static validatePetId(petId) {
    if (!petId || !Number.isInteger(Number(petId)) || Number(petId) <= 0) {
      return 'Invalid pet ID.';
    }
    return null;
  }
}