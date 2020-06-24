<?php
    declare(strict_types=1);

    /**
     * Users template
     *
     * Used for rendering of all users.
     *
     * (c) Giancarlo Mosso giancarlo.mosso@gmail.com
     *
     * @package Gmosso Endpoint
     * @since 1.0.0
     */

    use GmossoEndpoint\Configuration;

    $configuration = new Configuration();
    $prefix = $configuration['PLUGIN_PREFIX'];
    $module = 'users';

    [$prefixHtml, $moduleHtml] = str_replace('_', '-', [$prefix, $module]);

    // Add classes to body for styling
    add_filter('body_class', function ($classes) use ($prefixHtml, $moduleHtml) {

        $classes[] = "{$prefixHtml}-template-general";
        $classes[] = "{$prefixHtml}-template-{$moduleHtml}";

        return $classes;
    });

    ?>

<?php get_header(); ?>

<?php
    /**
     * Actions before page content.
     *
     * Code to execute before the content of the page.
     *
     * @since   1.0.0
     */
    do_action("{$prefix}_{$module}_before_content");
?>

<main id="site-content" role="main">
    <article
        id="<?php echo esc_attr("{$prefixHtml}-{$moduleHtml}"); ?>"
        class="<?php echo esc_attr("{$prefixHtml}-page"); ?>">

        <header class="<?php echo esc_attr("{$prefixHtml}-entry-header"); ?>">
            <h1>
                <?php
                /**
                 * h1 title definition.
                 *
                 * Defines the h1 title for the page.
                 *
                 * @since   1.0.0
                 *
                 * @param string page title.
                 */
                echo esc_html(apply_filters(
                    "{$prefix}_{$module}_title",
                    'Title'
                ));
                ?>
            </h1>
            <p><?php
                esc_html_e('Click table item to read details', 'gmosso-endpoint');
            ?></p>
        </header>

        <div class="<?php echo esc_attr("{$prefixHtml}-entry-content"); ?>">
            <div class="table-container">
                <?php
                /**
                 * Table content.
                 *
                 * Defines the html markup for the table of the items.
                 *
                 * @since   1.0.0
                 *
                 * @param string table markup.
                 */
                echo wp_kses_post(apply_filters(
                    "{$prefix}_{$module}_content",
                    'Table from api call'
                ));
                ?>
            </div>
        </div>

        <noscript>
            <?php
                esc_html_e(
                    'Please enable Javascript or use a browser that supports it to have full functionality',
                    'gmosso-endpoint'
                );
                ?>
        </noscript>
    </article>
</main>

<?php
    /**
     * Actions after page content.
     *
     * Code to execute after the content of the page.
     *
     * @since   1.0.0
     */
    do_action("{$prefix}_{$module}_after_content");
?>

<?php
get_footer();
