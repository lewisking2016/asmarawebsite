# Asmara Restaurant Website

A premium web application for Asmara Restaurant, featuring interactive menus, branch details, and newsletter management.

## Features
- Fully responsive design
- Ambience video sections
- Dynamic branch details
- Newsletter subscription
- Admin panel for management

## Installation & Setup
1. Clone the repository:
   ```bash
   git clone https://github.com/lewisking2016/asmarawebsite.git
   ```
2. Set up local web server pointing to the `frontend/` directory.
3. Import `asmara_backup_utf8.sql` database file.
4. For Google Analytics reports in the admin panel, set:
   - `ASMARA_GA4_PROPERTY_ID`
   - `ASMARA_GA4_SERVICE_ACCOUNT_JSON` or `ASMARA_GA4_SERVICE_ACCOUNT_KEY_FILE`
   - Optional: `ASMARA_GA4_SERVICE_ACCOUNT_JSON_B64`
   - `ASMARA_GA4_MEASUREMENT_ID` for the public site tracking snippet
   - Optional: `ASMARA_SITE_URL` if the production domain changes

5. Grant the service account email access to the GA4 property, and make sure the Analytics Data API is enabled in the Google Cloud project.
6. The current live site is `https://new.asmara.co.ke`, and the GA4 property used here should be attached to that domain until the final migration.
