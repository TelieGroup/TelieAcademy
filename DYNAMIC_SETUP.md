# TelieAcademy Dynamic Blog Setup Guide

## Overview
Your static HTML blog has been successfully converted to a dynamic PHP/MySQL application with the following features:

- ✅ MySQL database integration
- ✅ Dynamic post loading from database
- ✅ User authentication system
- ✅ Premium content protection
- ✅ Newsletter subscription with database storage
- ✅ Comment system with database storage
- ✅ Affiliate product management
- ✅ Banner ad integration
- ✅ API endpoints for AJAX functionality
- ✅ **All files converted to dynamic PHP templates**

## Setup Instructions

### 1. Database Setup
1. Start XAMPP (Apache and MySQL)
2. Open phpMyAdmin (http://localhost/phpmyadmin)
3. Create a new database named `telie_academy`
4. Import the `database.sql` file into your database

### 2. File Structure
```
TelieAcademy/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── Post.php             # Post management
│   ├── Category.php         # Category management
│   ├── Tag.php             # Tag management
│   ├── Comment.php         # Comment management
│   ├── Newsletter.php      # Newsletter management
│   ├── AffiliateProduct.php # Affiliate products
│   └── User.php            # User authentication
├── api/
│   ├── auth.php            # Authentication API
│   ├── newsletter.php      # Newsletter API
│   └── comments.php        # Comments API
├── database.sql            # Database schema and sample data
├── index.php              # Dynamic homepage ✅
├── post.php               # Dynamic post page ✅
├── categories.php          # Dynamic categories page ✅
├── tags.php               # Dynamic tags page ✅
├── posts.php              # All posts listing ✅
├── setup.php              # Setup verification script
└── DYNAMIC_SETUP.md       # This file
```

### 3. Test Accounts
- **Admin (Premium)**: username: `admin`, password: `password`
- **Regular User**: username: `john_doe`, password: `password`
- **Premium User**: username: `jane_smith`, password: `password`

### 4. Features Implemented

#### Database Tables
- `users` - User accounts and premium status
- `posts` - Blog posts with premium content flags
- `categories` - Post categories
- `tags` - Post tags
- `comments` - User comments
- `newsletter_subscribers` - Newsletter subscriptions
- `affiliate_products` - Affiliate product management
- `ad_placements` - Banner ad placements

#### Dynamic Features
- **Homepage**: Fetches featured posts, categories, and stats from database
- **Post Pages**: Dynamic content loading with premium content protection
- **Categories Page**: Filter posts by category with dynamic data
- **Tags Page**: Filter posts by tags with dynamic data
- **Posts Listing**: All posts with pagination
- **Authentication**: Login/register system with session management
- **Comments**: Database-stored comments with approval system
- **Newsletter**: Email subscription with database storage
- **Premium Content**: Protected content for premium users only

#### API Endpoints
- `api/auth.php` - User login, register, logout, status check
- `api/newsletter.php` - Newsletter subscription
- `api/comments.php` - Comment submission

### 5. ✅ All Files Converted

#### Completed Conversions
1. **index.html → index.php** ✅
   - Dynamic featured posts from database
   - Dynamic categories and stats
   - Premium content filtering

2. **categories.html → categories.php** ✅
   - Dynamic category filtering
   - Posts fetched from database
   - Category-based navigation

3. **tags.html → tags.php** ✅
   - Dynamic tag filtering
   - Popular tags from database
   - Tag-based post navigation

4. **Individual Post Pages** ✅
   - `post.php` handles all posts dynamically
   - URL format: `post.php?slug=post-slug`
   - Premium content protection
   - Dynamic comments system

5. **New Posts Listing** ✅
   - `posts.php` for all posts with pagination
   - Dynamic post count and navigation

### 6. Testing

1. **Access Setup**: Visit `http://localhost/TelieAcademy/setup.php`
2. **Test Homepage**: Visit `http://localhost/TelieAcademy/`
3. **Test Posts**: Visit `http://localhost/TelieAcademy/post.php?slug=getting-started-with-es6-features`
4. **Test Categories**: Visit `http://localhost/TelieAcademy/categories.php`
5. **Test Tags**: Visit `http://localhost/TelieAcademy/tags.php`
6. **Test All Posts**: Visit `http://localhost/TelieAcademy/posts.php`
7. **Test Login**: Use the test accounts above
8. **Test Newsletter**: Subscribe to newsletter
9. **Test Comments**: Add comments to posts

### 7. Database Configuration

The database configuration is in `config/database.php`. Default settings:
- Host: `localhost`
- Database: `telie_academy`
- Username: `root`
- Password: `` (empty)

Update these values if your MySQL setup is different.

### 8. Sample Data

The database includes sample data:
- 5 blog posts (3 free, 2 premium)
- 5 categories
- 12 tags
- 3 test users
- 5 affiliate products
- Sample comments and newsletter subscribers

### 9. Security Notes

- Passwords are hashed using PHP's `password_hash()`
- Input is sanitized using `htmlspecialchars()`
- SQL injection protection with prepared statements
- Session-based authentication

### 10. Customization

- **Add Posts**: Insert into `posts` table
- **Add Categories**: Insert into `categories` table
- **Add Tags**: Insert into `tags` table
- **Add Affiliate Products**: Insert into `affiliate_products` table
- **Modify Styling**: Edit `styles.css`
- **Add Features**: Extend PHP classes in `includes/`

## Next Steps (Optional Enhancements)

### Premium Features
1. **Premium Content Section**
   - Create premium-only tutorials
   - Add upgrade prompts for non-premium users

2. **Affiliate Product Integration**
   - Add affiliate products to post pages
   - Create affiliate product management interface

3. **Advanced Features**
   - Search functionality
   - User profiles
   - Admin panel
   - Analytics integration

## Troubleshooting

### Database Connection Issues
- Ensure MySQL is running in XAMPP
- Check database credentials in `config/database.php`
- Verify database `telie_academy` exists

### Page Not Found
- Ensure Apache is running in XAMPP
- Check file permissions
- Verify .htaccess configuration (if using)

### Login Issues
- Check if sessions are enabled in PHP
- Verify user accounts exist in database
- Check browser console for JavaScript errors

### API Errors
- Check browser network tab for failed requests
- Verify API endpoint URLs
- Check PHP error logs in XAMPP

## Support

For issues or questions:
1. Check the setup script: `setup.php`
2. Review browser console for JavaScript errors
3. Check XAMPP error logs
4. Verify database connectivity

## ✅ Conversion Complete!

Your static HTML blog has been successfully converted to a fully dynamic PHP/MySQL application! All pages now fetch data from the database and provide a complete user experience with authentication, comments, newsletter subscriptions, and premium content protection.

The dynamic blog is now ready to use with full MySQL integration! 