export class MapAndSightingDataValidation {
  constructor() {
  }

  /**
   * Threat: XSS (Cross-Site Scripting)
   * Risk: Attacker injects malicious script via pet name, comment, or other fields.
   * Mitigation: Escape all HTML special characters before rendering in innerHTML.
   */
  static escapeHTML(str) {
    if (!str && str !== 0) return '';
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
  }

  /**
   * Threat: Invalid or oversized input
   * Risk: Attacker sends excessively long comments to overflow storage or degrade UI.
   * Mitigation: Enforce max 200 character limit on client side (also enforced server-side).
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
   * Threat: Forged location data
   * Risk: Attacker submits impossible coordinates (e.g., lat=9999) to corrupt map data.
   * Mitigation: Validate lat (-90 to 90) and lng (-180 to 180) range.
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
   * Threat: Parameter tampering
   * Risk: Attacker manipulates pet ID to associate sightings with wrong pets or inject values.
   * Mitigation: Ensure pet ID is a positive integer.
   */
  static validatePetId(petId) {
    if (!petId || !Number.isInteger(Number(petId)) || Number(petId) <= 0) {
      return 'Invalid pet ID.';
    }
    return null;
  }
}