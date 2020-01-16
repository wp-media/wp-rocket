## Version 3.0 FAQ

### Do we need to wait for this to be bundled with WooCommerce?

No. Action Scheduler can be run as a standalone plugin. Action Scheduler 3.0 is also part of WooCommerce Subscriptions 3.0, so when updating WooCommerce Subscriptions, Action Scheduler will also be updated.

### Can we safely switch to action scheduler version 3.0 and ditch the custom tables plugin?

Yes! The Action Scheduler Custom Tables plugin code is now part of Action Scheduler itself (with a few improvements). We recommend disabling the Custom Tables plugin immediately after activating Action Scheduler 3.0, or a plugin containing Action Scheduler 3.0, like WooCommerce Subscriptions 3.0 and newer.

### How do we migrate from our own custom data store?

By default, Action Scheduler will only initiate a migration from the internal `WPPostStore` data store. To enable migration from any custom datastore add the following filter `add_filter( 'action_scheduler_migrate_data_store', '__return_true' );`.

### I'm currently on PHP <5.5. When I update PHP will the migration start automatically?

Yes! The migration is initiated as soon as all dependencies are met.

### I would like to update a plugin for testing that includes Action Scheduler 3.0 but would like to postpone the migration until that testing is complete. Is that possible?

Yes, while we recommend migrating to custom tables as soon as possible for performance reasons, you can use `add_filter( 'action_scheduler_migration_dependencies_met', '__return_false' );` to prevent the migration from initiating.

### Is there a strong likelihood of migration issues with any of the above?

There is always the possibilities of issues, but it is not a strong likelihood. We tested migrating from Action Scheduler 2.n with the custom data stores (including Action Scheduler Custom Tables plugin) active to Action Scheduler 3.0 on a number of test sites.

As with all major, and minor, upgrades, we still testing the update on a staging site before updating the live site. We also recommending taking a backup before updating the live site.

If you wish to undertake more comprehensive testing on a development or staging site before updating, or want to closely monitor the update on a live site, follow these steps:

#### Stage 1: Prepare your site:

1. Take a backup of your database (AS 3.0 migrates data to custom tables, this can not be undone, you’ll need to restore a backup to downgrade).
1. Go to Tools > Action Scheduler
1. Take a screenshot of the action status counts at the top of the page. Example screenshot: https://cld.wthms.co/kwIqv7


#### Stage 2: Install & Activate Action Scheduler 3.0 as a plugin

1. The migration will start almost immediately
1. Keep an eye on your error log
1. Report any notices, errors or other issues on GitHub

#### Stage 3: Verify Migration

1. Go to Tools > Action Scheduler
1. Take a screenshot of the action status counts at the top of the page.
1. Verify the counts match the status counts taken in Stage 1 (the Completed counts could be higher because actions will have been completed to run the migration).
