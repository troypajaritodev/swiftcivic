# SwiftCivic - Civil Registry Document Request System

A web-based civil registry system for managing document requests (birth certificates, marriage certificates, death certificates) with online payment simulation and tracking.

## Features

- **Citizen Portal**: Register, login, apply for documents, upload requirements, track requests
- **Admin Dashboard**: Verify documents, update status, export reports, view logs
- **Payment Integration**: GCash QR code payment simulation
- **Request Tracking**: Real-time status updates with progress indicators
- **File Management**: Secure document uploads with path helper for cross-platform deployment

## Tech Stack

- **Backend**: PHP 7.4+ (PDO MySQL)
- **Frontend**: HTML5, Tailwind CSS (CDN), Vanilla JavaScript
- **Database**: MySQL/MariaDB
- **Deployment**: InfinityFree compatible (no htdocs requirement)

## Installation

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/swiftcivic.git
cd swiftcivic
```

### 2. Database Setup
1. Create a MySQL database
2. Import `database.sql` (located in deploy folder after deployment prep)
3. Update `db.php` with your database credentials

### 3. Local Development (XAMPP)
1. Place files in `C:\xampp\htdocs\swiftcivic\`
2. Start Apache and MySQL in XAMPP Control Panel
3. Access via `http://localhost/swiftcivic/`

### 4. Production Deployment (InfinityFree)
1. Create account at https://infinityfree.com
2. Upload all files to **root `/`** (not htdocs)
3. Create folders: `admin/`, `assets/images/`, `uploads/documents/`, `uploads/ids/`, `uploads/receipts/`
4. Import database via phpMyAdmin
5. Update `db.php` with InfinityFree credentials

## Configuration

### Database Settings
Copy `db.example.php` to `db.php` and update:
```php
define('DB_HOST', 'your_host');
define('DB_NAME', 'your_database');
define('DB_USER', 'your_username');
define('DB_PASS', 'your_password');
```

### File Upload Paths
The system uses `getFullPath()` helper function that works on both:
- Localhost (XAMPP): Uses `__DIR__` fallback
- InfinityFree: Uses `$_SERVER['DOCUMENT_ROOT']`

## File Structure

```
swiftcivic/
├── admin/              # Admin dashboard files
├── assets/
│   └── images/         # Logos, QR codes
├── uploads/            # User uploads (not tracked by git)
│   ├── documents/
│   ├── ids/
│   └── receipts/
├── apply.php           # Document application
├── auth.php            # Authentication helpers
├── db.php              # Database config (gitignored)
├── login.php           # Login page
├── register.php        # Registration
├── track.php           # Request tracking
└── README.md
```

## Default Admin Account

After database setup, create an admin user:
```sql
INSERT INTO users (full_name, email, password, role) 
VALUES ('Admin', 'admin@swiftcivic.com', '$2y$10$hashed_password_here', 'admin');
```

Generate password hash:
```php
echo password_hash('your_password', PASSWORD_DEFAULT);
```

## Security Features

- Session-based authentication
- Password hashing with `password_hash()`
- SQL injection prevention (PDO prepared statements)
- File upload validation (type, size)
- Frame-busting JavaScript
- `.htaccess` security rules

## Troubleshooting

### HTTP 500 Error
- Check PHP syntax: `php -l filename.php`
- Verify `db.php` credentials
- Check `getFullPath()` returns correct path

### File Upload Fails
- Verify folder permissions (755)
- Check `uploads/` folders exist
- Confirm PHP `upload_max_filesize` and `post_max_size`

### InfinityFree Deployment
- Upload to **root `/`**, not `/htdocs`
- Use File Manager or FTP (`ftpupload.infinityfree.com`)
- Import database via phpMyAdmin in control panel

## License

MIT License - Feel free to use for educational or commercial projects.

## Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/YourFeature`)
3. Commit changes (`git commit -m 'Add some feature'`)
4. Push to branch (`git push origin feature/YourFeature`)
5. Open a Pull Request

## Contact

For issues or questions, please open an issue on GitHub.
