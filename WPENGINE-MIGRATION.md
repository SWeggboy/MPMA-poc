# WP Engine Migration Guide for MPMA-poc

## Files Prepared
- **Database Export**: `mpma-poc-export-20260129.sql` (871KB)
- **wp-content size**: 155MB
- **Current Local URL**: https://mpma-poc.local

## Migration Steps

### 1. Prepare WP Engine Staging Environment
- Log into WP Engine User Portal
- Create or access your staging environment
- Note the staging SFTP credentials and URL

### 2. Upload Files via SFTP
**Upload these directories/files:**
```
wp-content/themes/tailpress/    (your custom theme)
wp-content/plugins/              (all plugins)
wp-content/uploads/              (media files)
```

**SFTP Connection:**
- Host: [Your WP Engine SFTP host]
- Username: mpma-sftp
- Password: Rd&Si,^D5
- Port: 2222 (WP Engine uses port 2222)
- Path: `/sites/[your-site-name]/`

**Do NOT upload:**
- Core WordPress files (wp-admin, wp-includes, index.php, etc.)
- wp-config.php (WP Engine manages this)
- .htaccess (WP Engine manages this)

### 3. Import Database

**Option A: Via WP Engine Portal (Recommended)**
1. Go to WP Engine User Portal → Your Site → Backup Points
2. Click "Import from SQL file"
3. Upload `mpma-poc-export-20260129.sql`
4. WP Engine will automatically handle URL replacement

**Option B: Via phpMyAdmin**
1. Access phpMyAdmin from WP Engine portal
2. Select database
3. Click Import tab
4. Upload SQL file
5. **CRITICAL**: Run search/replace for URLs (see below)

### 4. Search & Replace URLs (if using Option B)

You need to replace:
- **Old URL**: `https://mpma-poc.local`
- **New URL**: `https://[your-staging-site].wpengine.com`

**Use WP Engine's Search-Replace tool:**
```bash
# SSH into WP Engine (or use WP Engine's built-in tool)
wp search-replace 'https://mpma-poc.local' 'https://[your-staging-url].wpengine.com' --all-tables
```

Or use plugin: Better Search Replace (already included in WP Engine)

### 5. Update wp-config.php Settings (if needed)

WP Engine auto-configures most settings, but verify these in WP Engine portal:
- Database credentials (auto-configured)
- WP_HOME and WP_SITEURL (should point to staging URL)
- Remove any local-specific defines (WP_DEBUG, FS_METHOD, etc.)

### 6. Post-Migration Checklist

- [ ] Test login: Username `admin`, Password: `h4&QIKK#4Jprkw72Tt`
- [ ] Verify permalinks work (Events → /events/)
- [ ] Test Events Calendar functionality
- [ ] Check TailPress theme loads correctly
- [ ] Verify Vite assets compiled (may need to rebuild)
- [ ] Test Upcoming Events block
- [ ] Check WPForms plugin
- [ ] Test REST API endpoints: `/wp-json/wp/v2/tribe_organizer`
- [ ] Verify SSL certificate (WP Engine provides automatic SSL)

### 7. Rebuild Vite Assets (if needed)

If TailPress theme CSS/JS doesn't load:

```bash
# SSH into WP Engine
cd wp-content/themes/tailpress
npm install
npm run build
```

### 8. Flush Permalinks
- Go to Settings → Permalinks
- Click "Save Changes" without changing anything
- This regenerates .htaccess rules (though WP Engine uses nginx)

### 9. Flush Object Cache
- WP Engine uses Redis/Memcached
- Go to WP Engine portal → Utilities → Purge All Caches

## Important Notes

### Theme-Specific Files
Your custom theme `TailPress` has:
- Custom block: `inc/upcoming-events-block.js`
- Custom widget: `inc/class-upcoming-events-widget.php`
- Custom REST endpoints in `functions.php`

### Plugin Dependencies
- The Events Calendar 6.15.14
- WPForms Lite
- All plugins should work on WP Engine

### Development vs Production
Current wp-config.php has local optimizations that WP Engine handles:
- `WP_HTTP_BLOCK_EXTERNAL` - Remove this
- `AUTOMATIC_UPDATER_DISABLED` - WP Engine manages updates
- `WP_CACHE` - WP Engine has built-in caching
- Memory limits - WP Engine sets these

### SSL/HTTPS
- WP Engine provides free SSL certificates
- No need to configure .htaccess for HTTPS redirect
- WP Engine handles this automatically

## Quick SFTP Commands

### Using Command Line SFTP:
```bash
sftp -P 2222 mpma-sftp@[your-site].sftp.wpengine.com
cd sites/[your-site-name]/wp-content
put -r themes/tailpress
put -r plugins/*
put -r uploads/*
```

### Using FileZilla/Cyberduck:
- Protocol: SFTP
- Host: [your-site].sftp.wpengine.com
- Port: 2222
- Username: mpma-sftp
- Password: Rd&Si,^D5

## Troubleshooting

### White Screen of Death
- Check WP Engine error logs in portal
- Enable WP_DEBUG via WP Engine portal
- Verify PHP version compatibility (8.2+)

### Theme Not Loading
- Rebuild Vite assets
- Check file permissions (755 for directories, 644 for files)
- WP Engine sets permissions automatically

### Events Calendar Issues
- Flush permalinks
- Re-save Events → Settings
- Check REST API is accessible: `/wp-json/wp/v2/tribe_organizer`

### Performance Issues
- Enable WP Engine's CDN
- Enable Redis object cache (usually on by default)
- Optimize images with WP Engine's built-in tools

## Support
- WP Engine Support: support.wpengine.com
- Their chat support is excellent for migration help
