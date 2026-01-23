# WP Rocket Banners Documentation

This document provides a comprehensive overview of all banners displayed in the WP Rocket plugin, including their content, display conditions, and code locations.

## Table of Contents

1. [Promo Banner](#1-promo-banner)
2. [Renewal Soon Banner](#2-renewal-soon-banner)
3. [Renewal Expired Banner](#3-renewal-expired-banner)
4. [Renewal Expired Banner (OCD Active)](#4-renewal-expired-banner-ocd-active)
5. [Renewal Expired Banner (OCD Disabled)](#5-renewal-expired-banner-ocd-disabled)
6. [Upgrade Section/Popin](#6-upgrade-sectionpopin)
7. [Rocket Insights Promotion Notice](#7-rocket-insights-promotion-notice)
8. [Rocket Insights License Banner](#8-rocket-insights-license-banner)
9. [Rocket Insights Quota Banner](#9-rocket-insights-quota-banner)

---

## 1. Promo Banner

### Description
Displays promotional offers when WP Rocket is running special promotions (e.g., Black Friday, seasonal sales).

### Content
- **Title**: Shows discount percentage and promotion name (e.g., "30% off Black Friday promotion is live!")
- **Message**: Dynamic message encouraging users to upgrade their license with specific promotion details
- **Countdown Timer**: Shows remaining days, hours, minutes, and seconds until promotion ends
- **CTA Button**: "Upgrade now" - Opens the upgrade popin modal

### Display Conditions
Located in: `inc/Engine/License/Upgrade.php` - Method: `display_promo_banner()`

The banner displays when:
- ✅ User can upgrade (has a license that can be upgraded to a higher tier)
- ✅ A promotion is currently active (`pricing->is_promo_active()`)
- ✅ License is NOT expiring in less than 30 days
- ✅ User is NOT a new user (license purchased more than 14 days ago)
- ✅ NOT a white-label account
- ✅ User has NOT dismissed the banner (transient `rocket_promo_banner_{user_id}` not set)

### Code Locations
- **Logic**: `inc/Engine/License/Upgrade.php` (lines 115-136)
- **View**: `inc/Engine/License/views/promo-banner.php`
- **Subscriber Hook**: `inc/Engine/License/Subscriber.php` (line 52) - `rocket_before_dashboard_content`
- **Dismiss Handler**: `inc/Engine/License/Upgrade.php` (lines 143-159)

### AJAX Actions
- **Dismiss**: `wp_ajax_rocket_dismiss_promo` sets transient for 2 weeks

---

## 2. Renewal Soon Banner

### Description
Alerts users when their license is about to expire within 30 days.

### Content
- **Countdown Timer**: Shows remaining days, hours, minutes, and seconds until license expiration
- **Message**: "Your WP Rocket license is about to expire: you will soon lose access to product updates and support."
- **Additional Info**: For users with grandfathered pricing, shows the renewal discount and price
- **CTA Button**: "Renew now" - Links to user's renewal URL

### Display Conditions
Located in: `inc/Engine/License/Renewal.php` - Method: `display_renewal_soon_banner()`

The banner displays when:
- ✅ License is NOT expired yet
- ✅ License expires in less than 30 days
- ✅ Auto-renewal is NOT enabled
- ✅ NOT a white-label account

### Code Locations
- **Logic**: `inc/Engine/License/Renewal.php` (lines 56-85)
- **View**: `inc/Engine/License/views/renewal-soon-banner.php`
- **Subscriber Hook**: `inc/Engine/License/Subscriber.php` (line 53) - `rocket_before_dashboard_content` (priority 11)
- **Helper Method**: `is_expired_soon()` (lines 262-270)

---

## 3. Renewal Expired Banner

### Description
Standard banner shown when the license has expired (general case).

### Content
- **Title**: "Your WP Rocket license is expired!"
- **Message**: "Your website could be much faster if it could take advantage of our **new features and enhancements**. 🚀"
- **CTA Button**: "Renew now" - Links to user's renewal URL
- **Dismissible**: Yes (sets transient for 1 month)

### Display Conditions
Located in: `inc/Engine/License/Renewal.php` - Method: `display_renewal_expired_banner()`

The banner displays when:
- ✅ License has an expiration date (not 0)
- ✅ License is expired
- ✅ NOT a white-label account
- ✅ User has NOT dismissed it (transient `rocket_renewal_banner_{user_id}` not set)
- ✅ OCD (Optimize CSS Delivery) is NOT enabled OR expired for more than 90 days
- ✅ If auto-renew is enabled, only shows after 4+ days of expiration

### Code Locations
- **Logic**: `inc/Engine/License/Renewal.php` (lines 94-171, specifically 150-170)
- **View**: `inc/Engine/License/views/renewal-expired-banner.php`
- **Subscriber Hook**: `inc/Engine/License/Subscriber.php` (line 54) - `rocket_before_dashboard_content` (priority 12)
- **Dismiss Handler**: `inc/Engine/License/Renewal.php` (lines 215-231)

### AJAX Actions
- **Dismiss**: `wp_ajax_rocket_dismiss_renewal` sets transient for 1 month

---

## 4. Renewal Expired Banner (OCD Active)

### Description
Specialized banner shown when license is expired but Optimize CSS Delivery (OCD) is still active. Warns users about imminent feature loss.

### Content
- **Title**: "You will soon lose access to some features."
- **Message**: "You need an **active license to continue optimizing your CSS delivery**. The Remove Unused CSS and Load CSS asynchronously features are great options to address the PageSpeed Insights recommendations and improve your website performance. These features will be **automatically disabled on {date}**."
- **Disabled Date**: Shows when OCD features will be disabled (15 days after expiration)
- **CTA Button**: "Renew now" - Links to user's renewal URL
- **Dismissible**: Yes

### Display Conditions
Located in: `inc/Engine/License/Renewal.php` - Method: `display_renewal_expired_banner()`

The banner displays when:
- ✅ License is expired
- ✅ OCD (Optimize CSS Delivery) is enabled
- ✅ Expired for LESS than 15 days
- ✅ NOT a white-label account
- ✅ User has NOT dismissed it
- ✅ If auto-renew is enabled, only after 4+ days of expiration

### Code Locations
- **Logic**: `inc/Engine/License/Renewal.php` (lines 127-138)
- **View**: `inc/Engine/License/views/renewal-expired-banner-ocd.php`
- **Subscriber Hook**: `inc/Engine/License/Subscriber.php` (line 54) - `rocket_before_dashboard_content` (priority 12)

---

## 5. Renewal Expired Banner (OCD Disabled)

### Description
Banner shown after OCD features have been automatically disabled due to expired license (after 15 days).

### Content
- **Title**: "The Optimize CSS Delivery feature is disabled."
- **Message**: "You can no longer use the Remove Unused CSS or Load CSS asynchronously options. You need an **active license** to keep optimizing your CSS delivery, which addresses a PageSpeed Insights recommendation and improves your page performance."
- **CTA Button**: "Renew now" - Links to user's renewal URL
- **Dismissible**: Yes

### Display Conditions
Located in: `inc/Engine/License/Renewal.php` - Method: `display_renewal_expired_banner()`

The banner displays when:
- ✅ License is expired
- ✅ OCD (Optimize CSS Delivery) WAS enabled
- ✅ Expired for MORE than 15 days but LESS than 90 days
- ✅ NOT a white-label account
- ✅ User has NOT dismissed it

### Code Locations
- **Logic**: `inc/Engine/License/Renewal.php` (lines 139-149)
- **View**: `inc/Engine/License/views/renewal-expired-banner-ocd-disabled.php`
- **Subscriber Hook**: `inc/Engine/License/Subscriber.php` (line 54) - `rocket_before_dashboard_content` (priority 12)

---

## 6. Upgrade Section/Popin

### Description
Not a banner per se, but an upgrade promotion UI that appears in the license info block and as a modal popin.

### Components

#### A. Upgrade Section
- **Location**: Dashboard license info block
- **Display**: Link/button to trigger upgrade popin
- **Hook**: `rocket_dashboard_license_info`

#### B. Upgrade Popin (Modal)
- **Content**: 
  - Shows available upgrade options (e.g., Plus → Infinite, Single → Plus)
  - Displays pricing information
  - Shows promotion pricing if active
  - Lists features/benefits of upgrading
- **CTA**: Upgrade buttons for each available tier
- **Trigger**: "Upgrade now" button or upgrade section link

### Display Conditions
Located in: `inc/Engine/License/Upgrade.php` - Methods: `display_upgrade_section()` and `display_upgrade_popin()`

Both display when:
- ✅ License is NOT expired
- ✅ User has available upgrades (not on highest tier)
- ✅ NOT a white-label account (popin only)

### Code Locations
- **Section Logic**: `inc/Engine/License/Upgrade.php` (lines 43-49)
- **Popin Logic**: `inc/Engine/License/Upgrade.php` (lines 56-71)
- **Section View**: `inc/Engine/License/views/upgrade-section.php`
- **Popin View**: `inc/Engine/License/views/upgrade-popin.php`
- **Subscriber Hooks**: 
  - Section: `inc/Engine/License/Subscriber.php` (line 41) - `rocket_dashboard_license_info`
  - Popin: `inc/Engine/License/Subscriber.php` (line 42) - `rocket_settings_page_footer`

---

## 7. Rocket Insights Promotion Notice

### Description
WordPress admin notice promoting the new Rocket Insights feature (introduced in v3.20).

### Content
- **Message**: 
  - "**New in WP Rocket: Meet Rocket Insights, your built-in performance tracking tool!**"
  - "Starting from WP Rocket 3.20, you can track your key pages' performance directly from your dashboard and get in-depth insights."
  - "Add your first page, run the test, and keep your site fast. 🚀"
- **CTA Button**: "Run the test now!" - Links to Rocket Insights tab
- **Dismissible**: Yes
- **Status**: Success (green notice)

### Display Conditions
Located in: `inc/Engine/Admin/RocketInsights/Subscriber.php` - Method: `maybe_display_rocket_insights_promotion_notice()`

The notice displays when:
- ✅ Rocket Insights is enabled/allowed
- ✅ User has NO URLs added to Rocket Insights yet (0 URLs tracked)
- ✅ User has `rocket_manage_options` capability
- ✅ User has NOT dismissed the notice (not in `rocket_boxes` user meta)

### Code Locations
- **Logic**: `inc/Engine/Admin/RocketInsights/Subscriber.php` (lines 646-707)
- **Subscriber Hook**: Line 181 - `admin_notices`
- **View**: Uses `rocket_notice_html()` helper function
- **Dismiss Handler**: Standard WordPress notice dismissal (via `rocket_boxes` user meta)

---

## 8. Rocket Insights License Banner

### Description
Banner displayed in the Rocket Insights tab showing license/plan information and upgrade options.

### Content
- Varies based on user's plan (Free, Starter, Growth, etc.)
- Shows current plan limits
- Displays upgrade CTA if applicable
- May include promotional messaging

### Display Conditions
Located in: `inc/Engine/Admin/RocketInsights/Subscriber.php` - Method: `render_license_banner_section()`

The banner displays when:
- ✅ Rocket Insights is enabled/allowed
- ✅ Controller determines banner should be displayed (`display_banner()` returns true)

### Code Locations
- **Logic**: `inc/Engine/Admin/RocketInsights/Subscriber.php` (lines 449-460)
- **Render Method**: `inc/Engine/Admin/RocketInsights/Render.php`
- **Subscriber Hook**: Line 160 - `rocket_insights_tab_content` (priority 10)

---

## 9. Rocket Insights Quota Banner

### Description
Banner shown when free users reach their URL limits or exhaust their monthly credits.

### Content
- Shows information about quota limits
- Displays upgrade options to get more credits/URLs
- May show remaining quota information

### Display Conditions
Located in: `inc/Engine/Admin/RocketInsights/Subscriber.php` - Method: `should_show_quota_banner()`

The banner displays when:
- ✅ User is on the FREE plan
- ✅ AND either:
  - No remaining URL slots available, OR
  - No credits left for testing

### Code Locations
- **Logic**: `inc/Engine/Admin/RocketInsights/Subscriber.php` (lines 433-442)
- **Render**: Passed as data to `render_rocket_insights_urls_table()` method (line 420)
- **Display Location**: Rocket Insights tab content area

---

## Additional Banner-Related Features

### Notification Bubbles

#### 1. Promo Notification Bubble
- **Location**: WP Rocket menu item in admin sidebar
- **Content**: "!" badge
- **Display**: When promotion is active and user can upgrade
- **Duration**: Dismissed automatically when user visits WP Rocket dashboard
- **Code**: `inc/Engine/License/Upgrade.php` (lines 79-108)

#### 2. Expired License Notification Bubble
- **Location**: WP Rocket menu item in admin sidebar
- **Content**: "!" badge (with different styling)
- **Display**: When license is expired and certain conditions are met
- **Duration**: Dismissed when user visits dashboard and transient is set
- **Code**: `inc/Engine/License/Renewal.php` (lines 583-636)

### License Expiration Warnings (Inline)

#### OCD Setting Warning
- **Location**: Next to "Optimize CSS Delivery" checkbox in settings
- **Content**: Warning icon with message about license expiration
- **Variations**:
  - "You need a valid license to continue using this feature. **Renew now** before losing access."
  - "You need an active license to enable this option. **Renew now**."
  - "You need an active license to enable this option. **More info**." (white-label)
- **Code**: `inc/Engine/License/Renewal.php` (lines 463-574)

---

## Summary Table

| Banner Type | Location | Dismissible | Target Audience | Priority |
|------------|----------|-------------|-----------------|----------|
| Promo Banner | Dashboard | Yes (2 weeks) | Upgradeable users | Normal |
| Renewal Soon | Dashboard | No | Expiring licenses | High |
| Renewal Expired (Standard) | Dashboard | Yes (1 month) | Expired licenses | High |
| Renewal Expired (OCD) | Dashboard | Yes (1 month) | Expired w/ OCD active | Critical |
| Renewal Expired (OCD Disabled) | Dashboard | Yes (1 month) | Expired w/ OCD disabled | Critical |
| Upgrade Section | License Block | N/A | Upgradeable users | Normal |
| Upgrade Popin | Modal | N/A | Upgradeable users | Normal |
| Insights Promotion | Admin Notice | Yes (permanent) | All users (new feature) | Normal |
| Insights License Banner | Insights Tab | No | Insights users | Normal |
| Insights Quota Banner | Insights Tab | No | Free users at limits | High |

---

## Hook Reference

### Display Hooks
- `rocket_before_dashboard_content` - Main dashboard banners (promo, renewal)
- `rocket_dashboard_license_info` - Upgrade section in license block
- `rocket_settings_page_footer` - Upgrade popin modal
- `rocket_menu_title` - Notification bubbles
- `admin_notices` - WordPress admin notices (Insights promotion)
- `rocket_insights_tab_content` - Rocket Insights tab banners
- `rocket_before_add_field_to_settings` - Inline setting warnings

### AJAX Actions
- `wp_ajax_rocket_dismiss_promo` - Dismiss promo banner
- `wp_ajax_rocket_dismiss_renewal` - Dismiss renewal banner

### Data Hooks
- `rocket_localize_admin_script` - Add countdown/expiration data to JS
- `rocket_menu_title` - Add notification bubbles to menu

---

## File Structure

```
inc/Engine/License/
├── Renewal.php                                    # Renewal banner logic
├── Upgrade.php                                    # Promo & upgrade logic
├── Subscriber.php                                 # Event management
└── views/
    ├── promo-banner.php                          # Promo banner template
    ├── renewal-soon-banner.php                   # Renewal soon template
    ├── renewal-expired-banner.php                # Standard expired template
    ├── renewal-expired-banner-ocd.php            # OCD active template
    ├── renewal-expired-banner-ocd-disabled.php   # OCD disabled template
    ├── upgrade-section.php                       # Upgrade section template
    └── upgrade-popin.php                         # Upgrade modal template

inc/Engine/Admin/RocketInsights/
├── Subscriber.php                                 # Rocket Insights events
├── Render.php                                    # Rendering logic
└── Controller.php                                # Business logic
```

---

## Notes

1. **White-Label Accounts**: Most license-related banners are hidden for white-label accounts via `WP_ROCKET_WHITE_LABEL_ACCOUNT` constant.

2. **Transient Keys**: 
   - `rocket_promo_banner_{user_id}` - Promo banner dismissal (2 weeks)
   - `rocket_renewal_banner_{user_id}` - Renewal banner dismissal (1 month)
   - `rocket_promo_seen_{user_id}` - Promo notification bubble (2 weeks)
   - `wpr_dashboard_seen_{user_id}` - Expired notification bubble (15 days or 1 year)

3. **Auto-Renewal Logic**: Users with auto-renewal enabled see modified banner timing to account for grace periods.

4. **OCD Feature**: Special handling for "Optimize CSS Delivery" feature which requires active license. Has a 15-day grace period after expiration before being disabled.

5. **Grandfather Pricing**: Users with legacy pricing see custom messaging about their discounted renewal rates.

---

Last Updated: 2026-01-23
