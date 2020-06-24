# Selects all plugin records saved in the `wp_options` table: option,
# rewrite rules, transients
SELECT * FROM `wp_options` WHERE option_name = 'gmosso_endpoint_options'
UNION
SELECT * FROM `wp_options` WHERE option_value like '%gmosso-%'
UNION
SELECT * FROM `wp_options` WHERE option_name like '%transient%gmosso_endpoint%'

# Selects just the plugin options record
SELECT * FROM `wp_options` WHERE option_name = 'gmosso_endpoint_options'

# Deletes plugin transients
DELETE FROM `wp_options` WHERE option_name like '_transient%_gmosso_endpoint_%'

