/*
 * Main site settings. Keep this object as valid JSON.
 * Do not store passwords, API keys or SMTP credentials here.
 * Replace all example.com addresses and set allowedHost before production deployment.
 * pageTitles, pageDescriptions and disclaimer support {brand} for the brand name.
 */
window.SITE_CONFIG = {
  "brand": "Wildlife Match",
  "company": "CONFIGURE LEGAL OPERATOR",
  "logo": "favicon.svg",
  "email": "hello@example.com",
  "supportLine": "Local help for a wilder world.",
  "consentLabel": "I agree to share my request details with independent providers who may contact me with more information.",

  "legal": {
    "lastUpdated": "August 29, 2026",
    "governingLaw": "CONFIGURE GOVERNING LAW",
    "venue": "CONFIGURE LEGAL VENUE"
  },

  "pageTitles": {
    "index.html": "{brand} | Local Wildlife-Removal Introductions",
    "wildlife-removal.html": "Wildlife Removal | {brand}",
    "attic-cleanup-restoration.html": "Attic Cleanup & Restoration | {brand}",
    "entry-point-sealing-prevention.html": "Entry Point Sealing & Prevention | {brand}",
    "privacy.html": "Privacy Policy | {brand}",
    "terms.html": "Terms of Service | {brand}",
    "cookie-policy.html": "Cookie Policy | {brand}"
  },

  "pageDescriptions": {
    "index.html": "Organize a wildlife-removal request and explore introductions to independent local providers.",
    "wildlife-removal.html": "Organize a wildlife-removal request for possible introduction to independent providers.",
    "attic-cleanup-restoration.html": "Organize an attic cleanup and restoration request for possible introduction to independent providers.",
    "entry-point-sealing-prevention.html": "Organize an entry-point sealing and prevention request for possible introduction to independent providers.",
    "privacy.html": "Privacy Policy for {brand} and its independent-provider introduction service.",
    "terms.html": "Terms of Service for the {brand} independent-provider introduction platform.",
    "cookie-policy.html": "Cookie Policy for {brand}, including current essential cookies and browser storage."
  },

  "disclaimer": "Disclaimer: This website is a free service that helps users connect with independent local service providers. The website owner and operator do not perform, supervise, direct, or guarantee any work. All contractors and service providers are independent businesses. This website does not warrant or guarantee estimates, availability, licensing status, workmanship, project outcomes, or services performed. Users are solely responsible for verifying that any provider they hire holds all licenses, insurance, permits, certifications, and other credentials required for the work. Any person depicted in a photograph or video is an actor or model unless expressly identified otherwise and is not necessarily a contractor or service provider available through this website.",

  "handler": {
    "recipient": "requests@example.com",
    "sender": "no-reply@example.com",
    "allowedHost": ""
  }
};
