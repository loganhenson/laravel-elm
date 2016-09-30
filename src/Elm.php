<?php

namespace Tighten\Elm;

/**
 * Class Elm
 * @package Laracasts\Utilities\Elm
 */
class Elm
{
    /**
     * Bind the given array of variables to the elm program,
     * render the script include,
     * and return the html.
     */
    public function make($app_name, $flags = [])
    {
        ob_start(); ?>

        <div id="<?= $app_name ?>"></div>

        <script src="/js/<?= $app_name ?>.js"></script>

        <script>
            Elm.Main.embed(
                document.getElementById('<?= $app_name ?>'),
                <?= json_encode($flags) ?>
            );
        </script>

        <?php return ob_get_clean();
    }
}
