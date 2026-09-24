# GamersHUB

### Versioning Convention

- **Official Update:** `major.0`
- **Minor Update:** `major.minor`, where `minor` is `1` or greater
- **Patch Update:** `major.minor.patch`, where `patch` is `1` or greater

## Minor Update v5.2

### Per-Device User Sessions and Secure Authentication

- Added per-device session creation so each successful login creates a fresh session record instead of reusing or replacing an existing session.
- Added a dedicated `user_sessions` table with hashed session tokens, device metadata, expiration, and revocation support.
- Added session bootstrapping with strict cookie settings and localhost-safe HTTPS handling for development.
- Added a central `requireAuth()` validator that checks the session token against the database on each protected request.
- Added session destruction and redirect handling when a stored session is missing, expired, or revoked.
- Enhanced logout behavior so logging out one device revokes only that device's session and leaves other active sessions intact.
- Added device-aware session metadata such as IP address, user agent, and device label for future security auditing and suspicious-session review.
- Kept the session layer separate from the PHP cookie so the browser does not directly control authentication state.
- Established the foundation for future active-session management, device review screens, and session revocation tools.

## Minor Update v5.1

### Password Reset, UTC Time, and Support Navigation

- Added password reset requests by email or username with generic responses that do not reveal whether an account exists.
- Added one-time, hashed password reset tokens stored in `password_reset_tokens` with one-hour expiration and database-backed validation before the new-password form is shown.
- Added secure password reset email delivery with clean `/reset/@username` links and hidden implementation paths.
- Added password confirmation, password hashing, token invalidation after use, and success/error modal states.
- Fixed reset-link validation by decoding encoded username route segments and aligning token expiration with the MySQL clock.
- Enforced UTC in PHP, application MySQL sessions, schema setup, and migrations.
- Added a live local timezone and UTC clock with signed offset and milliseconds to the dashboard header.
- Added new-tab navigation for Wiki, Changelog, Privacy Policy, and Terms of Use in the support sidebar.
- Refined the Wiki into a responsive project guide and added the shared `/partials/styles.css` entrypoint.

## Official Update v5.0

### Feed Discussions, Comments, and Normalized Companies

- Added feed post detail modals with full post content, author metadata, selected topics, full-size vertical media, and expandable comments.
- Added post reactions, comments, replies, and shares with authenticated and CSRF-protected interactions.
- Added inline comment expansion and live comment submission on dashboard feed cards.
- Added nested replies with explicit replied-to attribution, such as "User 2 replied to User 1".
- Added comment reactions and centralized comment/reply functions in the comments API.
- Added selectable game, developer, and publisher topics for posts.
- Added topic behavior that limits developer and publisher topics to General Update, Discussion, Review, and Question posts.
- Created normalized `company_catalog`, `game_companies`, and `user_companies` tables.
- Normalized developer and publisher names so each company is stored once and reused across games and user preferences.
- Updated the feed, account settings, onboarding preferences, My Games page, and catalog API to use normalized company relationships.
- Added fixed 160x160 dashboard media previews while preserving full-size media in post detail views.
- Added reply attribution, comment nesting, comment reactions, and related dashboard interaction fixes.

## Minor Update v4.1

### My Games and Update Feeds

- Added a dedicated My Games dashboard page for selected games and collected game updates.
- Added SCS Software RSS fetching for Euro Truck Simulator 2 and American Truck Simulator.
- Added Valorant game-update fetching from the public Valorant updates page.
- Added source hashing and duplicate detection for fetched articles.
- Added configurable source handling for RSS and HTML update pages.
- Changed the update fetch interval to one minute.
- Added game update source and update tables backed by `game_catalog`.
- Fixed dashboard font loading and My Games spacing to match the Analytics page.
- Added clean My Games routing and an active sidebar navigation item.

## Official Update v4.0

### Gamer's Preference Management

