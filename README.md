=== Planning Events ===
Contributors: esperluweb  
Tags: events, calendar, schedule, shortcode, planning  
Requires at least: 5.0  
Tested up to: 6.8 
Stable tag: 1.0.0  
License: GPLv2 or later  
License URI: https://www.gnu.org/licenses/gpl-2.0.html  

A simple and modern event planner plugin. Add events and display them anywhere using a shortcode.

== Description ==

**Planning Events** is a lightweight plugin to manage and display a schedule of upcoming events in a clean and responsive way.

**Features:**

- Create and manage events with date, time, location, and description
- Display upcoming events in a styled list
- Use a shortcode to insert the event list anywhere
- Admin interface for easy event management
- Fully responsive design

== Installation ==

1. Upload the `planning-events` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Usage ==

### Add a new event

1. Go to **Planning Events** > **Add New**
2. Fill in the event details (title, description, date, time, location)
3. Click **Publish**

### Display the events list

Use the `[planning_events]` shortcode in any page or post.

**Optional parameters:**

- `limit` – Number of events to display (default: `-1` for all)
- `order` – Sort order (`ASC` for ascending, `DESC` for descending; default: `ASC`)

Example:

```
[planning_events limit="5" order="ASC"]
```

== Customization ==

To customize the look of the event list:

- Edit the plugin’s CSS file: `planning-events.css`
- Or override styles in your theme’s CSS or via the WordPress Customizer

== Frequently Asked Questions ==

= Is the plugin responsive? =  
Yes! The layout adapts to all screen sizes.

= Can I insert the planning on multiple pages? =  
Yes, just use the `[planning_events]` shortcode wherever you want.

== Screenshots ==

1. Admin interface to add events  
2. Example of the event list on the front-end  

== Changelog ==

= 1.0.0 =  
* Initial release

== License ==

This plugin is licensed under the GPLv2 or later.

== Author ==

Developed by [EsperluWeb](https://esperluweb.com)
