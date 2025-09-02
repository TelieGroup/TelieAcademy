<?php
// Include session configuration
require_once dirname(__DIR__) . '/config/session.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - TelieAcademy' : 'TelieAcademy - Tech Tutorial Blog'; ?></title>
    <meta name="description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Learn modern web development with comprehensive tutorials on JavaScript, React, Python, and more.'; ?>">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    
    <!-- Open Graph Meta Tags -->
    <meta property="og:title" content="<?php echo isset($pageTitle) ? $pageTitle . ' - TelieAcademy' : 'TelieAcademy - Tech Tutorial Blog'; ?>">
    <meta property="og:description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Learn modern web development with comprehensive tutorials on JavaScript, React, Python, and more.'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>">
    <meta property="og:site_name" content="TelieAcademy">
    
    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo isset($pageTitle) ? $pageTitle . ' - TelieAcademy' : 'TelieAcademy - Tech Tutorial Blog'; ?>">
    <meta name="twitter:description" content="<?php echo isset($pageDescription) ? $pageDescription : 'Learn modern web development with comprehensive tutorials on JavaScript, React, Python, and more.'; ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    
    <!-- Theme Color for Mobile Browsers -->
    <meta name="theme-color" content="#007bff" id="themeColor">
    <meta name="color-scheme" content="light dark">
    
    <!-- Preconnect to external domains for performance -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
</head>
<body> 