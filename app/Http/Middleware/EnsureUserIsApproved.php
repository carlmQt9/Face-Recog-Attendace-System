<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureUserIsApproved Middleware
 * 
 * This middleware ensures that ALL users (including admins) cannot access 
 * any protected routes until their account is approved by an existing admin.
 * 
 * - ALL roles (admin, teacher, student, etc.): Must wait for admin approval
 * - First admin must be created directly in database with is_approved = true
 * - Subsequent admins must be approved by existing admins
 * 
 * If an unapproved user tries to access protected routes, they are:
 * 1. Logged out immediately
 * 2. Session invalidated
 * 3. Redirected to login with warning message
 */
class EnsureUserIsApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (auth()->check()) {
            $user = auth()->user();
            
            // Check if user is approved - applies to ALL roles including admins
            if (!$user->is_approved) {
                // Log them out
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                // Redirect to login with warning message
                return redirect()->route('login')
                    ->with('warning', 'Your account is pending admin approval. Please wait for an administrator to confirm your account before you can access the system.');
            }
        }
        
        return $next($request);
    }
}
