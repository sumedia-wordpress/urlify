# Urlify Wordpress Plugin

With this plugin i try to support a way to change the admins URL and the login path.
More things could follow.

It will change 3 things:

- Write to .htaccess some Rules
- Define a constant in the wp-config.php
- Hook into the url fetching methods to substitute the new urls

So far in version 0.1.0 it seems to work properly.

## Dependency

This plugin depends on:

wp-cli/wp-config-transformer 