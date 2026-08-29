/*
 * Main site settings. Keep this object as valid JSON.
 * Do not store passwords, API keys or SMTP credentials here.
 * Replace all example.com addresses and set allowedHost before production deployment.
 */
window.SITE_CONFIG = {
  "brand": "Wildlife Match",
  "company": "CONFIGURE LEGAL OPERATOR",
  "logo": "favicon.svg",
  "email": "hello@example.com",
  "supportLine": "Local help for a wilder world.",
  "consentLabel": "I agree to share my request details with independent providers who may contact me with more information.",

  "pageTitles": {
    "index.html": "{brand} | Local Wildlife-Removal Introductions",
    "wildlife-removal.html": "Wildlife Removal | {brand}",
    "attic-cleanup-restoration.html": "Attic Cleanup & Restoration | {brand}",
    "entry-point-sealing-prevention.html": "Entry Point Sealing & Prevention | {brand}",
    "privacy.html": "Privacy Policy | {brand}",
    "terms.html": "Terms of Service | {brand}",
    "cookie-policy.html": "Cookie Policy | {brand}"
  },

  "disclaimer": "Disclaimer: This website is a free service that helps users connect with independent local service providers. The website owner and operator do not perform, supervise, direct, or guarantee any work. All contractors and service providers are independent businesses. This website does not warrant or guarantee estimates, availability, licensing status, workmanship, project outcomes, or services performed. Users are solely responsible for verifying that any provider they hire holds all licenses, insurance, permits, certifications, and other credentials required for the work. Any person depicted in a photograph or video is an actor or model unless expressly identified otherwise and is not necessarily a contractor or service provider available through this website.",

  "handler": {
    "recipient": "requests@example.com",
    "sender": "no-reply@example.com",
    "allowedHost": ""
  }
};
