<div id="actions">

    <script type="text/javascript">
        $(function() {
            $('#actions img').on('click', function() {
                $('div#upload-picture').toggle();
            });
        });
    </script>

    <img src="/recipes/static/images/<?php if($recipe->image) { print($recipe->image); } else { print('no-picture.png'); } ?>" class="<?php print($recipe->image_orientation); ?>" />
    <div id="upload-picture" style="display: none;">
        <form method="POST" action="" enctype="multipart/form-data">
            <h3>Ny fil</h3>
            <input type="file" name="file" style="width: 198px;" /><br />
            <h3>Orientering</h3>

            <input type="radio" <?php if($recipe->image_orientation == 'horizontal') { print('checked="checked" '); } ?>name="orientation" value="horizontal" id="orientation-horizontal" /><label for="orientation-horizontal">Horizontal</label><br />
            <input type="radio" <?php if($recipe->image_orientation == 'vertical') { print('checked="checked" '); } ?>name="orientation" value="vertical" id="orientation-vertical" /><label for="orientation-vertical">Vertikal</label><br />
            <br /><input type="submit" value="Gem" />
        </form>
    </div>

    <div id="navigation">
        <a href="/recipes/">Tilbage</a>
        <a href="/recipes/recipe/<?php print($recipe->id); ?>/edit">Redigér</a>
    </div>
</div>

<div id="content">
    <h1><?php print($recipe->name); ?></h1>

    <h2>Information</h2>

    <b>Antal personer:</b> <?php print($recipe->persons); ?><br />

    <?php if($recipe->timings()) { ?>
    <b>Tid:</b> (samlet: <b><?php print($recipe->total_time()); ?></b> minutter)<br />
        <?php foreach($recipe->timings() as $time) { ?>
            <?php print($time->description); ?>: <?php print($time->minutes); ?> minutter<br />
        <?php } ?>
    <br />
    <?php } ?>

    <?php if($recipe->ingredients()) { ?>
    <h2>Ingredienser</h2>
        <?php foreach($recipe->ingredients() as $ingr) { ?>
            <?php print($ingr->amount.' '.$ingr->name); ?><br />
        <?php } ?>
    <br />
    <?php } ?>

    <?php if($recipe->recipe) { ?>
    <h2>Opskrift</h2>
        <?php print($recipe->recipe); ?><br /><br />
    <?php } ?>

    <?php if($recipe->garniture) { ?>
    <h2>Tilbehør</h2>
        <?php print($recipe->garniture); ?><br /><br />
    <?php } ?>
</div>
