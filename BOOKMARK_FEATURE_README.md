# Bookmark Feature Implementation

This document describes the bookmark feature that has been implemented for the TelieAcademy platform.

## Overview

The bookmark feature allows users to save posts they want to read later. Users can bookmark posts from the main page, posts page, or individual post pages, and manage their bookmarks from a dedicated bookmarks page.

## Features

- **Add/Remove Bookmarks**: Users can bookmark and unbookmark posts with a single click
- **Bookmark Management**: Dedicated page to view and manage all bookmarked posts
- **Visual Feedback**: Bookmark buttons change appearance when posts are bookmarked
- **User Authentication**: Only logged-in users can use the bookmark feature
- **Responsive Design**: Works on all device sizes

## Database Structure

### Bookmarks Table

```sql
CREATE TABLE bookmarks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    post_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_post (user_id, post_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
);
```

## Files Added/Modified

### New Files
- `includes/Bookmark.php` - Bookmark class for database operations
- `api/bookmarks.php` - API endpoint for bookmark operations
- `bookmarks.php` - User bookmarks page
- `add_bookmarks_table.sql` - Database upgrade script

### Modified Files
- `includes/header.php` - Added bookmarks navigation link
- `includes/Post.php` - Added method to get posts with bookmark info
- `script.js` - Added bookmark functionality
- `styles.css` - Added bookmark button styles
- `index.php` - Updated bookmark buttons with post IDs
- `posts.php` - Updated bookmark buttons with post IDs
- `database.sql` - Added bookmarks table schema

## API Endpoints

### GET /api/bookmarks.php?action=user
Get user's bookmarks with pagination.

**Parameters:**
- `limit` (optional): Number of bookmarks per page (default: 20)
- `offset` (optional): Number of bookmarks to skip (default: 0)

**Response:**
```json
{
    "success": true,
    "bookmarks": [...],
    "total": 25,
    "hasMore": true
}
```

### GET /api/bookmarks.php?action=check&post_id={id}
Check if a specific post is bookmarked by the current user.

**Response:**
```json
{
    "success": true,
    "isBookmarked": true,
    "bookmarkCount": 5
}
```

### POST /api/bookmarks.php
Add a bookmark for a post.

**Body:**
```json
{
    "post_id": 123
}
```

**Response:**
```json
{
    "success": true,
    "message": "Post bookmarked successfully",
    "isBookmarked": true,
    "bookmarkCount": 6
}
```

### DELETE /api/bookmarks.php
Remove a bookmark for a post.

**Body:**
```json
{
    "post_id": 123
}
```

**Response:**
```json
{
    "success": true,
    "message": "Bookmark removed successfully",
    "isBookmarked": false,
    "bookmarkCount": 5
}
```

## Usage

### For Users

1. **Bookmark a Post**: Click the bookmark button (bookmark icon) on any post card
2. **View Bookmarks**: Click the bookmark icon in the header navigation
3. **Remove Bookmark**: Click the bookmark button again on a bookmarked post, or use the remove button on the bookmarks page

### For Developers

#### Adding Bookmark Buttons

```html
<button class="btn btn-sm btn-outline-secondary bookmark-btn" 
        data-post-id="<?php echo $post['id']; ?>" 
        title="Bookmark">
    <i class="fas fa-bookmark"></i>
</button>
```

#### Checking Bookmark Status

```php
$bookmark = new Bookmark();
$isBookmarked = $bookmark->isBookmarked($userId, $postId);
```

#### Getting User Bookmarks

```php
$bookmark = new Bookmark();
$bookmarks = $bookmark->getUserBookmarks($userId, $limit, $offset);
```

## Installation

1. **Database Setup**: Run the `add_bookmarks_table.sql` script to create the bookmarks table
2. **File Upload**: Upload all new and modified files to your server
3. **Permissions**: Ensure the web server has write permissions for the uploads directory

## Security Features

- **Authentication Required**: All bookmark operations require user authentication
- **Input Validation**: Post IDs are validated before processing
- **SQL Injection Protection**: All database queries use prepared statements
- **CSRF Protection**: API endpoints validate user sessions

## Browser Support

- Modern browsers with ES6+ support
- Fallback for older browsers with polyfills
- Responsive design for mobile devices

## Performance Considerations

- Database indexes on `user_id` and `post_id` for fast queries
- Pagination for large bookmark lists
- Lazy loading of bookmark states
- Efficient database queries with JOINs

## Troubleshooting

### Common Issues

1. **Bookmark buttons not working**: Check if user is logged in and JavaScript is enabled
2. **Database errors**: Ensure the bookmarks table exists and has correct structure
3. **Permission denied**: Check file permissions and database user privileges

### Debug Mode

Enable debug logging in the Bookmark class by checking error logs for detailed error messages.

## Future Enhancements

- **Bookmark Categories**: Organize bookmarks into folders/categories
- **Bookmark Sharing**: Share bookmark collections with other users
- **Bookmark Export**: Export bookmarks to various formats
- **Bookmark Analytics**: Track reading patterns and preferences
- **Offline Support**: Cache bookmarks for offline reading

## Support

For issues or questions about the bookmark feature, check the error logs and ensure all files are properly uploaded and configured.
