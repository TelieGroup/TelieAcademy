# Image Upload Feature for Posts

## Overview
This feature allows administrators to upload images directly from their computer when creating or editing blog posts. It provides a user-friendly interface with preview capabilities and proper validation.

## Features

### 1. Image Upload from Computer
- **File Selection**: Click "Upload from Computer" to browse and select image files
- **Supported Formats**: JPG, PNG, GIF, WebP
- **File Size Limit**: Maximum 5MB per image
- **Image Preview**: See a preview of the selected image before upload
- **Auto-fill Alt Text**: Automatically fills alt text with the filename (without extension)

### 2. Image URL Input
- **URL Input**: Enter image URLs from external sources
- **URL Validation**: Ensures valid URL format before insertion
- **Alt Text & Caption**: Add accessibility and descriptive information

### 3. Enhanced Image Insertion
- **Responsive Images**: All images use Bootstrap's `img-fluid` class for responsive design
- **Caption Support**: Optional captions using Bootstrap's figure component
- **Accessibility**: Proper alt text support for screen readers

## How to Use

### Step 1: Access the Image Insertion Tool
1. Go to Admin Panel → Posts
2. Create a new post or edit an existing one
3. Click the "Insert Image" button in the rich text editor toolbar

### Step 2: Choose Image Source
- **Upload from Computer**: Click this button to upload a local image file
- **Enter Image URL**: Click this button to insert an image from a URL

### Step 3: Upload from Computer
1. Click "Choose File" and select an image from your computer
2. The image will be previewed automatically
3. Add alt text (descriptive text for accessibility)
4. Add an optional caption
5. Click "Upload & Insert"

### Step 4: Insert from URL
1. Enter the image URL
2. Add alt text for accessibility
3. Add an optional caption
4. Click "Insert Image"

## Technical Details

### File Storage
- Images are stored in `uploads/posts/` directory
- Unique filenames are generated to prevent conflicts
- Original filenames are preserved in the database

### Database Integration
- A `media` table is automatically created if it doesn't exist
- Stores metadata including filename, path, dimensions, and upload information
- Links media to the user who uploaded it

### Security Features
- File type validation (only images allowed)
- File size limits (5MB maximum)
- Admin-only access
- Secure file naming to prevent path traversal attacks

## File Structure
```
admin/
├── posts.php              # Main posts management file
├── upload_image.php       # Image upload handler
└── admin.css             # Admin styling

uploads/
└── posts/                # Image storage directory
```

## Error Handling
- File size validation
- File type validation
- Upload error handling
- User-friendly error messages
- Graceful fallbacks for database errors

## Browser Compatibility
- Modern browsers with File API support
- Responsive design for mobile devices
- Bootstrap 5 compatible

## Future Enhancements
- Image resizing and optimization
- Multiple image upload
- Drag and drop support
- Image library management
- CDN integration
- Image compression

## Troubleshooting

### Common Issues
1. **File too large**: Ensure image is under 5MB
2. **Invalid file type**: Use only JPG, PNG, GIF, or WebP formats
3. **Upload fails**: Check server permissions for uploads directory
4. **Image not displaying**: Verify file path and permissions

### Server Requirements
- PHP with file upload support enabled
- Write permissions on uploads directory
- Sufficient disk space for image storage
- Proper file upload limits in PHP configuration

## Support
For technical support or feature requests, please contact the development team. 