- Added a dedicated **Gamer's Preference** tab to account settings.
- Moved gaming style, voice chat availability, game platforms, and content preferences out of the Account tab.
- Added live game search by game name or developer with results loaded from the catalog API.
- Added Add/Remove controls for managing the games a user plays.
- Added live developer search and Add/Remove controls for developers a user likes.
- Added database-backed persistence for selected games and developers with catalog validation.
- Added migration support for the `user_developers` preference relation.

## Patch Update v3.2.3

### Profile and Dashboard Interaction Fixes

- Fixed profile warnings caused by the shared right sidebar overwriting the authenticated user data array
- Restored profile display names, usernames, avatars, and post card author data on authenticated profile pages
- Fixed broken profile post image URLs caused by the overwritten user data
- Made right-sidebar icon updates null-safe so optional layout controls cannot interrupt shared dashboard interactions
- Verified the affected PHP pages and shared JavaScript files with syntax checks

## Patch Update v3.2.2

### Notification Dropdown Fixes

- Restored the Facebook-inspired notification dropdown layout across all authenticated dashboard pages
- Moved shared notification dropdown styles into the global dashboard layout stylesheet
- Fixed the dropdown positioning so it anchors below the notification button instead of using conflicting fixed positioning
- Added shared stylesheet cache versioning so the styling fix is applied without requiring a manual hard refresh

## Patch Update v3.2.1

### Post Composer & Media Feed

- Added a click-to-open post creation modal with the current user's avatar and name
- Added audience controls for Public, Friends, Followers, and Only me posts
- Added game selection and post type controls for discussions, questions, looking-for-player posts, achievements, reviews, and general updates
- Added multipart image and video uploads with support for up to 10 attachments per post
- Added client-side video duration validation for clips up to 5 minutes and server-side media type and file size validation
- Added post media storage under `uploads/post/`, with multi-attachment posts grouped in `uploads/post/<username>_<post_id>/`
- Added ordered media records with dimensions and file metadata in `post_media`
- Added responsive feed galleries and collages for posts containing multiple images or videos
- Added visibility-aware feed filtering for the post author, public posts, followers, and mutual friends
- Added the `friends` post visibility option to the MySQL schema and migration

## Minor Update v3.2

### Your Feed Experience

- Renamed the dashboard home navigation item to **Your Feed** with a feed-specific icon
- Added a database-backed public feed showing posts from friends, followed users, and the wider community
- Added **Your Feed**, **Following**, and **Friends** feed tabs with relationship-based post filtering
- Added a CSRF-protected composer for publishing public text posts directly to the feed
- Added post author details, timestamps, media previews, reaction counts, comment counts, and share counts
- Added responsive feed styling that uses the full dashboard content width and matches the analytics page spacing
- Added feed search filtering through the shared dashboard search field

## Minor Update v3.1

### Community Right Sidebar

- Added a shared right sidebar positioned below the dashboard header across authenticated pages
- Added a collapsed-by-default community rail with an in-sidebar toggle and responsive mobile behavior
- Added database-backed mutual friend data, including online status, selected games, and weekly post activity
- Added online friends versus total friends counts
- Added a GClan members online placeholder marked as coming soon

## Patch Update v3.0.1

### Shared Layout and Branding Fixes

- Fixed the messaging page sizing and header layout to match the analytics dashboard reference
- Restored shared Bootstrap layout dependencies on the messaging page so header spacing and responsive sizing remain consistent
- Restored coming-soon modal styles and JavaScript actions on the messaging page
- Updated the shared dashboard latency label to display `Ping: <value>ms`
- Replaced the text-based `G` brand mark with the supplied GamersHUB logo across landing, authentication, and dashboard pages
- Added the supplied GamersHUB logo as the favicon across all page templates

## Official Update v3.0

### Messaging Experience

