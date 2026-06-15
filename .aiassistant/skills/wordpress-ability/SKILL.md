---
name: wordpress-ability
description: Use this skill when adding or modifying WordPress abilities
---

# WordPress ability core functions

- wp_register_ability( string $name, array $args ) to register a new ability
- wp_register_ability_category( string $slug, array $args ) to register a new ability category

Always check if the function exists before calling it, to ensure compatibility with WordPress versions prior to 6.9:

~~~php
if ( ! function_exists( 'wp_register_ability' ) ) {
    return;
}
~~~

# Adding a new ability

To add a new ability, follow these steps:
- One ability = one class implementing `AbilitiesInterface`
- Place the class adjacent to the feature it belongs to
- Register the ability class in the feature's `ServiceProvider`
- Register the ability in the related subscriber, using the `wp_abilities_api_init` hook

An ability name must be unique and follow the format `wp-rocket/ability-name`, where `wp-rocket` is the vendor name and `ability-name` is a descriptive name for the ability. Use hyphens to separate words in the ability name.

The `check_permissions()`method should always verify against the current user capabilities, using `current_user_can( 'rocket_manage_options )` by default, to ensure that only authorized users can execute the ability, except explicitly required otherwise.

An ability must be associated to an ability category. If the category doesn't exist yet, register a new one in the related subscriber using the `wp_abilities_api_categories_init` hook.

Make the ability available to the REST API and the MCP by adding meta.show_in_rest=true and meta.mcp.public=true when registering the ability, unless explicitly required otherwise.

An example of an ability class can be found in the `/inc/Engine/Abilities/Options/GetOptions.php` file.

# Testing

## Unit tests

Test only the `execute()` method of the ability class, which contains the logic to perform the action associated with the ability. Mock any dependencies and assert that the method behaves as expected under different conditions.

An example of unit tests for an ability class can be found in the `/tests/Unit/inc/Engine/Abilities/Options/GetOptions/ExecuteTest.php` file.

## Integration tests

Check for WordPress version compatibility, abilities are available starting WordPress 6.9. Mark skip test if version under test is lower than 6.9.

Set up user to ensure ability permissions are correctly applied. Add scenario where user has the ability and another where they don't, and assert that the expected outcomes occur in both cases.

Test ability execution output by getting the ability WP_Ability object using `wp_get_ability( $ability_name )` and calling the `execute()` method. Assert that the output is correct based on the input parameters and the expected behavior of the ability.

An example of integration tests for an ability class can be found in the `/tests/Integration/inc/Engine/Abilities/Options/GetOptions/RegisterGetOptionsAbilityTest.php` file.
