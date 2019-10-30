<?php

namespace Tightenco\Elm;

class Elm
{
    /**
     * Bind the given array of variables to the elm program,
     * render the script include,
     * and return the html.
     */
    public static function make($app_name, $flags = [])
    {
        ob_start(); ?>

        <div id="<?= $app_name ?>"></div>

        <script>
            window.addEventListener('load', function () {
                <?php if (! empty($flags)) : ?>
                Elm.<?= $app_name ?>.Main.init( {
                    node: document.getElementById( '<?= $app_name ?>' ),
                    flags: <?= json_encode( $flags ) ?>
                });
                <?php else : ?>
                Elm.<?= $app_name ?>.Main.init({
                    node: document.getElementById('<?= $app_name ?>')
                });
                <?php endif; ?>
            });
        </script>

        <?php return ob_get_clean();
    }
}
