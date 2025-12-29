<?php
/**
 * Authentication Helper
 * Provides role-based authentication and authorization functions
 */

/**
 * Check if any admin is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['admin_id']) && 
           isset($_SESSION['admin_role']);
}

/**
 * Require Super Admin authentication
 * Redirects to login if not authenticated or not super admin
 */
function requireSuperAdmin() {
    if (!isLoggedIn()) {
        header("Location: admin_login.php");
        exit;
    }
    
    if ($_SESSION['admin_role'] !== 'super_admin') {
        header("Location: access_denied.php");
        exit;
    }
}

/**
 * Require Club Admin authentication
 * Redirects to login if not authenticated or not club admin
 */
function requireClubAdmin() {
    if (!isLoggedIn()) {
        header("Location: admin_login.php");
        exit;
    }
    
    if ($_SESSION['admin_role'] !== 'club_admin') {
        header("Location: access_denied.php");
        exit;
    }
}

/**
 * Get the logged-in club admin's club ID
 * Returns null if not a club admin
 */
function getClubId() {
    if (isLoggedIn() && $_SESSION['admin_role'] === 'club_admin') {
        return $_SESSION['club_id'] ?? null;
    }
    return null;
}

/**
 * Get the logged-in admin's username
 */
function getAdminUsername() {
    return $_SESSION['admin_username'] ?? null;
}

/**
 * Get the logged-in admin's role
 */
function getAdminRole() {
    return $_SESSION['admin_role'] ?? null;
}

/**
 * Check if current admin owns a specific club
 */
function ownsClub($clubId) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if ($_SESSION['admin_role'] === 'super_admin') {
        return true; // Super admin owns all
    }
    
    if ($_SESSION['admin_role'] === 'club_admin') {
        return getClubId() == $clubId;
    }
    
    return false;
}

/**
 * Check if current admin can manage an event
 * Verifies the event belongs to the admin's club
 */
function canManageEvent($eventId, $pdo) {
    if (!isLoggedIn()) {
        return false;
    }
    
    // Super admin cannot manage events
    if ($_SESSION['admin_role'] === 'super_admin') {
        return false;
    }
    
    // Club admin can only manage their own club's events
    if ($_SESSION['admin_role'] === 'club_admin') {
        $stmt = $pdo->prepare("SELECT club_id FROM events WHERE id = ?");
        $stmt->execute([$eventId]);
        $event = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($event) {
            return $event['club_id'] == getClubId();
        }
    }
    
    return false;
}
