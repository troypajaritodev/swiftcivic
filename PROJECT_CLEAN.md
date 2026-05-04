# SwiftCivic - Clean Project Structure

## Root Files (Upload These)

### Core PHP Files
- `index.php` - Landing page
- `header.php` - Main header (fixed: meta tags, CSS)
- `footer.php` - Main footer
- `auth.php` - Authentication (fixed: added missing functions)
- `db.php` - Database config (set to InfinityFree)
- `login.php` - Login page (fixed: filter_input/filter_var)
- `logout.php` - Logout (fixed: session destroy + redirect)
- `register.php` - Registration
- `apply.php` - Document application
- `track.php` - Track requests
- `profile.php` - User profile
- `payment.php` - Payment upload
- `citizen_dashboard.php` - User dashboard (fixed: path resolution)
- `download.php` - Document download (NEW FILE)

### JavaScript
- `qr_toggle.js` - QR code toggle (fixed: DOMContentLoaded, gcash_qr ID)

## Admin Folder (Upload `admin/`)

- `admin/index.php` - Admin dashboard
- `admin/header.php` - Admin header (fixed: meta tags)
- `admin/footer.php` - Admin footer
- `admin/verify.php` - Verify payments (fixed: file upload path)
- `admin/logs.php` - Activity logs
- `admin/export.php` - Export CSV (fixed: charset, fputcsv syntax)
- `admin/setup.php` - Admin setup

## Assets (Upload `assets/`)

### Images
- `assets/images/logo.svg` - Main logo
- `assets/images/logo.png` - Logo PNG
- `assets/images/logo.jpeg` - Logo JPEG
- `assets/images/gcash_qr.png` - GCash QR code

## Uploads Folder (Create on Server)

Create these folders and set permissions to 755:
- `uploads/documents/` - Released documents
- `uploads/ids/` - User ID uploads
- `uploads/receipts/` - Payment receipts

## Database

Import `swiftcivic.sql` (or the updated schema) to InfinityFree database.

## Deployment Steps

1. Upload all files to `/htdocs/` on InfinityFree
2. Set folder permissions:
   - `uploads/` → 755
   - `uploads/documents/` → 755
   - `uploads/ids/` → 755
   - `uploads/receipts/` → 755
   - `assets/images/` → 755
3. Import database SQL to InfinityFree
4. Test:
   - Landing page loads
   - Login works
   - Dashboard shows documents
   - Download works
   - Payment page shows QR
   - Admin panel accessible
   - Logout redirects to landing page

## Bugs Fixed

| File | Bug | Fix |
|------|-----|-----|
| `auth.php` | Missing functions | Added logAction(), redirectIfNotStaff(), getCurrentUser(), isStaff() |
| `header.php` | Meta tags | Fixed charset="UTF-8", initial-scale=1.0 |
| `logout.php` | No redirect | Now destroys session and redirects to index.php |
| `login.php` | Missing commas | Fixed filter_input() and filter_var() |
| `citizen_dashboard.php` | Path issues | Uses DIRECTORY_SEPARATOR |
| `download.php` | Missing file | Created new file |
| `qr_toggle.js` | JS bugs | Fixed DOMContentLoaded and gcash_qr ID |
| `admin/header.php` | Meta tags | Fixed charset and viewport |
| `admin/verify.php` | File path | Changed to __DIR__ for cross-platform |
| `admin/export.php` | Syntax errors | Fixed charset and fputcsv() |

## Cleaned Up

Deleted test/debug files:
- test_*.php
- debug.php
- quick_test.php
- comprehensive_test.php
- final_test.php
- force_login.php
- simple_test.php
- auth_test.php
- auth_new.php
- login_new.php
- download_simple.php
- test_*.php
- *.md files (bug reports, checklists)
- .htaccess
- check_*.php
- fix_*.php
- register_success.php
- schema.sql
- mailer.php
- mail.log
- DEPLOYMENT_GUIDE.txt
- QR_CODE_SETUP.txt
- README.txt files

## Ready to Upload! ✅