- Added a database-backed messaging page at `/message` with a clean Messenger-style interface
- Added conversation and message API support with authenticated access control
- Added conversation switching, search, online status, unread counts, message bubbles, and date separators
- Added message sending, emoji insertion, responsive mobile chat layout, and automatic realtime polling
- Added conversation, participant, and message tables to the database schema and migration
- Added JSON API error handling so messaging failures return readable API responses instead of HTML errors
- Added current connection latency measurement to the shared dashboard header
- Added clean URL redirects so internal message and dashboard PHP paths are hidden from the browser

## Patch Update v2.2.1

### Social Link Validation Fix

- Accepted social links without a protocol, such as `tiktok.com/@kape_073`
- Normalized social links to secure `https://` URLs when saving them
- Made existing scheme-less social links open correctly from profile pages
- Added accessible labels and external-link behavior to profile social icons

## Minor Update v2.2

### Social Profiles and Dashboard Fixes

- Added a Social tab to account settings for Discord, TikTok, Facebook, Twitch, Kick, GitHub, Spotify, YouTube, Apple Music, Steam, and X links
- Added database storage and migration support for profile social links
- Added bio and available social links to the profile header beneath the profile identity
- Social links display only when a user has provided them and open securely in a new tab
- Added Simple Icons CDN assets for social media links
- Standardized application interface icons on Google Material Symbols
- Made the dashboard header permanently sticky across desktop and mobile layouts
- Fixed profile header container nesting and spacing issues
- Matched profile page spacing to the analytics page layout
- Fixed the active database schema by applying the missing social profile columns

## Minor Update v2.1

### Settings and Media Fixes

- Fixed the application base URL resolution so redirects and asset paths resolve correctly under the app root instead of nested API folders
- Corrected the settings save flow to avoid redirecting users back to the login page when a valid authenticated session is active
- Ensured file calls and asset paths work correctly for the settings page, API endpoints, and shared layout resources
- Removed external URL support for profile and cover photo updates so only uploaded files are accepted for image changes
- Kept the photo upload flow limited to the actual file upload path and prevented URL-based image inputs from interfering with the save process
- Restricted compression to images larger than 5MB so smaller uploads are stored without unnecessary processing
- Normalized uploaded media URLs to public app-relative paths so browser image requests resolve correctly instead of using local filesystem paths
- Added stronger protection in the image upload flow to keep the compressor from running for already-safe files

## Official Update v2.0

### Profile Page Refresh

- Redesigned the profile page into a cleaner desktop-first layout with a cover photo, avatar, username metadata, and stat summary
- Added a modern profile header that collapses into a compact sticky state while scrolling
- Reworked the tab structure to show Posts, Games, Achievements, and About sections
- Added a proper empty-state for users with no public posts
- Integrated the DB-backed post fetch flow through the profile post API
- Kept the Games, Achievements, and About tabs as coming-soon placeholders for future feature builds
- Updated the current-user profile experience to show Edit Profile instead of Follow
- Removed the legacy profile styling flow and replaced it with the new profile layout styles and script behavior

### Account Settings & Storage Update

- Added a dedicated account settings page with tabs for Account, Privacy, Notifications, and Security
- Implemented profile controls for display name, bio, profile photo URL, cover photo URL, gaming style, voice chat availability, and content preference updates
- Added privacy controls for profile visibility and who can message the user
- Added notification toggles for email, push, and social alerts
- Added a security panel with active sessions, login history, and 2FA toggle support
- Added clean URL routing for `/settings` and removed internal PHP file exposure from the browser path
- Documented the upload storage convention for profile, cover, and post media files under `uploads/profile`, `uploads/cover`, and `uploads/post/post_<username>_<post_id>/`
- Added migration support in `db/migration.sql` for settings and storage-related database updates

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
- Authenticated dashboard route protection for `authenticated_pages/analytics.php`
- User profile creation during registration
- Email verification token support
- PHPMailer SMTP integration through environment-based configuration
- Database and mail credentials loaded from `.env`

## Minor Update v0.1

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
