 # GamersHUB

## Minor Update v1.2

The onboarding and email verification flow has been fixed and expanded.

- Added display name input with uniqueness validation during signup
- Added password confirmation and Terms of Service / Privacy Policy validation
- Added disabled Create Account state until passwords match and terms are accepted
- Added Google, Discord, and Steam signup buttons with coming-soon feedback
- Replaced verification links with seven-digit email verification codes
- Added Gmail SMTP configuration using PHPMailer
- Signup now stops when the verification email cannot be sent
- Added SMTP connection diagnostics at `test_connection/smtp_mailer_test.php`
- Added the three-step flow: gamer details, email verification, and preferences
- Added optional gamer type, platform, game, goal, and content preferences
- Added searchable games and the option to skip preferences
- Added preference tables and unique display name enforcement to the database schema

## Minor Update v1.1

GamersHUB now uses clean application URLs without exposing internal PHP file paths.

- Local home route: `http://localhost/gamers_hub/home`
- Production home route: `https://domain.com/home`
- Authentication routes: `/login`, `/signup`, and `/verify`
- Direct requests to internal PHP files redirect to their clean routes

## Official Update v1.0

GamersHUB now includes a complete authentication foundation for account access and onboarding.

### Authentication Setup

- Landing page with clear Log in and Sign up actions
- Login and account registration pages under `auth_page/`
- Secure password hashing with PHP `password_hash()` and `password_verify()`
- CSRF protection for authentication forms
- Session-backed login with database session records
- Authenticated route protection for `authenticated_pages/home.php`
- User profile creation during registration
- Email verification token support
- PHPMailer SMTP integration through environment-based configuration
- Database and mail credentials loaded from `.env`

## Official Update v0.1

GamersHUB is a professional interactive gaming space built for modern gamers. It brings gamers together through a social feed where they can share gaming thoughts, achievements, questions, and looking-for-group posts.

### Current Experience

- Responsive desktop and mobile layout
- Shared header, sidebar, and footer components
- Gamer profile avatar menu with Profile and Log out placeholders
- Notification button with coming-soon feedback
- Search interface for users, groups, and games
- Facebook-inspired home feed titled **Your feed**
- Create post modal with Thoughts, LFG, Achievements, and Question options
- Game selection, visibility settings, gamer tagging, emoji support, and media upload controls
- Demo feed content for Valorant and Minecraft
- Coming-soon states for Streams, GClan, Discover, My Games, Messages, Notifications, and Settings

### Technology Stack

- PHP
- HTML
- CSS with Bootstrap
- JavaScript
- MySQL/MariaDB

### Project Structure

- `db/` - Database schema and starter game data
- `includes/` - Shared header, sidebar, and footer PHP components
- `assets/css/` - General and shared layout styles
- `assets/js/` - General layout behavior and shared component scripts
- `.htaccess` - Clean URL routing and index path handling

### Database Foundation

The database schema currently supports users, profiles, sessions, games, user games, followers, posts, media, reactions, comments, shares, notifications, LFG posts, communities, and community memberships.

This release establishes the GamersHUB interface foundation for future authentication, database-backed feeds, social interactions, messaging, notifications, and gaming communities.
